<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// Generate session ID if not exists
if (!isset($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = uniqid();
}

$session_id = $_SESSION['chat_session_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'send_message') {
        $user_message = trim($_POST['message']);
        $bot_response = getBotResponse($db, $user_message, $session_id);
        
        echo json_encode([
            'success' => true,
            'response' => $bot_response
        ]);
        exit();
    }
}

function getBotResponse($db, $user_message, $session_id) {
    $user_message_lower = strtolower($user_message);
    
    // Get all responses from database
    $query = "SELECT question_pattern, response FROM chatbot_responses WHERE is_active = 1";
    $stmt = $db->query($query);
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find matching response
    foreach ($responses as $response) {
        $pattern = strtolower($response['question_pattern']);
        if (strpos($user_message_lower, $pattern) !== false) {
            // Log conversation
            logConversation($db, $session_id, $user_message, $response['response']);
            return $response['response'];
        }
    }
    
    // Default response if no match found
    $default_response = "I'm sorry, I don't understand your question. Please try rephrasing or contact our support team at info@occ.edu.ph for assistance.";
    logConversation($db, $session_id, $user_message, $default_response);
    return $default_response;
}

function logConversation($db, $session_id, $user_message, $bot_response) {
    $query = "INSERT INTO chatbot_conversations (session_id, user_message, bot_response) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$session_id, $user_message, $bot_response]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCC Enrollment System - Chatbot Support</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chatbot-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            overflow: hidden;
        }
        
        .chatbot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .chatbot-header h2 {
            margin-bottom: 5px;
        }
        
        .chatbot-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.bot {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: #007bff;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #dee2e6;
            border-bottom-left-radius: 4px;
        }
        
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin: 0 8px;
        }
        
        .user-avatar {
            background: #007bff;
            color: white;
        }
        
        .bot-avatar {
            background: #28a745;
            color: white;
        }
        
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
        }
        
        .chat-input input:focus {
            border-color: #667eea;
        }
        
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        
        .send-btn:hover {
            transform: scale(1.1);
        }
        
        .quick-questions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .quick-questions h4 {
            margin-bottom: 10px;
            color: #333;
            font-size: 14px;
        }
        
        .quick-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .quick-btn {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quick-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .typing-indicator {
            display: none;
            padding: 12px 16px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            margin-bottom: 15px;
            max-width: 70%;
        }
        
        .typing-dots {
            display: flex;
            gap: 4px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="chatbot-container">
        <div class="chatbot-header">
            <h2>OCC Enrollment Assistant</h2>
            <p>24/7 Automated Support</p>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                <div class="message-avatar bot-avatar">B</div>
                <div class="message-content">
                    Hello! Welcome to OCC Enrollment System. I'm here to help you with enrollment-related questions. How can I assist you today?
                </div>
            </div>
        </div>
        
        <div class="typing-indicator" id="typingIndicator">
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
        
        <div class="quick-questions">
            <h4>Quick Questions:</h4>
            <div class="quick-buttons">
                <button class="quick-btn" onclick="sendQuickMessage('enrollment requirements')">Requirements</button>
                <button class="quick-btn" onclick="sendQuickMessage('enrollment deadline')">Deadlines</button>
                <button class="quick-btn" onclick="sendQuickMessage('available courses')">Courses</button>
                <button class="quick-btn" onclick="sendQuickMessage('check status')">Check Status</button>
                <button class="quick-btn" onclick="sendQuickMessage('tuition fee')">Fees</button>
                <button class="quick-btn" onclick="sendQuickMessage('contact')">Contact Info</button>
            </div>
        </div>
        
        <div class="chat-input">
            <div class="input-group">
                <input type="text" id="messageInput" placeholder="Type your message here..." onkeypress="handleKeyPress(event)">
                <button class="send-btn" onclick="sendMessage()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22,2 15,22 11,13 2,9"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');

        function addMessage(message, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
            
            const avatar = document.createElement('div');
            avatar.className = `message-avatar ${isUser ? 'user-avatar' : 'bot-avatar'}`;
            avatar.textContent = isUser ? 'U' : 'B';
            
            const content = document.createElement('div');
            content.className = 'message-content';
            content.textContent = message;
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            typingIndicator.style.display = 'block';
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            typingIndicator.style.display = 'none';
        }

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;
            
            addMessage(message, true);
            messageInput.value = '';
            
            showTyping();
            
            fetch('chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                hideTyping();
                if (data.success) {
                    setTimeout(() => {
                        addMessage(data.response);
                    }, 500);
                }
            })
            .catch(error => {
                hideTyping();
                addMessage('Sorry, there was an error processing your request. Please try again.');
            });
        }

        function sendQuickMessage(message) {
            messageInput.value = message;
            sendMessage();
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Focus on input when page loads
        messageInput.focus();
    </script>
</body>
</html>
