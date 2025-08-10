WP-CLI migration tool for customer user account and membership data via MySQL database connection.

Requires PHP8.3+ (Phel) and source MySQL server version MySQL 5.7.22+ or MariaDB 10.3+ (`JSON_OBJECTAGG()`).

Code provided as is without any warranties (MIT Licensed), contact for paid support & customization needs.

Developed with [Phel](https://phel-lang.org/) (functional Lisp-family language compiling to PHP), see also [wp-phel-plugin](https://github.com/jasalt/phel-wp-plugin) repository for more info.

# Usage

When installed, plugin registers new WP-CLI command `wp migrate-memberships`.

Command "pulls" customer data via MySQL connection from (remote) source system using PDB, populating the (local) target system database using WP PHP API functions. Source system customer data is read over MySQL connection and new user accounts are created on target system the software is running. Accounts that already exist on target are skipped.

Relevant customer data including password is imported with whitelisted `wp_usermeta` keys. Additional `legacy_user_id` usermeta is added to migrated users on target with source system's User ID.

Following variables are required for PDO connection and need to be set in `wp-config.php`:

```php
// WooCommerce Memberships migration config
define('SOURCE_MYSQL_HOST', '123.234.345.456');
define('SOURCE_MYSQL_USER', 'asdf');
define('SOURCE_MYSQL_PASSWORD', 'asdf');
define('SOURCE_MYSQL_DB_NAME', 'asdf');
define('SOURCE_MYSQL_DB_PREFIX', 'asdf_');
define('SOURCE_MYSQL_DB_CHARSET', 'asdf');
define('SOURCE_MYSQL_DB_COLLATION', 'asdf');
```

Matching membership plans must be created beforehand manually on new site and membership plan ID mapping is defined in `wp-config.php`:

```php
// Old site membership plan id -> new site membership plan id
define('MEMBERSHIP_PLAN_MIGRATION_MAPPING',
[
1234 => 4321,
2345 => 5432,
3456 => 6453,
4567 => 7654,
5678 => 8765
]);
```

Each user membership (`wc_user_membership` CPT) with active status (`wcm-active` post status) is read from source database and re-created on target. Additional `legacy_plan_id` postmeta is added to migrated user memberships with source system's membership plan ID.

Certain use-case specific variables and logic is hardcoded so customizing `src/main.phel` might be required for making it work optimally.

# Developer information

## WooCommerce Memberships database schema notes

CPT `wc_membership_plan` parent post defines the membership plan with columns:
- `ID` defining membership plan ID
- `post_title` membership plan title
- `post_name` membership slug

It's children are CPT `wc_user_membership` entries which indicate users' membership plans.

CPT `wc_user_membership` column `post_author` stores User ID of the customer, column `post_status` indicates the status with values:
- `wcm-active` active membership (active memberships should be migrated)
- `wcm-cancelled`
- `wcm-delayed`
- `wcm-expired`
- `wcm-paused`

It's postmeta includes relevant keys:
- `_start_date` (should be migrated)
- `_end_date`(should be migrated)
- `_product_id` (not necessary as CPT )

## Example SQL queries:
### Query all membership plans

```sql
select * from wp_posts where post_type = "wc_membership_plan"
```

### Query all active memberships with meta

```sql
SELECT
    p.*,
    JSON_OBJECTAGG(pm.meta_key, pm.meta_value) AS post_meta
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_status LIKE '%wcm-active%'
  AND p.post_type = 'wc_user_membership'
GROUP BY p.ID;
```

### Query user's memberships
select * from wp_posts where post_status LIKE '%wcm%' and post_author = 4703

### Query customer users and all their usermetas as JSON

```sql
SELECT
    u.ID,
    u.user_login,
    u.user_pass,
    u.user_nicename,
    u.user_email,
    u.user_registered,
    u.display_name,
    JSON_OBJECTAGG(um.meta_key, um.meta_value) AS user_meta
FROM wp_users u
JOIN wp_usermeta um ON u.ID = um.user_id
WHERE u.ID IN (
    SELECT user_id
    FROM wp_usermeta
    WHERE meta_key = 'wp_capabilities'
    AND meta_value LIKE '%"customer";b:1;%'
)
GROUP BY u.ID, u.user_login, u.user_email, u.user_registered, u.display_name
ORDER BY u.ID;
```
Returns single row per user with meta key-value pairs as single JSON column.

## Required workarounds

### `phel-config.php`

- XDebug's (included with VVV) infinite loop detection gives false positive on default setting and requires `ini_set('xdebug.max_nesting_level', 300);`
- Plugin Phel error log file path is set into plugin dir with `->setErrorLogFile($projectRootDir . 'error.log')`, but this should be changed for production.
