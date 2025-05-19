document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop();
    
    if (currentPage === 'feed.php') {
        document.getElementById('nav-home').classList.add('active');
    } else if (currentPage === 'explore.php') {
        document.getElementById('nav-explore').classList.add('active');
    } else if (currentPage === 'notifications.php') {
        document.getElementById('nav-notifications').classList.add('active');
    } else if (currentPage === 'messages.php') {
        document.getElementById('nav-messages').classList.add('active');
    } else if (currentPage === 'profile.php') {
        document.getElementById('nav-profile').classList.add('active');
    }
    
    fetch('/Project/app/controller/get_user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set username
                const usernameElement = document.getElementById('navbar-username');
                if (usernameElement) {
                    usernameElement.textContent = data.username;
                }
                
                // Set avatar with first letter of username
                const avatarElement = document.getElementById('navbar-avatar');
                if (avatarElement) {
                    avatarElement.textContent = data.username.charAt(0).toUpperCase();
                }
            }
        })
        .catch(error => {
            console.error('Error fetching user info:', error);
        });
    
    const postButton = document.getElementById('openPostModal');
    if (postButton) {
        postButton.addEventListener('click', function(e) {
            e.preventDefault();
            const postTextarea = document.querySelector('.create-post-container textarea');
            if (postTextarea) {
                postTextarea.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    postTextarea.focus();
                }, 500);
            }
        });
    }
});