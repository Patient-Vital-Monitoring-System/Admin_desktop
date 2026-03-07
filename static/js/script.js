// Sidebar navigation toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle nav groups
    const navToggles = document.querySelectorAll('.nav-group-toggle');
    
    navToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const navGroup = this.parentElement;
            navGroup.classList.toggle('active');
        });
    });
});

// Function to check authentication (call this on each page)
function checkAuth() {
    if (!localStorage.getItem('vw_token')) {
        window.location.href = '../login.html';
        return false;
    }
    return true;
}

// Logout function
function logout() {
    localStorage.removeItem('vw_token');
    localStorage.removeItem('vw_user');
    window.location.href = '../login.html';
}
