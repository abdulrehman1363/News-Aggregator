# News Aggregator Backend API

A comprehensive Laravel-based backend API for a news aggregator application that pulls articles from multiple news sources and provides powerful search, filtering, and personalization features.

## Features

- **Multi-Source News Aggregation**: Fetches articles from NewsAPI, The Guardian, and New York Times
- **RESTful API**: Clean and intuitive API endpoints for frontend consumption
- **Advanced Search**: Full-text search across article titles, descriptions, and content
- **Filtering**: Filter articles by date range, category, source, and author
- **User Preferences**: Personalized news feed based on user's preferred sources, categories, and authors
- **Authentication**: Secure authentication using Laravel Sanctum
- **Scheduled Updates**: Automatic hourly fetching of new articles
- **Best Practices**: Follows SOLID principles, DRY, and KISS principles
- **Repository-Service Pattern**: Clean separation of concerns with Repository and Service layers
- **Comprehensive Exception Handling**: Robust error handling with custom exceptions, detailed logging, and consistent error responses

## Architecture

This application follows a clean architecture pattern with clear separation of concerns:

```
┌──────────────────────────────────────────────────────────────────┐
│                        HTTP Request                               │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                   ┌─────────▼──────────┐
                   │   Controllers      │ (Handles HTTP requests/responses)
                   │  - Invokable       │ (Single responsibility per controller)
                   │  - Uses Resources  │ (API Resources for responses)
                   └─────────┬──────────┘
                             │
                   ┌─────────▼──────────┐
                   │   Services         │ (Business logic)
                   │  - ArticleService  │
                   │  - UserService     │
                   │  - SourceService   │
                   │  - CategoryService │
                   └─────────┬──────────┘
                             │
                   ┌─────────▼──────────┐
                   │   Repositories     │ (Data access layer)
                   │  - ArticleRepo     │
                   │  - UserRepo        │
                   │  - SourceRepo      │
                   │  - CategoryRepo    │
                   │  - AuthorRepo      │
                   └─────────┬──────────┘
                             │
                   ┌─────────▼──────────┐
                   │   Models           │ (Eloquent ORM)
                   │  - Article         │
                   │  - User            │
                   │  - Source          │
                   │  - Category        │
                   │  - Author          │
                   └────────────────────┘
```

### Layer Responsibilities

- **Controllers**: Handle HTTP requests, validate input (via Form Requests), call services, and return formatted responses (via API Resources)
- **Services**: Contain business logic, orchestrate operations across multiple repositories
- **Repositories**: Handle all database queries and data access operations
- **Models**: Represent database tables and define relationships

## Requirements

- PHP 8.2 or higher
- Composer
- SQLite (default) or MySQL/PostgreSQL
- API Keys for news sources

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd news
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Update the following environment variables in your `.env` file:

```env
# Database Configuration (SQLite is default)
DB_CONNECTION=sqlite

# News API Keys
NEWSAPI_KEY=your_newsapi_key_here
GUARDIAN_KEY=your_guardian_api_key_here
NYTIMES_KEY=your_nytimes_api_key_here
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Fetch Initial Articles

```bash
php artisan articles:fetch
```

You can also fetch from a specific provider:

```bash
php artisan articles:fetch --provider=newsapi
php artisan articles:fetch --provider=guardian
php artisan articles:fetch --provider=nytimes
```

## Getting API Keys

### NewsAPI
1. Visit [https://newsapi.org/register](https://newsapi.org/register)
2. Register for a free account
3. Copy your API key

### The Guardian
1. Visit [https://open-platform.theguardian.com/access/](https://open-platform.theguardian.com/access/)
2. Register for a developer key
3. Copy your API key

### New York Times
1. Visit [https://developer.nytimes.com/get-started](https://developer.nytimes.com/get-started)
2. Create an account and register an app
3. Enable the Article Search API and Top Stories API
4. Copy your API key

## Running the Application

### Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

### Scheduled Task

To enable automatic article fetching every hour, add this to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run the scheduler manually:

```bash
php artisan schedule:work
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication Endpoints

#### Register
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### Article Endpoints

#### Get All Articles (with filters)
```http
GET /api/articles?keyword=technology&from=2024-01-01&to=2024-12-31&category=tech&source=newsapi&per_page=20
```

