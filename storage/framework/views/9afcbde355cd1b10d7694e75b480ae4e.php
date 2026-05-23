<link rel="stylesheet" href="<?php echo e(asset('assets/css/pages/layout-sidebar.css')); ?>">

<?php if(auth()->guard()->check()): ?>
<?php $role = auth()->user()->role; ?>


<script>document.body.classList.add('<?php echo e($role); ?>-sidebar');</script>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    
    <div class="app-brand">
        <a href="<?php echo e($role === 'teacher' ? route('teacher.dashboard') : route('student.dashboard')); ?>"
           class="app-brand-link text-decoration-none">
            <div class="brand-box">
                <div class="brand-logo">P</div>
                <div class="brand-text">
                    <span class="brand-title">PMS</span>
                    <span class="brand-sub">Project Portal</span>
                </div>
            </div>
        </a>
    </div>

    
    <?php if($role === 'student'): ?>
    <div class="student-identity-strip">
        <div class="strip-avatar"><?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?></div>
        <div class="strip-info">
            <div class="strip-name"><?php echo e(auth()->user()->name); ?></div>
            <div class="strip-role">🎓 Student</div>
        </div>
        <span class="strip-pulse"></span>
    </div>
    <?php endif; ?>

    
    <ul class="menu-inner py-1">

        
        <li class="menu-item <?php echo e(request()->routeIs('teacher.dashboard') || request()->routeIs('student.dashboard') ? 'active' : ''); ?>">
            <a href="<?php echo e($role === 'teacher' ? route('teacher.dashboard') : route('student.dashboard')); ?>" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-alt"></i>
                <div>Dashboard</div>
            </a>
        </li>

        
        <?php if($role === 'teacher'): ?>

            <li class="menu-section">Teacher Panel</li>

            <li class="menu-item <?php echo e(request()->routeIs('teacher.projects.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('teacher.projects.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div>Projects</div>
                </a>
            </li>

            <li class="menu-item <?php echo e(request()->routeIs('teacher.groups.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('teacher.groups.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div>Groups</div>
                </a>
            </li>

            <li class="menu-item <?php echo e(request()->routeIs('teacher.sections.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('teacher.sections.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chalkboard"></i>
                    <div>Sections</div>
                </a>
            </li>

            <li class="menu-item <?php echo e(request()->routeIs('teacher.students.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('teacher.students.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-circle"></i>
                    <div>Students</div>
                </a>
            </li>

            <li class="menu-item <?php echo e(request()->routeIs('teacher.graded.index') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('teacher.graded.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-check-circle"></i>
                    <div>Graded Projects</div>
                    <?php
                        $gradedCount = \App\Models\ProjectSubmission::where('status','graded')
                            ->whereHas('project', fn($q) => $q->where('teacher_id', auth()->id()))
                            ->count();
                    ?>
                    <?php if($gradedCount > 0): ?>
                        <span class="menu-badge"><?php echo e($gradedCount); ?></span>
                    <?php endif; ?>
                </a>
            </li>

        <?php endif; ?>

        
        <?php if($role === 'student'): ?>

            <li class="menu-section">My Academics</li>

            <li class="menu-item <?php echo e(request()->routeIs('student.projects.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('student.projects.index')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder-open"></i>
                    <div>My Projects</div>
                </a>
            </li>

            <li class="menu-item <?php echo e(request()->routeIs('student.grades') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('student.grades')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-award"></i>
                    <div>My Grades</div>
                </a>
            </li>

            <li class="menu-section">My Community</li>

            <li class="menu-item <?php echo e(request()->routeIs('student.groups.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('student.groups.join')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div>My Groups</div>
                </a>
            </li>

            <?php if(auth()->user()->isStudent()): ?>
            <li class="menu-item <?php echo e(request()->routeIs('student.sections.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('student.sections.join')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chalkboard"></i>
                    <div>My Sections</div>
                </a>
            </li>
            <?php endif; ?>

            <li class="menu-item <?php echo e(request()->routeIs('student.teacher.*') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('student.teacher.join')); ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div>My Teacher</div>
                </a>
            </li>

        <?php endif; ?>

        
        <li class="menu-section">Account</li>

        <li class="menu-item">
            <form method="POST"
                  action="<?php echo e($role === 'teacher' ? route('teacher.logout') : route('student.logout')); ?>">
                <?php echo csrf_field(); ?>
                <a href="#" class="menu-link logout-link"
                   onclick="event.preventDefault();this.closest('form').submit();">
                    <i class="menu-icon tf-icons bx bx-log-out"></i>
                    <div>Logout</div>
                </a>
            </form>
        </li>

    </ul>

</aside>
<?php endif; ?>
<?php /**PATH C:\wamp64\www\ggzz\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>