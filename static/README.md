# VitalWear Admin - Static Version

This is a static HTML version of the VitalWear Patient Vital Monitoring Admin Dashboard, optimized for deployment on GitHub Pages.

## Features

- Dashboard - Real-time metrics and analytics
- User Management - Staff directory, user status, and management
- Incident Monitoring - Track and manage incidents
- Device Overview - Monitor device status and assignments
- Vital Statistics - Analytics from patient vital signs
- Audit Log - System activity tracking

## Demo Credentials

```
Email: admin@vitalwear.com
Password: admin123
```

## Deployment to GitHub Pages

1. Create a new repository on GitHub
2. Push this static folder to the repository
3. Go to Repository Settings - Pages
4. Select main branch and click Save
5. Your site will be available at https://yourusername.github.io/repository-name/

## Local Development (Static)

Simply open static/login.html in your browser, or use a simple HTTP server:

```bash
# Python 3
python -m http.server 8000

# Then open http://localhost:8000/login.html
```

---

## For PHP Version (Local Server Required)

If you want to use the full PHP version with database functionality, follow these steps:

### Prerequisites

1. XAMPP (or any local PHP server with MySQL)
   - Download from: https://www.apachefriends.org/
   - Install and start Apache and MySQL

2. Create the Database
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create a new database named: vitalwear

### Installation Steps

1. Start XAMPP Services
   - Open XAMPP Control Panel
   - Click Start for Apache
   - Click Start for MySQL

2. Import Database
   - Go to http://localhost/phpmyadmin
   - Select the vitalwear database
   - Click Import tab
   - Import the static/database.sql file (included in this folder)

3. Configure Database Connection
   - Edit api/auth/config.php
   - Update the credentials if needed:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'vitalwear';
   $DB_USER = 'root';
   $DB_PASS = ''; // leave empty for default XAMPP
   ```

4. Access the Application
   - Open your browser
   - Go to: http://localhost/Admin_desktop/public/login.php
   - Login with credentials below

### Login Credentials (PHP Version)

```
Admin:    admin1@vitalwear.com / admin123
Manager:  ops@vitalwear.com / manager123
Responder: juan@responder.com / resp123
Rescuer:  maria@rescuer.com / resc123
```

### Project Structure (PHP Version)

```
Admin_desktop/
├── api/                    # PHP API endpoints
│   └── auth/              # Authentication APIs
├── database/              # Database scripts
├── public/                # Public web files
│   ├── pages/             # Application pages
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── index.php              # Entry point (redirects to login)
└── package.json          # Dependencies
```

### Common Issues

- 404 Error: Make sure Apache is running and configured correctly
- Database Error: Check that MySQL is running and database exists
- Login Failed: Verify database has users with correct passwords

## Notes

- This is a static demo version with mock data - for production use with real data, use the PHP version
- No backend/database required for static version
- Session is managed via localStorage
- For the full PHP version with database, use the files in the parent directory

## License

ISC

