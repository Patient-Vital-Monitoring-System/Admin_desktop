// Dropdown Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Get all nav group toggles
    const toggles = document.querySelectorAll('.nav-group-toggle');
    
    // Add click handlers for dropdown toggles
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const navGroup = this.closest('.nav-group');
            navGroup.classList.toggle('active');
        });
    });
    
    // Auto-expand dropdown containing active link
    const activeLink = document.querySelector('.sidebar-nav .nav-link.active');
    if (activeLink) {
        const parentGroup = activeLink.closest('.nav-group');
        if (parentGroup) {
            parentGroup.classList.add('active');
        }
    }
    
    // Close dropdown when a link is clicked
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Optionally keep dropdown open or close it
            // navGroup.classList.remove('active'); // Uncomment to close on click
        });
    });
});
