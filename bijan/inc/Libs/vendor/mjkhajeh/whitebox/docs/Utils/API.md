# API Utility

Provides helper functions for API responses and data formatting.

---

## Methods

### to_lower_before_response
Converts all keys of an array to lowercase recursively.

**Signature:**
```php
public static function to_lower_before_response(array $data): array
```

**Parameters:**
- `data` (array): The array to process.

**Returns:**
- (array) Array with all keys in lowercase.

**Example:**
```php
$response = API::to_lower_before_response(['Name' => 'Ali', 'Data' => ['Age' => 30]]);
// ['name' => 'Ali', 'data' => ['age' => 30]]
```

**Notes:**
- Useful for standardizing API output keys.
- Handles nested arrays automatically.
