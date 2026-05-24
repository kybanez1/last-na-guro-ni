@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/teacher-project-create.css') }}">
@endpush

@section('content')
<div class="wrap">
<div class="card">

    <div class="header">➕ Create New Project</div>

    <div class="body">

        @if($errors->any())
        <div class="error-box">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('teacher.projects.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- TITLE + MAX SCORE --}}
        <div class="field-row">
            <div class="field">
                <label>Project Title</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Final Research Paper" required>
            </div>
            <div class="field">
                <label>Max Score</label>
                <input type="number" name="max_score" min="1" max="1000" value="{{ old('max_score', 100) }}" required>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="field">
            <label>Description <span style="color:#ef4444">*</span></label>
            <textarea name="description" rows="4" placeholder="What is this project about?" required>{{ old('description') }}</textarea>
        </div>

        {{-- START + DUE DATE --}}
        <div class="field-row">
            <div class="field">
                <label>Start Date</label>
                <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required>
            </div>
            <div class="field">
                <label>Due Date</label>
                <input type="datetime-local" name="due_date" value="{{ old('due_date') }}" required>
            </div>
        </div>

        {{-- STATUS --}}
        <div class="field">
            <label>Status</label>
            <select name="status" required>
                <option value="draft"     {{ old('status','draft') == 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="ongoing"   {{ old('status') == 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>

        {{-- INSTRUCTIONS --}}
        <div class="field">
            <label>Instructions <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
            <div class="seg-tabs">
                <button type="button" class="seg-tab active" id="tab-file" onclick="switchTab('file')">📎 Upload File</button>
                <button type="button" class="seg-tab"        id="tab-link" onclick="switchTab('link')">🔗 Paste Link</button>
            </div>
            <div id="panel-file">
                <div class="file-box">
                    <input type="file" name="instruction_file">
                    <div class="file-help">PDF, DOCX, PPT, ZIP, Images — max 20MB</div>
                </div>
            </div>
            <div id="panel-link" style="display:none;">
                <input type="url" name="instruction_link" placeholder="https://drive.google.com/..." value="{{ old('instruction_link') }}">
                <div class="file-help">Google Drive, Dropbox, OneDrive or any URL</div>
            </div>
        </div>

        {{-- ASSIGN TO --}}
        <div class="field">
            <label>Assign To</label>
            <div class="assign-tabs">
                <button type="button" class="assign-tab active" id="atab-group"      onclick="switchAssign('group')">👥 Group</button>
                <button type="button" class="assign-tab"        id="atab-individual" onclick="switchAssign('individual')">🧑 Individual Students</button>
            </div>

            {{-- GROUP --}}
            <div class="assign-panel open" id="panel-group">
                @if($sections->isNotEmpty())
                <div class="field" style="margin-bottom:.65rem;">
                    <select id="groupSectionFilter" onchange="filterGroupsBySection(this.value)">
                        <option value="">— All sections —</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->name }}{{ $sec->subject ? ' · '.$sec->subject : '' }}{{ $sec->school_year ? ' · '.$sec->school_year : '' }}{{ $sec->semester ? ' · '.$sec->semester : '' }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <select name="group_id" id="groupSelect">
                    <option value="">— No group —</option>
                    @foreach($groups as $g)
                    <option value="{{ $g->id }}" data-section="{{ $g->section_id ?? '' }}" {{ old('group_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}{{ $g->section ? ' · '.$g->section->name : '' }}
                    </option>
                    @endforeach
                </select>
                <div id="groupNoMatch" class="no-match" style="display:none;margin-top:.5rem;">No groups found for this section.</div>
            </div>

            {{-- INDIVIDUAL --}}
            <div class="assign-panel" id="panel-individual">
                @if($myStudents->isEmpty())
                    <div class="task-empty">No students under your code yet.</div>
                @else
                    @php
                        $secStudentMap = [];
                        foreach($sections as $sec) {
                            $secStudentMap[$sec->id] = $sec->students->pluck('id')->toArray();
                        }
                    @endphp
                    <div class="filter-row">
                        @if($sections->isNotEmpty())
                        <select id="studentSectionFilter" name="section_id" onchange="filterStudentsBySection(this.value)">
                            <option value="">— All sections —</option>
                            @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}{{ $sec->subject ? ' · '.$sec->subject : '' }}</option>
                            @endforeach
                        </select>
                        @endif
                        <input type="text" class="search-box" id="studentSearch" placeholder="Search name or ID…" oninput="filterStudents()">
                    </div>
                    <div class="student-checklist" id="studentList">
                        @foreach($myStudents as $st)
                        @php
                            $stSecs = [];
                            foreach($secStudentMap as $sId => $uIds) {
                                if(in_array($st->id, $uIds)) $stSecs[] = $sId;
                            }
                        @endphp
                        <label class="student-row"
                               data-name="{{ strtolower($st->name) }}"
                               data-sid="{{ strtolower($st->student_id ?? '') }}"
                               data-sections="{{ implode(',', $stSecs) }}">
                            <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" {{ in_array($st->id, old('student_ids', [])) ? 'checked' : '' }}>
                            <div>
                                <div class="student-name">
                                    {{ $st->name }}
                                    @foreach($sections->whereIn('id', $stSecs) as $ss)
                                        <span class="sec-tag">{{ $ss->name }}</span>
                                    @endforeach
                                </div>
                                @if($st->student_id)
                                <div class="student-meta">🆔 {{ $st->student_id }}{{ $st->department ? ' · '.$st->department : '' }}</div>
                                @endif
                            </div>
                        </label>
                        @endforeach
                        <div class="no-match" id="noMatch">No students match.</div>
                    </div>
                @endif
            </div>
        </div>

        <hr class="divider">

        {{-- TASKS --}}
        <div class="field">
            <label style="font-size:.9rem;font-weight:700;">📋 Tasks <span style="font-weight:400;color:#9ca3af;font-size:.78rem;">(optional)</span></label>
            <div id="task-wrapper"></div>
            <div class="task-empty" id="task-empty">No tasks yet — click <strong>+ Add Task</strong> to create one.</div>
            <button type="button" class="btn btn-add" id="add-task-btn">➕ Add Task</button>
        </div>

        <button type="submit" class="btn btn-primary btn-submit">🚀 Create Project</button>

        </form>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/pages/teacher-project-create.js') }}"></script>
<script>
function switchTab(mode) {
    document.getElementById('panel-file').style.display = mode === 'file' ? '' : 'none';
    document.getElementById('panel-link').style.display = mode === 'link' ? '' : 'none';
    document.getElementById('tab-file').classList.toggle('active', mode === 'file');
    document.getElementById('tab-link').classList.toggle('active', mode === 'link');
}
function switchAssign(mode) {
    ['group','individual'].forEach(function(m) {
        document.getElementById('panel-' + m).classList.toggle('open', m === mode);
        document.getElementById('atab-' + m).classList.toggle('active', m === mode);
    });
    if (mode === 'group') {
        document.querySelectorAll('input[name="student_ids[]"]').forEach(function(c){ c.checked = false; });
    } else {
        var gs = document.getElementById('groupSelect');
        if (gs) gs.value = '';
    }
}
function filterGroupsBySection(sId) {
    var opts = document.querySelectorAll('#groupSelect option');
    var vis = 0;
    opts.forEach(function(o) {
        if (!o.value) { o.style.display = ''; return; }
        var show = !sId || o.dataset.section == sId;
        o.style.display = show ? '' : 'none';
        if (show) vis++;
    });
    var gs = document.getElementById('groupSelect');
    if (gs && gs.selectedIndex > 0 && gs.options[gs.selectedIndex].style.display === 'none') gs.value = '';
    document.getElementById('groupNoMatch').style.display = (sId && vis === 0) ? 'block' : 'none';
}
var _secFilter = '';
function filterStudentsBySection(v) { _secFilter = v; filterStudents(); }
function filterStudents() {
    var q = (document.getElementById('studentSearch').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.student-row');
    var vis = 0;
    rows.forEach(function(r) {
        var secOk = !_secFilter || (r.dataset.sections || '').split(',').indexOf(_secFilter) > -1;
        var qOk   = !q || r.dataset.name.indexOf(q) > -1 || r.dataset.sid.indexOf(q) > -1;
        r.style.display = (secOk && qOk) ? '' : 'none';
        if (secOk && qOk) vis++;
    });
    document.getElementById('noMatch').style.display = vis === 0 ? 'block' : 'none';
}
@if(old('student_ids'))
    switchAssign('individual');
@endif
</script>
@endsection
