# Registrar Dashboard System - OCC Enrollment System

A comprehensive web-based registrar dashboard for managing student enrollment, faculty assignments, class schedules, and room allocations.

## Features

### 🎓 Student Management
- **Create and Add New Student Accounts**
  - Comprehensive student information forms
  - Auto-generated unique Student IDs
  - Personal, academic, and family information tracking
  - Secure password creation

### 👨‍🏫 Faculty & Subject Management
- **Assign Professors to Subjects**
  - Dropdown selection of available professors
  - Subject assignment with school year and semester tracking
  - Prevents duplicate assignments

### 📚 Section Management
- **Assign Sections to Students**
  - Morning, Afternoon, Evening shifts
  - Maximum 50 students per section
  - Automatic capacity checking
  - Prevents over-enrollment

### ⏰ Schedule Management
- **Create Class Schedules**
  - Day, start time, end time selection
  - Room assignment with conflict prevention
  - Professor availability checking
  - Weekly schedule view with color coding

### 🏢 Room Management
- **Room Allocation System**
  - Classroom, laboratory, and computer lab support
  - Building and floor organization
  - Capacity-based room selection
  - Double-booking prevention

## Database Structure

The system uses the following normalized database tables:

```sql
-- Core Tables
students (student_id, user_id, personal_info, academic_info, ...)
faculty (id, professor_name, department, specialization, ...)
subjects (id, subject_code, subject_name, course_code, ...)
courses (id, course_code, course_name, ...)

-- New Tables for Registrar System
sections (section_id, section_name, shift, max_capacity, current_enrollment)
rooms (room_id, room_name, capacity, room_type, building, floor)
subject_professor (id, subject_id, professor_id, school_year, semester)
subject_schedule (id, subject_id, professor_id, room_id, day, start_time, end_time, school_year, semester)
student_sections (id, student_id, section_id, school_year, semester, assigned_date)
```

## Installation & Setup

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Existing OCC Enrollment System database

### 2. Database Setup
Run the setup script to create all necessary tables:

```bash
# Navigate to your project directory
cd /path/to/occ_enrollment

# Run the setup script
php setup_registrar_system.php
```

This will:
- Create all required database tables
- Insert sample data for sections and rooms
- Set up proper foreign key relationships
- Create necessary indexes for performance

### 3. File Structure
```
occ_enrollment/
├── registrar/
│   ├── dashboard.php          # Main dashboard
│   ├── manage_students.php    # Student management
│   ├── assignments.php        # Professor & section assignments
│   ├── manage_schedules.php   # Schedule management
│   └── login.php             # Authentication
├── database/
│   └── registrar_tables.sql   # Database schema
├── config/
│   └── database.php          # Database connection
└── setup_registrar_system.php # Setup script
```

## Usage Guide

### Accessing the System
1. Navigate to `registrar/dashboard.php`
2. Login with registrar credentials
3. Use the sidebar navigation to access different modules

### Adding New Students
1. Go to **Manage Students**
2. Click **Add New Student**
3. Fill in all required information
4. System automatically generates Student ID
5. Creates user account with login credentials

### Assigning Professors to Subjects
1. Go to **Assignments** → **Professor-Subject Assignments**
2. Select subject from dropdown
3. Choose professor from available faculty
4. Set school year and semester
5. System prevents duplicate assignments

### Creating Class Schedules
1. Go to **Manage Schedules**
2. Click **Create Schedule**
3. Select subject (must have assigned professor)
4. Choose professor, room, day, and time
5. System checks for conflicts automatically

### Managing Student Sections
1. Go to **Assignments** → **Student Section Assignments**
2. Select student and section
3. System checks section capacity (max 50)
4. Prevents double assignment in same semester

## Security Features

- **Session-based authentication**
- **Role-based access control** (registrar only)
- **SQL injection prevention** (prepared statements)
- **Input validation** and sanitization
- **CSRF protection** through form tokens

## Validation Rules

### Section Capacity
- Maximum 50 students per section
- Real-time capacity checking
- Visual indicators for capacity status

### Schedule Conflicts
- **Room conflicts**: Same room, same time, same day
- **Professor conflicts**: Same professor, same time, same day
- **Time overlap detection**: Prevents overlapping schedules

### Data Integrity
- Foreign key constraints
- Unique constraints on assignments
- Automatic enrollment counting

## Customization

### Adding New Fields
To add new student fields:
1. Modify the `students` table structure
2. Update `manage_students.php` form
3. Adjust validation and processing logic

### Modifying Section Limits
To change section capacity:
1. Update `max_capacity` in `sections` table
2. Modify validation logic in `assignments.php`
3. Update capacity indicators

### Adding New Room Types
To add new room types:
1. Modify the `room_type` ENUM in `rooms` table
2. Update room selection forms
3. Add specific validation rules if needed

## Troubleshooting

### Common Issues

**"Database connection failed"**
- Check database credentials in `config/database.php`
- Verify MySQL service is running
- Check database permissions

**"Table doesn't exist"**
- Run `setup_registrar_system.php` again
- Check for SQL errors in setup output
- Verify database name is correct

**"Schedule conflict detected"**
- Check existing schedules for the same room/time
- Verify professor availability
- Ensure no overlapping time slots

### Performance Optimization

- Database indexes are automatically created
- Use appropriate WHERE clauses in queries
- Consider pagination for large datasets
- Monitor query execution times

## Support & Maintenance

### Regular Tasks
- Monitor section capacity levels
- Review and update room assignments
- Backup database regularly
- Check for schedule conflicts

### Updates
- Keep PHP and MySQL versions current
- Monitor security updates
- Test new features in development environment
- Maintain backup procedures

## License

This system is part of the OCC Enrollment System and follows the same licensing terms.

---

**For technical support or questions, please contact the system administrator.**

**Last Updated**: <?php echo date('F j, Y'); ?>
**Version**: 1.0.0
