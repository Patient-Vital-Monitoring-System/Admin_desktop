<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VitalWear — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0a0e1a;
    --surface: #111827;
    --surface2: #1a2235;
    --border: #1f2d45;
    --accent: #00e5ff;
    --accent2: #ff4d6d;
    --accent3: #39ff14;
    --text: #e2e8f0;
    --muted: #64748b;
    --warn: #f59e0b;
    --danger: #ef4444;
    --success: #10b981;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Syne',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; }
  
  /* LOGIN */
  #login-screen {
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    background: radial-gradient(ellipse at 20% 50%, #0a1628 0%, #0a0e1a 60%);
    position:relative; overflow:hidden;
  }
  #login-screen::before {
    content:''; position:absolute; inset:0;
    background: repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(0,229,255,0.03) 40px, rgba(0,229,255,0.03) 41px),
                repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(0,229,255,0.03) 40px, rgba(0,229,255,0.03) 41px);
  }
  .login-card {
    background: var(--surface); border:1px solid var(--border); border-radius:16px;
    padding:48px 40px; width:420px; position:relative; z-index:1;
    box-shadow: 0 0 60px rgba(0,229,255,0.08), 0 25px 50px rgba(0,0,0,0.5);
  }
  .login-logo { text-align:center; margin-bottom:32px; }
  .login-logo .brand { font-size:32px; font-weight:800; letter-spacing:-1px; color:#fff; }
  .login-logo .brand span { color:var(--accent); }
  .login-logo .sub { font-size:11px; letter-spacing:3px; color:var(--muted); margin-top:4px; font-family:'Space Mono',monospace; }
  .pulse-ring {
    width:64px; height:64px; border-radius:50%; background:rgba(0,229,255,0.1);
    border:2px solid var(--accent); margin:0 auto 16px;
    display:flex; align-items:center; justify-content:center;
    animation: pulse 2s ease-in-out infinite;
  }
  .pulse-ring svg { width:28px; height:28px; stroke:var(--accent); fill:none; stroke-width:2; }
  @keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(0,229,255,0.3)} 50%{box-shadow:0 0 0 12px rgba(0,229,255,0)} }
  
  .role-tabs { display:flex; gap:8px; margin-bottom:24px; background:var(--surface2); border-radius:10px; padding:4px; }
  .role-tab {
    flex:1; padding:8px 4px; border:none; background:transparent; color:var(--muted);
    font-family:'Syne',sans-serif; font-size:12px; font-weight:600; cursor:pointer;
    border-radius:7px; transition:all .2s; letter-spacing:.5px;
  }
  .role-tab.active { background:var(--accent); color:#000; }
  
  .form-group { margin-bottom:16px; }
  .form-group label { display:block; font-size:11px; letter-spacing:1.5px; color:var(--muted); margin-bottom:6px; font-family:'Space Mono',monospace; }
  .form-group input {
    width:100%; padding:12px 16px; background:var(--surface2); border:1px solid var(--border);
    border-radius:8px; color:var(--text); font-family:'Syne',sans-serif; font-size:14px;
    transition:border-color .2s; outline:none;
  }
  .form-group input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(0,229,255,0.1); }
  
  .btn-primary {
    width:100%; padding:14px; background:var(--accent); color:#000; border:none;
    border-radius:8px; font-family:'Syne',sans-serif; font-weight:700; font-size:15px;
    cursor:pointer; transition:all .2s; letter-spacing:.5px;
  }
  .btn-primary:hover { background:#33eeff; transform:translateY(-1px); box-shadow:0 8px 20px rgba(0,229,255,0.3); }
  .hint { font-size:11px; color:var(--muted); text-align:center; margin-top:16px; font-family:'Space Mono',monospace; }
  .hint span { color:var(--accent); }
</style>
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
    <div class="hint">Demo credentials: <span id="login-hint">auto‑filled above</span></div>
  </div>
</div>

<script>
const currentRole = 'admin';
// fill demo credentials for admin
const adminEmail = 'admin@vitalwear.com';
const adminPwd = 'password';
document.getElementById('login-email').value = adminEmail;
document.getElementById('login-pass').value = adminPwd;
const hint = document.getElementById('login-hint');
if (hint) hint.textContent = `${adminEmail} / ${adminPwd}`;

async function doLogin() {
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-pass').value;

  try {
    const res = await fetch('api/auth/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, role: currentRole })
    });
    const json = await res.json();
    if (!json.success) {
      // show any error details for debugging
      console.error('login error', json);
      alert((json.error||'Login failed') + (json.details? '\nDetails: '+json.details : ''));
      return;
    }
    // this demo page just redirects on success
    window.location.href = 'index.html';
  } catch (err) {
    console.error('fetch error', err);
    alert('Unable to reach server. Make sure you are running via http://localhost/Admin_desktop/login.php');
  }
}
</script>
</body>
</html>