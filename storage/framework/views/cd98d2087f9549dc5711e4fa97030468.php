<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/pages/teacher-project-create.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="wrap">
<div class="card">

    <div class="header">➕ Create New Project</div>

    <div class="body">

        <?php if($errors->any()): ?>
        <div class="error-box">
            <ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('teacher.projects.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        
        <div class="field-row">
            <div class="field">
                <label>Project Title</label>
                <input type="text" name="title" value="<?php echo e(old('title')); ?>" placeholder="e.g. Final Research Paper" required>
            </div>
            <div class="field">
                <label>Max Score</label>
                <input type="number" name="max_score" min="1" max="1000" value="<?php echo e(old('max_score', 100)); ?>" required>
            </div>
        </div>

        
        <div class="field">
            <label>Description <span style="color:#ef4444">*</span></label>
            <textarea name="description" rows="4" placeholder="What is this project about?" required><?php echo e(old('description')); ?></textarea>
        </div>

        
        <div class="field-row">
            <div class="field">
                <label>Start Date</label>
                <input type="datetime-local" name="start_date" value="<?php echo e(old('start_date')); ?>" required>
            </div>
            <div class="field">
                <label>Due Date</label>
                <input type="datetime-local" name="due_date" value="<?php echo e(old('due_date')); ?>" required>
            </div>
        </div>

        
        <div class="field">
            <label>Status</label>
            <select name="status" required>
                <option value="draft"     <?php echo e(old('status','draft') == 'draft'     ? 'selected' : ''); ?>>Draft</option>
                <option value="published" <?php echo e(old('status') == 'published' ? 'selected' : ''); ?>>Published</option>
                <option value="ongoing"   <?php echo e(old('status') == 'ongoing'   ? 'selected' : ''); ?>>Ongoing</option>
                <option value="completed" <?php echo e(old('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
            </select>
        </div>

        
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
                <input type="url" name="instruction_link" placeholder="https://drive.google.com/..." value="<?php echo e(old('instruction_link')); ?>">
                <div class="file-help">Google Drive, Dropbox, OneDrive or any URL</div>
            </div>
        </div>

        
        <div class="field">
            <label>Assign To <span style="color:#ef4444">*</span></label>
            <?php $__errorArgs = ['assign_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div style="margin-bottom:.5rem;padding:.6rem 1rem;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;font-size:.85rem;">
                    ⚠️ <?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div id="assignErrorBox" style="display:none;margin-bottom:.5rem;padding:.6rem 1rem;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;font-size:.85rem;"></div>
            <div class="assign-tabs">
                <button type="button" class="assign-tab active" id="atab-group"      onclick="switchAssign('group')">👥 Group</button>
                <button type="button" class="assign-tab"        id="atab-individual" onclick="switchAssign('individual')">🧑 Individual Students</button>
            </div>

            
            <div class="assign-panel open" id="panel-group">
                <?php if($sections->isNotEmpty()): ?>
                <div class="field" style="margin-bottom:.65rem;">
                    <select id="groupSectionFilter" onchange="filterGroupsBySection(this.value)">
                        <option value="">— All sections —</option>
                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sec->id); ?>"><?php echo e($sec->name); ?><?php echo e($sec->subject ? ' · '.$sec->subject : ''); ?><?php echo e($sec->school_year ? ' · '.$sec->school_year : ''); ?><?php echo e($sec->semester ? ' · '.$sec->semester : ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php endif; ?>
                <select name="group_id" id="groupSelect">
                    <option value="">— No group —</option>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($g->id); ?>" data-section="<?php echo e($g->section_id ?? ''); ?>" <?php echo e(old('group_id') == $g->id ? 'selected' : ''); ?>>
                        <?php echo e($g->name); ?><?php echo e($g->section ? ' · '.$g->section->name : ''); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div id="groupNoMatch" class="no-match" style="display:none;margin-top:.5rem;">No groups found for this section.</div>
            </div>

            
            <div class="assign-panel" id="panel-individual">
                <?php if($myStudents->isEmpty()): ?>
                    <div class="task-empty">No students under your code yet.</div>
                <?php else: ?>
                    <?php
                        $secStudentMap = [];
                        foreach($sections as $sec) {
                            $secStudentMap[$sec->id] = $sec->students->pluck('id')->toArray();
                        }
                    ?>
                    <div class="filter-row">
                        <?php if($sections->isNotEmpty()): ?>
                        <select id="studentSectionFilter" name="section_id" onchange="filterStudentsBySection(this.value)">
                            <option value="">— All sections —</option>
                            <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sec->id); ?>" <?php echo e(old('section_id') == $sec->id ? 'selected' : ''); ?>><?php echo e($sec->name); ?><?php echo e($sec->subject ? ' · '.$sec->subject : ''); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php endif; ?>
                        <input type="text" class="search-box" id="studentSearch" placeholder="Search name or ID…" oninput="filterStudents()">
                    </div>
                    <div class="student-checklist" id="studentList">
                        <?php $__currentLoopData = $myStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $stSecs = [];
                            foreach($secStudentMap as $sId => $uIds) {
                                if(in_array($st->id, $uIds)) $stSecs[] = $sId;
                            }
                        ?>
                        <label class="student-row"
                               data-name="<?php echo e(strtolower($st->name)); ?>"
                               data-sid="<?php echo e(strtolower($st->student_id ?? '')); ?>"
                               data-sections="<?php echo e(implode(',', $stSecs)); ?>">
                            <input type="checkbox" name="student_ids[]" value="<?php echo e($st->id); ?>" <?php echo e(in_array($st->id, old('student_ids', [])) ? 'checked' : ''); ?>>
                            <div>
                                <div class="student-name">
                                    <?php echo e($st->name); ?>

                                    <?php $__currentLoopData = $sections->whereIn('id', $stSecs); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="sec-tag"><?php echo e($ss->name); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if($st->student_id): ?>
                                <div class="student-meta">🆔 <?php echo e($st->student_id); ?><?php echo e($st->department ? ' · '.$st->department : ''); ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="no-match" id="noMatch">No students match.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="divider">

        
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('assets/js/pages/teacher-project-create.js')); ?>"></script>
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
    clearAssignError();
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

// ── ASSIGN-TO VALIDATION ──────────────────────────────────────────────────
function getAssignMode() {
    return document.getElementById('atab-group').classList.contains('active') ? 'group' : 'individual';
}
function showAssignError(msg) {
    var box = document.getElementById('assignErrorBox');
    if (box) { box.textContent = '⚠️ ' + msg; box.style.display = 'block'; }
}
function clearAssignError() {
    var box = document.getElementById('assignErrorBox');
    if (box) box.style.display = 'none';
}

document.querySelector('form').addEventListener('submit', function(e) {
    var mode = getAssignMode();
    var valid = false;

    if (mode === 'group') {
        var gs = document.getElementById('groupSelect');
        valid = gs && gs.value !== '';
        if (!valid) showAssignError('Please select a group before creating the project.');
    } else {
        var checked = document.querySelectorAll('input[name="student_ids[]"]:checked');
        valid = checked.length > 0;
        if (!valid) showAssignError('Please select at least one student before creating the project.');
    }

    if (!valid) {
        e.preventDefault();
        document.getElementById('assignErrorBox').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Clear error when user makes a selection
document.addEventListener('change', function(e) {
    if (e.target && (e.target.id === 'groupSelect' || e.target.name === 'student_ids[]')) {
        clearAssignError();
    }
});

<?php if(old('student_ids')): ?>
    switchAssign('individual');
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\ggzz\resources\views/teacher/projects/create.blade.php ENDPATH**/ ?>