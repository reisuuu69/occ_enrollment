# OCC Enrollment System

A comprehensive web-based enrollment management system for Our Lady of Fatima University - Computer Studies.

## 🎯 Overview

The OCC Enrollment System is a full-featured web application designed to streamline the student enrollment process. It provides a modern, user-friendly interface for both new and returning students, along with comprehensive administrative tools for managing applications, students, and system operations.

## ✨ Features

### 🏠 User Management Module
- **User Registration & Login**: Secure authentication for students, staff, and administrators
- **Role-Based Access Control**: Different access levels for admin, registrar, faculty, and students
- **User Profile Management**: Users can update their profile information
- **Password Reset & Account Recovery**: Secure password recovery system

### 📝 Online Enrollment Module
- **New Student Registration**: Comprehensive enrollment form with step-by-step process
- **Returning Student Enrollment**: Streamlined re-enrollment for current students
- **Document Checklist**: Automated tracking of required documents
- **Application Status Tracking**: Real-time status updates for applicants
- **Class Sectioning**: Automatic assignment to classes based on year level
- **Email Notifications**: Automated email confirmations and updates

### 👨‍🎓 Student Information Management
- **Student Profile Management**: Complete student record management
- **Academic Record Management**: Track subjects, grades, and performance
- **Curriculum Management**: Course assignments and scheduling
- **Teacher Assignment**: Assign teachers to classes

### 🤖 Automated Customer Support (Chatbot)
- **24/7 Assistance**: AI-powered chatbot for instant support
- **FAQs Support**: Automated responses to common questions
- **Status Inquiry**: Students can check enrollment status via chatbot
- **Smart Responses**: Context-aware responses based on user queries

### 📊 Document and Report Generation
- **Enrollment Forms**: Generate filled-out enrollment forms
- **Student Lists & Reports**: Create comprehensive student reports
- **Printable Admission Slips**: Generate enrollment confirmation documents
- **Statistical Reports**: Analytics on enrollment trends and demographics

### 🔒 Security and Audit Trail
- **User Authentication & Role Management**: Secure access control
- **Audit Logs**: Complete tracking of user actions and system changes
- **Data Encryption & Backup**: Secure data storage and backup systems

### 📈 Reports and Analytics
- **Enrollment Statistics**: Visual graphs and reports
- **Student Demographics**: Analysis of student distribution
- **System Usage Analytics**: Track system performance and usage

## 🛠️ Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache/Nginx
- **Security**: Password hashing, SQL injection prevention, XSS protection

## 📋 System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Minimum 512MB RAM
- 1GB available disk space

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/occ-enrollment.git
cd occ-enrollment
```

### 2. Database Setup
1. Create a MySQL database named `occ_enrollment`
2. Import the database schema files in the `database/` directory:
   ```bash
   mysql -u username -p occ_enrollment < database/users_table.sql
   mysql -u username -p occ_enrollment < database/create_enrollees_table.sql
   mysql -u username -p occ_enrollment < database/students_table.sql
   mysql -u username -p occ_enrollment < database/old_students_table.sql
   mysql -u username -p occ_enrollment < database/enrollment_status_table.sql
   mysql -u username -p occ_enrollment < database/audit_logs_table.sql
   mysql -u username -p occ_enrollment < database/chatbot_table.sql
   mysql -u username -p occ_enrollment < database/course_table.sql
   mysql -u username -p occ_enrollment < database/faculty_table.sql
   mysql -u username -p occ_enrollment < database/subject_table.sql
   mysql -u username -p occ_enrollment < database/admin_table.sql
   ```

### 3. Configuration
1. Copy `config/database.php.example` to `config/database.php`
2. Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'occ_enrollment';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

### 4. Web Server Configuration
1. Point your web server document root to the project directory
2. Ensure PHP has write permissions for session storage
3. Configure URL rewriting if needed

### 5. Default Login Credentials
- **Admin**: username: `admin`, password: `admin123`
- **Registrar**: Use the admin account to create registrar accounts

## 📁 Directory Structure

```
occ_enrollment/
├── admin/                 # Admin dashboard and management
├── auth/                  # Authentication system
├── config/               # Configuration files
├── database/             # Database schema files
├── old_student/          # Returning student portal
├── registrar/            # Registrar management interface
├── chatbot.php          # AI chatbot support
├── enrollment_form.php  # New student enrollment form
├── index.php            # Main landing page
├── process.php          # Form processing
└── README.md            # This file
```

## 🔐 Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session management with timeout
- **CSRF Protection**: Form token validation
- **Audit Logging**: Complete audit trail of all system actions

## 🎨 User Interface

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with gradient backgrounds
- **Step-by-Step Forms**: Multi-step enrollment process for better UX
- **Real-time Validation**: Client-side and server-side form validation
- **Progress Indicators**: Visual progress tracking for multi-step processes

## 📊 Database Schema

The system uses the following main tables:
- `users` - User accounts and authentication
- `enrollees` - New student applications
- `students` - Enrolled student records
- `old_students` - Returning student records
- `enrollment_status` - Application status tracking
- `document_checklist` - Document verification tracking
- `audit_logs` - System audit trail
- `chatbot_responses` - Chatbot knowledge base
- `courses` - Available academic programs
- `faculty` - Faculty member records

## 🔧 Configuration Options

### Email Settings
Configure email notifications in the system settings:
- SMTP server settings
- Email templates
- Notification preferences

### System Settings
- Application deadlines
- Document requirements
- Course offerings
- User roles and permissions

## 📈 Monitoring and Analytics

### Built-in Analytics
- Enrollment statistics by program
- Application status tracking
- User activity monitoring
- System performance metrics

### Audit Trail
- User login/logout tracking
- Data modification logs
- System access monitoring
- Security event logging

## 🚨 Limitations (As Per Scope)

1. **Limited Automation**: Document verification requires human intervention
2. **Chatbot Limitations**: May struggle with complex queries
3. **Server Dependency**: System requires online access
4. **Emotional Support**: Chatbot cannot provide emotional support

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For technical support or questions:
- Email: support@occ.edu.ph
- Phone: (123) 456-7890
- Office Hours: Monday - Friday, 8:00 AM - 5:00 PM

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🔄 Version History

- **v1.0.0** - Initial release with core enrollment functionality
- **v1.1.0** - Added chatbot support and enhanced UI
- **v1.2.0** - Improved security and audit logging
- **v1.3.0** - Added comprehensive reporting and analytics

## 🎯 Roadmap

- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Integration with student information systems
- [ ] Multi-language support
- [ ] Advanced chatbot with machine learning
- [ ] Document upload and verification system
- [ ] Payment integration
- [ ] SMS notifications

---

**Developed for Our Lady of Fatima University - Computer Studies**

*Empowering education through technology*
