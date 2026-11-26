# Implementation Plan: University Academic Management System

## 1. Overview & User Problem
- **Problem Solved:** We want to create an app which servers the basic purposes of university students and teachers. The app must allow adding new students and users. The users can be a part of the following Roles - student, teacher, head of department and administrator.
- **Target Users / Roles:** Students must have access to different courses and read materials in these courses. They must also receive a grade for every course. Teachers must be able to add new courses and materials in them. Teachers must be able to grade all courses created by them. Head of department must approve a course before students can be assigned to it. Head of department must be able to add/edit/delete new teachers. Administators must be able to add new students and users, and assing roles to the new users. Administartors must also be able to add students to different courses.
- **Core User Flows:**
  1. A teacher creates a new course, adds materials and awaits for the head of department to approve it.
  2. The head of department can either approve or reject the course. If rejected, the course is returned for editing to the teacher and he can send it for approval again. If approved, the course will be open for students to enroll in it.
  3. After a course is approved, administrators can add students to it. When a student is added to a course, they must see the course and all uploaded materials.
  4. After the course is finished, the teacher must grade all the students in the course. After receiving a grade, the students must also see it when opening the course.
  5. At any time, an administator must able to create an academic transcript which contains all grades the student has received.

---

## 2. Data Model, Migrations & Seeders
- **Models & Key Columns:**
  - `User` *(Laravel `Authenticatable` with `Spatie\Permission\Traits\HasRoles`)*: `id: bigint unsigned pk`, `name: string`, `email: string unique`, `password: string`, `student_code: string nullable unique`, `teacher_code: string nullable unique`, `department: string nullable`, `email_verified_at: timestamp nullable`, `timestamps`
  - `Course`: `id: bigint unsigned pk`, `title: string`, `code: string unique`, `description: text nullable`, `status: string default 'draft'` *(values: `draft`, `pending_approval`, `approved`, `rejected`, `completed`)*, `rejection_reason: text nullable`, `teacher_id: bigint unsigned fk -> users.id cascade on delete`, `approved_by: bigint unsigned nullable fk -> users.id null on delete`, `approved_at: timestamp nullable`, `timestamps`
  - `CourseMaterial`: `id: bigint unsigned pk`, `course_id: bigint unsigned fk -> courses.id cascade on delete`, `uploaded_by: bigint unsigned fk -> users.id cascade on delete`, `title: string`, `description: text nullable`, `file_path: string nullable`, `external_url: string nullable`, `order: integer default 0`, `timestamps`
  - `CourseEnrollment` *(pivot/join model for student enrollments and grades)*: `id: bigint unsigned pk`, `course_id: bigint unsigned fk -> courses.id cascade on delete`, `student_id: bigint unsigned fk -> users.id cascade on delete`, `enrolled_by: bigint unsigned nullable fk -> users.id null on delete`, `grade: decimal(5,2) nullable`, `grade_letter: string nullable`, `graded_by: bigint unsigned nullable fk -> users.id null on delete`, `graded_at: timestamp nullable`, `enrolled_at: timestamp useCurrent`, `unique(course_id, student_id)`, `timestamps`
  - `AcademicTranscript`: `id: bigint unsigned pk`, `transcript_number: string unique`, `student_id: bigint unsigned fk -> users.id cascade on delete`, `generated_by: bigint unsigned fk -> users.id cascade on delete`, `gpa: decimal(4,2) nullable`, `snapshot_data: json`, `generated_at: timestamp useCurrent`, `timestamps`
  - *(Spatie `permission` tables)*: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
- **Relationships:**
  - `User` `hasMany` `Course` *(as `createdCourses` via `teacher_id`)*
  - `User` `hasMany` `CourseMaterial` *(as `uploadedMaterials` via `uploaded_by`)*
  - `User` `belongsToMany` `Course` *(as `enrolledCourses` via `course_enrollments` with pivot columns `grade`, `grade_letter`, `graded_at`, `enrolled_at`)*
  - `User` `hasMany` `CourseEnrollment` *(as `enrollments` via `student_id`)*
  - `User` `hasMany` `AcademicTranscript` *(as `transcripts` via `student_id`)*
  - `Course` `belongsTo` `User` *(as `teacher` via `teacher_id`)*
  - `Course` `belongsTo` `User` *(as `approver` via `approved_by`)*
  - `Course` `hasMany` `CourseMaterial` *(ordered by `order`, `id`)*
  - `Course` `belongsToMany` `User` *(as `students` via `course_enrollments` with pivot columns `grade`, `grade_letter`, `graded_by`, `graded_at`, `enrolled_at`)*
  - `Course` `hasMany` `CourseEnrollment` *(as `enrollments`)*
  - `CourseMaterial` `belongsTo` `Course`
  - `CourseMaterial` `belongsTo` `User` *(as `uploader` via `uploaded_by`)*
  - `AcademicTranscript` `belongsTo` `User` *(as `student` via `student_id`)*
  - `AcademicTranscript` `belongsTo` `User` *(as `generator` via `generated_by`)*
