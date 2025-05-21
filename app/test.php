
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Web3Forms</title>
</head>
<body>
    <h1>Test Web3Forms Email</h1>
    <div id="result"></div>

    <button id="testButton">Send Test Email</button>

    <script>
    document.getElementById('testButton').addEventListener('click', function() {
        const result = document.getElementById('result');
        result.innerHTML = "Sending test email...";
        
        // Create data object
        const data = {
            access_key: '0546d7cd-d257-4e79-920e-ac5873d74133',
            subject: 'Test Email from Web3Forms',
            from_name: 'Project Notification',
            to: 'omaremad2642005@gmail.com', // Replace with your email
            message: '<h2>This is a test email</h2><p>If you received this, Web3Forms is working!</p>'
        };
        
        fetch('https://api.web3forms.com/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async (response) => {
            let json = await response.json();
            console.log(response);
            console.log(json);
            
            if (response.status == 200) {
                result.innerHTML = "Test email sent successfully! Check your inbox.";
            } else {
                result.innerHTML = "Error: " + json.message;
            }
        })
        .catch(error => {
            console.log(error);
            result.innerHTML = "Error: " + error.message;
        });
    });
    </script>
</body>
</html>