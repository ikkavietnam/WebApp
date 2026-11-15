# CLAUDE.md - Ikka ERP Application

## 📋 Tech Stack
- **Language**: PHP (8.x)
- **Architecture**: API-first REST with traditional web frontend
- **Session Management**: PHP Sessions (`$_SESSION`)
- **Database**: MySQL/MariaDB (via custom Database class)
- **Key Dependencies**:
  - `phpoffice/phpspreadsheet` (v5.7+) - Excel/spreadsheet operations
  - PHP Extensions: `gd` (image processing), `zip` (archive handling)
- **Build Tool**: Composer (dependency management)
- **Frontend**: HTML/CSS/JavaScript (jQuery likely based on structure)

## 🏗️ Project Structure

```
w:\WebStandard\www\App/
├── public/                      # Web root (document root)
│   ├── index.php               # Main application entry
│   ├── loginForm.php           # Login page
│   ├── About.php               # About page
│   ├── api_gateway.php         # API entry point → routes to src/core/ApiRouter
│   ├── api/                    # API endpoint files
│   │   └── user/
│   │       └── logout.php      # User logout endpoint
│   ├── view/                   # View templates
│   ├── config/                 # Frontend config (if any)
│   ├── assets/                 # Static files (CSS, JS, images)
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   ├── downloadAttach/         # File download handling
│   └── .htaccess              # Apache routing/security
├── src/                         # Source code (outside web root)
│   ├── core/                   # Core application logic
│   │   ├── ApiRouter.php       # API routing/dispatch
│   │   ├── Database.php        # Database connection & queries
│   │   ├── Authorization.php   # Auth/permission logic
│   │   ├── Helpers.php         # Utility functions
│   │   └── mapping/
│   │       └── UsersMapping.php # User data mapping
│   └── vendor/                 # Composer dependencies
├── composer.json               # Main Composer config
├── composer.lock               # Locked versions
└── tools/                       # Helper scripts/tools
```

## 🔧 Coding Standards & Conventions

### PHP Style
- PSR-12 style (camelCase for methods/variables, PascalCase for classes)
- Use type hints where possible for clarity
- Always use `<?php` and avoid closing `?>` tag
- Document API endpoints with input/output expectations

### API Endpoints
- **Location**: `public/api/` or routed through `api_gateway.php`
- **Pattern**: `public/api/{resource}/{action}.php`
- **Return Format**: JSON with structure: `{"ok": true/false, "data": {...}, "error": "..."}`
- **Session Check**: Start with `session_start()` at top of endpoint files
- **Example**: `public/api/user/logout.php` returns `{"ok": true}`

### Database Queries
- Use `src/core/Database.php` class for all queries
- Use prepared statements to prevent SQL injection
- Map results using mapping classes in `src/core/mapping/`

### Authentication Flow
- Check `$_SESSION['user']` for current user
- Use `Authorization.php` for permission checks
- Login stores user data in session
- Logout destroys session (see `public/api/user/logout.php`)

### File Organization
- **API logic**: `src/core/` or mapped via `ApiRouter`
- **Utilities**: `src/core/Helpers.php`
- **Views**: `public/view/` (if using server-side templates)
- **Static assets**: `public/assets/`

## 🧪 Running & Testing

### Setup
```bash
composer install  # Install dependencies
```

### Running
1. Ensure PHP with extensions (gd, zip) is installed
2. Configure web server to serve `public/` as document root
3. Setup MySQL database with credentials in config
4. Access via web browser: `http://localhost/`

### API Testing
- Test endpoints via Postman or curl
- Example: `POST http://localhost/public/api/user/logout.php`
- Always include `Cookie` header for session validation
- Response always JSON

### Database
- Connection string configured in `src/core/Database.php`
- Uses prepared statements (security best practice)
- User data stored in standard users table

## ⚡ Common Tasks

### Add New API Endpoint
1. Create file: `public/api/{resource}/{action}.php`
2. Start with: `session_start();`
3. Include auth check if needed
4. Query database via `Database` class
5. Return JSON: `echo json_encode(["ok" => true/false, ...]);`

### Modify Database Logic
- Update queries in `src/core/Database.php`
- Use prepared statements: `$stmt->bind_param()`
- Map results with `src/core/mapping/` classes

### Handle Excel Operations
- Use `PHPSpreadsheet` library (already included)
- Path: `src/vendor/phpoffice/phpspreadsheet/`

## ⚠️ Important Notes & Gotchas

### Security
- ✅ Always use prepared statements for DB queries
- ✅ Check `$_SESSION['user']` for authentication
- ✅ Validate/sanitize all user input
- ❌ NEVER use `eval()` or dynamic requires with user input
- ❌ NEVER put database credentials in `public/` folder

### Common Issues
- **CORS errors**: API responses need proper headers if called from different domain
- **Session not persisting**: Ensure `session_start()` called before session use
- **Database connection**: Check credentials in `Database.php` match your setup
- **File downloads fail**: Check `downloadAttach/` folder has write permissions
- **Excel generation slow**: Large files take time with PHPSpreadsheet - consider async

### Composer
- Always run `composer install` (not `install --no-dev`) for dev
- Check `composer.json` for dependency versions - update carefully
- Use `composer update` only when intentional version bumps needed

## 📝 Commit Messages
- Use Vietnamese or English (preferably English for consistency)
- Format: `<type>: <description>`
  - `fix: [issue description]`
  - `feat: [feature description]`
  - `refactor: [what changed]`
  - `docs: [what was updated]`
- Example: `feat: add user logout API endpoint`

## 🚀 Deployment Checklist
- [ ] All dependencies in `composer.lock`
- [ ] Database migrations applied
- [ ] PHP extensions (gd, zip) enabled on server
- [ ] Web root points to `public/` folder
- [ ] `.htaccess` configured for routing
- [ ] Error logging configured
- [ ] Session storage secure (not in web root)

## 📚 Key Files Reference
- **API Gateway**: `public/api_gateway.php` → routes to `src/core/ApiRouter.php`
- **User Logout**: `public/api/user/logout.php` (simple session destroy)
- **Database Access**: `src/core/Database.php` (main DB class)
- **Auth Logic**: `src/core/Authorization.php`
- **Helpers**: `src/core/Helpers.php` (utility functions)

---

**Last Updated**: 2026-11-15  
**Project**: Ikka ERP Application  
**Git User**: ikkavietnam