- **Factories & Seeder Data:**
  - **Roles & Permissions Seeder (`RoleAndPermissionSeeder`)**: Creates roles `student`, `teacher`, `head_of_department`, and `administrator`, and syncs granular permissions (`courses.create`, `courses.update.own`, `courses.submit`, `courses.review`, `courses.enroll_students`, `courses.grade.own`, `materials.manage.own`, `materials.view`, `teachers.manage`, `users.manage`, `transcripts.create`, `transcripts.view`).
  - **Demo Users Seeder (`AcademicDemoSeeder`)**: Creates 1 Administrator, 1 Head of Department, 2–3 Teachers (with `teacher_code`), and 5–8 Students (with `student_code`).
  - **Course & Enrollment Seeder**: Seeds sample courses across states (`pending_approval`, `rejected`, `approved`, `completed`) with uploaded materials, student enrollments, sample grades on completed/graded courses, and one sample generated `AcademicTranscript`.

---

## 3. Routes, Controllers / Components & Validation
- **Web / API Routes:**
  - `GET /dashboard` -> `DashboardController@index` *(role-aware dashboard summary)*
  - **Course Management (Teachers, HoD, Administrators, Enrolled Students):**
    - `GET /courses` -> `CourseController@index`
    - `GET /courses/create` -> `CourseController@create` *(teacher)*
    - `POST /courses` -> `CourseController@store` *(teacher)*
    - `GET /courses/{course}` -> `CourseController@show` *(course owner, HoD, admin, or enrolled student)*
    - `GET /courses/{course}/edit` -> `CourseController@edit` *(course owner when `draft` or `rejected`)*
    - `PUT|PATCH /courses/{course}` -> `CourseController@update` *(course owner when `draft` or `rejected`)*
    - `POST /courses/{course}/submit-approval` -> `CourseController@submitForApproval` *(teacher owner when `draft` or `rejected`)*
  - **Course Approval Workflow (Head of Department):**
    - `GET /hod/courses/pending` -> `CourseApprovalController@index`
    - `POST /hod/courses/{course}/approve` -> `CourseApprovalController@approve`
    - `POST /hod/courses/{course}/reject` -> `CourseApprovalController@reject`
    - `POST /hod/courses/{course}/complete` -> `CourseApprovalController@markCompleted`
  - **Course Materials (Teachers & Enrolled Students):**
    - `POST /courses/{course}/materials` -> `CourseMaterialController@store` *(teacher owner)*
    - `DELETE /courses/{course}/materials/{material}` -> `CourseMaterialController@destroy` *(teacher owner)*
    - `GET /courses/{course}/materials/{material}/download` -> `CourseMaterialController@download` *(authorized viewers: teacher owner, HoD, admin, or enrolled student)*
  - **Student Enrollment & Grading (Administrators & Teachers):**
    - `POST /courses/{course}/students` -> `CourseEnrollmentController@store` *(administrator; only if `course->isApproved()`)*
    - `DELETE /courses/{course}/students/{student}` -> `CourseEnrollmentController@destroy` *(administrator)*
    - `PATCH /courses/{course}/students/{student}/grade` -> `CourseEnrollmentController@updateGrade` *(teacher owner of the course)*
  - **Teacher Management (Head of Department):**
    - `GET /hod/teachers` -> `TeacherManagementController@index`
    - `GET /hod/teachers/create` -> `TeacherManagementController@create`
    - `POST /hod/teachers` -> `TeacherManagementController@store`
    - `GET /hod/teachers/{teacher}/edit` -> `TeacherManagementController@edit`
    - `PUT|PATCH /hod/teachers/{teacher}` -> `TeacherManagementController@update`
    - `DELETE /hod/teachers/{teacher}` -> `TeacherManagementController@destroy`
  - **User & Student Management + Academic Transcripts (Administrator):**
    - `GET /admin/users` -> `UserManagementController@index`
    - `GET /admin/users/create` -> `UserManagementController@create`
    - `POST /admin/users` -> `UserManagementController@store`
    - `PATCH /admin/users/{user}/roles` -> `UserManagementController@syncRoles`
    - `GET /admin/students/{student}/transcripts` -> `AcademicTranscriptController@index`
    - `POST /admin/students/{student}/transcripts` -> `AcademicTranscriptController@store`
    - `GET /admin/transcripts/{transcript}` -> `AcademicTranscriptController@show`
