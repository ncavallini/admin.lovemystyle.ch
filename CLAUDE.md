# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a retail boutique management system (gestionale) for "Love My Style" - a Swiss boutique. The system handles sales, inventory, customers, bookings, gift cards, discount codes, and POS integration with receipt/label printing.

**Language:** Italian (UI text, comments, database content)
**Tech Stack:** PHP 8.3.16, MySQL, Bootstrap 5.3.3, jQuery

## Development Commands

### Dependency Management
```bash
# Install dependencies
php composer.phar install

# Update dependencies
php composer.phar update

# Check Composer version
php composer.phar --version
```

### PHP Development
```bash
# Check PHP version
php --version

# Run built-in PHP server (if needed for local testing)
php -S localhost:8000
```

### Cron Jobs
Located in `/cron/` directory. These are meant to be scheduled:
- `close_clockings.php` - Close employee time tracking
- `close_cash_desk.php` - Close cash desk at end of day
- `delete_expired_bookings.php` - Clean up expired bookings
- `delete_expired_gift_cards.php` - Clean up expired gift cards
- `restock_infinite_products.php` - Restock products marked as infinite
- `update_exchange_rates.php` - Update currency exchange rates

## Architecture

### Entry Point & Routing
- **Single entry point:** `index.php`
- **Routing:** Query parameter `?page=` determines which page to load
- **Page mapping:** Underscores convert to slashes (e.g., `?page=sales_add` → `/pages/sales/add.php`)
- **Authorization:** `Auth::is_page_allowed($page)` checks access before loading pages
- **Unauthenticated pages:** Defined in `Auth::$allowedPages` array (login, forgot-password, public forms, etc.)

### Directory Structure

```
/actions/          # AJAX endpoints (backend logic)
  /auth/           # Login, logout, password reset
  /bookings/       # Booking management
  /customers/      # Customer CRUD, loyalty cards
  /products/       # Product management, inventory, labels
  /sales/          # Sales transactions, payments
  /variants/       # Product variants (color/size)
  /giftcards/      # Gift card operations
  /discount-codes/ # Discount code operations
  /pos/            # POS hardware integration (printer, cash drawer)
  /public-forms/   # Public-facing forms (no auth required)
  actions_init.php # Common initialization for all actions

/pages/            # UI views (frontend)
  (mirrors /actions/ structure)

/inc/              # Shared includes
  /classes/        # PHP utility classes
  inc.php          # Main include file (loads all classes)
  config.php       # Configuration (gitignored)
  style.css        # Custom styles
  *.js             # JavaScript utilities

/components/       # Reusable UI components
  head.php         # HTML head, CSS includes
  header.php       # Page header
  nav_user.php     # Navigation for logged-in users
  nav_minimal.php  # Minimal navigation for public pages
  footer.php       # Footer with JavaScript includes

/templates/        # Email, label, report templates
  /emails/         # HTML email templates
  /labels/         # DYMO label templates (.dymo XML)
  /internals/      # PDF/Excel templates (inventory, receipts)
  /wallet/         # Apple/Google Wallet pass templates

/cron/             # Scheduled tasks

/vendor/           # Composer dependencies

/logs/             # Application logs (Monolog)

/private/          # Private files (credentials, etc.)

/assets/           # Static assets
```

### Core Classes (`/inc/classes/`)

**Database & Auth:**
- `DBConnection` - Singleton PDO connection to MySQL
- `Auth` - Session management, login/logout, role-based access (OWNER, ADMIN, USER)

**Utilities:**
- `Utils` - General helpers (error printing, redirects, formatting addresses/phones/IBANs, dropdowns)
- `MoneyUtils` - Money formatting using moneyphp/money library (amounts stored in cents)
- `Country` - Country code to name conversion
- `Pagination` - Pagination helper for lists

**Business Logic:**
- `Email` - PHPMailer wrapper for SMTP (handles DMARC alignment, Reply-To logic)
- `Brevo` - Integration with Brevo email service
- `LoyaltyCard` - Generate Apple Wallet and Google Wallet passes
- `InternalNumbers` - Generate sequential internal numbers (invoices, etc.)
- `Logging` - Monolog wrapper for application logging

**POS & Printing:**
- `POSHttpClient` - Guzzle HTTP client for POS middleware API
- `DYMOLabel`, `DYMOUtils` - DYMO label printer integration
- `ProductTagLabel`, `AddressLabel` - Label generation
- `BarcodeGenerator` - Barcode generation for products

### Authentication & Authorization

**Session-based auth** with three roles:
- `OWNER` - Full access
- `ADMIN` - Administrative access
- Regular users - Limited access based on role

**Key methods:**
- `Auth::login($username, $password)` - Authenticate user
- `Auth::is_logged()` - Check if user is logged in
- `Auth::is_owner($includeAdmin = false)` - Check if user is owner (optionally include admin)
- `Auth::is_admin()` - Check if user is admin
- `Auth::require_admin()` - Halt execution if not admin
- `Auth::require_owner()` - Halt execution if not owner/admin
- `Auth::get_username()` - Get current username
- `Auth::get_fullname()` - Get current user's full name

**Actions authentication:**
All files in `/actions/` include `actions_init.php` which checks authentication (except for tablet mode and public forms).

### Money Handling

**CRITICAL:** All monetary amounts are stored as **integers in cents** in the database.

