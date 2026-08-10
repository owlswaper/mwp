# WC Utility

Provides helper functions for WooCommerce integration, account endpoints, cart, and coupons.

---

## Methods

### get_account_endpoint
Retrieve the current WooCommerce account endpoint slug.

**Signature:**
```php
public static function get_account_endpoint($items = [])
```

**Returns:**
- (string) Endpoint slug.

---

### get_cart_count
Get the number of items in the WooCommerce cart.

**Signature:**
```php
public static function get_cart_count(): int
```

**Returns:**
- (int) Cart item count.

---

### get_active_coupons_for_user
Get all active WooCommerce coupons available for the current user.

**Signature:**
```php
public static function get_active_coupons_for_user()
```

**Returns:**
- (array[WC_Coupon]) Array of active coupons.

**Notes:**
- Use these utilities to integrate WooCommerce features in your project.