- **Form Requests / Validation Rules:**
  - `StoreCourseRequest` / `UpdateCourseRequest`: `title` (`required|string|max:255`), `code` (`required|string|max:50|unique:courses,code,{id}`), `description` (`nullable|string|max:5000`). Prevent editing if course is already `approved` or `pending_approval` (unless returned to `rejected`).
  - `RejectCourseRequest`: `rejection_reason` (`required|string|min:5|max:2000`).
  - `StoreCourseMaterialRequest`: `title` (`required|string|max:255`), `description` (`nullable|string|max:2000`), `file` (`nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,txt,mp4|max:51200`), `external_url` (`nullable|url|max:2048`), with custom validation requiring at least one of `file` or `external_url`.
  - `EnrollStudentRequest`: `student_id` (`required|integer|exists:users,id`), plus policy/domain check verifying the target user has the `student` role, is not already enrolled, and the course is `approved`.
  - `GradeStudentRequest`: `grade` (`required|numeric|min:0|max:100`), `grade_letter` (`nullable|string|max:5`).
  - `StoreTeacherRequest` / `UpdateTeacherRequest` *(HoD)*: `name` (`required|string|max:255`), `email` (`required|email|unique:users,email,{id}`), `password` (`required_without:id|nullable|string|min:8|confirmed`), `teacher_code` (`nullable|string|max:50|unique:users,teacher_code,{id}`), `department` (`nullable|string|max:120`).
  - `StoreUserRequest` / `SyncUserRolesRequest` *(Administrator)*: `name` (`required|string|max:255`), `email` (`required|email|unique:users,email`), `password` (`required|string|min:8|confirmed`), `student_code` (`nullable|string|max:50|unique:users,student_code`), `teacher_code` (`nullable|string|max:50|unique:users,teacher_code`), `roles` (`required|array|min:1`), `roles.*` (`string|exists:roles,name`).
  - `StoreTranscriptRequest`: validates `student` is a valid user with the `student` role.
- **Authorization / Security Boundaries:**
  - **Combined `spatie/laravel-permissions` + Laravel Native `Gates` & `Policies`:**
    - `spatie/laravel-permissions` assigns roles (`student`, `teacher`, `head_of_department`, `administrator`) and permissions to users, and route middleware (`role:...` / `permission:...`) blocks broad unauthorized areas early.
    - **Native Laravel Gates (`Gate::define(...)` in `AppServiceProvider` / `AuthServiceProvider`)** enforce cross-cutting domain capabilities and role checks:
      - `manage-teachers` -> user is `head_of_department` and has `teachers.manage`
      - `manage-users-and-roles` -> user is `administrator` and has `users.manage`
      - `enroll-students-in-course` -> user is `administrator` and has `courses.enroll_students`
      - `generate-academic-transcript` -> user is `administrator` and has `transcripts.create`
    - **Native Laravel Policies (`CoursePolicy`, `CourseMaterialPolicy`, `AcademicTranscriptPolicy`, `UserManagementPolicy`)** enforce object-level ownership and workflow state rules:
      - `CoursePolicy::create`: `teacher` with `courses.create`
      - `CoursePolicy::update` / `submitForApproval`: `teacher` owning the course (`$course->teacher_id === $user->id`) AND `$course->status` is `draft` or `rejected`
      - `CoursePolicy::approve` / `reject`: `head_of_department` with `courses.review` AND `$course->status === 'pending_approval'`
      - `CoursePolicy::grade`: `teacher` owning the course (`$course->teacher_id === $user->id`) AND `$course->status` is `approved` or `completed`
      - `CoursePolicy::view`: `head_of_department`, `administrator`, owning `teacher`, or a `student` enrolled in `$course` *(unapproved courses are never visible to students)*
      - `CourseMaterialPolicy::create` / `delete`: `teacher` owning the course (`$course->teacher_id === $user->id`)
      - `CourseMaterialPolicy::view`: owning `teacher`, `head_of_department`, `administrator`, or an enrolled `student` when the course is `approved` (or `completed`)
      - `AcademicTranscriptPolicy::create` / `view`: `administrator` (or owning `student` for `view` on their own transcript if desired, while creation is strictly `administrator`).

---

