<?php
class ChatAPI {
    private $db;

    public function __construct($host, $username, $password, $dbname) {
        // Database connection
        $this->db = new mysqli($host, $username, $password, $dbname);
        if ($this->db->connect_error) {
            die(json_encode(["status" => "error", "message" => "Database connection failed: " . $this->db->connect_error]));
        }
    }

    private function handleError($stmt) {
        if ($stmt->error) {
            $errorMessage = "Database error: " . $stmt->error;
            // Log error to file
            error_log($errorMessage, 3, '/path/to/logs/errors.log');
            throw new Exception($errorMessage);
        }
    }

    public function handleAction($action, $data) {
        // Check if $data is an array and is not null
        if (!is_array($data)) {
            return ["status" => "error", "message" => "Data is not an array or is null"];
        }
    
       
    
        // Route the action to the corresponding method, $data['chatDescription'], $data['isMuted']
        switch ($action) {
            case 'getChats':
                return $this->getChats($data['user_id'], $data['page'], $data['pageSize']);
            case 'startNewChat':
                return $this->startNewChat($data['chatName'], $data['chatType'], $data['participants']);
            case 'getChatHistory':
                return $this->getChatHistory($data['chatId'], $data['page'], $data['pageSize']);
            case 'getContacts':
                return $this->getContacts($data['user_id']);
            case 'searchChats':
                return $this->searchChats($data['user_id'], $data['keyword']);
            case 'deleteChat':
                return $this->deleteChat($data['chatId']);
            case 'sendMessage':
                return $this->sendMessage($data['chatId'], $data['senderId'], $data['messageContent'], $data['messageType']);
            case 'notifyUsersOfNewMessage':
                return $this->notifyUsersOfNewMessage($data['chatId'], $data['senderId'], $data['messageContent']);
            case 'sendRealTimeUpdate':
                return $this->sendRealTimeUpdate($data['chatId'], $data['messageContent']);
            default:
                return ["status" => "error", "message" => "Unknown action: $action"];
        }
    }
    

    public function getChats($userId, $page = 1, $pageSize = 20) {
        // Pagination: Calculate the offset
        $offset = ($page - 1) * $pageSize;

        $stmt = $this->db->prepare("
            SELECT c.chat_id, c.chat_name, c.chat_type, c.created_at
            FROM chats c
            JOIN chat_participants cp ON c.chat_id = cp.chat_id
            WHERE cp.user_id = ?
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $userId, $pageSize, $offset);
        $stmt->execute();
        $this->handleError($stmt);
        $result = $stmt->get_result();
        $chats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return ["status" => "success", "data" => $chats];
    }

