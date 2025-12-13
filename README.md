# Mini-Blog: Articles + Comments + Replies

A simple web application for blogging with support for articles, comments, and replies to comments.

## Functionality

### Articles
- ✅ View list of all articles (title, author, short description)
- ✅ Add new articles (title, content, author)
- ✅ View full article text with comments

### Comments
- ✅ Display all comments under an article
- ✅ Add comments to an article (author + content)
- ✅ Reply to comments (1 level of nesting)
- ✅ Display discussion tree with indentation

## Technologies

**Backend:**
- PHP 8+
- Slim Framework 4
- MySQL/MariaDB
- REST API

**Frontend:**
- HTML5
- CSS3 (Bootstrap 5)
- Vanilla JavaScript
- AJAX for asynchronous requests

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

## Authors

Project completed as part of a lecture assignment.

