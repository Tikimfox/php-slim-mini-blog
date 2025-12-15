## Technical documentation

## Project Architecture
```
htdocs/
├── app/
│   ├── Config/          # Database configuration
│   ├── Controllers/     # API controllers
│   ├── Models/          # Data models
│   ├── Services/        # Business logic
│   └── routes/          # API routes
├── assets/
│   ├── css/            # Styles
│   ├── js/             # JavaScript files
│   └── img/            # Images
├── blog.html           # Articles list
├── blog-details.html   # Article details + comments
└── index.php           # API entry point
```

## API Endpoints

### Articles
- `GET /api/articles` - Get all articles
- `GET /api/articles/{id}` - Get article by ID
- `POST /api/articles` - Create new article

### Comments
- `GET /api/articles/{id}/comments` - Get article comments
- `POST /api/comments` - Add comment or reply

