@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/student-group-join.css') }}">
<style>
.group-list-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 18px;
    padding: 1.5rem;
    margin-top: 1.25rem;
}
.group-list-heading {
    font-weight: 700;
    font-size: .95rem;
    color: #111827;
    margin-bottom: 1rem;
}
.group-row {
    display: flex;
    align-items: center;
    gap: .9rem;
    padding: .85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    margin-bottom: .6rem;
    transition: border-color .15s;
}
.group-row:last-child { margin-bottom: 0; }
.group-row:hover { border-color: #a5b4fc; }
.group-icon-sm {
    width: 38px; height: 38px;
    background: #eef2ff;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.group-info { flex: 1; min-width: 0; }
.group-nm { font-weight: 700; font-size: .9rem; color: #111827; }
.group-meta { font-size: .76rem; color: #6b7280; margin-top: .15rem; }
.group-badge {
    padding: .25rem .65rem;
    background: #ede9fe;
    color: #6d28d9;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    white-space: nowrap;
}
.group-open-btn {
    padding: .3rem .8rem;
    background: #4f46e5;
    color: #fff;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}
.group-open-btn:hover { background: #4338ca; }
</style>
@endpush

@section('content')
<div class="join-wrap">

    @if(session('success'))
        <div style="margin-bottom:1.5rem;padding:1rem;background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:12px;font-size:.9rem;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="join-card">

        <div class="join-icon">🔑</div>

        <div class="join-title">Join a Group</div>

        <div class="join-sub">
            Enter the 6-character code your teacher gave you. You must be registered under that teacher and pre-selected for the group.
        </div>

        <form method="POST" action="{{ route('student.groups.join.store') }}">
            @csrf

            <label class="form-label" for="join_code">
                Group Join Code
            </label>

            <input type="text"
                   id="join_code"
                   name="join_code"
                   class="code-input"
                   placeholder="ABC123"
                   maxlength="6"
                   value="{{ old('join_code') }}"
                   autocomplete="off"
                   autofocus
                   oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">

            @error('join_code')
                <div class="error-msg">⚠️ {{ $message }}</div>
            @enderror

            <button type="submit" class="btn-join">
                🚀 Join Group
            </button>

        </form>

        <a href="{{ route('student.dashboard') }}" class="back-link">
            ← Back to Dashboard
        </a>

        <div class="how-it-works">
            <div class="how-title">How it works</div>
            <div class="how-step">
                <div class="how-num">1</div>
                <span>First, register under your teacher using their 6-character teacher code (via "My Teacher").</span>
            </div>
            <div class="how-step">
                <div class="how-num">2</div>
                <span>Your teacher creates a group and adds you as a member, then shares the 6-character join code.</span>
            </div>
            <div class="how-step">
                <div class="how-num">3</div>
                <span>Enter the code above — you'll be instantly added and all assigned projects appear in your dashboard.</span>
            </div>
        </div>

    </div>

    {{-- MY GROUPS LIST (always visible, same pattern as My Sections & My Teacher) --}}
    @if($myGroups->isNotEmpty())
    <div class="group-list-card">
        <div class="group-list-heading">👥 My Groups ({{ $myGroups->count() }})</div>

        @foreach($myGroups as $group)
        <div class="group-row">
            <div class="group-icon-sm">👥</div>
            <div class="group-info">
                <div class="group-nm">{{ $group->name }}</div>
                <div class="group-meta">
                    👩‍🏫 {{ $group->teacher->name ?? 'Teacher' }}
                    @if($group->section)
                        @if($group->section->subject)
                            &nbsp;·&nbsp; 📚 {{ $group->section->subject }}
                        @endif
                        @if($group->section->name)
                            &nbsp;·&nbsp; {{ $group->section->name }}
                        @endif
                    @endif
                    @if($group->projects && $group->projects->count())
                        &nbsp;·&nbsp; 📂 {{ $group->projects->count() }} {{ Str::plural('project', $group->projects->count()) }}
                    @endif
                </div>
            </div>
            <span class="group-badge">✅ Joined</span>
            <a href="{{ route('student.groups.show', $group->id) }}" class="group-open-btn">Open →</a>
        </div>
        @endforeach
    </div>
    @endif

</div>

@endsection