Query Parameters:
- `keyword` (optional): Search term for title, description, or content
- `from` (optional): Start date (YYYY-MM-DD)
- `to` (optional): End date (YYYY-MM-DD)
- `category` (optional): Filter by category slug
- `source` (optional): Filter by source identifier (newsapi, guardian, nytimes)
- `author` (optional): Filter by author name
- `sources` (optional): Array of source IDs
- `categories` (optional): Array of category IDs
- `authors` (optional): Array of author IDs
- `per_page` (optional): Results per page (default: 15, max: 100)

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Article Title",
      "description": "Article description...",
      "content": "Full article content...",
      "url": "https://example.com/article",
      "image_url": "https://example.com/image.jpg",
      "published_at": "2024-01-28T12:00:00.000000Z",
      "source": {
        "id": 1,
        "name": "NewsAPI",
        "api_identifier": "newsapi"
      },
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      },
      "author": {
        "id": 1,
        "name": "John Smith"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

#### Get Single Article
```http
GET /api/articles/{id}
```

### Source Endpoints

#### Get All Sources
```http
GET /api/sources
```

#### Get Single Source
```http
GET /api/sources/{id}
```

### Category Endpoints

#### Get All Categories
```http
GET /api/categories
```

#### Get Single Category
```http
GET /api/categories/{id}
```

### User Preference Endpoints (Authenticated)

#### Get User Preferences
```http
GET /api/preferences
Authorization: Bearer {token}
```

#### Update User Preferences
```http
POST /api/preferences
Authorization: Bearer {token}
Content-Type: application/json

{
  "preferred_sources": [1, 2],
  "preferred_categories": [1, 3, 5],
  "preferred_authors": [1, 2, 3]
}
```

#### Get Personalized Feed
```http
GET /api/preferences/feed?per_page=20
Authorization: Bearer {token}
```

Returns articles filtered by user's preferences.

## Project Structure

```
app/
├── Console/
│   └── Commands/
│       └── FetchArticles.php          # Command to fetch articles
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── ArticleController.php   # Article endpoints
│           ├── AuthController.php      # Authentication
│           ├── CategoryController.php  # Category endpoints
│           ├── SourceController.php    # Source endpoints
│           └── UserPreferenceController.php
├── Models/
│   ├── Article.php
│   ├── Author.php
│   ├── Category.php
│   ├── Source.php
│   ├── User.php
│   └── UserPreference.php
└── Services/
    ├── ArticleService.php              # Article business logic
    └── NewsProviders/
        ├── NewsProviderInterface.php   # Provider contract
        ├── NewsAPIProvider.php         # NewsAPI implementation
        ├── GuardianProvider.php        # Guardian implementation
        └── NYTimesProvider.php         # NY Times implementation
```

## Database Schema

### Tables

- **users**: User accounts
- **sources**: News sources (NewsAPI, Guardian, NY Times)
- **categories**: Article categories
- **authors**: Article authors
- **articles**: News articles with relationships to sources, categories, and authors
- **user_preferences**: User's preferred sources, categories, and authors

## Design Patterns & Best Practices

### SOLID Principles

1. **Single Responsibility**: Each class has one clear purpose
   - `ArticleService`: Handles article fetching and storage
   - `NewsProviderInterface`: Defines contract for news providers
   - Controllers: Handle HTTP requests/responses only

2. **Open/Closed**: Extensible without modification
   - New news providers can be added by implementing `NewsProviderInterface`
   - No changes needed to existing code

3. **Liskov Substitution**: Providers are interchangeable
   - All providers implement the same interface
   - Can swap providers without breaking functionality

4. **Interface Segregation**: Focused interfaces
   - `NewsProviderInterface` only includes necessary methods

5. **Dependency Inversion**: Depends on abstractions
   - `ArticleService` depends on `NewsProviderInterface`, not concrete implementations
   - Constructor injection for testability

### Other Best Practices

- **DRY (Don't Repeat Yourself)**: Reusable service classes and shared logic
- **KISS (Keep It Simple, Stupid)**: Clean, readable code without over-engineering
- **Repository Pattern**: Models encapsulate data access
- **Service Layer**: Business logic separated from controllers
- **API Resources**: Structured JSON responses (via pagination)

## Testing

Run the test suite:

```bash
php artisan test
```

## Scheduled Tasks

The application includes a scheduled task that runs every hour to fetch new articles:

```php
Schedule::command('articles:fetch')->hourly();
```

To manually run the scheduler:

```bash
php artisan schedule:work
```

## Troubleshooting

### API Keys Not Working

- Ensure your API keys are correctly set in the `.env` file
- Check that you haven't exceeded your API rate limits
- Verify keys are active and have the necessary permissions

### No Articles Fetching

- Check application logs in `storage/logs/laravel.log`
- Verify internet connectivity
- Ensure sources are marked as active in the database

### Authentication Issues

- Make sure Laravel Sanctum is properly installed
- Check that the `HasApiTokens` trait is added to the User model
- Verify the `Authorization: Bearer {token}` header is included in requests

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues and questions, please create an issue in the repository.
