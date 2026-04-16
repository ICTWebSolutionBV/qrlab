# QR Lab

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

A self-hosted WiFi QR code generator built with Laravel and Vue.js. Create styled QR codes with custom colors, dot styles, logos, and export in multiple formats. Invite-only user management with passkey authentication.

> **Disclaimer:** This software is provided "as is", without warranty of any kind. Use at your own risk. The authors are not responsible for any data loss, security breaches, or other damages resulting from the use of this software. Always review the code and configure proper security measures before deploying to production.

## Features

- **WiFi QR Codes** - Generate QR codes for WiFi networks with SSID, password, and encryption type (WPA/WPA2/WPA3/WEP).
- **Live Preview** - Instant client-side QR preview that updates as you type, powered by qr-code-styling.
- **Custom Styling** - Choose dot styles (square, dots, rounded, classy), corner styles, foreground/background colors, and error correction levels.
- **Logo Embedding** - Upload a logo to embed in the center of your QR code with adjustable size.
- **Text Labels** - Add header and footer text above/below the QR code.
- **Multiple Export Formats** - Download as PNG, JPG, or SVG from the browser. Server-side export supports PNG, SVG, and EPS.
- **Light & Dark Mode** - Automatic theme switching based on system preference, with manual toggle in profile settings.
- **Save & Edit** - Save QR codes to your account and modify them at any time.
- **Invite-Only Access** - No public registration. Admins invite users via secure, single-use invite links.
- **Passkey Authentication** - Sign in with Face ID, Touch ID, or Windows Hello via WebAuthn.
- **Admin Panel** - User management and invite administration.
- **Responsive Design** - Fully responsive layout with sidebar navigation on desktop and bottom nav on mobile.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3+
- **Frontend**: Vue 3, Inertia.js v3, Tailwind CSS 4
- **QR Generation**: qr-code-styling (client-side preview/download), endroid/qr-code v6 (server-side export)
- **Authentication**: spatie/laravel-passkeys (WebAuthn)
- **Database**: MySQL/PostgreSQL/SQLite

## Installation

### Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8.0+ / PostgreSQL 14+ / SQLite

### Local Development

```bash
# Clone the repository
git clone https://github.com/ICTWebSolutionBV/qrlab.git
cd qrlab

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Configure your database in .env
# For SQLite (default):
touch database/database.sqlite

# For MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=qrlab
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seed admin user
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development servers
php artisan serve
npm run dev
```

The default admin account is `admin@qrlab.test` / `password`. Change the password after first login.

## Deploying with Ploi

### 1. Create a New Site

- In Ploi, create a new site pointing to your domain
- Set the web directory to `/public`
- Select PHP 8.3+ as the PHP version

### 2. Connect Repository

- Go to your site's **Repository** tab
- Connect to `github.com/ICTWebSolutionBV/qrlab`
- Set branch to `main`
- Enable **Install Composer dependencies**

### 3. Deploy Script

Replace the default deploy script with:

```bash
cd {SITE_DIRECTORY}
git pull origin main

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

npm ci
npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

echo "Application deployed!"
```

### 4. Environment Variables

In the **Environment** tab, update your `.env`:

```env
APP_NAME="QR Lab"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qrlab
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 5. Run Migrations & Seed Admin

SSH into your server (or use Ploi's **Terminal**) and run:

```bash
cd {SITE_DIRECTORY}
php artisan migrate --seed
```

This creates the default admin user (`admin@qrlab.test` / `password`). Change the credentials after first login.

### 6. SSL

Enable **Let's Encrypt** SSL in Ploi's **SSL** tab for your domain.

### 7. Storage Link

If not already created by the deploy script:

```bash
cd {SITE_DIRECTORY}
php artisan storage:link
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

**USE AT YOUR OWN RISK.** The authors assume no liability for any damages or issues arising from the use of this software.
