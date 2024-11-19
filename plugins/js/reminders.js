// Check for reminders every 60 seconds
setInterval(function() {
    fetch('plugins/actions/check_reminders.php')  // Make sure this path is correct
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            // Debug log to check the response data
            console.log(data);
            
            // Check if reminder is found and show alert
            if (data.status === 'success' && data.reminder) {
                // Play the notification sound
                document.getElementById('notificationSound').play();

                alert('Reminder: ' + data.reminder.message);  // Show the reminder as an alert
                
            } else {
                console.log('No reminders at this time');  // No reminders available
            }
        })
        .catch(error => {
            console.error('Error checking reminders:', error);
        });
}, 60000); // Check every 60 seconds (1 minute)
