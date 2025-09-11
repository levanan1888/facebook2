# Facebook Fanpage Sync Command

## Sử dụng

### Sync với date range cụ thể (như ads insights):
```bash
php artisan facebook:sync-fanpage-posts --access-token="YOUR_TOKEN" --user-id="YOUR_USER_ID" --since="2024-01-01" --until="2024-01-31"
```

### Sync với số ngày:
```bash
php artisan facebook:sync-fanpage-posts --access-token="YOUR_TOKEN" --user-id="YOUR_USER_ID" --days=30
```

### Chỉ sync pages:
```bash
php artisan facebook:sync-fanpage-posts --access-token="YOUR_TOKEN" --user-id="YOUR_USER_ID" --pages-only
```

### Chỉ sync posts:
```bash
php artisan facebook:sync-fanpage-posts --access-token="YOUR_TOKEN" --posts-only --days=7
```

## Database Tables

- `facebook_fanpage`: Thông tin fanpage
- `post_facebook_fanpage_not_ads`: Posts và insights

## Features

- ✅ Facebook Graph API v23.0
- ✅ Date range support (since/until)
- ✅ Rate limiting handling
- ✅ Progress tracking
- ✅ Error handling
- ✅ Insights data theo ngày
