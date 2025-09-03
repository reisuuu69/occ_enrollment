USE occ_enrollment;

-- Create faculty table if it doesn't exist
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample faculty data only if table is empty
INSERT IGNORE INTO faculty (professor_name, department, specialization, email, contact_number) VALUES
('Dr. John Smith', 'Computer Science', 'Web Development', 'john.smith@occ.edu.ph', '09123456789'),
('Prof. Maria Garcia', 'Education', 'Mathematics Education', 'maria.garcia@occ.edu.ph', '09234567890'),
('Dr. Robert Wilson', 'Technology', 'Industrial Technology', 'robert.wilson@occ.edu.ph', '09345678901'),
('Prof. Sarah Johnson', 'Education', 'Science Education', 'sarah.johnson@occ.edu.ph', '09456789012'),
('Dr. Michael Brown', 'Computer Science', 'Database Systems', 'michael.brown@occ.edu.ph', '09567890123'),
('Prof. Lisa Davis', 'Information Technology', 'Network Security', 'lisa.davis@occ.edu.ph', '09678901234');
