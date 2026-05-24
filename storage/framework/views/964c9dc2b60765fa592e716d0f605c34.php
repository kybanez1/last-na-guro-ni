<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/pages/teacher-project-show.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="wrap">

    <?php if(session('success')): ?>
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
            ✅ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="margin-bottom:1rem;padding:1rem;border-radius:10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            ❌ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php $isGroupProject = !empty($project->group_id); ?>

    
    <div class="card">
        <div class="header">
            <div>📂 <?php echo e($project->title); ?></div>
            <div>
                <span class="badge <?php echo e(in_array($project->status, ['ongoing','published','active']) ? 'active' : 'closed'); ?>">
                    <?php echo e(ucfirst($project->status)); ?>

                </span>
                <?php if($isGroupProject): ?>
                    <span style="margin-left:.5rem;background:#eef2ff;color:#4f46e5;padding:.25rem .75rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                        👥 Group Project
                    </span>
                <?php else: ?>
                    <span style="margin-left:.5rem;background:#f0fdf4;color:#15803d;padding:.25rem .75rem;border-radius:999px;font-size:.75rem;font-weight:700;">
                        🧑 Individual Project
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="body">
            <div class="grid">
                <div>
                    <div class="label">Description</div>
                    <div class="value"><?php echo e($project->description ?: 'No description provided.'); ?></div>
                </div>
                <div>
                    <div class="label"><?php echo e($isGroupProject ? 'Group' : 'Assignment Type'); ?></div>
                    <div class="value">
                        <?php if($isGroupProject): ?>
                            <?php echo e($project->group->name ?? '—'); ?>

                        <?php else: ?>
                            Individual / Per Student
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($isGroupProject): ?>
                <div>
                    <div class="label">Subject</div>
                    <div class="value">
                        <?php if($project->subject): ?>
                            <span style="color:#7c3aed;font-weight:600;">📚 <?php echo e($project->subject); ?></span>
                        <?php else: ?>
                            <span style="color:#9ca3af;">—</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div>
                    <div class="label">Subject</div>
                    <div class="value">
                        <?php if($project->subject): ?>
                            <span style="color:#7c3aed;font-weight:600;">📚 <?php echo e($project->subject); ?></span>
                        <?php else: ?>
                            <span style="color:#9ca3af;">—</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div>
                    <div class="label">Requirements</div>
                    <div class="value"><?php echo e($project->requirements ?: 'No requirements provided.'); ?></div>
                </div>
                <?php if($project->instruction_file || $project->instruction_link): ?>
                <div style="grid-column:1/-1;">
                    <div class="label">Instruction File / Link</div>
                    <div class="value" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                        <?php if($project->instruction_file): ?>
                            <span style="font-weight:600;color:#4338ca;">
                                📎 <?php echo e($project->instruction_file_name ?? basename($project->instruction_file)); ?>

                            </span>
                            <a href="<?php echo e(route('files.instruction', $project->id)); ?>"
                               target="_blank"
                               style="padding:.3rem .8rem;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;">
                                👁 View
                            </a>
                            <a href="<?php echo e(route('files.instruction.download', $project->id)); ?>"
                               style="padding:.3rem .8rem;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;">
                                ⬇ Download
                            </a>
                        <?php elseif($project->instruction_link): ?>
                            <a href="<?php echo e($project->instruction_link); ?>"
                               target="_blank"
                               style="padding:.3rem .8rem;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;">
                                🔗 Open Link
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div>
                    <div class="label">Max Score</div>
                    <div class="value"><?php echo e($project->max_score); ?></div>
                </div>
                <div>
                    <div class="label">Teacher</div>
                    <div class="value"><?php echo e($project->teacher->name ?? '—'); ?></div>
                </div>
                <div>
                    <div class="label">Start Date</div>
                    <div class="value">
                        <?php echo e($project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y h:i A') : '—'); ?>

                    </div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value">
                        <?php echo e($project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('M d, Y h:i A') : '—'); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="body">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?php echo e($submittedCount ?? 0); ?></div>
                    <div class="stat-label">Submitted</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo e($gradedCount ?? 0); ?></div>
                    <div class="stat-label">Graded</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo e($project->tasks->count()); ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="body">
            <div class="btn-row">
                <a href="<?php echo e(route('teacher.projects.edit', $project->id)); ?>" class="btn btn-primary">✏️ Edit Project</a>
                <?php if($isGroupProject): ?>
                    
                    <a href="<?php echo e(route('teacher.grades.project', $project->id)); ?>" class="btn btn-outline">⭐ Grade Group</a>
                <?php else: ?>
                    <a href="<?php echo e(route('teacher.grades.project', $project->id)); ?>" class="btn btn-outline">⭐ View Grades</a>
                <?php endif; ?>
                <a href="<?php echo e(route('teacher.projects.index')); ?>" class="btn btn-outline">← Back</a>
            </div>
        </div>
    </div>

    
    <?php if(!$isGroupProject): ?>
    <div class="card">
        <div class="header">🧑‍🎓 Assigned Students</div>
        <div class="table-wrap">
            <?php
                $assignedStudents = $project->assignments()->get();
            ?>
            <?php if($assignedStudents->isEmpty()): ?>
                <div style="padding:2rem;text-align:center;color:#9ca3af;">No students assigned yet.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Graded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $assignedStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignedStudent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $pivot = $assignedStudent->pivot;
                        $pStatus = $pivot->assignment_status ?? 'assigned';
                    ?>
                    <tr>
                        <td>
                            <div class="student-name"><?php echo e($assignedStudent->name); ?></div>
                            <?php if($assignedStudent->student_id): ?>
                                <div style="font-size:.72rem;color:#6b7280;">🆔 <?php echo e($assignedStudent->student_id); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-pill
                                <?php echo e($pStatus === 'graded' ? 'status-graded' : ($pStatus === 'submitted' ? 'status-submitted' : 'status-pending')); ?>">
                                <?php echo e(ucfirst($pStatus)); ?>

                            </span>
                            <?php
                                $studentLateSub = \App\Models\ProjectSubmission::where('project_id', $project->id)
                                    ->where('student_id', $assignedStudent->id)
                                    ->whereNull('task_id')
                                    ->latest()->first();
                            ?>
                            <?php if($studentLateSub && $studentLateSub->is_late): ?>
                                <span style="display:inline-block;margin-left:4px;padding:1px 7px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:999px;font-size:.68rem;font-weight:700;">
                                    🕐 Late
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($pivot && $pivot->score !== null): ?>
                                <strong><?php echo e($pivot->score); ?></strong> / <?php echo e($project->max_score); ?>

                            <?php else: ?> —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($pivot && $pivot->graded_at ? \Carbon\Carbon::parse($pivot->graded_at)->format('M d, Y') : '—'); ?>

                        </td>
                        <td>
                            
                            <a href="<?php echo e(route('teacher.grades.individual.edit', [$project->id, $assignedStudent->id])); ?>"
                               class="btn btn-primary" style="font-size:.78rem;padding:.45rem .8rem;">
                                ⭐ <?php echo e($pStatus === 'graded' ? 'Update Grade' : 'Grade'); ?>

                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($isGroupProject && $project->group): ?>
    <div class="card">
        <div class="header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
            <span>👥 Group Members — <?php echo e($project->group->name); ?></span>
            <a href="<?php echo e(route('teacher.grades.project', $project->id)); ?>"
               class="btn btn-primary" style="font-size:.82rem;padding:.5rem 1.1rem;">
                ⭐ Grade This Group
            </a>
        </div>
        <div class="table-wrap">
            <?php
                $groupMembers = $project->group->students()->orderBy('name')->get();
            ?>
            <?php if($groupMembers->isEmpty()): ?>
                <div style="padding:2rem;text-align:center;color:#9ca3af;">No members in this group yet.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Student ID</th>
                        <th>Joined</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $groupMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $memberSub = \App\Models\ProjectSubmission::where('project_id', $project->id)
                            ->where('student_id', $member->id)
                            ->whereNull('task_id')
                            ->latest()->first();
                        $memberScore = $memberSub?->score;
                    ?>
                    <tr>
                        <td>
                            <div class="student-name"><?php echo e($member->name); ?></div>
                        </td>
                        <td><?php echo e($member->student_id ?? '—'); ?></td>
                        <td>
                            <?php if($member->pivot->is_joined): ?>
                                <span style="color:#16a34a;font-size:.8rem;font-weight:600;">✅ Joined</span>
                            <?php else: ?>
                                <span style="color:#9ca3af;font-size:.8rem;">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($memberScore !== null): ?>
                                <strong><?php echo e($memberScore); ?></strong> / <?php echo e($project->max_score); ?>

                            <?php else: ?>
                                <span style="color:#9ca3af;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="card">
        <div class="header">📋 Project Tasks</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $project->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><div class="student-name"><?php echo e($task->title); ?></div></td>
                            <td><?php echo e($task->description ?? '—'); ?></td>
                            <td>
                                <?php if($task->due_date): ?>
                                    <?php echo e(\Carbon\Carbon::parse($task->due_date)->format('M d, Y h:i A')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3"><div class="empty-box">No tasks added yet.</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="card">
        <div class="header">🧑‍🎓 Student Submissions</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>File</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $submissions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $submission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="student-name"><?php echo e($submission->student->name ?? 'Unknown Student'); ?></div>
                                <?php if(isset($submission->student->student_id)): ?>
                                    <div style="font-size:.72rem;color:#6b7280;margin-top:2px;">
                                        ID: <?php echo e($submission->student->student_id); ?>

                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($submission->task->title ?? '📄 General Submission'); ?></td>
                            <td>
                                <span class="status-pill
                                    <?php echo e($submission->status === 'graded'
                                        ? 'status-graded'
                                        : (in_array($submission->status, ['submitted','reviewed'])
                                            ? 'status-submitted'
                                            : 'status-pending')); ?>">
                                    <?php echo e(ucfirst($submission->status)); ?>

                                </span>
                            </td>
                            <td>
                                <?php echo e($submission->submitted_at
                                    ? $submission->submitted_at->format('M d, Y h:i A')
                                    : '—'); ?>

                                <?php if($submission->is_late): ?>
                                    <span style="display:inline-block;margin-left:4px;padding:1px 7px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:999px;font-size:.7rem;font-weight:700;">
                                        🕐 Late
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($submission->file_path): ?>
                                    <a href="<?php echo e(route('files.submission', $submission->id)); ?>"
                                       target="_blank" class="file-link">📎 View</a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('teacher.submissions.show', $submission->id)); ?>"
                                   class="btn btn-outline" style="font-size:.78rem;padding:.45rem .8rem;">
                                    👁 View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6"><div class="empty-box">No submissions yet.</div></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding:1rem 1.5rem;">
            <?php echo e($submissions->links()); ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\ggzz\resources\views/teacher/projects/show.blade.php ENDPATH**/ ?>