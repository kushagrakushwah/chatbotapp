<!DOCTYPE html>
<html>
<head>
    <title>ATMT Virtual Assistant</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            animation: pulse 2s infinite;
        }
        .chat-toggle button {
            background-color: #004aad;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-size: 16px;
            cursor: pointer;
        }
        .chatbox {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
            flex-direction: column;
            flex-grow: 1;
            z-index: 999;
            animation: slideUp 0.4s ease-out forwards;
        }
        .chat-header {
            background: #004aad;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header button {
            background: none;
            color: white;
            font-size: 20px;
            border: none;
            cursor: pointer;
        }
        .chat-log {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .chat-message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 85%;
            line-height: 1.4;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }
        .bot-message {
            background:rgb(216, 255, 237);
            align-self: flex-start;
            color: #000;
        }
        .user-message {
            background: #2196f3;
            color: white;
            align-self: flex-end;
        }
        .chat-buttons {
            margin-top: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .chat-buttons button {
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.1s ease;
        }
        .chat-buttons button:active {
            transform: scale(0.98);
        }
        .chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 0 0 0 12px;
            outline: none;
        }
        .chat-input .btn-group {
            display: flex;
        }
        .chat-input button {
            padding: 10px 15px;
            background: #004aad;
            color: white;
            border: none;
            cursor: pointer;
        }
        #main-menu-btn {
            background:rgb(204, 245, 214);
            color: #333;
            border-radius: 0;
        }
        .typing-indicator {
            font-size: 24px;
            color: #999;
            margin-top: 5px;
            animation: fadeIn 0.5s ease forwards;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="chat-toggle">
    <button onclick="toggleChatbox()">Chat with us</button>
</div>

<div class="chatbox" id="chatbox">
    <div class="chat-header">
        ATMT Virtual Assistant
        <button onclick="toggleChatbox()">×</button>
    </div>
    <div class="chat-log" id="chat-log"></div>
    <div class="chat-input">
        <input type="text" id="user-msg" placeholder="Type your message...">
        <div class="btn-group">
            <button id="main-menu-btn" onclick="sendMainMenu()">Main Menu</button>
            <button id="send-btn">Send</button>
        </div>
    </div>
</div>

<script>
    function toggleChatbox() {
        const chatbox = document.getElementById('chatbox');
        if (chatbox.style.display === 'flex') {
            chatbox.style.display = 'none';
        } else {
            chatbox.style.display = 'flex';
            chatbox.dataset.started = 'true';
            sendInitialMessage();
        }
    }

    function sendInitialMessage() {
        showTyping();
        setTimeout(() => {
            fetch('/chatbot/reply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: 'start' })
            })
            .then(res => res.json())
            .then(data => handleBotResponse(data));
        }, 800);
    }

    function sendMainMenu() {
        showTyping();
        setTimeout(() => {
            fetch('/chatbot/reply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: 'start' })
            })
            .then(res => res.json())
            .then(data => handleBotResponse(data));
        }, 800);
    }

    document.getElementById('send-btn').addEventListener('click', function () {
        const msg = document.getElementById('user-msg').value.trim();
        if (!msg) return;
        appendMessage(msg, 'user-message');
        showTyping();
        setTimeout(() => {
            fetch('/chatbot/reply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: msg })
            })
            .then(res => res.json())
            .then(data => handleBotResponse(data));
        }, 800);
        document.getElementById('user-msg').value = '';
    });

    function handleBotResponse(data) {
        removeTyping();
        const log = document.getElementById('chat-log');
        if (data.message) {
            appendMessage(data.message, 'bot-message');
            if (data.type === 'fallback' || data.isLastAnswer) {
                showSupportFooter();
            }
        }
        if (data.options) {
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'chat-buttons';
            data.options.forEach(option => {
                const btn = document.createElement('button');
                btn.innerText = option;
                btn.onclick = () => sendOption(option);
                buttonContainer.appendChild(btn);
            });
            log.appendChild(buttonContainer);
            log.scrollTop = log.scrollHeight;
        }
    }

    function appendMessage(text, className) {
        const log = document.getElementById('chat-log');
        const div = document.createElement('div');
        div.className = 'chat-message ' + className;
        div.innerText = text;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    }

    function sendOption(option) {
        document.getElementById('user-msg').value = option;
        document.getElementById('send-btn').click();
    }

    function showTyping() {
        removeTyping();
        const log = document.getElementById('chat-log');
        const typing = document.createElement('div');
        typing.id = 'typing-indicator';
        typing.className = 'typing-indicator';
        typing.innerText = '...';
        log.appendChild(typing);
        log.scrollTop = log.scrollHeight;
    }

    function removeTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    function showSupportFooter() {
        const log = document.getElementById('chat-log');
        const support = document.createElement('div');
        support.className = 'chat-message bot-message';
        support.innerHTML = `Still stuck? <a href="https://wa.me/919130480758?text=Hello,%20I%20need%20help%20with%20ATMT%20Pro" target="_blank">💬 Chat on WhatsApp</a><br>📞 <a href="tel:+919560446447">Talk to us: +91 9560446447</a>`;
        log.appendChild(support);
        log.scrollTop = log.scrollHeight;
    }
</script>
</body>
</html>
