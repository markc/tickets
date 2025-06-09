# TntSearch Integration Documentation

## Overview

TIKM uses TntSearch as its search engine, providing full-text search capabilities for tickets and FAQs. TntSearch is a pure PHP full-text search engine with minimal memory footprint, making it perfect for small to medium-sized applications.

## Why TntSearch?

- **Lightweight**: Pure PHP implementation with minimal memory usage
- **No External Dependencies**: Doesn't require separate search servers or databases
- **High Performance**: Fast indexing and searching for small to medium datasets
- **Easy Setup**: Works out of the box without complex configuration
- **Cost Effective**: No additional server resources required

## Configuration

### Environment Variables

```env
# TntSearch Configuration
SCOUT_DRIVER=tntsearch
SCOUT_QUEUE=true
TNTSEARCH_FUZZINESS=true
TNTSEARCH_FUZZY_MAX_EXPANSIONS=3
TNTSEARCH_FUZZY_DISTANCE=2
TNTSEARCH_ASYOUTYPE=false
```

### Scout Configuration

The TntSearch configuration is defined in `config/scout.php`:

```php
'tntsearch' => [
    'storage' => storage_path('scout'),
    'fuzziness' => env('TNTSEARCH_FUZZINESS', true),
    'fuzzy' => [
        'max_expansions' => env('TNTSEARCH_FUZZY_MAX_EXPANSIONS', 3),
        'distance' => env('TNTSEARCH_FUZZY_DISTANCE', 2),
    ],
    'tokenizers' => [
        '\TeamTNT\TNTSearch\Support\TokenizerInterface' => [
            //
        ],
    ],
    'asYouType' => env('TNTSEARCH_ASYOUTYPE', false),
],
```

## Index Storage

TntSearch stores its indexes as files in the `storage/scout` directory:
- `tickets.index` - Ticket search index
- `f_a_q_s.index` - FAQ search index

## Searchable Models

### Ticket Model

The Ticket model includes comprehensive search data:

```php
public function toSearchableArray()
{
    return [
        'id' => $this->id,
        'uuid' => $this->uuid,
        'subject' => $this->subject,
        'content' => strip_tags($this->content),
        'creator_name' => $this->creator?->name,
        'creator_email' => $this->creator?->email,
        'assigned_to_name' => $this->assignedTo?->name,
        'office_name' => $this->office?->name,
        'status_name' => $this->status?->name,
        'priority_name' => $this->priority?->name,
        'created_at' => $this->created_at,
    ];
}
```

### FAQ Model

The FAQ model provides question and answer content:

```php
public function toSearchableArray()
{
    return [
        'id' => $this->id,
        'question' => $this->question,
        'answer' => strip_tags($this->answer),
        'is_published' => $this->is_published,
    ];
}
```

## Search Features

### 1. Full-Text Search
- Search across ticket subjects, content, and related data
- Search FAQ questions and answers
- Fuzzy matching for typo tolerance

### 2. Advanced Filtering
- Filter by ticket status, priority, office, and assignee
- Date range filtering
- Office-specific FAQ filtering

### 3. Role-Based Results
- **Customers**: See only their own tickets
- **Agents**: See tickets from their assigned offices
- **Admins**: See all tickets

### 4. Saved Searches
- Save frequently used search queries
- Public and private saved searches
- Usage tracking and analytics

## Management Commands

### Import Data
```bash
# Import all tickets
php artisan scout:import "App\Models\Ticket"

# Import all FAQs
php artisan scout:import "App\Models\FAQ"
```

### Flush Indexes
```bash
# Clear ticket index
php artisan scout:flush "App\Models\Ticket"

# Clear FAQ index
php artisan scout:flush "App\Models\FAQ"
```

### Re-index Data
```bash
# Re-index all search data
php artisan scout:flush "App\Models\Ticket" && php artisan scout:import "App\Models\Ticket"
php artisan scout:flush "App\Models\FAQ" && php artisan scout:import "App\Models\FAQ"
```

## Search Implementation

### SearchController

The `SearchController` handles search requests with fallback support:

```php
private function searchTickets(string $query, array $filters, string $sortBy, string $sortOrder)
{
    // Start with Scout search if query is provided
    if (!empty(trim($query))) {
        try {
            $ticketQuery = Ticket::search($query)->query(function ($builder) use ($filters, $sortBy, $sortOrder) {
                return $this->applyTicketFilters($builder, $filters, $sortBy, $sortOrder);
            });
        } catch (\Exception $e) {
            // Fallback to regular query if Scout fails
            $ticketQuery = $this->applyTicketFilters(Ticket::query(), $filters, $sortBy, $sortOrder);
        }
    }
    
    // Apply authorization and pagination
    // ...
}
```

