document.addEventListener('DOMContentLoaded', function() {
    loadComponents();
    fetchUserData();
});

function loadComponents() {
    const componentContainers = document.querySelectorAll('[data-component]');
    
    componentContainers.forEach(container => {
        const componentName = container.getAttribute('data-component');
        loadComponent(componentName, container);
    });
}

function loadComponent(componentName, container) {
    fetch(`/Project/public/html/${componentName}.html`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load ${componentName} component`);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            
            container.setAttribute('data-loaded', 'true');
            
            if (componentName === 'navbar') {
                highlightCurrentPage();
            }
            
            const event = new CustomEvent('componentLoaded', { 
                detail: { componentName: componentName }
            });
            document.dispatchEvent(event);
        })
        .catch(error => {
            console.error('Error loading component:', error);
            container.innerHTML = `<div class="error">Failed to load ${componentName}</div>`;
        });
}

function fetchUserData() {
    fetch('/Project/app/controller/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNavbarWithUserData(data);
            }
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
        });
}

function updateNavbarWithUserData(data) {
    document.addEventListener('componentLoaded', function(e) {
        if (e.detail.componentName === 'navbar') {
            updateNavbar();
        }
    });
    
    if (document.querySelector('[data-component="navbar"][data-loaded="true"]')) {
        updateNavbar();
    }
    
    function updateNavbar() {
        const avatarElem = document.getElementById('navbar-avatar');
        const usernameElem = document.getElementById('navbar-username');
        if (avatarElem) avatarElem.textContent = data.username.charAt(0).toUpperCase();
        if (usernameElem) usernameElem.textContent = data.username;
        
        const followingElem = document.getElementById('navbar-following-count');
        const followersElem = document.getElementById('navbar-followers-count');
        if (followingElem) followingElem.textContent = data.following_count;
        if (followersElem) followersElem.textContent = data.followers_count;
    }
}

function highlightCurrentPage() {
    const currentPage = window.location.pathname.split('/').pop();
    
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-item');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
    
    if (currentPage === '' || currentPage === 'index.php') {
        const homeLink = document.getElementById('nav-home');
        if (homeLink) homeLink.classList.add('active');
    }
}
function loadUserDataIntoNavbar() {
    fetch('../app/controller/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update avatar
                const avatarElement = document.getElementById('navbar-avatar');
                if (avatarElement) {
                    avatarElement.textContent = data.username.charAt(0).toUpperCase();
                }
                
                // Update username
                const usernameElement = document.getElementById('navbar-username');
                if (usernameElement) {
                    usernameElement.textContent = data.username;
                }
                
                // Update handle
                const handleElement = document.getElementById('navbar-handle');
                if (handleElement) {
                    handleElement.textContent = '@' + data.username.toLowerCase();
                }
            }
        })
        .catch(error => {
            console.error('Error loading user data:', error);
        });
}

function setActiveNavItem() {
    const currentPage = window.location.pathname.split('/').pop();
    
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const pageToNavMap = {
        'feed.php': 'nav-home',
        'explore.php': 'nav-explore',
        'notifications.php': 'nav-notifications',
        'messages.php': 'nav-messages',
        'profile.php': 'nav-profile'
    };
    
    // Add active class to current page nav
    const navId = pageToNavMap[currentPage];
    if (navId) {
        const navItem = document.getElementById(navId);
        if (navItem) {
            navItem.classList.add('active');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadUserDataIntoNavbar();
    setActiveNavItem();
});