    public function startNewChat($chatName, $chatType, $participants) {
        // Validate inputs
        if (empty($chatName)) {
            echo json_encode(["status" => "error", "message" => "Chat name is required."]);
            return;
        }
    
        if (!in_array($chatType, ['direct', 'group'])) {
            echo json_encode(["status" => "error", "message" => "Invalid chat type. Must be 'direct' or 'group'."]);
            return;
        }
    
        if (empty($participants) || !is_array($participants)) {
            echo json_encode(["status" => "error", "message" => "Participants must be a non-empty array."]);
            return;
        }
    
        // Begin transaction for atomicity
        $this->db->begin_transaction();
    
        try {
            // Insert the chat into the database
            $stmt = $this->db->prepare("INSERT INTO chats (chat_name, chat_type, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $chatName, $chatType);
            $stmt->execute();
    
            if ($stmt->error) {
                throw new Exception("Database error: " . $stmt->error);
            }
    
            $chatId = $this->db->insert_id; // Get the newly created chat ID
            $stmt->close();
    
            // Add participants to the chat
            $stmt = $this->db->prepare("INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?)");
            foreach ($participants as $userId) {
                if (!is_numeric($userId)) {
                    throw new Exception("Invalid participant ID: $userId");
                }
                $stmt->bind_param("ii", $chatId, $userId);
                $stmt->execute();
    
                if ($stmt->error) {
                    throw new Exception("Database error: " . $stmt->error);
                }
            }
            $stmt->close();
    
            // Commit transaction
            $this->db->commit();
            echo json_encode(["status" => "success", "message" => "Chat started successfully.", "chat_id" => $chatId]);
        } catch (Exception $e) {
            // Rollback on failure
            $this->db->rollback();
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    
    

    public function getChatHistory($chatId, $page = 1, $pageSize = 20) {
        // Pagination: Calculate the offset
        $offset = ($page - 1) * $pageSize;

        $stmt = $this->db->prepare("
            SELECT m.message_id, m.sender_id, m.content, m.message_type, m.sent_at
            FROM messages m
            WHERE m.chat_id = ?
            ORDER BY m.sent_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $chatId, $pageSize, $offset);
        $stmt->execute();
        $this->handleError($stmt);
        $result = $stmt->get_result();
        $history = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return ["status" => "success", "data" => $history];
    }

    public function getContacts($userId) {
        $stmt = $this->db->prepare("SELECT * FROM contacts WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $this->handleError($stmt);
        $result = $stmt->get_result();
        $contacts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return ["status" => "success", "data" => $contacts];
    }

    public function sendMessage($chatId, $senderId, $messageContent, $messageType = 'text') {
        // Basic validation
        if (empty($chatId) || empty($senderId) || empty($messageContent)) {
            return ["status" => "error", "message" => "Chat ID, sender ID, and message content are required"];
        }
    
        // Insert the message into the database
        $this->db->begin_transaction();
        try {
            // Insert the message into the messages table
            $stmt = $this->db->prepare("
                INSERT INTO messages (chat_id, sender_id, content, message_type, sent_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("iiss", $chatId, $senderId, $messageContent, $messageType);
            $stmt->execute();
            $this->handleError($stmt);
            $messageId = $stmt->insert_id;
    
            // Update the chat's last message
            $stmt = $this->db->prepare("
                UPDATE chats 
                SET last_message = ?, last_message_time = NOW() 
                WHERE chat_id = ?
            ");
            $stmt->bind_param("si", $messageContent, $chatId);
            $stmt->execute();
            $this->handleError($stmt);
    
            // Commit the transaction
            $this->db->commit();
    
            // Notify users of the new message (optional)
            $this->notifyUsersOfNewMessage($chatId, $senderId, $messageContent);
    
            return ["status" => "success", "message" => "Message sent successfully", "message_id" => $messageId];
        } catch (Exception $e) {
            $this->db->rollback();
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
    
    public function searchChats($userId, $keyword) {
        $stmt = $this->db->prepare("
            SELECT c.chat_id, c.chat_name, c.chat_type, c.created_at
            FROM chats c
            JOIN chat_participants cp ON c.chat_id = cp.chat_id
            WHERE cp.user_id = ? AND c.chat_name LIKE ?
        ");
        $likeKeyword = "%" . $keyword . "%";
        $stmt->bind_param("is", $userId, $likeKeyword);
        $stmt->execute();
        $this->handleError($stmt);
        $result = $stmt->get_result();
        $chats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return ["status" => "success", "data" => $chats];
    }

    public function deleteChat($chatId) {
        // Soft delete chat by marking as deleted instead of removing from database
        $stmt = $this->db->prepare("UPDATE chats SET deleted_at = NOW() WHERE chat_id = ?");
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $this->handleError($stmt);
        $stmt->close();
        return ["status" => "success", "message" => "Chat deleted successfully!"];
    }

    // New Method: Notify users of new messages
    public function notifyUsersOfNewMessage($chatId, $senderId, $messageContent) {
        // Get participants of the chat
        $stmt = $this->db->prepare("
            SELECT cp.user_id, u.email
            FROM chat_participants cp
            JOIN users u ON cp.user_id = u.user_id
            WHERE cp.chat_id = ? AND cp.user_id != ?");
        $stmt->bind_param("ii", $chatId, $senderId);
        $stmt->execute();
        $this->handleError($stmt);
        $result = $stmt->get_result();
        $participants = $result->fetch_all(MYSQLI_ASSOC);

        // Send email or push notification to each participant
        foreach ($participants as $participant) {
            // Send Email (Placeholder)
            $this->sendEmailNotification($participant['email'], $messageContent);
        }

        return ["status" => "success", "message" => "Notifications sent successfully"];
    }

    // Placeholder for sending email notifications
    private function sendEmailNotification($email, $messageContent) {
        $subject = "New message in your chat";
        $body = "You have a new message: " . $messageContent;

        // Use PHP mail function or an external service like SendGrid, Mailgun, etc.
        mail($email, $subject, $body);
    }

    // Placeholder for real-time updates (WebSocket)
    public function sendRealTimeUpdate($chatId, $messageContent) {
        // Implement WebSocket or other real-time update logic here
        // Example: Broadcasting to a WebSocket channel
        return ["status" => "success", "message" => "Real-time update sent"];
    }
}


$chatApi = new ChatAPI('localhost', 'root', 'Alby@18$', 'personal_assistant');

// Check the action from the request
if (isset($_GET['action'])) {
    $action = $_GET['action']; // Action passed via URL (e.g., sendMessage)
    
    // Get the data from the request (for POST, use $_POST)
    $data = json_decode(file_get_contents('php://input'), true);

    // Call the corresponding method based on action
    $response = $chatApi->handleAction($action, $data);

    // Return the response as JSON
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Action parameter missing"]);
}
?>
