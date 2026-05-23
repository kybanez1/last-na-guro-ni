/* teacher/projects/update_project.blade.php */
/* taskIndex is initialized inline from the blade template */

function updateEmptyState() {
    var wrapper   = document.getElementById('task-wrapper');
    var emptyMsg  = document.getElementById('task-empty');
    var addBtn    = document.getElementById('add-task-btn');
    if (!wrapper || !emptyMsg) return;
    var hasTasks = wrapper.querySelectorAll('.task-card').length > 0;
    emptyMsg.style.display = hasTasks ? 'none' : 'block';
    if (addBtn) addBtn.textContent = hasTasks ? '➕ Add Another Task' : '➕ Add Task';
}

function removeTask(btn) {
    btn.closest('.task-card').remove();
    updateEmptyState();
}

document.addEventListener('DOMContentLoaded', function () {
    // Sync empty state on load (in case there are existing tasks)
    updateEmptyState();

    var addBtn = document.getElementById('add-task-btn');
    if (!addBtn) return;

    addBtn.addEventListener('click', function () {
        var wrapper = document.getElementById('task-wrapper');

        var taskHTML = `
            <div class="task-card" style="position:relative;">

                <button
                    type="button"
                    class="remove-btn"
                    onclick="removeTask(this)"
                    title="Remove task"
                >
                    ✕
                </button>

                <div class="field">
                    <label>Task Title</label>
                    <input
                        type="text"
                        name="tasks[${taskIndex}][title]"
                        placeholder="Enter task title"
                    >
                </div>

                <div class="field">
                    <label>Task Description</label>
                    <textarea
                        name="tasks[${taskIndex}][description]"
                        rows="3"
                        placeholder="Enter task details"
                    ></textarea>
                </div>

                <div class="field">
                    <label>Task Due Date</label>
                    <input
                        type="datetime-local"
                        name="tasks[${taskIndex}][due_date]"
                    >
                </div>

            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', taskHTML);
        taskIndex++;
        updateEmptyState();
    });
});
