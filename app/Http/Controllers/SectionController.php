<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | TEACHER — list all sections
    |--------------------------------------------------------------------------
    */
    public function index(): View
    {
        $sections = Section::with(['students'])
            ->where('teacher_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('teacher.sections.index', compact('sections'));
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — store new section
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:100',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'school_year' => 'required|string|max:20',
            'semester'    => 'required|string|max:30',
        ], [
            'subject.required'     => 'Subject is required.',
            'name.required'        => 'Section name is required.',
            'school_year.required' => 'School year is required.',
            'semester.required'    => 'Semester is required.',
        ]);

        $section = Section::create([
            'teacher_id'  => auth()->id(),
            'subject'     => $validated['subject'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'school_year' => $validated['school_year'] ?? null,
            'semester'    => $validated['semester'] ?? null,
            'status'      => 'active',
        ]);

        return redirect()
            ->route('teacher.sections.show', $section->id)
            ->with('success', 'Section "' . $section->name . '" created! Code: ' . $section->code);
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — show section detail
    |--------------------------------------------------------------------------
    */
    public function show(Section $section): View
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $section->load('students');

        // Students under this teacher NOT yet in this section
        $available = auth()->user()->myStudents()
            ->whereNotIn('users.id', $section->students->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('teacher.sections.show', compact('section', 'available'));
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — update section
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Section $section): RedirectResponse
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'subject'     => 'nullable|string|max:100',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'school_year' => 'nullable|string|max:20',
            'semester'    => 'nullable|string|max:30',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $section->update($validated);

        return back()->with('success', 'Section updated successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — delete section
    |--------------------------------------------------------------------------
    */
    public function destroy(Section $section): RedirectResponse
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $section->students()->detach();
        $section->delete();

        return redirect()->route('teacher.sections.index')
            ->with('success', 'Section deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — add student to section manually
    |--------------------------------------------------------------------------
    */
    public function addStudent(Request $request, Section $section): RedirectResponse
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $request->validate(['student_id' => 'required|exists:users,id']);

        $section->students()->syncWithoutDetaching([
            $request->student_id => ['joined_at' => now()],
        ]);

        return back()->with('success', 'Student added to section!');
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — remove student from section
    |--------------------------------------------------------------------------
    */
    public function removeStudent(Section $section, User $student): RedirectResponse
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $section->students()->detach($student->id);

        return back()->with('success', 'Student removed from section.');
    }

    /*
    |--------------------------------------------------------------------------
    | TEACHER — regenerate section code
    |--------------------------------------------------------------------------
    */
    public function regenerateCode(Section $section): RedirectResponse
    {
        if ($section->teacher_id !== auth()->id()) abort(403);

        $code = $section->regenerateCode();

        return back()->with('success', 'New section code: ' . $code);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT — show join form
    |--------------------------------------------------------------------------
    */
    public function joinForm(): View
    {
        $student = auth()->user();
        $mySections = $student->joinedSections()->with('teacher')->latest()->get();

        return view('student.sections.join', compact('mySections'));
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT — process join
    |--------------------------------------------------------------------------
    */
    public function join(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $code    = strtoupper(trim($request->code));
        $student = auth()->user();
        $section = Section::where('code', $code)->where('status', 'active')->first();

        if (!$section) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Invalid or inactive section code. Please check with your teacher.']);
        }

        if ($section->hasStudent($student->id)) {
            return back()->with('success', 'You are already enrolled in "' . $section->name . '".');
        }

        // Validate: student must be registered under this section's teacher
        $teacher = $section->teacher;
        if (!$teacher || !$student->myTeachers()->where('users.id', $teacher->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'You are not registered under the teacher who owns this section. Please register with the teacher first using their teacher code.']);
        }

        $section->students()->attach($student->id, ['joined_at' => now()]);

        return back()->with('success', 'You have joined "' . $section->name . '" successfully!');
    }
}
