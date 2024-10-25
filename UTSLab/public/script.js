document.addEventListener("DOMContentLoaded", function () {
    const addTaskButtons = document.querySelectorAll('.btn-add-task');
    const priorityInput = document.getElementById('priority');

    addTaskButtons.forEach(button => {
        button.addEventListener('click', function () {
            priorityInput.value = this.dataset.priority;
        });
    });

    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            this.form.submit(); 
        });
    });

    const deleteButtons = document.querySelectorAll('form .btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            if (!confirm('Are you sure you want to delete this?')) {
                event.preventDefault(); 
            }
        });
    });
});
