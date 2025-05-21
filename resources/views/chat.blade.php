<!DOCTYPE html>
<html>
<head>
    <title>Chatbot</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>ATMT Chatbot</h1>

    <form id="chat-form">
        <input type="text" name="message" id="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
    </form>

    <div id="chat-box"></div>

    <script>
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let userMessage = document.getElementById('message').value;
            document.getElementById('chat-box').innerHTML += "<b>You:</b> " + userMessage + "<br>";

            fetch('/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: userMessage })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('chat-box').innerHTML += "<b>Bot:</b> " + data.reply + "<br><br>";
                document.getElementById('message').value = "";
            });
        });
    </script>
</body>
</html>
