@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/teacher-students.css') }}">
<style>
/* ── Mobile responsiveness for teacher students page ── */
@media (max-width: 768px) {
    .wrap { padding: 1rem 0.75rem; }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .page-header a {
        width: 100%;
        text-align: center;
    }

    .code-box {
        display: flex !important;
        flex-direction: column;
        width: 100%;
        box-sizing: border-box;
        gap: 0.75rem;
        padding: 1rem;
    }

    .code-value { font-size: 1.4rem !important; letter-spacing: .15em !important; }

    .panel { overflow-x: auto; }

    table { min-width: 560px; }
}
</style>
@endpush

@section('content')
<div class="wrap">

    <div class="page-header">
        <div>
            <div class="page-title">🧑‍🎓 My Students</div>
            <div class="page-sub">Students who registered using your teacher code</div>
        </div>
        <a href="{{ route('teacher.dashboard') }}"
           style="padding:.65rem 1.2rem;border:1px solid #d1d5db;border-radius:10px;
                  text-decoration:none;color:#374151;font-size:.85rem;font-weight:600;">
            ← Dashboard
        </a>
    </div>

    {{-- TEACHER CODE --}}
    <div class="code-box">
        <div>
            <div class="code-label">YOUR TEACHER CODE</div>
            <div class="code-value" id="teacherCode" onclick="copyCode()" title="Click to copy">
                {{ $teacher->teacher_code ?? '------' }}
            </div>
        </div>
        <div>
            <button class="btn-copy" id="copyBtn" onclick="copyCode()">📋 Copy</button>
            <div id="copyConfirm"
                 style="display:none;font-size:.75rem;color:#166534;font-weight:600;margin-top:4px;">
                ✅ Copied!
            </div>
            <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;max-width:180px;">
                Share this with students so they can register under you.
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom:1rem;padding:1rem;background:#dcfce7;color:#166534;
                    border-radius:10px;border:1px solid #bbf7d0;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- STUDENTS TABLE --}}
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td style="color:#9ca3af;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="name-cell">
                                <div class="avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                                <div>
                                    <div class="s-name">{{ $student->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->student_id ?? '—' }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->department ?? '—' }}</td>
                        <td style="color:#9ca3af;font-size:.78rem;">
                            {{ $student->pivot->created_at
                                ? $student->pivot->created_at->format('M d, Y')
                                : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div class="empty-icon">🧑‍🎓</div>
                                <div style="font-weight:600;color:#374151;margin-bottom:.5rem;">
                                    No students yet
                                </div>
                                <div style="font-size:.85rem;">
                                    Share your teacher code
                                    <strong style="color:#4f46e5;">
                                        {{ $teacher->teacher_code }}
                                    </strong>
                                    with your students.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1.25rem;">
        {{ $students->links() }}
    </div>

</div>
@endsection

@section('scripts')
<script>
// Read teacher code directly from the DOM — no PHP template in .js files
function copyCode() {
    const code = document.getElementById('teacherCode').textContent.trim();
    if (!code || code === '------') return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(showCopied).catch(fallbackCopy);
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        const el = document.createElement('textarea');
        el.value = code;
        el.style.position = 'fixed';
        el.style.opacity = '0';
        document.body.appendChild(el);
        el.focus();
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showCopied();
    }

    function showCopied() {
        document.getElementById('copyConfirm').style.display = 'block';
        document.getElementById('copyBtn').textContent = '✅ Copied!';
        setTimeout(() => {
            document.getElementById('copyConfirm').style.display = 'none';
            document.getElementById('copyBtn').textContent = '📋 Copy';
        }, 2500);
    }
}
</script>
@endsection
