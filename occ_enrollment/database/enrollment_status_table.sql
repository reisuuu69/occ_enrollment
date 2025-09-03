USE occ_enrollment;

DROP TABLE IF EXISTS enrollment_status;

CREATE TABLE enrollment_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollee_id INT NOT NULL,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'enrolled') DEFAULT 'pending',
    remarks TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollee_id) REFERENCES enrollees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS document_checklist;

CREATE TABLE document_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollee_id INT NOT NULL,
    birth_certificate BOOLEAN DEFAULT FALSE,
    report_card BOOLEAN DEFAULT FALSE,
    good_moral BOOLEAN DEFAULT FALSE,
    certificate_of_enrollment BOOLEAN DEFAULT FALSE,
    certificate_of_completion BOOLEAN DEFAULT FALSE,
    form_137 BOOLEAN DEFAULT FALSE,
    form_138 BOOLEAN DEFAULT FALSE,
    transcript_of_records BOOLEAN DEFAULT FALSE,
    certificate_of_transfer BOOLEAN DEFAULT FALSE,
    certificate_of_graduation BOOLEAN DEFAULT FALSE,
    certificate_of_eligibility BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_2 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_3 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_4 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_5 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_6 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_7 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_8 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_9 BOOLEAN DEFAULT FALSE,
    certificate_of_authenticity_10 BOOLEAN DEFAULT FALSE,
    remarks TEXT,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollee_id) REFERENCES enrollees(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
