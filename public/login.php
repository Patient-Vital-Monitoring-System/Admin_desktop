<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VitalWear — Login</title>
<link rel="stylesheet" href="css/vitalwear.css">
</head>
<body>

<!-- LOGIN -->
<div id="login-screen">
  <div class="login-card">
    <div class="login-logo">
      <div class="pulse-ring">
        <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <div class="brand">Vital<span>Wear</span></div>
      <div class="sub">EMERGENCY RESPONSE SYSTEM</div>
    </div>

    <div class="form-group">
      <label>EMAIL ADDRESS</label>
      <input type="email" id="login-email" placeholder="Enter your email" value="admin@vitalwear.com">
    </div>
    <div class="form-group">
      <label>PASSWORD</label>
      <input type="password" id="login-pass" placeholder="Enter password" value="••••••••">
    </div>
    <button class="btn-primary" onclick="doLogin()">ACCESS SYSTEM</button>
  </div>
</div>

<script>
const currentRole = 'admin';
// no demo credentials are prefilled

async function doLogin() {
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-pass').value;

  try {
    const res = await fetch('../api/auth/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, role: currentRole })
    });
    if (res.status === 403) {
      console.error('login request forbidden (403)');
      alert('Server denied access (403 forbidden) when attempting login.');
      return;
    }
    if (!res.ok) {
      console.error('login http status', res.status);
      alert('Login request failed with status ' + res.status);
      return;
    }
    const json = await res.json();
    if (!json.success) {
      // show any error details for debugging
      console.error('login error', json);
      alert((json.error||'Login failed') + (json.details? '\nDetails: '+json.details : ''));
      return;
    }
    // store session info then navigate to dashboard
    localStorage.setItem('vw_token', json.data.token);
    localStorage.setItem('vw_user', JSON.stringify(json.data.user));
    // redirect to pages/index.php
    window.location.href = 'pages/index.php';
  } catch (err) {
    console.error('fetch error', err);
    alert('Unable to reach server. Make sure you are running via http://localhost/Admin_desktop/public/login.php');
  }
}
</script>
</body>
</html>