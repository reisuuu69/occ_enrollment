<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCC Enrollment System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .nav {
            background: #343a40;
            padding: 15px 0;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: #495057;
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .hero-section {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hero-section h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #333;
        }
        
        .hero-section p {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 30px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
            color: white;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2em;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .stats-section {
            background: #f8f9fa;
            padding: 50px 0;
            margin: 50px 0;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }
        
        .stat-item h3 {
            font-size: 2.5em;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-item p {
            color: #666;
            font-size: 1.1em;
        }
        
        .footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer h3 {
            margin-bottom: 20px;
        }
        
        .footer p {
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Chatbot Styles */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .chatbot-toggle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .chatbot-modal {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chatbot-modal.active {
            display: flex;
        }

        .chatbot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-weight: bold;
        }

        .chatbot-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .chatbot-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }

        .chatbot-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            background: #f8f9fa;
        }

        .chatbot-input:focus {
            border-color: #667eea;
            background: white;
        }

        .chatbot-input::placeholder {
            color: #6c757d;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
        }

        .message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .message.bot .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .message.user .message-avatar {
            background: #6c757d;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }

            .chatbot-modal {
                width: 300px;
                height: 400px;
                right: -20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>OCC Enrollment System</h1>
        <p>Welcome to One Cainta College Enrollment System</p>        
    </div>
    
    <nav class="nav">
        <div class="nav-container">
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="hero-section" id="home">
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?php 
                    if ($_GET['status'] === 'verification_sent') echo 'alert-success';
                    elseif ($_GET['status'] === 'verification_error') echo 'alert-warning';
                    elseif ($_GET['status'] === 'success') echo 'alert-success';
                    else echo 'alert-danger';
                ?>">
                    <?php 
                    if ($_GET['status'] === 'verification_sent') {
                        echo "Enrollment form submitted successfully! Please check your email for a verification link to complete your application.";
                    } elseif ($_GET['status'] === 'verification_error') {
                        echo "Enrollment form submitted successfully! However, there was an issue sending the verification email. Please contact the registrar's office.";
                    } elseif ($_GET['status'] === 'success') {
                        echo "Enrollment form submitted successfully! We will review your application and contact you soon.";
                    } else {
                        echo "Error submitting form. Please try again. " . (isset($_GET['message']) ? $_GET['message'] : '');
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <h2>Start Your Academic Journey Today</h2>
            <p>Join thousands of students who have chosen One Cainta College for their higher education. Our comprehensive enrollment system makes it easy to apply and track your application status.</p>
            
            <div class="cta-buttons">
                <a href="enrollment_form.php" class="btn btn-primary">
                    <span>👤</span>
                    New Student Enrollment
                </a>
                <a href="old_student/login.php" class="btn btn-secondary">
                    <span>↩️</span>
                    Returning Student
                </a>
                <a href="admin/login.php" class="btn btn-info">
                    <span>👨‍💼</span>
                    Admin Login
                </a>
                <a href="registrar/login.php" class="btn btn-warning">
                    <span>👨‍🎓</span>
                    Registrar Login
                </a>
            </div>
        </div>
        
        <div class="features-grid" id="features">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Easy Online Enrollment</h3>
                <p>Complete your enrollment application online with our user-friendly form. No need to visit the campus for initial registration.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Real-time Status Tracking</h3>
                <p>Track your application status in real-time. Get instant updates on your enrollment progress and document requirements.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>24/7 Chatbot Support</h3>
                <p>Get instant answers to your questions with our AI-powered chatbot. Available 24/7 to assist you with enrollment queries.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3>Document Checklist</h3>
                <p>Comprehensive document tracking system ensures you have all required documents for successful enrollment.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📧</div>
                <h3>Email Notifications</h3>
                <p>Receive email updates on your application status, important deadlines, and enrollment requirements.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure & Private</h3>
                <p>Your personal information is protected with industry-standard security measures and data encryption.</p>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3>1000+</h3>
                        <p>Students Enrolled</p>
                    </div>
                    <div class="stat-item">
                        <h3>4</h3>
                        <p>Academic Programs</p>
                    </div>
                    <div class="stat-item">
                        <h3>50+</h3>
                        <p>Faculty Members</p>
                    </div>
                    <div class="stat-item">
                        <h3>95%</h3>
                        <p>Student Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="about">
            <h2 style="text-align: center; margin-bottom: 30px;">About One Cainta College</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto; line-height: 1.8; color: #666;">
                One Cainta College is committed to providing quality education in the field of technology. 
                We offer comprehensive programs designed to prepare students for successful careers in information systems, 
                education, and technical-vocational teaching. Our modern enrollment system ensures a smooth and efficient application process 
                for all prospective students.
            </p>
        </div>
    </div>
    
    <footer class="footer" id="contact">
        <div class="footer-content">
            <h3>Contact Information</h3>
            <p><strong>Email:</strong> info@occ.edu.ph</p>
            <p><strong>Phone:</strong> (123) 456-7890</p>
            <p><strong>Address:</strong> 123 Main Street, City, Province</p>
            <p><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            <p style="margin-top: 20px;">&copy; 2025 OCC Enrollment System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Chatbot -->
    <div class="chatbot-container">
        <button class="chatbot-toggle" onclick="toggleChatbot()">💬</button>
        <div class="chatbot-modal" id="chatbotModal">
            <div class="chatbot-header">
                OCC Enrollment Assistant
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="message bot">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        Hello! I'm your OCC Enrollment Assistant. How can I help you today?
                    </div>
                </div>
            </div>
            <div class="chatbot-input-container">
                <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Type your question here..." onkeypress="handleChatbotInput(event)">
            </div>
        </div>
    </div>

    <script>
        function toggleChatbot() {
            const modal = document.getElementById('chatbotModal');
            modal.classList.toggle('active');
            
            if (modal.classList.contains('active')) {
                document.getElementById('chatbotInput').focus();
            }
        }

        function handleChatbotInput(event) {
            if (event.key === 'Enter') {
                const input = event.target;
                const message = input.value.trim();
                
                if (message) {
                    addMessage('user', message);
                    input.value = '';
                    
                    // Simulate bot response
                    setTimeout(() => {
                        const botResponse = getBotResponse(message);
                        addMessage('bot', botResponse);
                    }, 1000);
                }
            }
        }

        function addMessage(type, content) {
            const messagesContainer = document.getElementById('chatbotMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = type === 'bot' ? '🤖' : '👤';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            messageContent.textContent = content;
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(messageContent);
            messagesContainer.appendChild(messageDiv);
            
            // Auto-scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function getBotResponse(userMessage) {
            const message = userMessage.toLowerCase();
            
            if (message.includes('enrollment') || message.includes('apply') || message.includes('form')) {
                return "To enroll as a new student, click the 'New Student Enrollment' button on the main page. For returning students, use the 'Returning Student' button to access your account.";
            } else if (message.includes('login') || message.includes('account')) {
                return "Returning students can log in using the 'Returning Student' button. Admin and Registrar staff have separate login portals available on the main page.";
            } else if (message.includes('requirements') || message.includes('documents')) {
                return "Required documents typically include: Transcript of Records, Birth Certificate, 2x2 ID Photo, and other academic credentials. Check the enrollment form for the complete list.";
            } else if (message.includes('status') || message.includes('track')) {
                return "You can track your enrollment status through your student account after logging in, or contact the registrar's office for assistance.";
            } else if (message.includes('contact') || message.includes('help')) {
                return "For additional help, you can contact the registrar's office during business hours (Monday-Friday, 8:00 AM - 5:00 PM) or email info@occ.edu.ph";
            } else {
                return "I'm here to help with enrollment questions! You can ask about the application process, requirements, login procedures, or contact information.";
            }
        }

        // Close chatbot when clicking outside
        document.addEventListener('click', function(event) {
            const chatbotContainer = document.querySelector('.chatbot-container');
            const chatbotModal = document.getElementById('chatbotModal');
            
            if (!chatbotContainer.contains(event.target) && chatbotModal.classList.contains('active')) {
                chatbotModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>

