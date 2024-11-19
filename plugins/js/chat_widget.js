document.addEventListener('DOMContentLoaded', function() {
    const sendMessageBtn = document.getElementById('send-message');
    const chatCloseBtn = document.getElementById('chat-close');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const contactList = document.getElementById('contact-list');
    let activeChatId = null; // Track the active chat ID
    const userId = window.userId;  // Access user ID passed globally

    // Toggle chat widget visibility
    window.toggleChatWidget = function() {
        const chatWidget = document.getElementById('chat-widget');
        if (chatWidget.style.display === 'none' || chatWidget.style.display === '') {
            chatWidget.style.display = 'block';
            console.log('Chat widget is now visible');
        } else {
            chatWidget.style.display = 'none';
            console.log('Chat widget is now hidden');
        }
    };

    // Close the chat widget
    chatCloseBtn.addEventListener('click', () => {
        document.getElementById('chat-widget').style.display = 'none';
    });

    // Send message
    sendMessageBtn.addEventListener('click', function() {
        const message = chatInput.value.trim();
        if (message && activeChatId) {
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    chat_id: activeChatId,
                    message: message,
                    sender_id: userId,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadChat(activeChatId);  // Reload chat messages
                    chatInput.value = '';  // Clear the input field
                } else {
                    alert('Failed to send message');
                }
            });
        }
    });

    // Load chat messages (this could be a separate function to load from the server)
    function loadChat(chatId) {
        fetch(`get_chat_history.php?chat_id=${chatId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    chatMessages.innerHTML = '';  // Clear old messages
                    data.data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message');
                        messageDiv.classList.add(message.sender_id === userId ? 'sent' : 'received');
                        messageDiv.textContent = message.content;
                        chatMessages.appendChild(messageDiv);
                    });
                }
            });
    }

    // Set active chat ID when user selects a chat
    function setActiveChat(chatId) {
        activeChatId = chatId;
        loadChat(chatId);
    }

    // Load contacts (users that the current user can chat with)
    function loadContacts() {
        fetch(`get_contacts.php?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    contactList.innerHTML = '';  // Clear old contacts
                    data.data.forEach(contact => {
                        const contactDiv = document.createElement('div');
                        contactDiv.classList.add('contact');
                        contactDiv.textContent = contact.name;
                        contactDiv.addEventListener('click', () => setActiveChat(contact.chat_id));
                        contactList.appendChild(contactDiv);
                    });
                }
            });
    }

    // Initialize the chat by loading contacts
    loadContacts();
});
