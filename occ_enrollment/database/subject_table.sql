USE occ_enrollment;

-- Create subjects table if it doesn't exist
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(10) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    units INT NOT NULL,
    year_level INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data only if table is empty
INSERT IGNORE INTO subjects (course_code, subject_code, subject_name, units, year_level, semester) VALUES
('BSCS', 'CS101', 'Introduction to Computing', 3, 1, 1),
('BSCS', 'CS102', 'Programming 1', 3, 1, 1),
('BSIS', 'IS101', 'Information Systems Fundamentals', 3, 1, 1),
('BSE', 'ED101', 'Principles of Teaching', 3, 1, 1);
