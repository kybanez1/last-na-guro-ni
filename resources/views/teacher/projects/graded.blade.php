@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/teacher-projects-graded.css') }}">
@endpush

@section('content')
<div class="page">

    <div class="title">
        ✅ Graded Projects
    </div>

    <div class="subtitle">
        Projects with graded student submissions
    </div>

    @if($gradedSubmissions->count())

        <div class="grid">

            @foreach($gradedSubmissions as $project)

                @php

                    $gradedCount = $project->assignments
                        ->where('pivot.assignment_status', 'graded')
                        ->count();

                @endphp

                <div class="card">

                    <div class="body">

                        <div class="badge">
                            GRADED
                        </div>

                        <div class="name">
                            {{ $project->title }}
                        </div>

                        <div class="desc">
                            {{ $project->description }}
                        </div>

                        @if($project->subject)
                            <div style="font-size:.78rem;color:#7c3aed;font-weight:600;margin-bottom:.5rem;">📚 {{ $project->subject }}</div>
                        @endif

                        <div class="stats">

                            <div class="pill">
                                👥 {{ $gradedCount }} Graded
                            </div>

                            <div class="pill">
                                🏆 {{ $project->max_score }} Max Score
                            </div>

                        </div>

                        <a href="{{ route('teacher.projects.show', $project->id) }}"
                           class="btn">

                            👁 View Project

                        </a>

                    </div>

                </div>

            @endforeach

        </div>

        <div style="margin-top:2rem;">
            {{ $gradedSubmissions->links() }}
        </div>

    @else

        <div class="empty">

            <div style="font-size:60px;">
                📄
            </div>

            <h2>No graded projects yet</h2>

            <p style="color:#6b7280;">
                Once you grade student submissions,
                projects will appear here.
            </p>

        </div>

    @endif

</div>

@endsection