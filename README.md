# News Aggregator API

Laravel-based news aggregator API that pulls articles from multiple sources with powerful search and personalization features.

## Features

- Multi-source aggregation (NewsAPI, The Guardian, New York Times)
- PostgreSQL full-text search with tsvector indexing
- Advanced filtering (date, category, source, author)
- User preferences and personalized feeds
- Laravel Sanctum authentication
- Repository-Service pattern with comprehensive exception handling
- Rate limiting and response compression
- Automated hourly article fetching

## Requirements

- PHP 8.2+
- Composer
- **PostgreSQL** (required for full-text search)
- News API keys (NewsAPI, Guardian, NYTimes)

## Installation

```bash
# Clone and install
git clone <repository-url>
cd news
composer install

# Configure environment
cp .env.example .env
# Update .env with PostgreSQL credentials and API keys:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=news
# NEWSAPI_KEY=your_key
# GUARDIAN_KEY=your_key
# NYTIMES_KEY=your_key

# Setup application
php artisan key:generate
php artisan migrate

# Fetch articles
php artisan articles:fetch
php artisan articles:fetch --provider=newsapi  # Or specific provider
```

## Running

```bash
# Start server
php artisan serve  # Available at http://localhost:8000/api

# Enable hourly article fetching
php artisan schedule:work
```

## API Endpoints

**Base URL**: `http://localhost:8000/api`

### Authentication
- `POST /register` - Register user
- `POST /login` - Login (returns token)
- `POST /logout` - Logout (requires auth)

### Articles
- `GET /articles` - List articles with filters
  - Params: `keyword`, `from`, `to`, `category`, `source`, `author`, `sources[]`, `categories[]`, `authors[]`, `per_page`
  - **Uses PostgreSQL full-text search** for `keyword` parameter
- `GET /articles/{id}` - Single article

### Sources & Categories
- `GET /sources` - List sources
- `GET /sources/{id}` - Single source
- `GET /categories` - List categories
- `GET /categories/{id}` - Single category

### User Preferences (Authenticated)
- `GET /preferences` - Get user preferences
- `POST /preferences` - Update preferences
- `GET /preferences/feed` - Personalized feed

See `postman_collection.json` for detailed examples.

## Database

**PostgreSQL with Full-Text Search**:
- `articles` table includes `search_vector` tsvector column (generated)
- GIN index on `search_vector` for fast text search
- Uses `to_tsvector()` and `plainto_tsquery()` for English search

**Tables**: `users`, `sources`, `categories`, `authors`, `articles`, `user_preferences`, `personal_access_tokens`

## Troubleshooting

Check logs at `storage/logs/laravel.log` for errors. Ensure PostgreSQL is running and API keys are valid.

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues and questions, please create an issue in the repository.
