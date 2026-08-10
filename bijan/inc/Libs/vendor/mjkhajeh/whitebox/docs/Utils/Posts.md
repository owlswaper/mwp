# Posts Utility

Provides helper functions for working with WordPress posts and post meta options.

---

## Methods

### get_post
Get a post object by ID, object, or array.

**Signature:**
```php
public static function get_post($post = null)
```

**Returns:**
- (WP_Post|null) Post object or null.

---

### get_post_id
Get the ID of a post by various means.

**Signature:**
```php
public static function get_post_id($post = null)
```

**Returns:**
- (int) Post ID or 0.

---

### get_post_options
Retrieve post options with defaults applied from post meta.

**Signature:**
```php
public static function get_post_options($defaults, $post_id = 0, array $options = [])
```

**Returns:**
- (array) Option keys and values.

---

### save_post_options
Save post options to post meta.

**Signature:**
```php
public static function save_post_options(array $options, $defaults, $post_id = 0)
```

**Returns:**
- (void)

**Notes:**
- Use these utilities to manage custom post meta and options.
