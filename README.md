# Rwanda Driving License (RDL) Admin System

A comprehensive web application for managing driving license candidates and exam results in Rwanda.

## Features

### 🎯 Core Functionality
- **Candidate Management**: Add, edit, view, and delete driving license candidates
- **Exam Results**: Record and track exam scores with automatic pass/fail determination
- **Reporting**: Advanced filtering and search capabilities for exam reports
- **Admin Management**: Secure admin account creation and profile management

### 🎨 Modern UI/UX
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Professional Interface**: Clean, modern design with consistent styling
- **Interactive Elements**: Smooth animations, hover effects, and visual feedback
- **Accessibility**: WCAG-compliant design with proper focus states and keyboard navigation

### 🔒 Security Features
- **Session Management**: Secure login/logout functionality
- **Input Validation**: Both client-side and server-side validation
- **SQL Injection Prevention**: Prepared statements throughout the application
- **Password Security**: Strong password requirements and secure hashing

### 📊 Dashboard Analytics
- **Statistics Overview**: Real-time candidate and exam statistics
- **Quick Actions**: Easy access to frequently used functions
- **Visual Data**: Interactive charts and counters

## Technology Stack

- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: HTML5, CSS3 (with CSS Grid & Flexbox), JavaScript (ES6+)
- **Icons**: Font Awesome 5
- **Fonts**: Google Fonts (Roboto)

## Installation

### Prerequisites
- XAMPP, WAMP, or similar PHP development environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Setup Steps

1. **Clone/Download** the application to your web server directory:
   ```
   c:\xampp\htdocs\kevin\
   ```

2. **Database Setup**:
   - Launch XAMPP Control Panel and start Apache and MySQL services
   - Open phpMyAdmin by visiting http://localhost/phpmyadmin
   - Create a new database named `RDL`
   - Import the `rdl.sql` file into your MySQL database
   - Update database connection settings in `config.php` if needed

3. **Configuration**:
   - Verify database credentials in `config.php`
   - Ensure proper file permissions are set

4. **Access the Application**:
   - Navigate to `http://localhost/kevin/` in your web browser
   - **For first-time setup**: Go to `http://localhost/kevin/register.php` to create your admin account
   - **Existing users**: Use the login page with your credentials

## User Registration & Authentication

The application now supports both public registration and internal admin creation:

### 🆕 Public Registration
Anyone can create an admin account through the registration system:

1. **Visit Registration Page**: Go to `http://localhost/kevin/register.php`
2. **Fill Registration Form**:
   - **Admin Name**: At least 3 characters (letters, numbers, underscores only)
   - **Password**: Minimum 8 characters with uppercase, lowercase, and numbers
   - **Confirm Password**: Must match the password exactly
3. **Real-time Validation**: Form provides instant feedback on password strength and requirements
4. **Account Creation**: Click "Create Account" to register
5. **Login**: After successful registration, use the login page with your new credentials

### 🔐 Password Requirements
- Minimum 8 characters in length
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- Password strength indicator shows real-time feedback

### 👨‍💼 Internal Admin Creation
Existing admins can create additional admin accounts:
- Access through "Create Admin" menu (requires login)
- Separate from public registration system
- Maintains administrative control over account creation

## File Structure

```
kevin/
├── css/
│   └── style.css                 # Main stylesheet with modern design
├── js/
│   └── main.js                   # Client-side functionality
├── templates/
│   ├── header.php               # Common header template
│   ├── sidebar.php              # Navigation sidebar
│   └── footer.php               # Common footer template
├── add_candidate.php            # Add new candidate form
├── config.php                   # Database configuration
├── create_admin.php             # Admin registration
├── dashboard.php                # Main dashboard with statistics
├── delete_candidate.php         # Candidate deletion handler
├── edit_candidate.php           # Edit candidate information
├── functions.php                # Common utility functions
├── index.php                    # Login page
├── logout.php                   # Logout handler
├── profile.php                  # Admin profile management
├── report.php                   # Comprehensive exam reports
├── view_candidates.php          # Candidate listing with search
└── rdl.sql                      # Database schema
```

## Usage Guide

### Admin Login
1. Access the application through your web browser
2. Enter your admin credentials
3. Click "Login" to access the dashboard

### Managing Candidates
- **Add**: Use "Add Candidate" to register new candidates
- **View**: Browse all candidates with search and filter options
- **Edit**: Click edit icon to modify candidate information
- **Delete**: Use delete icon with confirmation for removal

### Exam Reports
- Access detailed exam reports with multiple filter options:
  - Search by name or National ID
  - Filter by exam category (A, B, C, D, E)
  - Filter by result (Passed/Failed)
  - Date range filtering
  - Export capabilities

### Admin Management
- Create additional admin accounts through "Create Admin"
- Update password and profile information in "Profile"

## Key Features

### Modern UI/UX Design
- **Responsive Layout**: Mobile-first design that works on all devices
- **Professional Styling**: Clean, modern interface with consistent branding
- **Interactive Elements**: Smooth animations and hover effects
- **Accessibility**: WCAG-compliant with proper focus states

### Security Features
- **Password Hashing**: Secure password storage using PHP's `password_hash()`
- **SQL Injection Prevention**: All queries use prepared statements
- **Session Security**: Automatic session timeout and secure session handling
- **Input Validation**: Both client-side and server-side validation

### Performance Optimizations
- **Database Efficiency**: Optimized queries with proper indexing
- **Asset Optimization**: Minified CSS and JavaScript
- **Caching**: Proper HTTP headers for static assets
- **Responsive Images**: Optimized for different screen sizes

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Opera 47+

## Development Notes

- Error reporting is disabled for production. To enable during development, edit `config.php`
- Password requirements: minimum 8 characters with uppercase, lowercase, and numbers
- All forms include both client-side and server-side validation
- Database uses prepared statements throughout for security

## Future Enhancements

- [ ] PDF report generation
- [ ] Email notifications for exam results
- [ ] Bulk import/export functionality
- [ ] Advanced analytics and charts
- [ ] Multi-language support
- [ ] API endpoints for mobile app integration

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify MySQL service is running
   - Check database credentials in `config.php`
   - Ensure database `RDL` exists and is properly imported

2. **Login Issues**
   - Use default credentials: admin/password
   - Check session configuration in PHP
   - Clear browser cookies and try again

3. **File Permission Errors**
   - Ensure web server has read/write permissions
   - Check file ownership and permissions

4. **Styling Issues**
   - Verify CSS and JS files are loading properly
   - Check browser console for errors
   - Clear browser cache

## Support

For technical support or questions about the Rwanda Driving License Admin System, please refer to the documentation or contact your system administrator.

## License

This application is developed for the Rwanda Driving License management system. All rights reserved.

---

*Last updated: January 2025*
