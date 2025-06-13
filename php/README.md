# School Attendance and Teaching Evidence Management System

A web-based system for managing school attendance and teaching evidence with location verification.

## Features

- **Teacher Module**
  - Secure login system
  - Mark student attendance with GPS verification
  - Submit teaching evidence (photos/screenshots)
  - View class-specific student lists
  - Track attendance history

- **Admin Module**
  - Manage teachers and class assignments
  - Manage students and class enrollments
  - View attendance reports
  - Monitor teaching evidence submissions
  - Configure school location settings

- **Security Features**
  - Location-based verification
  - Secure session management
  - CSRF protection
  - Input sanitization
  - Password hashing

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Browser with Geolocation API support

## Installation

1. **Database Setup**
   ```sql
   -- Create database
   CREATE DATABASE attendance_db;
   USE attendance_db;

   -- Run the provided SQL schema
   -- (See database_schema.sql)
   ```

2. **Configuration**
   - Copy the project files to your web server
   - Update database credentials in `config/config.php`
   - Set school coordinates in admin panel
   - Ensure write permissions for uploads directory

3. **Web Server Configuration**
   - Enable mod_rewrite
   - Configure virtual host (if needed)
   - Ensure .htaccess is enabled

## Directory Structure

```
php/
├── config/
│   ├── config.php
│   └── Database.php
├── controllers/
│   ├── TeacherController.php
│   └── AdminController.php
├── models/
│   ├── Teacher.php
│   ├── Student.php
│   ├── ClassModel.php
│   ├── Attendance.php
│   └── Evidence.php
├── views/
│   ├── login.php
│   ├── logout.php
│   ├── teacher/
│   │   └── dashboard.php
│   └── admin/
│       └── dashboard.php
├── assets/
│   ├── uploads/
│   ├── css/
│   └── js/
├── .htaccess
└── index.php
```

## Usage

1. **Admin Access**
   - Default admin credentials:
     - Email: admin@school.com
     - Password: (set during installation)
   - Configure school settings first
   - Add classes, teachers, and students

2. **Teacher Access**
   - Teachers log in with credentials provided by admin
   - Must be within school radius to mark attendance
   - Can submit teaching evidence with photos

3. **Location Verification**
   - System uses HTML5 Geolocation API
   - Checks if user is within allowed radius
   - Stores location data with attendance records

## Security Considerations

- All passwords are hashed using PHP's password_hash()
- CSRF tokens protect against cross-site request forgery
- Input sanitization prevents SQL injection
- Session security measures implemented
- File upload restrictions for evidence photos

## Browser Support

- Chrome 50+
- Firefox 50+
- Safari 10+
- Edge 79+
- Opera 37+

## Development

- Uses Bootstrap 5 for responsive design
- Modern PHP practices (OOP, MVC structure)
- PDO for database operations
- Prepared statements for queries

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## Support

For support, email support@schoolattendance.com or create an issue in the repository.
