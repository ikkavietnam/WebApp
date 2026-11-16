# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 📋 Tech Stack
- **Language**: PHP (8.x)
- **Session Management**: PHP Sessions (`$_SESSION`)
- **Database**: SQL Server 2019 (via `sqlsrv_*` extension)
- **Key Dependencies**:
  - `phpoffice/phpspreadsheet` (v5.7+) - Excel/spreadsheet operations
  - PHP Extensions: `sqlsrv` (SQL Server driver), `gd` (image processing), `zip` (archive handling)
- **Build Tool**: Composer (dependency management)
- **Frontend**: HTML/CSS/JavaScript (Tabulator.js, Handsontable for data grids)

## 🏗️ Project Structure

```
w:\WebStandard\www\App/
├── public/                              # Web root (document root)
│   ├── index.php                       # Main application SPA entry point
│   ├── loginForm.php                   # Login page
│   ├── About.php                       # About page
│   ├── api_gateway.php                 # API Gateway - routes all API requests to src/api/
│   ├── assets/                         # Static files (CSS, JS, images, fonts, vendor libs)
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   ├── fonts/
│   │   └── vendor/                     # Tabulator, Handsontable, SweetAlert2, etc.
│   ├── downloadAttach/                 # File download handling
│   ├── .htaccess                       # Apache routing/URL rewriting
│   └── *.json                          # Build/config metadata
├── src/                                 # Source code (outside web root)
│   ├── api/                            # API endpoint implementations
│   │   ├── auth/                       # Authentication (login, session check)
│   │   ├── manager/                    # Admin/manager features
│   │   ├── modules/                    # Feature modules (YCMH, RequestRemoteAccess, etc.)
│   │   └── ...                         # Other API route endpoints
│   ├── config/                         # Configuration (gitignored)
│   │   ├── config.php                  # DB connection, app config
│   │   └── config_smb_server.php       # SMB/network config
│   ├── core/                           # Core utility functions
│   │   ├── Database.php                # DB connection & helper functions (RESPONSE_CLIENT, db_connect, CHKQUERY)
│   │   ├── Helpers.php                 # Utility functions
│   │   ├── Authorization.php           # Auth/permission logic
│   │   └── mapping/                    # Data mapping classes (UsersMapping, etc.)
│   ├── vendor/                         # Composer dependencies (phpspreadsheet, etc.)
│   └── composer.json                   # Composer config with dependencies
├── tools/                               # Helper scripts
│   └── prepare_gateway.sh               # Shell script utility
└── CLAUDE.md                            # This file
```

**Important Note**: Database config files (`src/config/*.php`) are gitignored. Setup must create these locally with appropriate SQL Server credentials.

## 🔧 API Gateway & Routing System

The application uses a **centralized API Gateway** pattern:

1. **API Gateway** (`public/api_gateway.php`):
   - Single entry point for all API requests
   - URL pattern: `/api/<route>` (via `.htaccess` URL rewriting) or `api_gateway.php?route=<route>`
   - Maintains a `$routes` array mapping route names to handlers and permissions
   - All responses return JSON with structure: `{"ok": true/false, ...}`

2. **Route Declaration**:
   ```php
   $routes = [
       'auth/login'          => ['auth/login.php',          GW_PUBLIC,  [20, 300]],  // rate limit
       'manager/account/list' => ['manager/account/list.php', GW_USER, [100, 60]],
       'YCMH/Export'         => ['modules/YCMH/YCMH_Export_Excel.php', 3],  // require UsType <= 3
   ];
   ```
   - Element 1: relative path to handler file in `src/api/`
   - Element 2: permission level (`GW_PUBLIC`, `GW_USER`, or numeric UsType threshold)
   - Element 3 (optional): rate limiting `[max_requests, time_window_seconds]`

3. **Permission Levels**:
   - `GW_PUBLIC` - no authentication required
   - `GW_USER` - any authenticated user
   - Numeric (1, 3, 5, etc.) - role-based, numeric ≤ specified level (1=Admin, higher numbers=lower privilege)

## 🔧 Coding Standards & Conventions

### PHP Style
- PSR-12 style (camelCase for methods/variables, PascalCase for classes)
- Use type hints where possible for clarity
- Always use `<?php` and avoid closing `?>` tag (no closing tag in files)
- Document API endpoint requirements in comments

### API Endpoint Implementation
- **Location**: `src/api/{feature}/{action}.php`
- **Entry**: Always call `session_start()` at the top
- **Database**: Use helper functions from `src/core/Database.php`:
  - `db_connect()` - establish SQL Server connection
  - `CHKQUERY($stmt, $conn, $msg)` - verify query execution
  - `RESPONSE_CLIENT($code, $msg, $title)` - send error response and exit
