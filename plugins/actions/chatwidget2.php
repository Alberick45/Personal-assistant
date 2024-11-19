
<?php
require_once("config.php");
require_once("functions.php");

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = null; // or handle user not logged in
}
?>

<!-- In chat_widget.php -->
<!-- <link href="../css/chat_widget.css" rel="stylesheet">
<script src="../js/chat_widget.js" defer></script> -->

<!-- chat_widget.php -->
<div id="chat-toggle-btn" onclick="toggleChatWidget()">Chat</div>




<div id="chat-widget" style="display: none;">
    <div id="chat-header">
        <h3>Chat</h3>
        <button id="chat-close">X</button>
    </div>
    <div id="chat-body">
        <div id="contact-list">
            <!-- Contacts/Staff will be loaded dynamically here -->
        </div>
        <div id="chat-messages">
            <!-- Messages will be dynamically loaded here -->
        </div>
        <textarea id="chat-input" placeholder="Type a message..."></textarea>
        <button id="send-message">Send</button>
    </div>
</div>
