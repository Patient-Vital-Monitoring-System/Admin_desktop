<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Patient Vital Monitoring Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>
    <nav class="navbar-top">
        <h2 class="navbar-brand">Patient Vitals Rescue</h2>
    </nav>

    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px 20px;">
        <div style="text-align: center; max-width: 500px;">
            <h1 id="logout-message">Logging out...</h1>
            <p id="logout-status">Please wait while we log you out securely.</p>
            <div style="margin-top: 24px; border: 3px solid rgba(0, 229, 255, 0.2); border-top: 3px solid var(--accent); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 24px auto 0;"></div>
            <style>
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logout functionality
        function performLogout() {
            
            // Clear session storage
            sessionStorage.clear();
            
            // Clear local storage (including auth token)
            localStorage.removeItem('vw_token');
            localStorage.removeItem('vw_user');
            localStorage.removeItem('userSession');
            localStorage.removeItem('authToken');
            
            // Clear all cookies
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });

            // Update UI with success message
            const message = document.getElementById('logout-message');
            const status = document.getElementById('logout-status');

            message.textContent = 'You have been logged out successfully!';
            status.textContent = 'Redirecting to login page...';

            // Redirect to login page after 2 seconds
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 2000);
        }

        // Run logout on page load
        window.addEventListener('DOMContentLoaded', performLogout);
    </script>
</body>
</html>
