USE occ_enrollment;

-- Create audit_logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('admin', 'registrar', 'faculty', 'student') NOT NULL,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_user_type (user_type),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create chatbot_responses table
CREATE TABLE IF NOT EXISTS chatbot_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_pattern VARCHAR(500) NOT NULL,
    response_text TEXT NOT NULL,
    category VARCHAR(100),
    keywords TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_keywords (keywords(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create chatbot_conversations table
CREATE TABLE IF NOT EXISTS chatbot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'registrar', 'faculty', 'student', 'guest') DEFAULT 'guest',
    user_id INT NULL,
    user_ip VARCHAR(45),
    user_agent TEXT,
    message TEXT NOT NULL,
    response TEXT,
    response_id INT,
    is_helpful BOOLEAN NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_user_type (user_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_response_id (response_id),
    FOREIGN KEY (response_id) REFERENCES chatbot_responses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample chatbot responses
INSERT INTO chatbot_responses (question_pattern, response_text, category, keywords) VALUES
('enrollment|enroll|how to enroll|enrollment process', 'To enroll at OCC, you need to: 1) Complete the enrollment form, 2) Submit required documents, 3) Pay tuition fees, 4) Attend orientation. Visit our registrar office for assistance.', 'enrollment', 'enrollment,enroll,process,form,documents,fees'),
('tuition|fees|payment|how much|cost', 'Tuition fees vary by course and year level. Please contact the accounting office for the current fee structure. Payment plans are available.', 'fees', 'tuition,fees,payment,cost,money'),
('schedule|class schedule|time table', 'Class schedules are available at the registrar office or through the student portal. Schedules are posted before each semester begins.', 'schedule', 'schedule,class,time,table,timetable'),
('requirements|documents needed|what to bring', 'Required documents: 1) Birth certificate, 2) High school diploma, 3) Good moral certificate, 4) 2x2 photos, 5) Medical certificate', 'requirements', 'requirements,documents,birth certificate,diploma,photos'),
('contact|phone|email|address', 'Contact OCC at: Phone: (123) 456-7890, Email: info@occ.edu.ph, Address: 123 Main Street, City, Province', 'contact', 'contact,phone,email,address,location'),
('admission|admission requirements|how to apply', 'Admission requirements: 1) Must be 16 years old, 2) Completed high school, 3) Pass entrance exam, 4) Submit all required documents', 'admission', 'admission,apply,requirements,entrance exam'),
('courses|programs|degrees offered', 'OCC offers: BS Computer Science, BS Information Technology, BS Business Administration, BS Education, and Associate degrees in various fields.', 'courses', 'courses,programs,degrees,computer science,business'),
('faculty|teachers|professors', 'Our faculty members are qualified professionals with advanced degrees and industry experience. You can view faculty profiles at the registrar office.', 'faculty', 'faculty,teachers,professors,instructors'),
('library|books|study materials', 'The OCC library is open Monday to Friday, 8:00 AM to 5:00 PM. Students can borrow books and use study facilities.', 'library', 'library,books,study,materials,reading'),
('canteen|food|meals', 'The school canteen serves affordable meals and snacks. Operating hours: 7:00 AM to 6:00 PM, Monday to Friday.', 'canteen', 'canteen,food,meals,snacks,eating'),
('parking|vehicle|car|motorcycle', 'Free parking is available for students and visitors. Motorcycle and car parking areas are clearly marked.', 'parking', 'parking,vehicle,car,motorcycle,transport'),
('uniform|dress code|attire', 'Students must wear the prescribed school uniform during class days. PE uniform is required for physical education classes.', 'uniform', 'uniform,dress code,attire,clothing'),
('holidays|breaks|vacation', 'School holidays and breaks are announced at the beginning of each semester. Check the school calendar for details.', 'holidays', 'holidays,breaks,vacation,calendar'),
('graduation|commencement|ceremony', 'Graduation ceremonies are held annually. Requirements for graduation include completing all academic requirements and clearing all obligations.', 'graduation', 'graduation,commencement,ceremony,diploma'),
('scholarship|financial aid|discount', 'Various scholarships and financial aid programs are available. Contact the student affairs office for application procedures.', 'scholarship', 'scholarship,financial aid,discount,assistance'),
('internship|on-the-job training|ojt', 'Internship programs are available for senior students. The school coordinates with partner companies for OJT opportunities.', 'internship', 'internship,ojt,training,work experience'),
('student organizations|clubs|activities', 'Various student organizations and clubs are available. Join to develop leadership skills and meet new friends.', 'organizations', 'organizations,clubs,activities,leadership'),
('health services|medical|clinic', 'Basic health services are available at the school clinic. First aid and health consultations are provided.', 'health', 'health,medical,clinic,first aid'),
('security|safety|emergency', 'Security personnel are on duty 24/7. Emergency contact numbers are posted throughout the campus.', 'security', 'security,safety,emergency,protection'),
('wifi|internet|computer lab', 'Free WiFi is available in designated areas. Computer laboratories are open for student use during scheduled hours.', 'wifi', 'wifi,internet,computer,lab,technology');

-- Insert sample audit log entries
INSERT INTO audit_logs (user_id, user_type, action, table_name, record_id, ip_address, created_at) VALUES
(1, 'admin', 'LOGIN', 'users', 1, '192.168.1.100', NOW() - INTERVAL 1 HOUR),
(1, 'admin', 'CREATE', 'users', 2, '192.168.1.100', NOW() - INTERVAL 2 HOUR),
(1, 'admin', 'UPDATE', 'students', 1, '192.168.1.100', NOW() - INTERVAL 3 HOUR),
(1, 'admin', 'DELETE', 'faculty', 3, '192.168.1.100', NOW() - INTERVAL 4 HOUR),
(1, 'admin', 'VIEW', 'reports', NULL, '192.168.1.100', NOW() - INTERVAL 5 HOUR);

-- Insert sample chatbot conversations
INSERT INTO chatbot_conversations (session_id, user_type, user_ip, message, response, response_id, is_helpful, created_at) VALUES
('session_001', 'guest', '192.168.1.101', 'How do I enroll?', 'To enroll at OCC, you need to: 1) Complete the enrollment form, 2) Submit required documents, 3) Pay tuition fees, 4) Attend orientation. Visit our registrar office for assistance.', 1, TRUE, NOW() - INTERVAL 1 DAY),
('session_001', 'guest', '192.168.1.101', 'What are the tuition fees?', 'Tuition fees vary by course and year level. Please contact the accounting office for the current fee structure. Payment plans are available.', 2, TRUE, NOW() - INTERVAL 1 DAY + INTERVAL 5 MINUTE),
('session_002', 'student', '192.168.1.102', 'Where is the library?', 'The OCC library is open Monday to Friday, 8:00 AM to 5:00 PM. Students can borrow books and use study facilities.', 9, TRUE, NOW() - INTERVAL 2 DAY),
('session_003', 'guest', '192.168.1.103', 'What courses do you offer?', 'OCC offers: BS Computer Science, BS Information Technology, BS Business Administration, BS Education, and Associate degrees in various fields.', 7, FALSE, NOW() - INTERVAL 3 DAY);
