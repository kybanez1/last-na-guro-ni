@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/teacher-project-show.css') }}">
@endpush

@section('content')
<div class="wrap">

    @if(session('success'))
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            ❌ {{ session('error') }}
        </div>
    @endif

    @php $isGroupProject = !empty($project->group_id); @endphp

    {{-- PROJECT DETAILS --}}
    <div class="card">
        <div class="header">
            <div>📂 {{ $project->title }}</div>
            <div>
                <span class="badge {{ in_array($project->status, ['ongoing','published','active']) ? 'active' : 'closed' }}">
                    {{ ucfirst($project->status) }}
                </span>
                @if($isGroupProject)
                    <span style="margin-left:.5rem;background:#eef2ff;color:#4f46e5;padding:.25rem .75rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                        👥 Group Project
                    </span>
                @else
                    <span style="margin-left:.5rem;background:#f0fdf4;color:#15803d;padding:.25rem .75rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                        🧑 Individual Project
                    </span>
                @endif
            </div>
        </div>
        <div class="body">
            <div class="grid">
                <div>
                    <div class="label">Description</div>
                    <div class="value">{{ $project->description ?: 'No description provided.' }}</div>
                </div>
                <div>
                    <div class="label">{{ $isGroupProject ? 'Group' : 'Assignment Type' }}</div>
                    <div class="value">
                        @if($isGroupProject)
                            {{ $project->group->name ?? '—' }}
                        @else
                            Individual / Per Student
                        @endif
                    </div>
                </div>
                <div>
                    <div class="label">Requirements</div>
                    <div class="value">{{ $project->requirements ?: 'No requirements provided.' }}</div>
                </div>
                <div>
                    <div class="label">Max Score</div>
                    <div class="value">{{ $project->max_score }}</div>
                </div>
                <div>
                    <div class="label">Teacher</div>
                    <div class="value">{{ $project->teacher->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="label">Start Date</div>
                    <div class="value">
                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value">
                        {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="card">
        <div class="body">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number">{{ $submittedCount ?? 0 }}</div>
                    <div class="stat-label">Submitted</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $gradedCount ?? 0 }}</div>
                    <div class="stat-label">Graded</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $project->tasks->count() }}</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="card">
        <div class="body">
            <div class="btn-row">
                <a href="{{ route('teacher.projects.edit', $project->id) }}" class="btn btn-primary">✏️ Edit Project</a>
                @if($isGroupProject)
                    {{-- Group project: single "Grade Group" button --}}
                    <a href="{{ route('teacher.grades.project', $project->id) }}" class="btn btn-outline">⭐ Grade Group</a>
                @else
                    <a href="{{ route('teacher.grades.project', $project->id) }}" class="btn btn-outline">⭐ View Grades</a>
                @endif
                <a href="{{ route('teacher.projects.index') }}" class="btn btn-outline">← Back</a>
            </div>
        </div>
    </div>

    {{-- ASSIGNED STUDENTS PANEL (only for individual projects) --}}
    @if(!$isGroupProject)
    <div class="card">
        <div class="header">🧑‍🎓 Assigned Students</div>
        <div class="table-wrap">
            @php
                $assignedStudents = $project->assignments()->get();
            @endphp
            @if($assignedStudents->isEmpty())
                <div style="padding:2rem;text-align:center;color:#9ca3af;">No students assigned yet.</div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Graded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedStudents as $assignedStudent)
                    @php
                        $pivot = $assignedStudent->pivot;
                        $pStatus = $pivot->assignment_status ?? 'assigned';
                    @endphp
                    <tr>
                        <td>
                            <div class="student-name">{{ $assignedStudent->name }}</div>
                            @if($assignedStudent->student_id)
                                <div style="font-size:.72rem;color:#6b7280;">🆔 {{ $assignedStudent->student_id }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="status-pill
                                {{ $pStatus === 'graded' ? 'status-graded' : ($pStatus === 'submitted' ? 'status-submitted' : 'status-pending') }}">
                                {{ ucfirst($pStatus) }}
                            </span>
                        </td>
                        <td>
                            @if($pivot && $pivot->score !== null)
                                <strong>{{ $pivot->score }}</strong> / {{ $project->max_score }}
                            @else —
                            @endif
                        </td>
                        <td>
                            {{ $pivot && $pivot->graded_at ? \Carbon\Carbon::parse($pivot->graded_at)->format('M d, Y') : '—' }}
                        </td>
                        <td>
                            {{-- Individual project: show Grade button per student --}}
                            <a href="{{ route('teacher.grades.individual.edit', [$project->id, $assignedStudent->id]) }}"
                               class="btn btn-primary" style="font-size:.78rem;padding:.45rem .8rem;">
                                ⭐ {{ $pStatus === 'graded' ? 'Update Grade' : 'Grade' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- GROUP MEMBERS PANEL (only for group projects) --}}
    @if($isGroupProject && $project->group)
    <div class="card">
        <div class="header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
            <span>👥 Group Members — {{ $project->group->name }}</span>
            <a href="{{ route('teacher.grades.project', $project->id) }}"
               class="btn btn-primary" style="font-size:.82rem;padding:.5rem 1.1rem;">
                ⭐ Grade This Group
            </a>
        </div>
        <div class="table-wrap">
            @php
                $groupMembers = $project->group->students()->orderBy('name')->get();
            @endphp
            @if($groupMembers->isEmpty())
                <div style="padding:2rem;text-align:center;color:#9ca3af;">No members in this group yet.</div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Student ID</th>
                        <th>Joined</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupMembers as $member)
                    @php
                        $memberSub = \App\Models\ProjectSubmission::where('project_id', $project->id)
                            ->where('student_id', $member->id)
                            ->whereNull('task_id')
                            ->latest()->first();
                        $memberScore = $memberSub?->score;
                    @endphp
                    <tr>
                        <td>
                            <div class="student-name">{{ $member->name }}</div>
                        </td>
                        <td>{{ $member->student_id ?? '—' }}</td>
                        <td>
                            @if($member->pivot->is_joined)
                                <span style="color:#16a34a;font-size:.8rem;font-weight:600;">✅ Joined</span>
                            @else
                                <span style="color:#9ca3af;font-size:.8rem;">⏳ Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($memberScore !== null)
                                <strong>{{ $memberScore }}</strong> / {{ $project->max_score }}
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- PROJECT TASKS --}}
    <div class="card">
        <div class="header">📋 Project Tasks</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($project->tasks as $task)
                        <tr>
                            <td><div class="student-name">{{ $task->title }}</div></td>
                            <td>{{ $task->description ?? '—' }}</td>
                            <td>
                                @if($task->due_date)
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y h:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-box">No tasks added yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- STUDENT SUBMISSIONS --}}
    <div class="card">
        <div class="header">🧑‍🎓 Student Submissions</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>File</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions ?? [] as $submission)
                        <tr>
                            <td>
                                <div class="student-name">{{ $submission->student->name ?? 'Unknown Student' }}</div>
                                @if(isset($submission->student->student_id))
                                    <div style="font-size:.72rem;color:#6b7280;margin-top:2px;">
                                        ID: {{ $submission->student->student_id }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $submission->task->title ?? '📄 General Submission' }}</td>
                            <td>
                                <span class="status-pill
                                    {{ $submission->status === 'graded'
                                        ? 'status-graded'
                                        : (in_array($submission->status, ['submitted','reviewed'])
                                            ? 'status-submitted'
                                            : 'status-pending') }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </td>
                            <td>
                                {{ $submission->submitted_at
                                    ? $submission->submitted_at->format('M d, Y h:i A')
                                    : '—' }}
                            </td>
                            <td>
                                @if($submission->file_path)
                                    <a href="{{ asset('storage/' . $submission->file_path) }}"
                                       target="_blank" class="file-link">📎 View</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('teacher.submissions.show', $submission->id) }}"
                                   class="btn btn-outline" style="font-size:.78rem;padding:.45rem .8rem;">
                                    👁 View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="empty-box">No submissions yet.</div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:1rem 1.5rem;">
            {{ $submissions->links() }}
        </div>
    </div>

</div>
@endsection
