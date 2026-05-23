@extends('layouts.app')

@section('content')
@php
    $isGroupProject = !empty($submission->project->group_id);
@endphp

<div style="max-width:900px;margin:auto;padding:2rem;">

    @if(session('success'))
        <div style="background:#dcfce7;padding:1rem;border-radius:10px;margin-bottom:1rem;color:#166534;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2;padding:1rem;border-radius:10px;margin-bottom:1rem;color:#991b1b;">
            <strong>Please fix the following:</strong>
            <ul style="margin-top:8px;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUBMISSION DETAILS --}}
    <div style="background:white;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;margin-bottom:1.5rem;">

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
            <h2 style="font-size:1.5rem;font-weight:700;margin:0;">📄 Submission Details</h2>
            @if($isGroupProject)
                <span style="background:#eef2ff;color:#4f46e5;padding:.3rem .85rem;border-radius:999px;font-size:.78rem;font-weight:700;">
                    👥 Group Project
                </span>
            @else
                <span style="background:#f0fdf4;color:#15803d;padding:.3rem .85rem;border-radius:999px;font-size:.78rem;font-weight:700;">
                    🧑 Individual Project
                </span>
            @endif
        </div>

        <div style="margin-bottom:1rem;">
            <strong>Student:</strong> {{ $submission->student->name }}
        </div>
        <div style="margin-bottom:1rem;">
            <strong>Project:</strong> {{ $submission->project->title }}
            @if($isGroupProject)
                <span style="margin-left:.5rem;font-size:.78rem;color:#6b7280;">
                    (Group: {{ $submission->project->group->name ?? '—' }})
                </span>
            @endif
        </div>
        <div style="margin-bottom:1rem;">
            <strong>Task:</strong> {{ $submission->task->title ?? 'General Submission' }}
        </div>
        <div style="margin-bottom:1rem;">
            <strong>Status:</strong> {{ ucfirst($submission->status) }}
        </div>
        <div style="margin-bottom:1rem;">
            <strong>Submitted At:</strong>
            {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : '—' }}
        </div>

        @if($submission->content)
        <div style="margin-bottom:1rem;">
            <strong>Content:</strong>
            <div style="padding:1rem;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa;white-space:pre-wrap;margin-top:6px;">
                {{ $submission->content }}
            </div>
        </div>
        @endif

        <div style="margin-bottom:1.5rem;">
            <strong>File:</strong>
            @if($submission->file_path)
                <a href="{{ asset('storage/' . $submission->file_path) }}"
                   target="_blank" style="margin-left:8px;">
                    📎 Open Submission File
                </a>
            @else
                <span style="color:#9ca3af;margin-left:8px;">No file uploaded.</span>
            @endif
        </div>

    </div>

    {{-- GRADE SECTION --}}
    @if($isGroupProject)
        {{-- GROUP PROJECT: no individual grading here --}}
        <div style="background:white;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;">
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
                <span style="font-size:2rem;">👥</span>
                <div>
                    <div style="font-size:1.1rem;font-weight:700;color:#111827;">This is a Group Project</div>
                    <div style="font-size:.875rem;color:#6b7280;margin-top:.25rem;">
                        Individual grading is disabled. Use the Group Grade button to assign one grade to all members at once.
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:1.25rem;">
                <a href="{{ route('teacher.grades.project', $submission->project_id) }}"
                   style="background:#4f46e5;color:white;padding:.8rem 1.4rem;border-radius:10px;text-decoration:none;font-weight:700;">
                    ⭐ Grade Group
                </a>
                <a href="{{ route('teacher.projects.show', $submission->project_id) }}"
                   style="border:1px solid #d1d5db;padding:.8rem 1.2rem;border-radius:10px;text-decoration:none;color:#374151;">
                    ← Back to Project
                </a>
            </div>
        </div>

    @else
        {{-- INDIVIDUAL PROJECT: show grading form --}}
        <div style="background:white;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;">
            <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1.5rem;">
                ⭐ Grade This Submission
            </h2>

            <form method="POST"
                  action="{{ route('teacher.submissions.grade.store', $submission->id) }}">
                @csrf
                @method('PUT')

                {{-- TASK SCORE --}}
                @if($submission->task)
                <div style="margin-bottom:1rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">
                    <div style="font-weight:700;color:#166534;margin-bottom:.75rem;">
                        📋 Task: {{ $submission->task->title }}
                    </div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Task Score
                        <span style="font-weight:400;color:#6b7280;font-size:.85rem;">
                            (max: {{ $submission->task->max_points ?? 100 }} pts)
                        </span>
                    </label>
                    <input type="number"
                           name="task_score"
                           value="{{ old('task_score', $submission->task_score) }}"
                           min="0"
                           max="{{ $submission->task->max_points ?? 100 }}"
                           style="width:100%;padding:.8rem;border:1px solid #d1d5db;border-radius:10px;margin-bottom:.5rem;">
                    <div style="font-size:.78rem;color:#6b7280;">
                        Grades this specific task — based on the max points set for this task.
                    </div>
                </div>
                @endif

                {{-- OVERALL SCORE --}}
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">
                        Overall Project Score
                        <span style="font-weight:400;color:#6b7280;font-size:.85rem;">
                            (max: {{ $submission->project->max_score }})
                        </span>
                    </label>
                    <input type="number"
                           name="score"
                           value="{{ old('score', $submission->score) }}"
                           min="0"
                           max="{{ $submission->project->max_score }}"
                           required
                           style="width:100%;padding:.8rem;border:1px solid #d1d5db;border-radius:10px;">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-weight:600;margin-bottom:6px;">Feedback</label>
                    <textarea name="feedback" rows="5"
                              style="width:100%;padding:.8rem;border:1px solid #d1d5db;border-radius:10px;">{{ old('feedback', $submission->feedback) }}</textarea>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit"
                            style="background:#4f46e5;color:white;padding:.8rem 1.4rem;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                        ✅ Save Grade
                    </button>
                    <a href="{{ route('teacher.projects.show', $submission->project_id) }}"
                       style="border:1px solid #d1d5db;padding:.8rem 1.2rem;border-radius:10px;text-decoration:none;color:#374151;">
                        ← Back to Project
                    </a>
                </div>

            </form>
        </div>
    @endif

</div>
@endsection
