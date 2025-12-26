document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickInsideToggle = toggleBtn ? toggleBtn.contains(event.target) : false;

        if (window.innerWidth <= 768 && !isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
});