- **Return Format**: Always `echo json_encode([...], JSON_UNESCAPED_UNICODE);`
- **Response Structure**: `{"ok": true/false, "data": {...}, "msg": "...", "ErrCode": ...}`

### Database Queries
- Database connection uses SQL Server via `sqlsrv_*` functions (NOT MySQL/mysqli)
- Always use prepared statements: `$stmt = sqlsrv_query($conn, $sql, $params);`
- Check result with `CHKQUERY()` before processing
- On error: call `RESPONSE_CLIENT()` which sends JSON and exits

### Authentication Flow
- Check `$_SESSION['user']` existence for current user
- User role stored in `$_SESSION['user']['UsType']` (numeric)
- Use `Authorization.php` for permission checks
- Gateway enforces permissions before invoking handler

### File Organization
- **API implementations**: `src/api/` with feature-based directories
- **Core utilities**: `src/core/` functions (Database.php helpers, Authorization.php)
- **Data mapping**: `src/core/mapping/` for query result mapping
- **Frontend assets**: `public/assets/` (CSS, JS, fonts, vendor libraries)
- **Static pages**: `public/*.php` (index.php, loginForm.php, etc.)

## ⚙️ Development Setup & Commands

### Initial Setup
```bash
# Install PHP dependencies
cd src/
composer install

# Create configuration files (local only, not committed)
# Copy and customize:
cp src/config/config.php.example src/config/config.php
cp src/config/config_smb_server.php.example src/config/config_smb_server.php
```

### Environment Requirements
- **PHP**: 8.x with extensions: `sqlsrv`, `gd`, `zip`, `json`
- **SQL Server**: 2019 or later (local or remote connection)
- **Web Server**: Apache with `.htaccess` support or IIS
- **Composer**: For dependency management (vendor packages in `src/vendor/`)

### Running Locally
1. Configure web server document root to `public/` folder
2. Ensure SQL Server connection credentials in `src/config/config.php`
3. Set correct `$appRoot` path in `public/api_gateway.php` (currently hardcoded for Docker: `/var/www/App`)
4. Access via: `http://localhost/` or `http://localhost/index.php`
5. Login via: `http://localhost/loginForm.php`

### API Testing
- **Via curl**:
  ```bash
  curl -X POST http://localhost/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"username":"...", "password":"..."}'
  ```
- **Via Postman**: Set Cookie header with PHP session ID from login response
- All responses return JSON format

### Database Connection
- **Driver**: SQL Server (PHP `sqlsrv_*` functions)
- **Connection**: Configured in `src/config/config.php` (gitignored)
- **Security**: Always use prepared statements via `sqlsrv_query($conn, $sql, $params)`
- **Helper**: Use `db_connect()` to establish connection, `CHKQUERY()` to verify queries

## ⚡ Common Tasks

### Add New API Endpoint
1. Create handler file in `src/api/{feature}/{action}.php`:
   ```php
   <?php
   session_start();
   $conn = db_connect();
   
   // Verify session/auth if needed
   if (!isset($_SESSION['user'])) {
       RESPONSE_CLIENT(401, 'Not authenticated');
   }
   
   // Your logic here
   $sql = "SELECT * FROM table WHERE id = ?";
   $stmt = sqlsrv_query($conn, $sql, [$_POST['id']]);
   CHKQUERY($stmt, $conn, "Error fetching data");
   
   $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
   echo json_encode(["ok" => true, "data" => $data], JSON_UNESCAPED_UNICODE);
   sqlsrv_close($conn);
   ```

2. Register in `public/api_gateway.php` route table:
   ```php
   'feature/action' => ['feature/action.php', GW_USER, [100, 60]],  // rate limit optional
   ```

3. Access via: `POST http://localhost/api/feature/action`

### Modify Database Logic
- Database connection & helpers in `src/core/Database.php`
- Key functions:
  - `db_connect()` - returns SQL Server connection resource
  - `RESPONSE_CLIENT($code, $msg, $title)` - send error + exit
  - `CHKQUERY($stmt, $conn, $msg)` - verify query success
- Use `sqlsrv_query($conn, $sql, $params)` for all queries
- Use `sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)` for results

### Handle Excel Operations
- **Library**: PHPSpreadsheet v5.7+
- **Location**: `src/vendor/phpoffice/phpspreadsheet/`
- **Example**: See `src/api/modules/YCMH/YCMH_Export_Excel.php`
- **Usage**:
  ```php
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'Data');
  $spreadsheet->getWriter('Xlsx')->save('file.xlsx');
  ```

