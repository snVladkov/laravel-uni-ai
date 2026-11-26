<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\GradeBand;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\User;
use App\Services\AcademicTranscriptService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AcademicDemoSeeder extends Seeder
{
    /**
     * Seed a realistic academic demo dataset.
     */
    public function run(): void
    {
        $admin = User::factory()->administrator()->create([
            'name' => 'Администратор',
            'email' => 'admin@uev.test',
            'password' => Hash::make('password'),
        ]);

        $hodIt = User::factory()->headOfDepartment('Информатика')->create([
            'name' => 'Доц. д-р Иван Петров',
            'email' => 'hod.it@uev.test',
            'password' => Hash::make('password'),
        ]);

        $hodFinance = User::factory()->headOfDepartment('Финанси')->create([
            'name' => 'Проф. д-р Мария Иванова',
            'email' => 'hod.finance@uev.test',
            'password' => Hash::make('password'),
        ]);

        $teacherIt = User::factory()->teacher('Информатика')->create([
            'name' => 'Гл. ас. д-р Петър Георгиев',
            'email' => 'teacher1@uev.test',
            'teacher_code' => 'T-1001',
            'password' => Hash::make('password'),
        ]);

        $teacherIt2 = User::factory()->teacher('Информатика')->create([
            'name' => 'Ас. д-р Елена Димитрова',
            'email' => 'teacher2@uev.test',
            'teacher_code' => 'T-1002',
            'password' => Hash::make('password'),
        ]);

        $teacherFinance = User::factory()->teacher('Финанси')->create([
            'name' => 'Доц. д-р Николай Стоянов',
            'email' => 'teacher3@uev.test',
            'teacher_code' => 'T-2001',
            'password' => Hash::make('password'),
        ]);

        $students = collect(range(1, 8))->map(fn (int $index): User => User::factory()->student()->create([
            'name' => 'Студент '.$index,
            'email' => "student{$index}@uev.test",
            'student_code' => sprintf('S-%04d', $index),
            'password' => Hash::make('password'),
        ]));

        $programming = $this->course($teacherIt, [
            'title' => 'Основи на програмирането',
            'code' => 'INF101',
            'description' => 'Въведение в алгоритмите и структурното програмиране.',
        ], CourseStatus::Approved, $hodIt);

        $databases = $this->course($teacherIt, [
            'title' => 'Бази от данни',
            'code' => 'INF201',
            'description' => 'Релационни модели, SQL и нормализация.',
        ], CourseStatus::Draft);

        $web = $this->course($teacherIt2, [
            'title' => 'Уеб технологии',
            'code' => 'INF202',
            'description' => 'HTML, CSS, JavaScript и съвременни рамки.',
        ], CourseStatus::PendingApproval);

        $operatingSystems = $this->course($teacherIt2, [
            'title' => 'Операционни системи',
            'code' => 'INF203',
            'description' => 'Процеси, памет и файлови системи.',
        ], CourseStatus::Rejected, $hodIt, 'Липсва актуален учебен план.');

        $networks = $this->course($teacherIt, [
            'title' => 'Компютърни мрежи',
            'code' => 'INF301',
            'description' => 'Мрежови протоколи и сигурност.',
        ], CourseStatus::Completed, $hodIt);

        $financialAnalysis = $this->course($teacherFinance, [
            'title' => 'Финансов анализ',
            'code' => 'FIN101',
            'description' => 'Анализ на финансовите отчети.',
        ], CourseStatus::Approved, $hodFinance);

        $corporateFinance = $this->course($teacherFinance, [
            'title' => 'Корпоративни финанси',
            'code' => 'FIN201',
            'description' => 'Капиталова структура и дивидентна политика.',
        ], CourseStatus::PendingApproval);

        $investments = $this->course($teacherFinance, [
            'title' => 'Инвестиции',
            'code' => 'FIN301',
            'description' => 'Оценка на инвестиционни проекти и портфейли.',
        ], CourseStatus::Completed, $hodFinance);

        $this->material($programming, $teacherIt, 'Учебна програма', 'Учебната програма за семестъра.', 'syllabus.txt');
        $this->material($programming, $teacherIt, 'Лекции (видео)', null, null, 'https://example.com/inf101-lectures');
        $this->material($networks, $teacherIt, 'Лабораторни упражнения', null, 'labs.txt');
        $this->material($financialAnalysis, $teacherFinance, 'Примерни отчети', null, 'reports.txt');
        $this->material($investments, $teacherFinance, 'Курсов проект', null, null, 'https://example.com/fin301-project');

        foreach ([$programming, $networks, $financialAnalysis, $investments] as $course) {
            $course->students()->syncWithoutDetaching(
                $students->take(4)->mapWithKeys(fn (User $student): array => [
                    $student->id => ['enrolled_by' => $admin->id, 'enrolled_at' => now()],
                ])->all(),
            );
        }

        $this->grade($programming, $students[0], 6.0, $teacherIt);
        $this->grade($programming, $students[1], 5.5, $teacherIt);
        $this->grade($programming, $students[2], 4.5, $teacherIt);
        $this->grade($networks, $students[0], 5.0, $teacherIt);
        $this->grade($financialAnalysis, $students[0], 5.5, $teacherFinance);
        $this->grade($investments, $students[0], 4.5, $teacherFinance);

        app(AcademicTranscriptService::class)->generate($students[0], $admin);
    }

    /**
     * Create a course in the requested workflow state.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function course(User $teacher, array $attributes, CourseStatus $status, ?User $reviewer = null, ?string $rejectionReason = null): Course
    {
        return Course::factory()->forTeacher($teacher)->create([
            ...$attributes,
            'status' => $status,
            'approved_by' => in_array($status, [CourseStatus::Approved, CourseStatus::Completed], true) ? $reviewer?->id : null,
            'approved_at' => in_array($status, [CourseStatus::Approved, CourseStatus::Completed], true) ? now() : null,
            'rejection_reason' => $rejectionReason,
        ]);
    }

    /**
     * Attach a demo material with either a stored file or an external link.
     */
    private function material(Course $course, User $uploader, string $title, ?string $description, ?string $fileName, ?string $url = null): void
    {
        $path = null;

        if ($fileName !== null) {
            $path = "course-materials/{$course->id}/{$fileName}";
            Storage::disk('local')->put($path, "Demo material for {$course->title}");
        }

        CourseMaterial::factory()->create([
            'course_id' => $course->id,
            'uploaded_by' => $uploader->id,
            'title' => $title,
            'description' => $description,
            'file_path' => $path,
            'external_url' => $url,
        ]);
    }

    /**
     * Record a final grade for a student.
     */
    private function grade(Course $course, User $student, float $score, User $teacher): void
    {
        $course->enrollments()->where('student_id', $student->id)->update([
            'grade' => $score,
            'grade_letter' => GradeBand::fromScore($score)->letter(),
            'graded_by' => $teacher->id,
            'graded_at' => now(),
        ]);
    }
}
