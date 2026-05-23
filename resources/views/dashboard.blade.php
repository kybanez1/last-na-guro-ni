@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/teacher-dashboard.css') }}">
@endpush

@section('content')
<div class="pms-dash">

    {{-- ── HEADER ───────────────────────────────────────── --}}
    <div class="dash-header">

        <div class="dash-greeting">
            <h2>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', Auth::user()->name)[0] }} 👋</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Your classroom at a glance</p>
        </div>

        <div class="header-actions">
            <span class="role-pill">🎓 Teacher</span>

            <span class="teacher-code-chip" onclick="copyTeacherCode()" title="Click to copy your teacher code">
                🔑 {{ Auth::user()->teacher_code ?? 'N/A' }}
            </span>

            <a href="{{ route('teacher.students.index') }}"  class="btn-outline">🧑‍🎓 Students</a>
            <a href="{{ route('teacher.sections.index') }}"  class="btn-outline">🏫 Sections</a>
            <a href="{{ route('teacher.groups.create') }}"   class="btn-outline">＋ Group</a>
            <a href="{{ route('teacher.projects.create') }}" class="btn-primary">＋ New Project</a>
        </div>

    </div>

    {{-- ── STATS ────────────────────────────────────────── --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon-wrap indigo">📁</div>
            <div class="stat-info">
                <div class="stat-val">{{ $totalProjects }}</div>
                <div class="stat-lbl">Projects</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap green">👥</div>
            <div class="stat-info">
                <div class="stat-val">{{ $totalGroups }}</div>
                <div class="stat-lbl">Groups</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap slate">🧑‍🎓</div>
            <div class="stat-info">
                <div class="stat-val">{{ $totalStudents }}</div>
                <div class="stat-lbl">Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap amber">⏳</div>
            <div class="stat-info">
                <div class="stat-val">{{ $pendingGrades }}</div>
                <div class="stat-lbl">Pending Grades</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap indigo">🏫</div>
            <div class="stat-info">
                <div class="stat-val">{{ $totalSections }}</div>
                <div class="stat-lbl">Sections</div>
            </div>
        </div>
    </div>

    {{-- ── MAIN GRID ─────────────────────────────────────── --}}
    <div class="dash-grid">

        {{-- LEFT --}}
        <div>

            {{-- PROJECTS --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">📂 Recent Projects</div>
                    <a href="{{ route('teacher.projects.index') }}" class="panel-action">View all →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Group</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td><strong>{{ $project->title }}</strong></td>
                                <td style="color:var(--muted)">{{ $project->group->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $project->status === 'active' ? 'badge-success' : 'badge-pending' }}">
                                        {{ ucfirst($project->status ?? 'active') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('teacher.projects.show', $project->id) }}" class="action-btn">View →</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="4">No projects yet — <a href="{{ route('teacher.projects.create') }}" style="color:var(--accent)">create one</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- SECTIONS --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">🏫 Sections</div>
                    <a href="{{ route('teacher.sections.index') }}" class="panel-action">Manage →</a>
                </div>
                @if($sections->isEmpty())
                    <div class="panel-empty">No sections yet. <a href="{{ route('teacher.sections.index') }}" style="color:var(--accent)">Create one →</a></div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Code</th>
                                <th>Students</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sections as $section)
                                <tr>
                                    <td>
                                        <strong>{{ $section->name }}</strong>
                                        @if($section->semester)
                                            <div style="font-size:.73rem;color:var(--muted)">{{ $section->semester }}</div>
                                        @endif
                                    </td>
                                    <td><span class="section-code">{{ $section->code }}</span></td>
                                    <td style="color:var(--muted)">{{ $section->students_count }}</td>
                                    <td>
                                        <a href="{{ route('teacher.sections.show', $section->id) }}" class="action-btn">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- RECENTLY GRADED --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">✅ Recently Graded</div>
                    <a href="{{ route('teacher.graded.index') }}" class="panel-action">View all →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Project</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentlyGraded as $submission)
                            <tr>
                                <td>{{ $submission->student->name ?? 'Unknown' }}</td>
                                <td style="color:var(--muted)">{{ $submission->project->title ?? '—' }}</td>
                                <td><span class="badge badge-success">Graded</span></td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="3">No graded submissions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- RIGHT SIDEBAR --}}
        <div>

            {{-- GROUPS --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">👥 Groups</div>
                    <a href="{{ route('teacher.groups.index') }}" class="panel-action">All →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>👥</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td><strong style="font-size:.84rem">{{ $group->name }}</strong></td>
                                <td style="color:var(--muted);font-size:.82rem">{{ $group->students_count }}</td>
                                <td>
                                    <a href="{{ route('teacher.groups.show', $group->id) }}" class="action-btn">→</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="3">No groups yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- STUDENTS --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">🧑‍🎓 Students</div>
                    <a href="{{ route('teacher.students.index') }}" class="panel-action">All →</a>
                </div>

                @forelse($students->take(8) as $student)
                    <div class="student-item">
                        <div class="student-avatar">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                        <div class="student-name">{{ $student->name }}</div>
                    </div>
                @empty
                    <div class="panel-empty">No students yet.</div>
                @endforelse

                @if($students->count() > 8)
                    <div style="padding:.75rem 1.2rem;text-align:center;border-top:1px solid var(--border);">
                        <a href="{{ route('teacher.students.index') }}"
                           style="font-size:.78rem;color:var(--accent);font-weight:600;text-decoration:none;">
                            + {{ $students->count() - 8 }} more students →
                        </a>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/pages/teacher-dashboard.js') }}"></script>
<script>
function copyTeacherCode() {
    var code = '{{ Auth::user()->teacher_code ?? "" }}';
    navigator.clipboard.writeText(code).then(function() {
        var chip = document.querySelector('.teacher-code-chip');
        var orig = chip.innerHTML;
        chip.innerHTML = '✅ Copied!';
        chip.style.background = '#d1fae5';
        chip.style.borderColor = '#6ee7b7';
        chip.style.color = '#065f46';
        setTimeout(function() {
            chip.innerHTML = orig;
            chip.style = '';
        }, 2000);
    });
}
</script>
@endsection
