<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StudentProjectController extends Controller
{
    /**
     * CHECK ACCESS
     */
    private function canAccessProject($student, $project): bool
    {
        /*
        |--------------------------------------------------------------------------
        | DIRECT ASSIGNMENT
        |--------------------------------------------------------------------------
        */
        $assigned = $student->assignedProjects()
            ->where('projects.id', $project->id)
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | GROUP ACCESS — allow teacher-added OR self-joined students
        |--------------------------------------------------------------------------
        */
        $groupAccess = false;

        if ($project->group_id) {

            $groupAccess = DB::table('group_student')
                ->where('group_id', $project->group_id)
                ->where('student_id', $student->id)
                ->exists();
        }

        return $assigned || $groupAccess;
    }

    /**
     * ALL PROJECTS
     */
    public function index(): View
    {
        $student = auth()->user();

        if (!$student->isStudent()) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | GET GROUP IDS
        |--------------------------------------------------------------------------
        */
        // Only groups student has actively joined via code
        $groupIds = DB::table('group_student')
            ->where('student_id', $student->id)
            ->pluck('group_id');

        /*
        |--------------------------------------------------------------------------
        | GET PROJECTS
        |--------------------------------------------------------------------------
        */
        $assignedProjects = Project::with([
                'teacher',
                'group',
                'tasks',
            ])
            ->where(function ($query) use ($student, $groupIds) {

                /*
                |--------------------------------------------------------------------------
                | GROUP PROJECTS
                |--------------------------------------------------------------------------
                */
                if ($groupIds->count()) {

                    $query->orWhereIn(
                        'group_id',
                        $groupIds
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | DIRECT ASSIGNMENTS
                |--------------------------------------------------------------------------
                */
                $query->orWhereHas(
                    'assignments',
                    function ($q) use ($student) {

                        $q->where(
                            'project_student.student_id',
                            $student->id
                        );
                    }
                );
            })
            ->latest()
            ->get();

        return view(
            'student.projects.index',
            compact('assignedProjects')
        );
    }

    /**
     * SHOW PROJECT
     */
    public function show(Project $project): View
    {
        $student = auth()->user();

        abort_unless(
            $this->canAccessProject($student, $project),
            403
        );

        // Eager-load tasks so the view can iterate them
        $project->loadMissing(['teacher', 'tasks', 'group']);

        /*
        |--------------------------------------------------------------------------
        | LOAD SUBMISSIONS
        | — For GROUP projects: fetch submissions from ANY joined member of the group
        |   so that if one member submits a task, all others see it as "Completed"
        | — For INDIVIDUAL projects: only the student's own submissions
        |--------------------------------------------------------------------------
        */
        if ($project->group_id) {
            // Get all joined member IDs in this group
            $groupMemberIds = DB::table('group_student')
                ->where('group_id', $project->group_id)
                ->pluck('student_id');

            // Fetch the earliest (first) submission per task from any group member
            $submissions = ProjectSubmission::with('student')
                ->where('project_id', $project->id)
                ->whereIn('student_id', $groupMemberIds)
                ->get()
                ->groupBy('task_id')
                ->map(fn($group) => $group->sortBy('submitted_at')->first());
        } else {
            $submissions = ProjectSubmission::where('student_id', $student->id)
                ->where('project_id', $project->id)
                ->get()
                ->keyBy('task_id');
        }

        return view(
            'student.projects.show',
            compact(
                'project',
                'submissions'
            )
        );
    }

    /**
     * SUBMIT PAGE
     */
    public function submitForm(
        Project $project,
        Request $request
    ): View {

        $student = auth()->user();

        if (!$student->isStudent()) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | ACCESS CHECK
        |--------------------------------------------------------------------------
        */
        abort_unless(
            $this->canAccessProject($student, $project),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | TASK
        |--------------------------------------------------------------------------
        */
        $task = null;

        if ($request->task) {

            $task = $project->tasks()
                ->where(
                    'id',
                    $request->task
                )
                ->firstOrFail();
        }

        /*
        |--------------------------------------------------------------------------
        | SUBMISSION — for group projects with no tasks, check any group member's
        |   submission so the form shows "already submitted" correctly.
        |--------------------------------------------------------------------------
        */
        if ($project->group_id && !$task) {

            $groupMemberIds = DB::table('group_student')
                ->where('group_id', $project->group_id)
                ->pluck('student_id');

            $submission = ProjectSubmission::with('student')
                ->whereIn('student_id', $groupMemberIds)
                ->where('project_id', $project->id)
                ->whereNull('task_id')
                ->latest()
                ->first();

        } else {

            $submission = ProjectSubmission::where([
                    'project_id' => $project->id,
                    'student_id' => $student->id,
                    'task_id'    => $task?->id,
                ])
                ->latest()
                ->first();
        }

        return view(
            'student.projects.submit',
            compact(
                'project',
                'task',
                'submission'
            )
        );
    }

    /**
     * FINALIZE SUBMISSION
     */
    public function finalize(
        Request $request,
        Project $project
    ): RedirectResponse {

        $student = auth()->user();

        if (!$student->isStudent()) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | ACCESS CHECK
        |--------------------------------------------------------------------------
        */
        abort_unless(
            $this->canAccessProject($student, $project),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([

            'task_id' => 'nullable|exists:tasks,id',

            'content' => 'nullable|string|max:10000',

            'file' => 'nullable|file|max:10240',
        ]);

        $taskId =
            $validated['task_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | FIND SUBMISSION — null-safe task_id so each task tracked separately
        |--------------------------------------------------------------------------
        */
        $submissionQuery = ProjectSubmission::where('project_id', $project->id)
            ->where('student_id', $student->id);

        if ($taskId) {
            $submissionQuery->where('task_id', $taskId);
        } else {
            $submissionQuery->whereNull('task_id');
        }

        $submission = $submissionQuery->first();

        /*
        |--------------------------------------------------------------------------
        | FILE
        |--------------------------------------------------------------------------
        */
        $filePath = $submission->file_path ?? null;

        if ($request->hasFile('file')) {

            $filePath = $request->file('file')->store(
                'submissions/' . $project->id,
                'public'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */
        $content =
            $validated['content'] ??
            ($submission->content ?? null);

        /*
        |--------------------------------------------------------------------------
        | EMPTY CHECK
        |--------------------------------------------------------------------------
        */
        if (
            empty(trim($content ?? '')) &&
            empty($filePath)
        ) {
            return back()->with(
                'error',
                'Please upload a file or write content.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE OR CREATE
        |--------------------------------------------------------------------------
        */
        if ($submission) {

            $submission->update([
                'content'      => $content,
                'file_path'    => $filePath,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

        } else {

            ProjectSubmission::create([
                'project_id'   => $project->id,
                'task_id'      => $taskId,
                'student_id'   => $student->id,
                'content'      => $content,
                'file_path'    => $filePath,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PIVOT UPDATE — for group projects with no tasks, mark ALL group members
        |   as submitted in project_student so teacher sees the group's status.
        |   For individual projects (no tasks), update or create the student's pivot.
        |--------------------------------------------------------------------------
        */
        if (!$taskId) {

            if ($project->group_id) {

                // Group project: update every member's project_student pivot
                $memberIds = DB::table('group_student')
                    ->where('group_id', $project->group_id)
                    ->pluck('student_id');

                foreach ($memberIds as $memberId) {
                    $exists = $project->assignments()
                        ->where('users.id', $memberId)
                        ->exists();

                    if ($exists) {
                        $project->assignments()->updateExistingPivot($memberId, [
                            'assignment_status' => 'submitted',
                            'submitted_at'      => now(),
                        ]);
                    } else {
                        $project->assignments()->attach($memberId, [
                            'assignment_status' => 'submitted',
                            'submitted_at'      => now(),
                        ]);
                    }
                }

            } else {

                // Individual project: update or create the student's own pivot row
                $exists = $project->assignments()
                    ->where('users.id', $student->id)
                    ->exists();

                if ($exists) {
                    $project->assignments()->updateExistingPivot($student->id, [
                        'assignment_status' => 'submitted',
                        'submitted_at'      => now(),
                    ]);
                } else {
                    $project->assignments()->attach($student->id, [
                        'assignment_status' => 'submitted',
                        'submitted_at'      => now(),
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route(
                'student.projects.show',
                $project->id
            )
            ->with(
                'success',
                'Task submitted successfully.'
            );
    }
}