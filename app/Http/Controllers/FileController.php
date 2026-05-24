<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SERVE INSTRUCTION FILE
    | Route: GET /files/instruction/{project}
    | Access: teacher who owns it, or student assigned to the project
    |--------------------------------------------------------------------------
    */
    public function instruction(Request $request, Project $project): StreamedResponse
    {
        $user = auth()->user();

        // Access check
        $allowed = false;

        if ($user->role === 'teacher' && $project->teacher_id === $user->id) {
            $allowed = true;
        }

        if ($user->role === 'student') {
            // Direct assignment
            $directlyAssigned = $project->assignments()
                ->where('users.id', $user->id)
                ->exists();

            // Group access
            $groupAccess = false;
            if ($project->group_id) {
                $groupAccess = \DB::table('group_student')
                    ->where('group_id', $project->group_id)
                    ->where('student_id', $user->id)
                    ->exists();
            }

            $allowed = $directlyAssigned || $groupAccess;
        }

        abort_unless($allowed, 403);
        abort_unless($project->instruction_file, 404);
        abort_unless(
            Storage::disk('public')->exists($project->instruction_file),
            404
        );

        $fileName = $project->instruction_file_name
            ?? basename($project->instruction_file);

        $mime = Storage::disk('public')->mimeType($project->instruction_file)
            ?: 'application/octet-stream';

        return Storage::disk('public')->response(
            $project->instruction_file,
            $fileName,
            ['Content-Type' => $mime]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD INSTRUCTION FILE (forces download)
    | Route: GET /files/instruction/{project}/download
    |--------------------------------------------------------------------------
    */
    public function instructionDownload(Request $request, Project $project): StreamedResponse
    {
        $user = auth()->user();

        $allowed = false;

        if ($user->role === 'teacher' && $project->teacher_id === $user->id) {
            $allowed = true;
        }

        if ($user->role === 'student') {
            $directlyAssigned = $project->assignments()
                ->where('users.id', $user->id)
                ->exists();

            $groupAccess = false;
            if ($project->group_id) {
                $groupAccess = \DB::table('group_student')
                    ->where('group_id', $project->group_id)
                    ->where('student_id', $user->id)
                    ->exists();
            }

            $allowed = $directlyAssigned || $groupAccess;
        }

        abort_unless($allowed, 403);
        abort_unless($project->instruction_file, 404);
        abort_unless(
            Storage::disk('public')->exists($project->instruction_file),
            404
        );

        $fileName = $project->instruction_file_name
            ?? basename($project->instruction_file);

        return Storage::disk('public')->download(
            $project->instruction_file,
            $fileName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SERVE SUBMISSION FILE
    | Route: GET /files/submission/{submission}
    | Access: teacher who owns the project, or the student who submitted
    |--------------------------------------------------------------------------
    */
    public function submission(Request $request, ProjectSubmission $submission): StreamedResponse
    {
        $user = auth()->user();

        $allowed = false;

        // Teacher: must own the project
        if ($user->role === 'teacher') {
            $project = $submission->project;
            $allowed = $project && $project->teacher_id === $user->id;
        }

        // Student: must be the submitter OR a group member for group projects
        if ($user->role === 'student') {
            if ($submission->student_id === $user->id) {
                $allowed = true;
            } elseif ($submission->project && $submission->project->group_id) {
                $allowed = \DB::table('group_student')
                    ->where('group_id', $submission->project->group_id)
                    ->where('student_id', $user->id)
                    ->exists();
            }
        }

        abort_unless($allowed, 403);
        abort_unless($submission->file_path, 404);
        abort_unless(
            Storage::disk('public')->exists($submission->file_path),
            404
        );

        $fileName = basename($submission->file_path);

        $mime = Storage::disk('public')->mimeType($submission->file_path)
            ?: 'application/octet-stream';

        return Storage::disk('public')->response(
            $submission->file_path,
            $fileName,
            ['Content-Type' => $mime]
        );
    }
}
