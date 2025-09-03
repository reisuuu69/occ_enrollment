USE occ_enrollment;

-- Drop tables in reverse dependency order to avoid foreign key constraint errors
-- First drop tables that depend on others
DROP TABLE IF EXISTS student_sections;
DROP TABLE IF EXISTS subject_schedule;
DROP TABLE IF EXISTS subject_professor;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS sections;

-- Sections table for managing student sections
CREATE TABLE sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(20) NOT NULL,
    shift ENUM('Morning', 'Afternoon', 'Evening') NOT NULL,
    max_capacity INT NOT NULL DEFAULT 50,
    current_enrollment INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default sections
INSERT INTO sections (section_name, shift, max_capacity) VALUES
('A', 'Morning', 50),
('B', 'Morning', 50),
('C', 'Afternoon', 50),
('D', 'Afternoon', 50),
('E', 'Evening', 50),
('F', 'Evening', 50);

-- Rooms table for classroom management
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    room_type ENUM('Classroom', 'Laboratory', 'Computer Lab', 'Conference Room') DEFAULT 'Classroom',
    building VARCHAR(50),
    floor INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample rooms
INSERT INTO rooms (room_name, capacity, room_type, building, floor) VALUES
('101', 50, 'Classroom', 'Main Building', 1),
('102', 50, 'Classroom', 'Main Building', 1),
('103', 50, 'Classroom', 'Main Building', 1),
('201', 50, 'Classroom', 'Main Building', 2),
('202', 50, 'Classroom', 'Main Building', 2),
('Computer Lab 1', 30, 'Computer Lab', 'Technology Building', 1),
('Computer Lab 2', 30, 'Computer Lab', 'Technology Building', 1),
('Science Lab 1', 25, 'Laboratory', 'Science Building', 1);

-- Subject-Professor assignments
CREATE TABLE subject_professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    professor_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES faculty(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (subject_id, professor_id, school_year, semester)
);

-- Subject schedules with rooms
CREATE TABLE subject_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    professor_id INT NOT NULL,
    room_id INT NOT NULL,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES faculty(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (room_id, day, start_time, end_time, school_year, semester),
    UNIQUE KEY unique_professor_schedule (professor_id, day, start_time, end_time, school_year, semester)
);

-- Student section assignments
CREATE TABLE student_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    section_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    assigned_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_section (student_id, school_year, semester)
);

-- Update students table to add section_id foreign key (only if column doesn't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'occ_enrollment' 
     AND TABLE_NAME = 'students' 
     AND COLUMN_NAME = 'section_id') = 0,
    'ALTER TABLE students ADD COLUMN section_id INT AFTER section',
    'SELECT "section_id column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint only if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'occ_enrollment' 
     AND TABLE_NAME = 'students' 
     AND COLUMN_NAME = 'section_id' 
     AND REFERENCED_TABLE_NAME = 'sections') = 0,
    'ALTER TABLE students ADD FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL',
    'SELECT "section_id foreign key already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_subject_professor_school_year ON subject_professor(school_year, semester);
CREATE INDEX IF NOT EXISTS idx_subject_schedule_school_year ON subject_schedule(school_year, semester);
CREATE INDEX IF NOT EXISTS idx_student_sections_school_year ON student_sections(school_year, semester);
CREATE INDEX IF NOT EXISTS idx_sections_shift ON sections(shift);
