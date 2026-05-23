@extends('layouts.app')

@section('content')
@php
    $isGroupProject = !empty($submission->project->group_id);
@endphp

<div style="max-width:700px;margin:auto;padding:2rem;">

    @if(session('success'))
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <strong>Please fix the following:</strong>
            <ul style="margin-top:10px;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STUDENT INFO CARD --}}
    <div style="background:#fff;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
            <h2 style="font-size:1.4rem;font-weight:700;margin:0;">⭐ Grade Submission</h2>
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

        <div style="padding:1rem;border-radius:12px;background:#f9fafb;border:1px solid #e5e7eb;">
            <div style="margin-bottom:.6rem;">
                <strong>Student:</strong> {{ $submission->student->name ?? 'Unknown Student' }}
            </div>
            <div style="margin-bottom:.6rem;">
                <strong>Project:</strong> {{ $submission->project->title ?? 'Unknown Project' }}
                @if($isGroupProject)
                    <span style="margin-left:.5rem;font-size:.78rem;color:#6b7280;">
                        (Group: {{ $submission->project->group->name ?? '—' }})
                    </span>
                @endif
            </div>
            <div>
                <strong>Task:</strong> {{ $submission->task->title ?? 'General Submission' }}
            </div>
        </div>
    </div>

    {{-- GRADE FORM or GROUP NOTICE --}}
    @if($isGroupProject)
        {{-- GROUP PROJECT: redirect to group grade --}}
        <div style="background:white;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:1rem;">👥</div>
            <div style="font-size:1.1rem;font-weight:700;color:#111827;margin-bottom:.5rem;">
                This is a Group Project
            </div>
            <div style="font-size:.875rem;color:#6b7280;margin-bottom:1.5rem;line-height:1.6;">
                Individual grading is disabled for group projects.<br>
                Use the button below to assign one grade to all group members at once.
            </div>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('teacher.grades.project', $submission->project_id) }}"
                   style="background:#4f46e5;color:white;padding:.8rem 1.6rem;border-radius:10px;text-decoration:none;font-weight:700;">
                    ⭐ Grade Group
                </a>
                <a href="{{ route('teacher.projects.show', $submission->project_id) }}"
                   style="border:1px solid #d1d5db;padding:.8rem 1.2rem;border-radius:10px;text-decoration:none;color:#374151;">
                    ← Back to Project
                </a>
            </div>
        </div>

    @else
        {{-- INDIVIDUAL PROJECT: grading form --}}
        <div style="background:#fff;border-radius:16px;padding:2rem;border:1px solid #e5e7eb;">

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
                        Grades this specific task only — based on the max points set for this task.
                    </div>
                </div>
                @endif

                {{-- OVERALL SCORE --}}
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-weight:600;margin-bottom:8px;">
                        Overall Project Score
                        <span style="font-weight:400;color:#6b7280;font-size:.85rem;">
                            (max: {{ $submission->project->max_score }})
                        </span>
                    </label>
                    <input type="number"
                           name="score"
                           min="0"
                           max="{{ $submission->project->max_score }}"
                           value="{{ old('score', $submission->score) }}"
                           required
                           style="width:100%;padding:.8rem;border:1px solid #d1d5db;border-radius:10px;">
                </div>

                {{-- FEEDBACK --}}
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;font-weight:600;margin-bottom:8px;">Feedback</label>
                    <textarea name="feedback" rows="5"
                              style="width:100%;padding:.8rem;border:1px solid #d1d5db;border-radius:10px;">{{ old('feedback', $submission->feedback) }}</textarea>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit"
                            style="background:#4f46e5;color:white;padding:.8rem 1.2rem;border:none;border-radius:10px;font-weight:600;cursor:pointer;">
                        💾 Save Grade
                    </button>
                    <a href="{{ route('teacher.submissions.show', $submission->id) }}"
                       style="border:1px solid #d1d5db;padding:.8rem 1.2rem;border-radius:10px;text-decoration:none;color:#374151;">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    @endif

</div>
@endsection
