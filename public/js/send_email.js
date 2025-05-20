/**
 * Send email via Web3Forms API
 * @param {string} recipientEmail - The recipient's email
 * @param {string} subject - Email subject
 * @param {string} message - HTML message to send
 * @param {Function} callback - Optional callback function after sending
 */
async function sendEmail(recipientEmail, subject, message, callback = null) {
    // Create data object instead of FormData
    const data = {
        access_key: '0546d7cd-d257-4e79-920e-ac5873d74133',
        subject: subject,
        from_name: 'Project Notification',
        to: recipientEmail,
        message: message,
    };
    
    // Add debug information
    console.log('Sending email with the following data:');
    console.log('To:', recipientEmail);
    console.log('Subject:', subject);
    console.log('API Key:', data.access_key.substring(0, 8) + '...');
    
    try {
        const response = await fetch('https://api.web3forms.com/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('Web3Forms API response:', result);
        
        if (response.status == 200) {
            console.log('Email sent successfully');
            if (callback) callback(true);
        } else {
            console.error('Failed to send email:', result.message);
            if (callback) callback(false);
        }
    } catch (error) {
        console.error('Error sending email:', error);
        if (callback) callback(false);
    }
}

/**
 * Send a welcome email to a newly registered user
 * @param {string} username - User's username
 * @param {string} email - User's email address
 */
function sendWelcomeEmail(username, email) {
    const subject = 'Welcome to Our Platform!';
    const message = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 5px;">
            <h2 style="color: #333; border-bottom: 1px solid #eaeaea; padding-bottom: 10px;">Welcome, ${username}!</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">Thank you for registering on our platform. We're excited to have you join our community!</p>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">You can now:</p>
            <ul style="color: #555; font-size: 16px; line-height: 1.5;">
                <li>Create and share posts</li>
                <li>Connect with other users</li>
                <li>Explore trending content</li>
            </ul>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">If you have any questions, feel free to contact our support team.</p>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eaeaea; text-align: center; color: #888; font-size: 14px;">
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </div>
    `;
    
    console.log('Sending welcome email to:', email);
    return sendEmail(email, subject, message);
}

/**
 * Send a login notification email to a user
 * @param {string} username - User's username
 * @param {string} email - User's email address
 */
function sendLoginNotificationEmail(username, email) {
    const now = new Date();
    const formattedDate = now.toLocaleDateString();
    const formattedTime = now.toLocaleTimeString();
    
    const subject = 'New Login Detected';
    const message = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 5px;">
            <h2 style="color: #333; border-bottom: 1px solid #eaeaea; padding-bottom: 10px;">New Login Alert</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">Hello ${username},</p>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">We detected a new login to your account:</p>
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p style="margin: 5px 0; color: #555;"><strong>Date:</strong> ${formattedDate}</p>
                <p style="margin: 5px 0; color: #555;"><strong>Time:</strong> ${formattedTime}</p>
                <p style="margin: 5px 0; color: #555;"><strong>Username:</strong> ${username}</p>
            </div>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">If this was you, you can ignore this message.</p>
            <p style="color: #555; font-size: 16px; line-height: 1.5;">If you did not log in recently, please reset your password immediately.</p>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eaeaea; text-align: center; color: #888; font-size: 14px;">
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </div>
    `;
    
    console.log('Sending login notification email to:', email);
    return sendEmail(email, subject, message);
}