Status: Work in progress.

Migration tool for customer user account and membership data via MySQL database connection.

# Developer information
WordPress plugin made with [Phel](https://phel-lang.org/) (functional Lisp-family language compiling to PHP), consult wp-phel-plugin repository for more info.

Tool "pulls" customer data via MySQL connection from (remote) source system using PDB, populating the (local) target system database using WP PHP API functions.

Following information is required for PDO connection:

- `SOURCE_MYSQL_HOST`: IP or fqdn to MySQL server that is accessible over network
- `SOURCE_MYSQL_USER`: database username
- `SOURCE_MYSQL_PASSWORD` database password
- `SOURCE_MYSQL_DB`: database name
- `SOURCE_MYSQL_DB_PREFIX`: prefix (if not set, default `wp_` is expected)

## User account importing

Source system customer data is read over MySQL connection and new user accounts are created on target system the software is running.
Accounts that already exist on target are skipped.

Relevant customer data including password is imported with whitelisted keys in `wp_usermeta`.

Additional `legacy_user_id` usermeta is added to newly created users on target with source system's User ID.

## Membership importing
A membership plan ID mapping (associative array) is required for re-creating membership plans from source system on the target system.

Each membership with active status is read from source database and re-created on target using plugin's PHP API functions.

# WooCommerce Memberships database schema notes

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
### Query all active memberships
select * from wp_posts where post_status LIKE '%wcm-active%' AND post_type = 'wc_user_membership'

### Query user's memberships
select * from wp_posts where post_status LIKE '%wcm%' and post_author = 4703

### Query usermetas
select * from wp_usermeta where user_id = 4703


## Required workarounds

### `phel-config.php`

- XDebug's (included with VVV) infinite loop detection gives false positive on default setting and requires `ini_set('xdebug.max_nesting_level', 300);`
- Plugin Phel error log file path is set into plugin dir with `->setErrorLogFile($projectRootDir . 'error.log')`, but this should be changed for production.