### Handle File Uploads/Downloads
- **Downloads**: `src/api/modules/AttachDownload/AttachDownload.php`
- **Upload folder**: `public/uploadAttach/` (ensure write permissions)
- **Download handler**: `public/downloadAttach/index1.php`

## ⚠️ Important Notes & Security

### Security Practices
- ✅ **Always use prepared statements**: `sqlsrv_query($conn, $sql, $params)` with param binding
- ✅ **Check authentication**: Verify `$_SESSION['user']` exists before accessing user data
- ✅ **Check authorization**: Gateway enforces role checks, but handlers should validate
- ✅ **Validate/sanitize input**: Check `$_POST`, `$_GET`, `$_REQUEST` data before use
- ✅ **Use `RESPONSE_CLIENT()`**: For error responses - it sets proper headers, encodes JSON, and exits
- ❌ **NEVER use `eval()`** or `include $_POST['file']` with user input
- ❌ **NEVER hardcode credentials** in code - use `config/config.php` (gitignored)
- ❌ **NEVER put config files in `public/`** - they must be in `src/config/`

### Rate Limiting
- Gateway supports rate limiting by IP: `[max_requests, time_window_seconds]`
- Example: `[20, 300]` = max 20 requests per 300 seconds from one IP
- Protected routes prevent brute-force attacks (especially login)

### Common Issues
- **404 on API routes**: Check route is registered in `$routes` array in `api_gateway.php`
- **Session lost**: Always call `session_start()` before `$_SESSION` access
- **SQL Server connection fails**: Verify credentials in `src/config/config.php`, check SQL Server is running, ensure `sqlsrv` extension installed
- **File downloads fail**: Check `public/downloadAttach/` and `public/uploadAttach/` have write permissions
- **Excel generation slow**: PHPSpreadsheet is CPU-intensive for large files - consider streaming or async processing
- **CORS errors**: API returns JSON with proper headers - if calling from different domain, ensure browser allows it

### Configuration Files (Gitignored)
These must be created locally before running:
- `src/config/config.php` - SQL Server connection credentials, app settings
- `src/config/config_smb_server.php` - SMB/network share credentials (if used)
- `.env` - Environment variables (if used)

### Composer & Dependencies
- Installed in `src/` directory (separate from root)
- Run `composer install` in `src/` folder, NOT root
- Key dependency: `phpoffice/phpspreadsheet` ^5.7
- Extensions required: sqlsrv, gd, zip (verified in `src/composer.json` config)
- `composer update` should be rare - prefer pinned versions in `composer.lock`

## 📚 Key Files & Entry Points

| File | Purpose |
|------|---------|
| `public/index.php` | Main SPA entry point, loads UI framework |
| `public/loginForm.php` | Authentication page |
| `public/api_gateway.php` | Central API router - maintains `$routes` mapping |
| `src/core/Database.php` | SQL Server connection & helper functions |
| `src/core/Helpers.php` | Utility functions for handlers |
| `src/core/Authorization.php` | Permission & auth helper functions |
| `src/api/auth/login.php` | Login endpoint - returns user data & sets session |
| `src/api/auth/checkSession.php` | Check session validity (public endpoint) |
| `.htaccess` | Apache URL rewriting - maps `/api/<route>` to `api_gateway.php` |

## 🚀 Deployment Checklist
- [ ] PHP 8.x with `sqlsrv`, `gd`, `zip` extensions enabled
- [ ] SQL Server 2019+ configured and accessible
- [ ] All `src/composer.json` dependencies installed
- [ ] Config files created: `src/config/config.php`, `src/config/config_smb_server.php`
- [ ] Web root document root set to `public/` folder
- [ ] `.htaccess` configured for URL rewriting (or IIS equivalent)
- [ ] Folders with write permissions: `public/uploadAttach/`, `public/downloadAttach/`
- [ ] Session storage path outside web root (PHP default or custom `session.save_path`)
- [ ] Error logging configured (check PHP ini settings)
- [ ] `$appRoot` path in `public/api_gateway.php` matches deployment path

## 📝 Commit Message Format
- Use Vietnamese or English (preferably English)
- Format: `<type>: <description>`
  - `feat: add new API endpoint for X`
  - `fix: correct SQL query in module Y`
  - `refactor: simplify database connection logic`
  - `docs: update CLAUDE.md with new patterns`
  - `chore: update dependencies`
- Example: `feat: add rate limiting to sensitive endpoints`
- **Note**: Git user is `ikkavietnam`, attribution added automatically

---

**Last Updated**: 2026-11-16  
**Project**: Ikka ERP Application  
**Tech Stack**: PHP 8.x, SQL Server 2019, Composer, Tabulator.js
