document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle with Overlay
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    console.log('App.js - Sidebar elements:', { toggleBtn, sidebar, sidebarOverlay });

    if (toggleBtn && sidebar && sidebarOverlay) {
        // Toggle sidebar and overlay
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            console.log('App.js - Toggle clicked');
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function () {
            console.log('App.js - Overlay clicked');
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking nav links on mobile
        const navLinks = sidebar.querySelectorAll('.sidebar-nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    } else {
        console.error('App.js - Sidebar elements not found!');
    }
});
