const { app, BrowserWindow } = require('electron');
const path = require('path');

function createWindow() {
  const win = new BrowserWindow({
    width: 2000,
    height: 1200,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  // Since the application uses PHP, it must be served via a web server (XAMPP).
  // Loading PHP files directly with loadFile() results in a white page because PHP isn't executed.
  // Ensure XAMPP (Apache & MySQL) is running before starting the app.
  win.loadURL('http://localhost/Admin_desktop/public/login.php');
  // win.webContents.openDevTools(); // Uncomment this line to debug errors in the console
}

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
