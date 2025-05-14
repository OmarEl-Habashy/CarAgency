Project/
├── app/
│   ├── controllers/    # Controller logic
│   ├── models/         # Database models
│   ├── views/          # View templates
│   └── core/           # Core framework files
├── public/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── images/         # Image assets
│   └── index.php       # Main entry point
├── config/             # Configuration files
├── database/           # Database migrations/seeds
└── vendor/             # Dependencies (Composer)



app/
└── views/
    ├── layout/
    │   ├── header.php
    │   └── footer.php
    ├── tweets/
    │   ├── index.php
    │   ├── create.php
    │   └── show.php
    ├── users/
    │   ├── profile.php
    │   └── settings.php
    └── auth/
        ├── login.php  # Your current login page
        └── register.php