**Formatting:**
```php
// Format from cents
MoneyUtils::format_price_int(12345, 'CHF') // Returns "123.45"

// Format Money object
$money = new Money\Money(12345, new Money\Currency('CHF'));
MoneyUtils::format_price($money) // Returns "123.45"
```

**Supported currencies:** CHF (Swiss Franc), EUR (Euro)

### Sales & Discounts

**Key business rules:**
- Sale-level discounts apply ONLY to items with `is_discounted = false`
- Items with `is_discounted = true` (already discounted) CANNOT receive additional discounts
- Discounted items CANNOT be returned (negative sales are blocked if they contain discounted items)
- Subtotals are computed separately for discountable vs. non-discountable items

**Sale workflow:**
1. Create sale → `sales` table with status='open'
2. Add items → `sales_items` table
3. Apply discount (optional) → Updates `sale_discount_percentage`
4. Process payment → Updates status to 'paid', creates cash/card entries
5. Print receipt → Via POS middleware

### Database Conventions

- **PDO with prepared statements** - Always use parameterized queries
- **Fetch mode:** `PDO::FETCH_ASSOC` (associative arrays)
- **UUIDs:** Used for sale_id, gift_card_code, etc. (generated via `UUID()`)
- **Timestamps:** `created_at`, `updated_at` columns (use `NOW()`)
- **Soft deletes:** Some tables use `deleted_at` instead of hard deletes
- **Foreign keys:** Use `USING(column_name)` in JOINs when column names match

### Error Handling & Control Flow

**Error display:**
```php
Utils::print_error("Error message");
goto end; // Jump to end label
```

**Redirects:**
```php
Utils::redirect("index.php?page=sales_view");
```

**Labels for control flow:**
Many pages use `goto` labels (e.g., `end:`, `footer:`) to skip rendering when errors occur.

### Frontend Patterns

**AJAX actions:**
Most forms submit to `/actions/*/` endpoints via AJAX or traditional POST.

**Success/error handling:**
- Actions typically redirect on success or print error messages
- JavaScript handles AJAX responses and updates UI

**Libraries used:**
- Bootstrap 5.3.3 (UI framework)
- jQuery (DOM manipulation, AJAX)
- Select2 (enhanced dropdowns)
- JsBarcode (barcode rendering)
- Bootbox (modal dialogs)
- Bootstrap Toaster (toast notifications)

### POS Integration

**Middleware API:** Uses `POSHttpClient` to communicate with POS hardware middleware.

**Endpoints:**
- Receipt printing
- Label printing (DYMO)
- Cash drawer control
- Test printer functionality

**Configuration:** `$GLOBALS['CONFIG']['POS_MIDDLEWARE_URL']` and API key

### Email System

**Two email backends:**
1. **SMTP** (PHPMailer) - Direct SMTP sending
2. **Brevo** - Transactional email service

**DMARC alignment:** From address always uses authenticated SMTP mailbox. Reply-To set to logged-in user's email for better UX.

**Templates:** HTML email templates in `/templates/emails/`

### Configuration

**Config file:** `/inc/config.php` (gitignored)

**Expected configuration variables:**
```php
$CONFIG = [
    'DB_HOSTNAME' => '...',
    'DB_PORT' => '...',
    'DB_NAME' => '...',
    'DB_USERNAME' => '...',
    'DB_PASSWORD' => '...',
    'SMTP_HOSTNAME' => '...',
    'SMTP_PORT' => '...',
    'SMTP_USERNAME' => '...',
    'SMTP_PASSWORD' => '...',
    'SMTP_ENCRYPTION' => 'tls', // or 'ssl'
    'SMTP_AUTH' => true,
    'SMTP_DEBUG' => 0,
    'POS_MIDDLEWARE_URL' => '...',
    'POS_MIDDLEWARE_API_KEY' => '...',
    // ... other config
];
```

## Code Style & Conventions

- **Language:** Italian for user-facing text, comments can be mixed IT/EN
- **Indentation:** Spaces (typically 4 spaces)
- **Database queries:** Always use prepared statements with `?` placeholders
- **Error messages:** Italian, user-friendly
- **Class names:** PascalCase
- **Function names:** snake_case
- **Constants:** UPPER_SNAKE_CASE

## Common Tasks

### Adding a new page
1. Create view in `/pages/category/name.php`
2. Create action endpoint in `/actions/category/name.php` (if needed)
3. Add route to navigation in `/components/nav_user.php`
4. Add page to `Auth::$allowedPages` if public access needed

### Adding a new product feature
1. Update database schema (add columns/tables)
2. Update product forms in `/pages/products/add.php` and `/pages/products/edit.php`
3. Update product actions in `/actions/products/add.php` and `/actions/products/edit.php`
4. Update product view in `/pages/products/view.php`

### Working with money
- Always store as cents (integer) in database
- Use `MoneyUtils::format_price_int($cents, $currency)` for display
- Never use floats for monetary calculations

### Testing POS hardware
- Navigate to POS settings page
- Use test endpoints in `/actions/pos/test_receipt_printer.php` and `/actions/pos/test_label_printer.php`

## Security Notes

- All actions check authentication via `actions_init.php`
- Session cookies use `secure`, `httponly`, `samesite` flags
- Passwords hashed with `PASSWORD_BCRYPT`
- SQL injection prevented via prepared statements
- XSS prevention: Always escape output with `htmlspecialchars()` when needed