### Key Features
- **Fallback Support**: Falls back to database search if TntSearch fails
- **Relationship Loading**: Efficiently loads related models after pagination
- **Authorization**: Applies role-based filtering to search results

## Performance Considerations

### Indexing Performance
- Indexes are built incrementally as models are created/updated
- Queue indexing for better performance: `SCOUT_QUEUE=true`
- Index files are stored locally for fast access

### Search Performance
- TntSearch provides fast full-text search for small to medium datasets
- Fuzzy matching adds slight overhead but improves user experience
- Results are paginated to manage memory usage

### Memory Usage
- TntSearch uses minimal memory compared to external search engines
- Index files are memory-mapped for efficient access
- Typical memory usage: < 50MB for moderate datasets

## Troubleshooting

### Common Issues

#### 1. Search Not Finding Results
```bash
# Re-index the data
php artisan scout:flush "App\Models\Ticket"
php artisan scout:import "App\Models\Ticket"
```

#### 2. Permission Errors on Index Files
```bash
# Fix storage permissions
chmod -R 755 storage/scout
chown -R www-data:www-data storage/scout
```

#### 3. Index Corruption
```bash
# Delete and recreate indexes
rm storage/scout/*.index
php artisan scout:import "App\Models\Ticket"
php artisan scout:import "App\Models\FAQ"
```

### Debugging Search Issues

#### Check Index Files
```bash
ls -la storage/scout/
# Should show .index files with recent timestamps
```

#### Test Search Functionality
```bash
php artisan tinker
>>> App\Models\Ticket::search('test')->get();
>>> App\Models\FAQ::search('password')->get();
```

#### Enable Debug Mode
Add to your `.env` for detailed error logging:
```env
LOG_LEVEL=debug
```

## Comparison with Other Search Engines

| Feature | TntSearch | Meilisearch | Elasticsearch |
|---------|-----------|-------------|---------------|
| Memory Usage | ~50MB | ~3GB | ~2GB |
| Setup Complexity | Minimal | Moderate | Complex |
| External Dependencies | None | Rust Binary | Java/JVM |
| Fuzzy Search | ✓ | ✓ | ✓ |
| Real-time Updates | ✓ | ✓ | ✓ |
| Cost | Free | Free/Paid | Free/Paid |
| Best For | Small-Medium | Medium-Large | Large Scale |

## Migration from Meilisearch

If migrating from Meilisearch to TntSearch:

1. **Update Environment**:
   ```env
   SCOUT_DRIVER=tntsearch
   # Remove MEILISEARCH_* variables
   ```

2. **Update Configuration**:
   - Remove Meilisearch-specific config from `scout.php`
   - Add TntSearch configuration

3. **Re-index Data**:
   ```bash
   php artisan scout:import "App\Models\Ticket"
   php artisan scout:import "App\Models\FAQ"
   ```

4. **Update SearchController**:
   - Remove Meilisearch-specific query syntax
   - Use standard Scout methods
   - Add relationship loading after pagination

## Production Deployment

### Server Requirements
- PHP 8.3+
- Sufficient disk space for index files (typically < 100MB)
- Write permissions on `storage/scout` directory

### Backup Strategy
```bash
# Backup index files
tar -czf search-indexes-$(date +%Y%m%d).tar.gz storage/scout/

# Restore indexes
tar -xzf search-indexes-YYYYMMDD.tar.gz
```

### Monitoring
- Monitor index file sizes and growth
- Track search performance via application logs
- Monitor storage/scout directory disk usage

## Security Considerations

### Index File Protection
- Index files are stored outside the web root
- No direct HTTP access to search indexes
- Regular backup of index files recommended

### Data Privacy
- Search indexes contain searchable content
- Ensure proper access controls on server filesystem
- Consider encryption for sensitive data in indexes

## Future Enhancements

### Planned Features
- Advanced search syntax support
- Search result highlighting
- Search analytics and metrics
- Custom tokenizers for specialized content

### Performance Optimizations
- Index optimization for large datasets
- Incremental index updates
- Search result caching
- Parallel index building

## Support and Resources

### Documentation
- [TntSearch GitHub](https://github.com/teamtnt/tntsearch)
- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [TntSearch Driver Documentation](https://github.com/teamtnt/laravel-scout-tntsearch-driver)

### Community
- Laravel Scout community forums
- TntSearch GitHub issues
- TIKM project documentation

---

*This documentation covers the TntSearch integration in TIKM version 1.0. For updates and additional features, refer to the main project documentation.*