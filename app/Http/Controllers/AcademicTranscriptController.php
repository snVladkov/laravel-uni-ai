<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTranscriptRequest;
use App\Models\AcademicTranscript;
use App\Models\User;
use App\Services\AcademicTranscriptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicTranscriptController extends Controller
{
    public function __construct(private readonly AcademicTranscriptService $transcripts) {}

    /**
     * List the transcripts of the given student.
     */
    public function index(Request $request, User $student): View
    {
        $this->authorize('create', [AcademicTranscript::class, $student]);

        $transcripts = $student->transcripts()->latest('generated_at')->get();

        $students = User::query()
            ->role('student')
            ->orderBy('name')
            ->get();

        return view('admin.transcripts.index', compact('student', 'transcripts', 'students'));
    }

    /**
     * Generate a new transcript for the student.
     */
    public function store(StoreTranscriptRequest $request, User $student): RedirectResponse
    {
        $this->authorize('create', [AcademicTranscript::class, $student]);

        $transcript = $this->transcripts->generate($student, $request->user());

        return redirect()
            ->route('admin.transcripts.show', $transcript)
            ->with('success', __('transcripts.flash.generated', ['number' => $transcript->transcript_number]));
    }

    /**
     * Display the printable transcript.
     */
    public function show(Request $request, AcademicTranscript $transcript): View
    {
        $this->authorize('view', $transcript);

        return view('admin.transcripts.show', compact('transcript'));
    }
}
