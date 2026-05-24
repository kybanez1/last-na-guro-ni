@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/student-projects.css') }}">
@endpush


@section('content')
<div class="projects-wrap">

    <div class="page-header">

        <div>

            <div class="page-title">
                📂 My Projects
            </div>

            <div class="page-sub">
                {{ $assignedProjects->count() }} project(s) assigned to you
            </div>

        </div>

        <a href="{{ route('student.dashboard') }}"
           style="font-size:0.85rem;color:#6b7280;text-decoration:none;">

            ← Dashboard

        </a>

    </div>

    {{-- FILTER --}}
    <div class="filter-bar">

        <a href="{{ route('student.projects.index') }}"
           class="filter-btn {{ !request('status') ? 'active' : '' }}">

            All

        </a>

        <a href="{{ route('student.projects.index', ['status' => 'assigned']) }}"
           class="filter-btn {{ request('status') === 'assigned' ? 'active' : '' }}">

            ⏳ Pending

        </a>

        <a href="{{ route('student.projects.index', ['status' => 'submitted']) }}"
           class="filter-btn {{ request('status') === 'submitted' ? 'active' : '' }}">

            📤 Submitted

        </a>

        <a href="{{ route('student.projects.index', ['status' => 'graded']) }}"
           class="filter-btn {{ request('status') === 'graded' ? 'active' : '' }}">

            ✅ Graded

        </a>

    </div>

    @php

        /*
        |--------------------------------------------------------------------------
        | COMPUTE REAL STATUS ONCE PER PROJECT — single pass, no duplication
        | Priority: graded (submission) > graded (pivot) > submitted > pivot > assigned
        |--------------------------------------------------------------------------
        */

        $projectStatuses = [];  // project_id => ['status'=>..., 'score'=>...]
        $studentId = auth()->id();

        foreach ($assignedProjects as $project) {

            $pivotStatus = $project->pivot->assignment_status ?? 'assigned';
            $pivotScore  = $project->pivot->score ?? null;

            // Fetch the real submission record
            if ($project->group_id) {
                $groupMemberIds = \DB::table('group_student')
                    ->where('group_id', $project->group_id)
                    ->pluck('student_id');
                $latestSub = \App\Models\ProjectSubmission::where('project_id', $project->id)
                    ->whereIn('student_id', $groupMemberIds)
                    ->orderByDesc('submitted_at')
                    ->first();
            } else {
                $latestSub = \App\Models\ProjectSubmission::where('project_id', $project->id)
                    ->where('student_id', $studentId)
                    ->orderByDesc('submitted_at')
                    ->first();
            }

            // Determine final status
            $realStatus = $pivotStatus;
            $realScore  = $pivotScore;

            if ($latestSub) {
                if (in_array($latestSub->status, ['graded', 'reviewed'])) {
                    $realStatus = 'graded';
                    if ($realScore === null) $realScore = $latestSub->score;
                } elseif ($latestSub->status === 'submitted' && $realStatus !== 'graded') {
                    $realStatus = 'submitted';
                }
            }

            // Pivot graded always wins
            if ($pivotStatus === 'graded') {
                $realStatus = 'graded';
                if ($realScore === null) $realScore = $pivotScore;
            }

            $projectStatuses[$project->id] = [
                'status' => $realStatus,
                'score'  => $realScore,
            ];
        }

        // Filter using pre-computed statuses
        $filterStatus = request('status');
        $filtered = $assignedProjects->filter(function ($project) use ($projectStatuses, $filterStatus) {
            if (!$filterStatus) return true;
            $s = $projectStatuses[$project->id]['status'] ?? 'assigned';
            // "assigned" and "pending" both map to the Pending filter
            if ($filterStatus === 'assigned') return in_array($s, ['assigned', 'pending']);
            return $s === $filterStatus;
        })->values();

    @endphp

    @if($filtered->isEmpty())

        <div class="empty-state">

            <div class="icon">
                📭
            </div>

            <h3>
                No projects found
            </h3>

            <p>

                @if(request('status') === 'assigned')
                    No pending projects found.
                @elseif(request('status') === 'submitted')
                    No submitted projects found.
                @elseif(request('status') === 'graded')
                    No graded projects found.
                @else
                    No projects have been assigned to you yet.
                @endif

            </p>

        </div>

    @else

        <div class="projects-grid">

            @foreach($filtered as $project)

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | REAL STATUS
                    |--------------------------------------------------------------------------
                    */

                    $status =
                        $project->pivot->assignment_status
                        ?? 'assigned';

                    $score =
                        $project->pivot->score
                        ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | REAL-TIME STATUS — always derive from actual submissions
                    |--------------------------------------------------------------------------
                    */
                    if ($project->group_id) {
                        $groupStudentIds2 = \DB::table('group_student')
                            ->where('group_id', $project->group_id)
                            ->pluck('student_id');
                        $latestSub2 = \App\Models\ProjectSubmission::where('project_id', $project->id)
                            ->whereIn('student_id', $groupStudentIds2)
                            ->orderByDesc('submitted_at')
                            ->first();
                    } else {
                        $latestSub2 = \App\Models\ProjectSubmission::where('project_id', $project->id)
                            ->where('student_id', auth()->id())
                            ->orderByDesc('submitted_at')
                            ->first();
                    }

                    if ($latestSub2) {
                        if (in_array($latestSub2->status, ['graded', 'reviewed'])) {
                            $status = 'graded';
                            if ($score === null && $latestSub2->score !== null) {
                                $score = $latestSub2->score;
                            }
                        } elseif ($latestSub2->status === 'submitted' && $status !== 'graded') {
                            $status = 'submitted';
                        }
                    }

                    if ($project->pivot && $project->pivot->assignment_status === 'graded') {
                        $status = 'graded';
                        if ($score === null) $score = $project->pivot->score;
                    }

                    $dueDate =
                        \Carbon\Carbon::parse(
                            $project->due_date
                        );

                    $isOverdue =
                        $dueDate->isPast()
                        && $status === 'assigned';

                @endphp

                <div class="project-card">

                    <div class="project-card-header">

                        <div>

                            <div class="project-title">
                                {{ $project->title }}
                            </div>

                            <div class="project-teacher">

                                by
                                {{ $project->teacher->name ?? '—' }}

                            </div>

                            @if($project->subject)
                                <div style="font-size:.73rem;color:#7c3aed;font-weight:600;margin-top:3px;">📚 {{ $project->subject }}</div>
                            @endif

                        </div>

                        <span class="status-badge {{ $status }}">

                            @if($status === 'graded')

                                ✅ Graded

                            @elseif($status === 'submitted')

                                📤 Submitted

                            @else

                                ⏳ Pending

                            @endif

                        </span>

                    </div>

                    @if($project->description)

                        <div class="project-desc">

                            {{ \Illuminate\Support\Str::limit(
                                $project->description,
                                100
                            ) }}

                        </div>

                    @endif

                    <div class="project-meta-row">

                        <div class="meta-item">

                            <span class="meta-label">
                                Due Date
                            </span>

                            <span class="meta-val {{ $isOverdue ? 'due-overdue' : '' }}">

                                {{ $dueDate->format('M d, Y') }}

                                @if($isOverdue)
                                    ⚠️
                                @endif

                            </span>

                        </div>

                        <div class="meta-item">

                            <span class="meta-label">
                                Max Score
                            </span>

                            <span class="meta-val">
                                {{ $project->max_score }}
                            </span>

                        </div>

                        @if($status === 'graded' && $score !== null)

                            <div class="meta-item">

                                <span class="meta-label">
                                    Your Score
                                </span>

                                <span class="score-chip">

                                    {{ $score }}/{{ $project->max_score }}

                                </span>

                            </div>

                        @endif

                    </div>

                    <div class="project-actions">

                        <a href="{{ route('student.projects.show', $project->id) }}"
                           class="btn-view">

                            👁 View

                        </a>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

@endsection