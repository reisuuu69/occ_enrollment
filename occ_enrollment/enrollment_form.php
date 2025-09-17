<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// Get courses for dropdown
$courses_query = "SELECT course_code, course_name FROM courses ORDER BY course_code";
$courses_stmt = $db->query($courses_query);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Student Enrollment - OCC Enrollment System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .form-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .progress-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .step-label {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
        }
        
        .step.active .step-label {
            color: #667eea;
            font-weight: 500;
        }
        
        .step.completed .step-label {
            color: #28a745;
            font-weight: 500;
        }
        
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .section-header h3 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 5px;
        }
        
        .section-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .required {
            color: #dc3545;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 10px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Student Enrollment</h1>
        <p>Complete your enrollment application step by step</p>
    </div>
    
    <div class="form-container">
        <a href="index.php" class="back-link">← Back to Home</a>
        
        <div class="progress-bar">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Personal Info</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Educational Info</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Family Info</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Review & Submit</div>
                </div>
            </div>
        </div>
        
        <form id="enrollmentForm" action="process.php" method="POST">
            <!-- Step 1: Personal Information -->
            <div class="form-section active" id="step1">
                <div class="section-header">
                    <h3>Personal Information</h3>
                    <p>Please provide your basic personal details</p>
                </div>
                
                <div class="alert alert-info">
                    <strong>Note:</strong> All fields marked with <span class="required">*</span> are required.
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
					<div class="form-group">
						<label for="account_password">Account Password <span class="required">*</span></label>
						<input type="password" id="account_password" name="account_password" minlength="8" required placeholder="Minimum 8 characters">
					</div>
                    <div class="form-group">
                        <label for="lrn">LRN (Learner Reference Number) <span class="required">*</span></label>
                        <input type="text" id="lrn" name="lrn" required>
                    </div>
                </div>

				<div class="form-row">
					<div class="form-group">
						<label for="account_password_confirm">Confirm Password <span class="required">*</span></label>
						<input type="password" id="account_password_confirm" name="account_password_confirm" minlength="8" required>
					</div>
				</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="lastname">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                    <div class="form-group">
                        <label for="firstname">First Name <span class="required">*</span></label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label for="middlename">Middle Name</label>
                        <input type="text" id="middlename" name="middlename">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="age">Age <span class="required">*</span></label>
                        <input type="number" id="age" name="age" min="15" max="100" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="civil_status">Civil Status <span class="required">*</span></label>
                        <select id="civil_status" name="civil_status" required>
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number <span class="required">*</span></label>
                        <input type="text" id="contact" name="contact" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Complete Address <span class="required">*</span></label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                
                <div class="btn-group">
                    <div></div>
                    <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                </div>
            </div>
            
            <!-- Step 2: Educational Information -->
            <div class="form-section" id="step2">
                <div class="section-header">
                    <h3>Educational Information</h3>
                    <p>Provide details about your previous education</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="last_school">Name of School Last Attended <span class="required">*</span></label>
                        <input type="text" id="last_school" name="last_school" required>
                    </div>
                    <div class="form-group">
                        <label for="school_address">School Address <span class="required">*</span></label>
                        <input type="text" id="school_address" name="school_address" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="strand">Senior High School Strand <span class="required">*</span></label>
                        <select id="strand" name="strand" required>
                            <option value="">Select Strand</option>
                            <option value="STEM">STEM (Science, Technology, Engineering, Mathematics)</option>
                            <option value="HUMSS">HUMSS (Humanities and Social Sciences)</option>
                            <option value="ABM">ABM (Accountancy, Business, and Management)</option>
                            <option value="GAS">GAS (General Academic Strand)</option>
                            <option value="TVL">TVL (Technical-Vocational-Livelihood)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="program">Preferred Bachelor's Program <span class="required">*</span></label>
                        <select id="program" name="program" required>
                            <option value="">Select Program</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['course_code']); ?>">
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="is_working">Are you a Working Student? <span class="required">*</span></label>
                        <select id="is_working" name="is_working" required onchange="toggleWorkFields()">
                            <option value="N">No</option>
                            <option value="Y">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="preferred_schedule">Preferred Schedule <span class="required">*</span></label>
                        <select id="preferred_schedule" name="preferred_schedule" required>
                            <option value="">Select Schedule</option>
                            <option value="Morning">Morning (7:00 AM - 12:00 PM)</option>
                            <option value="Afternoon">Afternoon (1:00 PM - 6:00 PM)</option>
                            <option value="Evening">Evening (6:00 PM - 9:00 PM)</option>
                        </select>
                    </div>
                </div>
                
                <div id="workFields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="employer">Employer</label>
                            <input type="text" id="employer" name="employer" placeholder="Company/Organization name">
                        </div>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position" placeholder="Your job title">
                        </div>
                        <div class="form-group">
                            <label for="working_hours">Working Hours</label>
                            <input type="text" id="working_hours" name="working_hours" placeholder="e.g., 8:00 AM - 5:00 PM">
                        </div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                </div>
            </div>
            
            <!-- Step 3: Family Information -->
            <div class="form-section" id="step3">
                <div class="section-header">
                    <h3>Family Information</h3>
                    <p>Provide details about your family background</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="father_name">Father's Name <span class="required">*</span></label>
                        <input type="text" id="father_name" name="father_name" required>
                    </div>
                    <div class="form-group">
                        <label for="father_occupation">Father's Occupation</label>
                        <input type="text" id="father_occupation" name="father_occupation">
                    </div>
                    <div class="form-group">
                        <label for="father_contact">Father's Contact Number</label>
                        <input type="text" id="father_contact" name="father_contact">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mother_name">Mother's Name <span class="required">*</span></label>
                        <input type="text" id="mother_name" name="mother_name" required>
                    </div>
                    <div class="form-group">
                        <label for="mother_occupation">Mother's Occupation</label>
                        <input type="text" id="mother_occupation" name="mother_occupation">
                    </div>
                    <div class="form-group">
                        <label for="mother_contact">Mother's Contact Number</label>
                        <input type="text" id="mother_contact" name="mother_contact">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="num_brothers">Number of Brothers</label>
                        <input type="number" id="num_brothers" name="num_brothers" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="num_sisters">Number of Sisters</label>
                        <input type="number" id="num_sisters" name="num_sisters" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="family_income">Monthly Family Income <span class="required">*</span></label>
                        <input type="number" id="family_income" name="family_income" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="guardian_name">Guardian's Name <span class="required">*</span></label>
                        <input type="text" id="guardian_name" name="guardian_name" required>
                    </div>
                    <div class="form-group">
                        <label for="guardian_contact">Guardian's Contact Number <span class="required">*</span></label>
                        <input type="text" id="guardian_contact" name="guardian_contact" required>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                </div>
            </div>

            
            <!-- Step 4: Review and Submit -->
            <div class="form-section" id="step4">
                <div class="section-header">
                    <h3>Review and Submit</h3>
                    <p>Please review your information before submitting</p>
                </div>
                
                <div id="reviewContent">
                    <!-- Review content will be populated by JavaScript -->
                </div>
                
                <div class="alert alert-info">
                    <strong>Important:</strong> By submitting this form, you confirm that all information provided is accurate and complete. 
                    You will receive a confirmation email once your application is submitted.
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                    <button type="submit" class="btn btn-success">Submit Application</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;
        
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show current step
            document.getElementById(`step${step}`).classList.add('active');
            
            // Update progress bar
            updateProgressBar(step);
        }
        
        function updateProgressBar(step) {
            document.querySelectorAll('.step').forEach((stepElement, index) => {
                const stepNumber = index + 1;
                stepElement.classList.remove('active', 'completed');
                
                if (stepNumber < step) {
                    stepElement.classList.add('completed');
                } else if (stepNumber === step) {
                    stepElement.classList.add('active');
                }
            });
        }
        
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                    
                    if (currentStep === 4) {
                        populateReview();
                    }
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }
        
        function validateCurrentStep() {
            const currentSection = document.getElementById(`step${currentStep}`);
            const requiredFields = currentSection.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

			// Additional password check on step 1
			if (currentStep === 1) {
				const pw = document.getElementById('account_password').value;
				const pwc = document.getElementById('account_password_confirm').value;
				if (pw.length < 8) {
					alert('Password must be at least 8 characters.');
					return false;
				}
				if (pw !== pwc) {
					alert('Passwords do not match.');
					return false;
				}
			}
            
            if (!isValid) {
                alert('Please fill in all required fields.');
            }
            
            return isValid;
        }
        
        function toggleWorkFields() {
            const isWorking = document.getElementById('is_working').value;
            const workFields = document.getElementById('workFields');
            
            if (isWorking === 'Y') {
                workFields.style.display = 'block';
            } else {
                workFields.style.display = 'none';
            }
        }
        
        function populateReview() {
            const reviewContent = document.getElementById('reviewContent');
            const formData = new FormData(document.getElementById('enrollmentForm'));
            
            let reviewHTML = '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">';
            reviewHTML += '<h4 style="margin-bottom: 20px;">Application Summary</h4>';
            
            // Personal Information
            reviewHTML += '<h5 style="color: #667eea; margin-bottom: 10px;">Personal Information</h5>';
            reviewHTML += `<p><strong>Name:</strong> ${formData.get('lastname')}, ${formData.get('firstname')} ${formData.get('middlename') || ''}</p>`;
            reviewHTML += `<p><strong>Email:</strong> ${formData.get('email')}</p>`;
            reviewHTML += `<p><strong>LRN:</strong> ${formData.get('lrn')}</p>`;
            reviewHTML += `<p><strong>Gender:</strong> ${formData.get('gender') === 'M' ? 'Male' : 'Female'}</p>`;
            reviewHTML += `<p><strong>Date of Birth:</strong> ${formData.get('dob')}</p>`;
            reviewHTML += `<p><strong>Contact:</strong> ${formData.get('contact')}</p>`;
            reviewHTML += `<p><strong>Address:</strong> ${formData.get('address')}</p>`;
            
            // Educational Information
            reviewHTML += '<h5 style="color: #667eea; margin: 20px 0 10px 0;">Educational Information</h5>';
            reviewHTML += `<p><strong>Last School:</strong> ${formData.get('last_school')}</p>`;
            reviewHTML += `<p><strong>Strand:</strong> ${formData.get('strand')}</p>`;
            reviewHTML += `<p><strong>Preferred Program:</strong> ${formData.get('program')}</p>`;
            reviewHTML += `<p><strong>Working Student:</strong> ${formData.get('is_working') === 'Y' ? 'Yes' : 'No'}</p>`;
            reviewHTML += `<p><strong>Preferred Schedule:</strong> ${formData.get('preferred_schedule')}</p>`;
            
            // Family Information
            reviewHTML += '<h5 style="color: #667eea; margin: 20px 0 10px 0;">Family Information</h5>';
            reviewHTML += `<p><strong>Father's Name:</strong> ${formData.get('father_name')}</p>`;
            reviewHTML += `<p><strong>Mother's Name:</strong> ${formData.get('mother_name')}</p>`;
            reviewHTML += `<p><strong>Guardian's Name:</strong> ${formData.get('guardian_name')}</p>`;
            reviewHTML += `<p><strong>Family Income:</strong> ₱${formData.get('family_income')}</p>`;
            
            reviewHTML += '</div>';
            
            reviewContent.innerHTML = reviewHTML;
        }
        
        // Auto-calculate age based on date of birth
        document.getElementById('dob').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('age').value = age;
        });
    </script>
</body>
</html>
