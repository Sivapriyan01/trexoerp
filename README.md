# ⚡ TrexoERP — Multi-Tenant Enterprise ERP & Storefront Engine

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11%2B-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/React-18-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React" />
  <img src="https://img.shields.io/badge/Vite-5.4-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/Tenancy-Stancl%20v3-0ea5e9?style=for-the-badge" alt="Tenancy" />
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License" />
</p>

<p align="center">
  <strong>TrexoERP</strong> is a robust, production-ready, multi-tenant Enterprise Resource Planning (ERP) platform featuring an integrated modern headless e-commerce engine. Built for high-volume retail, wholesale, and manufacturing businesses, it delivers tenant-isolated databases, real-time POS, GST accounting, CRM automation, and live customizable online storefronts.
</p>

---

## 🌟 Key Features & Modules

### 🏢 1. Robust Multi-Tenancy Engine
- Powered by `stancl/tenancy` v3.
- Complete data isolation with dedicated database schemas/files per tenant.
- Automatic routing via subdomains (e.g. `tenant.domain.com`) or custom root domains.
- Super Admin portal for tenant onboarding, subscription plans, domain mapping, and impersonation.

### 🧾 2. Smart POS & Billing Engine
- **Fast Checkout**: Barcode scanning, keyboard shortcuts, fast customer search, and split-second invoice generation.
- **Tax & Compliance**: Built-in GST calculation (CGST, SGST, IGST), HSN/SAC codes, and GSTR-1 export.
- **Invoicing & Print**: Thermal POS receipts (58mm/80mm), standard A4/A5 PDF invoices, and digital invoice links.
- **Advance & Pre-Orders**: Customer advance deposits, layaway/pre-orders, wallet balances, and instalment tracking.

### 📦 3. Inventory & Multi-Branch Management
- **Stock Movement**: Multi-branch stock transfers with approval workflows and in-transit tracking.
- **Traceability**: Complete item audit trails with historical stock logs and unit-of-measure conversions.
- **Returns & Warranty**: RMA management, customer & vendor returns, warranty claim workflows, and expiry date alerts.

### 🏭 4. Manufacturing & Production Flow
- Production planning and multi-stage assembly logs.
- Dynamic BOM (Bill of Materials) costing, direct/indirect labour, and electricity/MIS overheads.
- Worker task allocations, efficiency tracking, and piece-rate wage calculation.

### 🤝 5. CRM & Sales Automation
- Visual pipeline Kanban boards with customizable deal stages and workspaces.
- Embeddable public lead capture forms with automatic lead assignment.
- WhatsApp automation and customer engagement logs.

### 💰 6. Accounting & Financial Reports
- Double-entry bookkeeping: Chart of Accounts, Journal entries, and ledger postings.
- Real-time financial analytics: Profit & Loss, Balance Sheet, Trial Balance, and Daily Expense registers.
- Bi-directional **Tally Prime / ERP 9** integration (XML & JSON ledger synchronization).

### 🛍️ 7. Integrated Headless Storefront (`website/`)
- Modern, ultra-responsive React 18 + Vite customer storefront.
- **Multiple Theme Presets**: Modern Minimal, Premium Dark, Bold Commerce.
- Live no-code website configurator: update banners, branding, and color palettes directly from the ERP.
- Instant catalog synchronization: toggle items as "Show on Website", handle online orders, and trigger automatic inventory deduction.

---

## 🛠️ Architecture & Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 11/12 (PHP 8.3+) |
| **Multi-Tenancy** | `stancl/tenancy` (Database & Tenant Scoping) |
| **Database** | MySQL / SQLite (Tenant DBs auto-migrated) |
| **ERP Frontend** | Blade, Alpine.js, Tailwind CSS, Livewire / Vanilla JS |
| **Online Storefront** | React 18, Vite 5, Lucide Icons, Canvas Confetti |
| **Document Engine** | Barryvdh DomPDF, Smalot PDF Parser, Maatwebsite Excel |
| **Reverse Proxy** | Caddy / Nginx for multi-domain routing & automatic SSL |

---

## 🚀 Quick Start & Installation

### 1. Prerequisites
- **PHP** >= 8.3 with extensions: `pdo`, `mbstring`, `openssl`, `xml`, `curl`, `gd`, `zip`
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm** >= 9.x
- **SQLite** or **MySQL** server

---

### 2. Clone and Setup Environment

```bash
# Clone the repository
git clone https://github.com/Sivapriyan01/trexoerp.git
cd trexoerp

# Install PHP dependencies
composer install

# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate
```

---

### 3. Database Configuration & Migrations

Configure your primary database in `.env`:

```env
DB_CONNECTION=sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=trexoerp
# DB_USERNAME=root
# DB_PASSWORD=your_password
```

Run primary migrations:

```bash
php artisan migrate --seed
```

---

### 4. Setup ERP Frontend Assets

```bash
npm install
npm run build
# Or start Vite dev server:
npm run dev
```

---

### 5. Setup Headless Online Storefront (`website/`)

```bash
cd website
npm install
npm run dev
```

The storefront will start locally (typically at `http://localhost:5173`).

---

### 6. Local Multi-Domain Routing (Recommended)

To test subdomains locally (e.g., `admin.localhost`, `tenant1.localhost`), use Caddy:

```bash
caddy run
```
*A ready-to-use [Caddyfile](file:///d:/projects/square/Caddyfile) is included in the project root.*

---

## 📁 Repository Structure

```plaintext
trexoerp/
├── app/
│   ├── Http/Controllers/    # SuperAdmin and Tenant business logic
│   ├── Models/              # Central & Tenant Eloquent Models
│   ├── Services/            # Tally, Billing, SMS & WhatsApp integrations
│   └── Tenancy/             # Tenancy bootstrappers & domain resolvers
├── config/                  # App, database, and tenancy configuration
├── database/
│   ├── migrations/          # Central database migrations
│   └── migrations/tenant/   # Tenant-scoped database migrations
├── public/                  # Public web assets
├── resources/
│   ├── views/               # ERP Blade views (Billing, CRM, Reports, Setup)
│   └── js/ & css/           # ERP frontend styles and scripts
├── routes/
│   ├── web.php              # Central landing & superadmin routes
│   └── tenant.php           # Tenant application routes
├── storage/                 # Storage framework & logs
└── website/                 # Headless React + Vite e-commerce storefront
    ├── src/
    │   ├── themes/          # Modern Minimal, Premium Dark, Bold themes
    │   └── services/        # ERP API integration layer
    └── vite.config.js       # Storefront Vite bundler configuration
```

---

## 🔒 Security & Best Practices

- Ensure `.env` is never committed (it is already included in `.gitignore`).
- For production, run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
- Maintain secure database backup schedules using the built-in backup tools.

---

## 📄 License

This project is open-sourced under the [MIT License](LICENSE).
