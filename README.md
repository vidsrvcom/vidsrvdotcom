# VidSrv.com API Suite

Complete RESTful API infrastructure for AI-generated content management.

## 📁 Structure

```
vidsrv.com/
├── character/          # Character API (40K+ characters)
├── video/              # Video API (784K+ videos)
├── image/              # Image API (7.8M+ images)
├── .htaccess          # Apache/IIS optimization
└── README.md          # This file
```

## 🚀 APIs Overview

### Character API (Blue Theme)
- **Base URL**: `/character/api.php`
- **Records**: 40,421 characters
- **Endpoints**: 9 (list, get, stats, random, similar, suggest, tags, trending, batch)
- **Filters**: 7 parameters
- **Features**: Full-text search on 5 fields, gender/style filtering

### Video API (Pink/Purple Theme)
- **Base URL**: `/video/api.php`
- **Records**: 784,701 videos
- **Endpoints**: 9 (list, get, stats, random, similar, suggest, tags, trending, batch)
- **Filters**: 21 parameters
- **Features**: Advanced filtering (type, action, quality, resolution, pose, outfit, background)

### Image API (Green Theme)
- **Base URL**: `/image/api.php`
- **Records**: 7,865,698 images
- **Endpoints**: 9 (list, get, stats, random, similar, suggest, tags, trending, batch)
- **Filters**: 13 parameters
- **Features**: Resolution filtering, pose/outfit/background search

## ⚡ Performance Optimizations

### Database Level
1. **Separate COUNT Queries**: Replaced `SQL_CALC_FOUND_ROWS` with separate COUNT queries to avoid temp table creation
2. **Indexed Sorting**: ORDER BY `id` (VARCHAR indexed) instead of `created_at` (TEXT) to avoid filesort
3. **Persistent Connections**: `PDO::ATTR_PERSISTENT => true` reduces connection overhead
4. **Prepared Statements**: All queries use prepared statements with `ATTR_EMULATE_PREPARES => false`
5. **Optimized Pagination**: LIMIT/OFFSET with efficient indexing

### Application Level
1. **Gzip Compression**: `ob_gzhandler` enabled on all API endpoints (60-80% size reduction)
2. **Response Optimization**: Remove null/empty values, type casting for numeric fields
3. **Input Validation**: Range validation (limit 1-100, page ≥1) to prevent abuse
4. **Error Handling**: Proper PDO exception handling with user-friendly messages

### Server Level (.htaccess)
1. **mod_deflate**: Server-side gzip compression for JSON responses
2. **Expires Headers**: Cache control (2min for lists, 5min for individual gets)
3. **Security Headers**: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
4. **PHP Settings**: memory_limit 256M, max_execution_time 30s

### Query Optimizations
- **Video List**: Handles 784K records with <3s response time
- **Image List**: Handles 7.8M records with <5s response time
- **Stats Endpoints**: Aggregation queries optimized with GROUP BY and LIMIT
- **Similar Endpoints**: Weighted scoring algorithm with efficient joins

## 📊 Benchmark Results

| Endpoint | Dataset Size | Response Time | Notes |
|----------|-------------|---------------|-------|
| Character Stats | 40K | ~200ms | Pre-aggregated counts |
| Character List | 40K | ~150ms | 20 items/page |
| Video Stats | 784K | ~2.5s | Complex aggregations |
| Video List | 784K | ~300ms | 20 items/page |
| Image Stats | 7.8M | ~4.5s | Largest dataset |
| Image List | 7.8M | ~500ms | 20 items/page |
| Batch (any) | N/A | <100ms | Max 50 IDs |

## 🔒 Security Features

1. **SQL Injection Protection**: PDO prepared statements with native type binding
2. **Input Sanitization**: Whitelist validation for sort fields, enum validation for filters
3. **CORS Configuration**: Controlled Access-Control headers
4. **Rate Limiting Ready**: Structure supports easy rate limit integration
5. **Error Message Sanitization**: No sensitive info exposure in error responses

## 📖 Documentation

Each API has comprehensive documentation:
- **Landing Page**: `index.html` - Feature overview with examples
- **API Docs**: `api-docs.html` - Complete endpoint reference with parameters

### Quick Links
- [Character API Docs](character/api-docs.html)
- [Video API Docs](video/api-docs.html)
- [Image API Docs](image/api-docs.html)

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL/MariaDB with utf8mb4_unicode_ci
- **Server**: Apache/IIS with mod_rewrite, mod_deflate, mod_expires
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (no dependencies)

## 🎯 API Design Patterns

### Consistent Response Format
```json
{
    "success": true|false,
    "data": [...],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 1000,
        "total_pages": 50
    }
}
```

### Error Response Format
```json
{
    "success": false,
    "error": "Error message"
}
```

### Similarity Scoring Algorithm
- **Character**: gender(5) + style(3) + visibility(2)
- **Video**: character_id(5) + type(3) + action(2) + style(2) + gender(1)
- **Image**: character_id(5) + pose(3) + outfit(2) + style(2) + gender(1)

## 🚦 Usage Examples

### Character API
```bash
# Get stats
curl "http://localhost/character/api.php?action=stats"

# List female anime characters
curl "http://localhost/character/api.php?action=list&gender=Female&style=Anime&limit=10"

# Search by name
curl "http://localhost/character/api.php?action=list&search=sakura"
```

### Video API
```bash
# Get stats
curl "http://localhost/video/api.php?action=stats"

# Filter by action and quality
curl "http://localhost/video/api.php?action=list&action_filter=Cowgirl&quality=ultra&limit=10"

# Find similar videos
curl "http://localhost/video/api.php?action=similar&id=VIDEO_ID&limit=20"
```

### Image API
```bash
# Get stats
curl "http://localhost/image/api.php?action=stats"

# Filter by pose and style
curl "http://localhost/image/api.php?action=list&pose=standing&style=Anime&limit=10"

# Autocomplete suggestions
curl "http://localhost/image/api.php?action=suggest&field=pose&q=stand&limit=5"
```

## 📈 Future Optimizations

1. **Redis Caching**: Cache stats and popular queries
2. **CDN Integration**: Serve static content via CDN
3. **Database Sharding**: Horizontal scaling for 10M+ records
4. **ElasticSearch**: Full-text search optimization
5. **GraphQL Gateway**: Unified query interface
6. **Rate Limiting**: Per-IP throttling with Redis
7. **API Versioning**: /v1/, /v2/ endpoint structure

## 🔧 Maintenance

### Database Maintenance
```sql
-- Optimize tables monthly
OPTIMIZE TABLE characters;
OPTIMIZE TABLE videos;
OPTIMIZE TABLE images;

-- Analyze tables for query planning
ANALYZE TABLE characters;
ANALYZE TABLE videos;
ANALYZE TABLE images;

-- Check indexes
SHOW INDEX FROM characters;
SHOW INDEX FROM videos;
SHOW INDEX FROM images;
```

### Log Monitoring
- PHP error logs: Check for PDO exceptions
- Apache access logs: Monitor response times
- MySQL slow query log: Identify optimization opportunities

## 📝 Change Log

### v1.0.0 (2025-12-02)
- Initial release with 3 complete APIs
- 27 total endpoints (9 per API)
- Gzip compression enabled
- Separate COUNT query optimization
- Comprehensive documentation

## 👥 Credits

Developed for VidSrv.com AI content management platform.

## 📄 License

Proprietary - All rights reserved.
"# vidsrvdotcom" 
"# vidsrvdotcom" 
