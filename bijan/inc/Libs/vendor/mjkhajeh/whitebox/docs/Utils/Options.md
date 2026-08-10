# Options Utility

Provides helper functions for managing plugin/theme options and logo content.

---

## Methods

### get_option_name
Abstract method to get the option name.

**Signature:**
```php
abstract public static function get_option_name()
```

---

### get_options
Get options with defaults applied.

**Signature:**
```php
public static function get_options(array $defaults): array
```

**Parameters:**
- `defaults` (array): Default option values.

**Returns:**
- (array) Options with defaults.

---

### get_logo
Get logo content from options (text or image).

**Signature:**
```php
public static function get_logo($keys, $defaults, $bloginfo_option = 'name')
```

**Parameters:**
- `keys` (array): Option keys.
- `defaults` (array): Default values.
- `bloginfo_option` (string): 'name' or 'description'.

**Returns:**
- (string) Logo HTML or text.

**Notes:**
- Use in themes/plugins to retrieve and display logo content based on options.
