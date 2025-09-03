USE occ_enrollment;

-- Create courses table if it doesn't exist
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(10) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data only if table is empty
INSERT IGNORE INTO courses (course_code, course_name) VALUES
('BSCS', 'Bachelor of Science in Computer Science'),
('BSIS', 'Bachelor of Science in Information Systems'),
('BSE', 'Bachelor of Science in Education'),
('BTVTED', 'Bachelor in Technical-Vocational Teacher Education');
