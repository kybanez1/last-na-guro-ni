@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/student-dashboard.css') }}">
@endpush

@section('content')
<div class="student-dashboard">

    @php
        $gradedProjects = $assignedProjects->filter(fn($p) =>
            optional($p->pivot)->assignment_status === 'graded'
        );
        $highestScore = $gradedProjects->max(fn($p) => optional($p->pivot)->score ?? 0);
        $lowestScore  = $gradedProjects->min(fn($p) => optional($p->pivot)->score ?? 0);
    @endphp

    {{-- ── IDENTITY BAR ─────────────────────────────────── --}}
    <div class="student-identity-bar">
        <span class="student-role-chip">
            <span class="dot"></span>
            🎓 Student Portal
        </span>
        <span class="id-divider">/</span>
        <span class="student-id-chip">
            {{ Auth::user()->student_id ?? 'Student' }}
            @if(Auth::user()->department)
                &nbsp;·&nbsp; {{ Auth::user()->department }}
            @endif
        </span>
    </div>

    {{-- ── HERO ──────────────────────────────────────────── --}}
    <div class="hero">

        <div class="hero-top">
            <div>
                <div class="hero-greeting">Welcome back</div>
                <h1 class="hero-title">
                    <em>{{ explode(' ', Auth::user()->name)[0] }}</em>
                    @if(count(explode(' ', Auth::user()->name)) > 1)
                        {{ implode(' ', array_slice(explode(' ', Auth::user()->name), 1)) }}
                    @endif
                </h1>
                <div class="hero-sub">
                    <span>{{ now()->format('l, F j') }}</span>
                    <span class="sep">|</span>
                    <span>{{ $sections->count() }} {{ Str::plural('section', $sections->count()) }} enrolled</span>
                </div>
            </div>

            <div class="student-badge">
                <div class="badge-label">Average Score</div>
                <div class="badge-val">{{ $averageScore ?? 0 }}%</div>
            </div>
        </div>

        {{-- STATS --}}
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">📂</span>
                <div class="stat-value">{{ $totalProjects }}</div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <div class="stat-value">{{ $pendingCount }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📤</span>
                <div class="stat-value">{{ $submittedCount }}</div>
                <div class="stat-label">Submitted</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">✅</span>
                <div class="stat-value">{{ $gradedProjects->count() }}</div>
                <div class="stat-label">Graded</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <div class="stat-value">{{ $groups->count() }}</div>
                <div class="stat-label">My Groups</div>
            </div>
        </div>

    </div>

    {{-- ── QUICK ACTIONS ─────────────────────────────────── --}}
    <div class="quick-actions">
        <a href="{{ route('student.teacher.join') }}"   class="qa-btn amber">🎓 Enter Teacher Code</a>
        <a href="{{ route('student.groups.join') }}"    class="qa-btn indigo">🔑 Join a Group</a>
        <a href="{{ route('student.sections.join') }}"  class="qa-btn teal">🏫 Join a Section</a>
        <a href="{{ route('student.projects.index') }}" class="qa-btn amber">📚 All Projects</a>
        <a href="{{ route('student.grades') }}"         class="qa-btn teal">📊 My Grades</a>
    </div>

    {{-- ── MAIN GRID ─────────────────────────────────────── --}}
    <div class="dashboard-grid">

        {{-- LEFT COLUMN --}}
        <div>

            {{-- PROJECTS --}}
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">📚 Assigned Projects</div>
                        <div class="panel-sub">Your active school projects</div>
                    </div>
                    <a href="{{ route('student.projects.index') }}" class="panel-action">View all →</a>
                </div>

                @if($assignedProjects->isEmpty())
                    <div class="empty">
                        <span class="empty-icon">📭</span>
                        No projects assigned yet
                    </div>
                @else
                    <div class="project-table-wrap">
                        <table class="project-table">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedProjects as $project)
                                    @php
                                        $pivotStatus = optional($project->pivot)->assignment_status ?? 'assigned';
                                        $score = optional($project->pivot)->score;
                                    @endphp
                                    <tr class="project-row">
                                        <td>
                                            <div class="project-name">{{ $project->title }}</div>
                                            <div class="project-teacher">{{ $project->teacher->name ?? 'Teacher' }}</div>
                                        </td>
                                        <td>
                                            {{ $project->due_date
                                                ? \Carbon\Carbon::parse($project->due_date)->format('M d, Y')
                                                : 'No deadline' }}
                                        </td>
                                        <td>
                                            @if($pivotStatus === 'graded')
                                                <span class="status graded">✅ Graded</span>
                                            @elseif($pivotStatus === 'submitted')
                                                <span class="status submitted">📤 Submitted</span>
                                            @else
                                                <span class="status pending">⏳ Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="score-pill">
                                                {{ $score ?? '—' }} / {{ $project->max_score }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="{{ route('student.projects.show', $project->id) }}" class="btn btn-dark">👁 View</a>
                                                @if($pivotStatus !== 'graded')
                                                    <a href="{{ route('student.projects.submit', $project->id) }}" class="btn btn-primary">🚀 Submit</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- GROUPS --}}
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">👥 My Groups</div>
                        <div class="panel-sub">Your academic groups and classes</div>
                    </div>
                </div>

                @if($groups->isEmpty())
                    <div class="empty">
                        <span class="empty-icon">🧑‍🤝‍🧑</span>
                        No groups joined yet.<br>
                        <small style="color:#9ca3af;">Ask your teacher for a join code.</small>
                    </div>
                @else
                    <div class="group-list">
                        @foreach($groups as $group)
                            <div class="group-item">
                                <div>
                                    <div class="group-name">{{ $group->name }}</div>
                                    <div class="group-teacher">👩‍🏫 {{ $group->teacher->name ?? 'Teacher' }}</div>
                                </div>
                                <a href="{{ route('student.groups.show', $group->id) }}" class="btn btn-primary">Open →</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- SECTIONS --}}
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">🏫 My Sections</div>
                        <div class="panel-sub">Class sections you are enrolled in</div>
                    </div>
                    <a href="{{ route('student.sections.join') }}" class="panel-action">＋ Join</a>
                </div>

                @if($sections->isEmpty())
                    <div class="empty">
                        <span class="empty-icon">🏫</span>
                        Not enrolled in any section yet.
                    </div>
                @else
                    <div class="group-list">
                        @foreach($sections as $section)
                            <div class="group-item">
                                <div>
                                    <div class="group-name">{{ $section->name }}</div>
                                    <div class="group-teacher">
                                        👩‍🏫 {{ $section->teacher->name ?? 'Teacher' }}
                                        @if($section->school_year) &nbsp;·&nbsp; {{ $section->school_year }} @endif
                                        @if($section->semester) &nbsp;·&nbsp; {{ $section->semester }} @endif
                                    </div>
                                </div>
                                <span class="enrolled-chip">✅ Enrolled</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div>

            {{-- PERFORMANCE --}}
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">📊 Performance</div>
                        <div class="panel-sub">Your score overview</div>
                    </div>
                </div>
                <div class="summary-wrap">
                    <div class="summary-box">
                        <div>
                            <div class="summary-label">Average Score</div>
                            <div class="summary-value">{{ $averageScore ?? 0 }}%</div>
                        </div>
                        <span class="summary-icon">⭐</span>
                    </div>
                    <div class="summary-box">
                        <div>
                            <div class="summary-label">Highest Score</div>
                            <div class="summary-value">{{ $highestScore ?? 0 }}</div>
                        </div>
                        <span class="summary-icon">🏆</span>
                    </div>
                    <div class="summary-box">
                        <div>
                            <div class="summary-label">Lowest Score</div>
                            <div class="summary-value">{{ $lowestScore ?? 0 }}</div>
                        </div>
                        <span class="summary-icon">📉</span>
                    </div>
                </div>
            </div>

            {{-- RECENT ACTIVITY --}}
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <div class="panel-title">📤 Recent Activity</div>
                        <div class="panel-sub">Latest submissions & grades</div>
                    </div>
                </div>

                @if($recentSubmissions->isEmpty())
                    <div class="empty">
                        <span class="empty-icon">📭</span>
                        No recent activity
                    </div>
                @else
                    <div class="recent-wrap">
                        @foreach($recentSubmissions as $sub)
                            <div class="recent-item">
                                <div class="recent-title">{{ $sub->project->title ?? 'Project' }}</div>
                                <div class="recent-meta">
                                    {{ $sub->project->teacher->name ?? 'Teacher' }}
                                    &nbsp;·&nbsp;
                                    {{ $sub->created_at->diffForHumans() }}
                                </div>
                                @if($sub->status === 'graded' && $sub->score !== null)
                                    <span class="recent-grade">
                                        ⭐ {{ $sub->score }} / {{ $sub->project->max_score ?? '—' }}
                                    </span>
                                @else
                                    <div style="font-size:.73rem;color:#9ca3af;margin-top:5px;">
                                        {{ ucfirst($sub->status) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
