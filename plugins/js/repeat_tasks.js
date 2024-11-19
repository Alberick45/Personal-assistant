setInterval(function() {
    fetch('plugins/actions/repeat_tasks.php') // Trigger the PHP script every minute
        .then(response => response.text()) // Process the response
        .then(data => {
            console.log(data); // Log the response (optional)
        })
        .catch(error => console.error('Error triggering repeat task:', error));
}, 60000); // Trigger every 60 seconds (1 minute)