## 4. UI & Frontend Behavior
- **Pages / Views / Components:**
  - `layouts/app.blade.php` & `components/nav-menu.blade.php`: Role-aware navigation showing links based on Spatie roles/permissions and Gates (`@can` / `@role`).
  - `dashboard/index.blade.php`: Quick cards/metrics tailored to the signed-in role (e.g., pending approvals count for HoD, my courses & grading queue for Teacher, enrolled courses & grades for Student, user/enrollment/transcript shortcuts for Administrator).
  - `courses/index.blade.php`: Filterable course list (teachers see their created courses & statuses; HoDs see courses for review/oversight; admins see approved/all courses; students see only courses they are enrolled in).
  - `courses/create.blade.php` & `courses/edit.blade.php`: Course creation/editing form plus "Save Draft" and "Submit for Approval" actions; displays `rejection_reason` banner when `status === 'rejected'`.
  - `courses/show.blade.php`: Course overview, status badge, materials list/upload form (for owner teacher), enrolled students + grade input form (for owner teacher), student enrollment selector (for administrator when approved), and the student's own received grade & feedback/timestamp (when viewed by an enrolled student).
  - `hod/courses/pending.blade.php`: Queue of `pending_approval` courses with one-click Approve and modal/inline Reject with required `rejection_reason`.
  - `hod/teachers/index.blade.php`, `create.blade.php`, `edit.blade.php`: CRUD interface for teachers managed by the Head of Department.
  - `admin/users/index.blade.php` & `create.blade.php`: User/student creation form with multi-role assignment checkboxes (`student`, `teacher`, `head_of_department`, `administrator`) and role filter/search.
  - `admin/transcripts/index.blade.php` & `show.blade.php`: Student selector/list, "Generate Academic Transcript" button, and printable/formatted transcript view listing all courses, grades, GPA, generation timestamp, and transcript number.
- **UX Details:**
  - Clear status badges (`Draft`, `Pending Approval`, `Rejected`, `Approved`, `Completed`) and explicit rejection feedback so teachers know what to fix before resubmitting.
  - Guardrails in UI and server: enrollment controls only appear/succeed on `approved` courses; students only see courses they are assigned to along with materials and their personal grade.
  - Session flash banners (`success`, `error`, `warning`), validation error highlights on every form field, empty states with helpful CTAs, and confirmation dialogs before deleting teachers or course materials.

---

## 5. Automated Tests (Pest)
- **Happy Path Tests:**
  - Teacher creates a course, uploads course materials, and submits it for approval (`pending_approval`).
  - Head of Department approves a pending course (`approved`), allowing an Administrator to enroll students; enrolled students can view the course and download/read its materials.
  - Head of Department rejects a pending course with a reason (`rejected`); Teacher edits the course and resubmits it for approval.
  - Teacher assigns grades to enrolled students in their course, and each student sees their grade when opening the course.
  - Administrator creates a new student/user, assigns roles via Spatie, enrolls the student in approved courses, and generates an `AcademicTranscript` containing all grades received by that student.
  - Head of Department creates, updates, and deletes a teacher account.
- **Validation & Edge Case Tests:**
  - Course creation/update fails when `title` or `code` is missing or `code` is duplicated; material upload fails when neither a valid file nor `external_url` is provided.
  - Rejecting a course fails if `rejection_reason` is empty.
  - Grade submission rejects out-of-range scores (e.g., `< 0` or `> 100`) or non-numeric values.
  - Enrolling a duplicate student in the same course or enrolling a non-student user is rejected.
  - Generating an academic transcript for a student with zero grades or multiple courses handles GPA/empty states cleanly without crashing.
- **Domain-Specific Tests:**
  - **Authorization & Policy/Gate Enforcement:** Students cannot create/approve courses, view unapproved courses, view courses they are not enrolled in, or grade themselves/others; Teachers cannot approve their own courses, grade courses created by another teacher, or manage users/transcripts; Head of Department cannot enroll students in courses or generate transcripts; Administrators cannot approve courses or grade courses unless explicitly authorized by policy/gate rules.
  - **Workflow State Integrity:** Administrators cannot enroll students into a `draft`, `pending_approval`, or `rejected` course; Teachers cannot edit an `approved` course unless returned to `rejected`/`draft` per workflow rules.

---

## 6. Execution Phases & Checklist
- [ ] **Phase 1 — Database & Models:** Migrations, models, relationships, factories, seeder, and initial migration/seed check.
- [ ] **Phase 2 — Domain Logic, Validation & Security:** Form Requests, policies/middleware/jobs/events/tools as needed.
- [ ] **Phase 3 — Routes & UI / Endpoints:** Controllers/Livewire components/views/API resources and navigation.
- [ ] **Phase 4 — Pest Test Suite & Verification:** Write and run `php artisan test`, inspect `git status` / diffs, and verify in browser or client.