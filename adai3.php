<?php
include 'stdnav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Assistant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --primary-dark: #3a0ca3;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.15);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(-45deg, #f5f7fa, #e4e8f0, #f0f2f5, #e8ecf1);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--dark);
    }

    .chat-container {
      width: 100%;
      max-width: 500px;
      height: 90vh;
      max-height: 800px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: var(--shadow);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: var(--transition);
    }

    .chat-container:hover {
      box-shadow: var(--shadow-hover);
      transform: translateY(-5px);
    }

    .chat-header {
      background: var(--gradient);
      color: white;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .chat-title {
      font-size: 1.3rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chat-actions {
      display: flex;
      gap: 12px;
    }

    .chat-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .chat-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.1);
    }

    .chat-messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    /* Custom scrollbar */
    .chat-messages::-webkit-scrollbar {
      width: 6px;
    }

    .chat-messages::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.05);
      border-radius: 10px;
    }

    .chat-messages::-webkit-scrollbar-thumb {
      background: var(--primary-light);
      border-radius: 10px;
    }

    .message {
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.5;
      position: relative;
      opacity: 0;
      transform: translateY(10px);
      animation: fadeIn 0.3s ease forwards;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .user-message {
      align-self: flex-end;
      background: var(--gradient);
      color: white;
      border-bottom-right-radius: 4px;
      margin-left: 20%;
    }

    .bot-message {
      align-self: flex-start;
      background: white;
      color: var(--dark);
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-bottom-left-radius: 4px;
      margin-right: 20%;
    }

    .message-time {
      font-size: 0.7rem;
      opacity: 0.7;
      margin-top: 4px;
      display: block;
      text-align: right;
    }

    .typing-indicator {
      display: flex;
      align-self: flex-start;
      padding: 12px 16px;
      background: white;
      border-radius: 18px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      margin-bottom: 15px;
      border-bottom-left-radius: 4px;
    }

    .typing-dot {
      width: 8px;
      height: 8px;
      background: var(--gray);
      border-radius: 50%;
      margin: 0 2px;
      animation: typingAnimation 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) {
      animation-delay: 0s;
    }

    .typing-dot:nth-child(2) {
      animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
      animation-delay: 0.4s;
    }

    .chat-input-container {
      padding: 15px;
      background: white;
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      display: flex;
      gap: 10px;
    }

    .chat-input {
      flex: 1;
      padding: 12px 18px;
      border: 1px solid rgba(0, 0, 0, 0.1);
      border-radius: 30px;
      font-size: 1rem;
      outline: none;
      transition: var(--transition);
      background: rgba(0, 0, 0, 0.02);
    }

    .chat-input:focus {
      border-color: var(--primary-light);
      background: white;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .send-btn {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: var(--gradient);
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .send-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .send-btn:disabled {
      background: var(--gray);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Animations */
    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes gradientBG {
      0% {
        background-position: 0% 50%;
      }
      50% {
        background-position: 100% 50%;
      }
      100% {
        background-position: 0% 50%;
      }
    }

    @keyframes typingAnimation {
      0%, 60%, 100% {
        transform: translateY(0);
      }
      30% {
        transform: translateY(-5px);
      }
    }

    /* Responsive design */
    @media (max-width: 600px) {
      .chat-container {
        height: 100vh;
        max-height: none;
        border-radius: 0;
      }
      
      .message {
        max-width: 90%;
      }
      
      .user-message {
        margin-left: 10%;
      }
      
      .bot-message {
        margin-right: 10%;
      }
    }

    /* Dark mode toggle */
    .dark-mode {
      background: linear-gradient(-45deg, #1a1a2e, #16213e, #0f3460, #1a1a2e);
      color: white;
    }

    .dark-mode .chat-container {
      background: #1e1e2d;
      color: white;
    }

    .dark-mode .chat-header {
      background: var(--primary-dark);
    }

    .dark-mode .bot-message {
      background: #2d2d42;
      color: white;
      border-color: rgba(255, 255, 255, 0.1);
    }

    .dark-mode .typing-indicator {
      background: #2d2d42;
      border-color: rgba(255, 255, 255, 0.1);
    }

    .dark-mode .chat-input-container {
      background: #1e1e2d;
      border-color: rgba(255, 255, 255, 0.1);
    }

    .dark-mode .chat-input {
      background: #2d2d42;
      color: white;
      border-color: rgba(255, 255, 255, 0.1);
    }

    .dark-mode .chat-input:focus {
      background: #2d2d42;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
    }

    .dark-mode .typing-dot {
      background: var(--primary-light);
    }

    .ad{
      margin-left:170px;
    }
  </style>
</head>
<body class="ad">
  <div class="chat-container" id="chat-container">
    <div class="chat-header">
      <div class="chat-title">
        <i class="fas fa-robot"></i>
        <span>AI Assistant</span>
      </div>
      <div class="chat-actions">
        <button class="chat-btn" id="dark-mode-toggle" title="Toggle Dark Mode">
          <i class="fas fa-moon"></i>
        </button>
        <button class="chat-btn" id="clear-chat" title="Clear Conversation">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>
    
    <div class="chat-messages" id="chat-messages">
      <!-- Messages will be inserted here by JavaScript -->
    </div>
    
    <div class="chat-input-container">
      <input 
        type="text" 
        class="chat-input" 
        id="user-input" 
        placeholder="Type your message..." 
        autocomplete="off"
      >
      <button class="send-btn" id="send-btn">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </div>

  <script>
    // Configuration
    const API_KEY = 'AIzaSyAwMyOYTo7xDdXMDF7v1bpfSQa1t5XkQWk';
    const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`;
    
    // DOM Elements
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const clearChatBtn = document.getElementById('clear-chat');
    const chatContainer = document.getElementById('chat-container');
    
    // State
    let isDarkMode = localStorage.getItem('darkMode') === 'true';
    let conversationHistory = JSON.parse(localStorage.getItem('conversationHistory')) || [];
    
    // Initialize
    function init() {
      // Apply saved dark mode preference
      toggleDarkMode(isDarkMode);
      
      // Load conversation history
      if (conversationHistory.length > 0) {
        conversationHistory.forEach(msg => {
          appendMessage(msg.role, msg.content, msg.timestamp, false);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
      } else {
        // Show welcome message
        appendMessage('bot', "Hello! I'm your AI assistant. How can I help you today?", new Date().toISOString());
      }
      
      // Set up event listeners
      sendBtn.addEventListener('click', sendMessage);
      userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
      });
      darkModeToggle.addEventListener('click', () => {
        isDarkMode = !isDarkMode;
        toggleDarkMode(isDarkMode);
        localStorage.setItem('darkMode', isDarkMode);
      });
      clearChatBtn.addEventListener('click', clearConversation);
    }
    
    // Toggle dark mode
    function toggleDarkMode(enable) {
      if (enable) {
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
        document.body.classList.remove('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }
    }
    
    // Append message to chat
    function appendMessage(role, content, timestamp = null, saveToHistory = true) {
      const messageDiv = document.createElement('div');
      messageDiv.classList.add('message', `${role}-message`);
      
      const messageContent = document.createElement('div');
      messageContent.textContent = content;
      
      const time = timestamp ? new Date(timestamp) : new Date();
      const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      
      const timeElement = document.createElement('span');
      timeElement.classList.add('message-time');
      timeElement.textContent = timeString;
      
      messageDiv.appendChild(messageContent);
      messageDiv.appendChild(timeElement);
      chatMessages.appendChild(messageDiv);
      
      // Scroll to bottom
      chatMessages.scrollTop = chatMessages.scrollHeight;
      
      // Save to conversation history
      if (saveToHistory) {
        conversationHistory.push({
          role,
          content,
          timestamp: time.toISOString()
        });
        localStorage.setItem('conversationHistory', JSON.stringify(conversationHistory));
      }
    }
    
    // Show typing indicator
    function showTypingIndicator() {
      const typingDiv = document.createElement('div');
      typingDiv.classList.add('typing-indicator');
      typingDiv.id = 'typing-indicator';
      
      for (let i = 0; i < 3; i++) {
        const dot = document.createElement('div');
        dot.classList.add('typing-dot');
        typingDiv.appendChild(dot);
      }
      
      chatMessages.appendChild(typingDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Hide typing indicator
    function hideTypingIndicator() {
      const typingIndicator = document.getElementById('typing-indicator');
      if (typingIndicator) {
        typingIndicator.remove();
      }
    }
    
    // Clear conversation
    function clearConversation() {
      if (confirm('Are you sure you want to clear the conversation?')) {
        chatMessages.innerHTML = '';
        conversationHistory = [];
        localStorage.removeItem('conversationHistory');
        appendMessage('bot', "Hello! I'm your AI assistant. How can I help you today?", new Date().toISOString());
      }
    }
    
    // Send message to API
    async function sendMessage() {
      const userMessage = userInput.value.trim();
      if (!userMessage) return;
      
      // Disable input while processing
      userInput.disabled = true;
      sendBtn.disabled = true;
      
      // Add user message to chat
      appendMessage('user', userMessage);
      userInput.value = '';
      
      // Show typing indicator
      showTypingIndicator();
      
      try {
        // Prepare conversation context
        const messages = conversationHistory.map(msg => ({
          role: msg.role === 'user' ? 'user' : 'model',
          parts: [{ text: msg.content }]
        }));
        
        // Add current user message
        messages.push({
          role: 'user',
          parts: [{ text: userMessage }]
        });
        
        // Call API
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            contents: messages,
            generationConfig: {
              temperature: 0.9,
              topK: 1,
              topP: 1,
              maxOutputTokens: 2048,
              stopSequences: []
            },
            safetySettings: [
              {
                category: "HARM_CATEGORY_HARASSMENT",
                threshold: "BLOCK_MEDIUM_AND_ABOVE"
              },
              {
                category: "HARM_CATEGORY_HATE_SPEECH",
                threshold: "BLOCK_MEDIUM_AND_ABOVE"
              },
              {
                category: "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                threshold: "BLOCK_MEDIUM_AND_ABOVE"
              },
              {
                category: "HARM_CATEGORY_DANGEROUS_CONTENT",
                threshold: "BLOCK_MEDIUM_AND_ABOVE"
              }
            ]
          }),
        });
        
        const data = await response.json();
        
        // Hide typing indicator
        hideTypingIndicator();
        
        if (data.candidates && data.candidates.length > 0) {
          const botMessage = data.candidates[0].content.parts[0].text;
          appendMessage('bot', botMessage);
        } else {
          appendMessage('bot', "I'm sorry, I couldn't generate a response. Please try again.");
        }
      } catch (error) {
        hideTypingIndicator();
        appendMessage('bot', "Oops! Something went wrong. Please check your connection and try again.");
        console.error('API Error:', error);
      } finally {
        // Re-enable input
        userInput.disabled = false;
        sendBtn.disabled = false;
        userInput.focus();
      }
    }
    
    // Initialize the app
    init();
  </script>
</body>
</html>