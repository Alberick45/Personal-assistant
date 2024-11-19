<?php
require_once("config.php");
require_once("functions.php");

// Start the session if not already started
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = null; // Handle user not logged in
}
?>

<!-- Link to Chat Widget CSS -->
<style>
    /* General styles */
    #chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 350px;
        height: 500px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        display: none;
        overflow: hidden;
        font-family: Arial, sans-serif;
        z-index: 1000;
    }

    /* Header */
    #chat-header {
        background-color: #007bff;
        color: #fff;
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: bold;
    }

    #chat-header button {
        background: none;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }

    /* Body */
    #chat-body {
        display: flex;
        flex-direction: column;
        height: calc(100% - 50px); /* Adjust this as needed */
        overflow: hidden;
    }

    #contacts-container {
        padding: 10px;
        margin-top: 10px;
        background-color: lightgray;
        height: auto;
    }

    #contact-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
        background-color: #f9f9f9;
        height: 300px;
    }

    .contact {
        padding: 10px;
        margin: 5px 0;
        background-color: #fff;
        border-radius: 5px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: background-color 0.2s;
    }

    .contact:hover {
        background-color: #f1f1f1;
    }

    /* Messages */
    #chat-messages {
        flex: 2;
        overflow-y: auto;
        padding: 10px;
        background-color: #f4f6f9;
    }

    .message {
        margin: 5px 0;
        padding: 10px;
        border-radius: 8px;
        max-width: 80%;
        font-size: 14px;
        word-wrap: break-word;
    }

    .message.sent {
        background-color: #d1e7dd;
        align-self: flex-end;
    }

    .message.received {
        background-color: #f8d7da;
        align-self: flex-start;
    }

    /* Input Area */
    #chat-input-area {
        display: flex;
        padding: 10px;
        border-top: 1px solid #ddd;
        background-color: #fff;
        z-index: 10; /* Ensure it's above the messages */
    }

    #chat-input {
    flex: 1; /* Allows it to take up remaining space in the flex container */
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    outline: none;
    resize: none;
    background-color: #fff; /* Ensure the background isn't transparent */
}


    #send-message {
        margin-left: 10px;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
    }

    #send-message:hover {
        background-color: #0056b3;
    }

    /* Chat Toggle Button */
    #chat-toggle-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #007bff;
        color: #fff;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        text-align: center;
        line-height: 50px;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    #chat-toggle-btn:hover {
        background-color: #0056b3;
    }

</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sendMessageBtn = document.getElementById('send-message');
    const backToContactsBtn = document.getElementById('back-to-contacts');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const messagesContainer = document.getElementById('messages');
    const contactsContainer = document.getElementById('contacts-container');
    const chatInputArea = document.getElementById('chat-input-area');
    const contactList = document.getElementById('contact-list');
    let activeChatId = null;
    const userId = <?php echo json_encode($user_id); ?>; // PHP to JS - user_id

    // Initially hide chat messages and input area
    chatMessages.style.display = 'none';
    chatInputArea.style.display = 'none';

     // Toggle chat widget visibility
     window.toggleChatWidget = function () {
        const chatWidget = document.getElementById('chat-widget');
        if (chatWidget) {
            chatWidget.style.display = chatWidget.style.display === 'none' ? 'block' : 'none';
        }
    };

    // Handle back button click
    backToContactsBtn.addEventListener('click', function () {
        chatMessages.style.display = 'none'; // Hide chat messages
        chatInputArea.style.display = 'none'; // Hide input area
        contactsContainer.style.display = 'block'; // Show contacts
    });

    // Send a message
    sendMessageBtn.addEventListener('click', function () {
        const message = chatInput.value.trim();
        if (message && activeChatId) {
            fetch('plugins/actions/chat_api.php?action=sendMessage', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_id: activeChatId,
                    message: message,
                    sender_id: userId,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        loadChat(activeChatId); // Reload chat messages
                        chatInput.value = ''; // Clear input field
                    } else {
                        alert('Failed to send message');
                    }
                })
                .catch((error) => console.error('Error sending message:', error));
        }
    });

    function loadChat(chatId) {
    activeChatId = chatId;
    chatMessages.style.display = 'block'; // Show chat messages
    chatInputArea.style.display = 'flex'; // Show input area
    contactsContainer.style.display = 'none'; // Hide contacts

    fetch(`plugins/actions/chat_api.php?action=getChatHistory&chat_id=${chatId}`)
        .then((response) => response.json())
        .then((data) => {
            messagesContainer.innerHTML = '';
            if (data.status === 'success') {
                if (data.data && data.data.length > 0) {
                    data.data.forEach((message) => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add(
                            'message',
                            message.sender_id === userId ? 'sent' : 'received'
                        );
                        messageDiv.textContent = message.content;
                        messagesContainer.appendChild(messageDiv);
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight; // Auto-scroll to bottom
                } else {
                    messagesContainer.innerHTML = '<p>No chat history available.</p>';
                }
            } else {
                messagesContainer.innerHTML = '<p>Failed to load chat history.</p>';
            }
        })
        .catch((error) => {
            messagesContainer.innerHTML = '<p>Failed to load chat history.</p>';
        });
}

function loadContacts() {
    fetch('plugins/actions/chat_api.php?action=getContacts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            page: 1,
            pageSize: 10,
        }),
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.status === 'success') {
            data.data.forEach((contact) => {
                const contactElement = document.createElement('div');
                contactElement.classList.add('contact');
                contactElement.innerHTML = `
                    <p>${contact.Contact_name || 'No Name'}</p>
                    <p>${contact.Contact_phone || 'No Phone'}</p>
                `;
                contactList.appendChild(contactElement);

                // Add click event to start a chat when a contact is clicked
                contactElement.addEventListener('click', function () {
                    const contactId = contact.Contact_id;
                    const contactName = contact.Contact_name || 'No Name';

                    // Define the chat parameters
                    const chatName = `Chat with ${contactName}`;  // You can customize this as needed
                    const chatType = 'direct';  // Set as 'direct' for one-on-one chat
                    const participants = [userId, contactId];  // Both sender and receiver are participants
                    
                    // Start a new chat session
                    fetch('plugins/actions/chat_api.php?action=startNewChat', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            chatName: chatName,
                            chatType: chatType,
                            participants: participants
                        }),
                    })
                    .then((response) => response.json())
                    .then((data) => {
    if (data.status === 'success') {
        activeChatId = data.chat_id;
        loadChat(activeChatId);  // Load the chat after creation
        console.log(activeChatId);
    } else {
        alert('Failed to start chat: ' + data.message);  // Show detailed error message
    }
   
})

                    .catch((error) => {
                        console.error('Error starting chat:', error);
                    });
                });
            });
        } else {
            console.error('Error fetching contacts:', data.message);
        }
    })
    .catch((error) => console.error('Error loading contacts:', error));
}

    // Initialize by loading contacts
    loadContacts();
});
</script>


<!-- Chat Widget HTML -->
<div id="chat-toggle-btn" onclick="toggleChatWidget()">Chat</div>
<div id="chat-widget">
    <div id="chat-header">
        <span>Chat</span>
        <button id="chat-close">&times;</button>
    </div>
    <div id="chat-body">
        <div id="contacts-container">
            <h2>Contacts</h2>
            <div id="contact-list"></div>
        </div>

        <div id="chat-messages" style="display: none;">
            <button id="back-to-contacts">Back</button>
            <div id="messages"></div>
        </div>

        <div id="chat-input-area" style="display: none;">
            <input type="text" id="chat-input" placeholder="Type your message" />
            <button id="send-message">Send</button>
        </div>
    </div>
</div>
