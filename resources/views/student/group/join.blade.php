@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/student-group-join.css') }}">
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

</div>

@endsection