<div align="center">

<img src="https://img.shields.io/badge/TZLDashy-1.0.0-00ffbf?style=for-the-badge&logo=server&logoColor=black" alt="Version">
<img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/MariaDB-10.11-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB">
<img src="https://img.shields.io/badge/Nginx-1.25-009639?style=for-the-badge&logo=nginx&logoColor=white" alt="Nginx">
<img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
<img src="https://img.shields.io/badge/License-AGPL--3.0-blue?style=for-the-badge" alt="AGPL-3.0 License">

<br><br>

# ⚙️ TZLDashy

**A beautiful, self-hosted server dashboard for homelabbers and sysadmins.**

Manage bookmarks, monitor system health, access a browser terminal, manage users, and personalize your experience — all from a single polished UI running on your server.

[🚀 Quick Deploy](#-quick-deploy) · [📸 Screenshots](#-screenshots) · [✨ Features](#-features) · [⚙️ Configuration](#-configuration) · [🤝 Contributing](#-contributing)

---

</div>

## 📸 Screenshots

> _Dark mode · Custom accent colours · Live stats · Drag-and-drop apps_

| Dashboard (Home) | Apps Grid | System Stats |
|:---:|:---:|:---:|
| _screenshot_ | _screenshot_ | _screenshot_ |

| Profile & 2FA | Settings | First Run Setup |
|:---:|:---:|:---:|
| _screenshot_ | _screenshot_ | _screenshot_ |

---

## ✨ Features

### 🏠 Dashboard
- **Clock & weather** widget on the home screen (powered by WeatherAPI)
- **Google search bar** built-in
- **Bookmarks** — categorised app cards with icons, drag-and-drop reordering
- **Apps tab** — flat grid of all your web services
- **System Stats** — live CPU %, temperature, RAM, SSD, RAID disk, network I/O (refreshed every 3 s)
- **Browser Terminal** — embed ttyd/Wetty for SSH-in-a-tab

### 🔐 Authentication & Security
- **Multi-user login** with bcrypt password hashing
- **First-run setup** wizard — creates admin account on first launch
- **Role-based access** — Admin and User roles
- **Authenticator App 2FA** (TOTP / RFC 6238 — Google Authenticator, Aegis, etc.)
- 
- Secure session handling with HTTP-only cookies

### 👤 User Profile
- Upload a profile **avatar / picture**
- Update name and email
- Change password with current password verification
- Full **2FA management** per-user (enable/disable TOTP or Email OTP)

### ⚙️ Settings
- **Theme** — Dark / Light mode
- **Accent colour** — 10 presets + full custom colour picker
- **Custom primary & secondary colours** override
- **Font** — 9 Google Fonts options with live preview
- **Language** — interface language selection (en, bn, fr, de, es, ar, zh)
- **General** (admin) — App name, weather city, terminal URL
- **User Management** (admin) — Add / edit / delete users, assign roles

### 📬 Communication
- **Contact Us** page with categorised form (Bug / Feature / Question / Security)
- Messages stored in DB + email notifications to admin
- Auto-confirmation email sent to sender

### ℹ️ Info Pages
- **About** — app details, stack, version, owner
- **FAQ** — 10 common questions with accordion answers
- **Help** — feature guide and Docker quick-start reference

### 🐳 DevOps
- **Docker Compose** one-command deploy
- **Nginx 1.25** baked in, serving on **port 1011**
- **MariaDB 10.11** with health-checked startup
- Named volumes for all persistent data
- Production PHP-FPM 8.2 with OPcache

---

## 🚀 Quick Deploy

### Prerequisites
- Docker ≥ 24 with the Compose plugin (`docker compose version`)
- 512 MB RAM minimum

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/TechZeeLand/tzldashy.git
cd tzldashy

# 2. Create your environment file
cp .env.example .env

# 3. Start everything
docker compose up -d

# 4. Open your browser
open http://your-server-ip:1011
```

On first visit you'll see the **Setup Wizard** — create your admin account and you're done.

---

## ⚙️ Configuration

All configuration is done via environment variables in your `.env` file.

| Variable | Default | Description |
|---|---|---|
| `APP_NAME` | `TZLDashy` | Name displayed in the UI |
| `APP_KEY` | _(required)_ | 32-char secret key for session signing |
| `APP_URL` | `http://localhost:1011` | Full public URL |
| `APP_PORT` | `1011` | Host port for the dashboard |
| `DB_NAME` | `tzldashy` | Database name |
| `DB_USER` | `tzldashy` | Database user |
| `DB_PASS` | _(required)_ | Database password |
| `MYSQL_ROOT_PASSWORD` | _(required)_ | MariaDB root password |

### Generate a secure APP_KEY

```bash
openssl rand -hex 32
```

---

## 🐳 Portainer Deploy

1. In Portainer → **Stacks** → **+ Add stack**
2. Choose **Git repository** and point to this repo, or paste the `docker-compose.yml` content
3. In the **Environment variables** section, add all variables from `.env.example`
4. Click **Deploy the stack**

---

## 🗄️ Database

The schema is automatically imported from `database/schema.sql` on first startup.

- Server: `db`
- Username: `root`
- Password: `MYSQL_ROOT_PASSWORD` from your `.env`

---

## 🏗️ Project Structure

```
tzldashy/
├── docker-compose.yml          # Main compose file
├── .env.example                # Environment template
├── database/
│   └── schema.sql              # DB schema (auto-imported)
├── docker/
│   ├── nginx/
│   │   └── tzldashy.conf       # Nginx config (port 1011)
│   └── php/
│       └── Dockerfile          # PHP 8.2-FPM image
└── src/                        # Application source
    ├── config.php              # Bootstrap & constants
    ├── composer.json
    ├── index.php               # Main dashboard
    ├── api/                    # JSON & form endpoints
    │   ├── stats.php
    │   ├── system.php
    │   ├── users.php
    │   └── ...
    ├── auth/                   # Auth pages
    │   ├── setup.php           # First-run wizard
    │   ├── login.php
    │   ├── 2fa.php
    │   └── logout.php
    ├── lib/                    # Core libraries
    │   ├── Auth.php
    │   ├── Database.php
    │   ├── Helpers.php
    │   └── TOTP.php
    ├── mail/
    │   └── Mailer.php          # PHPMailer wrapper
    ├── pages/                  # UI pages
    │   ├── profile.php
    │   ├── settings.php
    │   ├── about.php
    │   ├── faq.php
    │   ├── help.php
    │   └── contact.php
    ├── partials/
    │   ├── header.php
    │   └── footer.php
    └── public/
        ├── Logos/              # App icons (volume)
        ├── uploads/avatars/    # User avatars (volume)
        └── assets/
            ├── css/app.css
            └── js/app.js
```

---

## 🔧 Optional: Browser Terminal (ttyd)

Add ttyd to your `docker-compose.yml` for a full in-browser SSH experience:

```yaml
  ttyd:
    image: tsl0922/ttyd:latest
    container_name: tzldashy_ttyd
    ports:
      - "2222:2222"
    command: ttyd --port 2222 bash
    restart: unless-stopped
    networks:
      - tzldashy_net
    privileged: true
```

Then set **Terminal URL** in TZLDashy → Settings → General to `http://your-ip:2222`.

---

## 🤝 Contributing

Contributions are very welcome! Here's how:

```bash
# Fork the repo, then:
git clone https://github.com/YOUR_USERNAME/tzldashy.git
cd tzldashy
cp .env.example .env
# Edit .env for local dev (APP_ENV=development)
docker compose up -d
```

### Guidelines
- Follow existing PHP/JS style (PSR-12 loosely)
- Test all changes locally with Docker
- Open a PR with a clear description of what changed and why
- For major features, open an issue first to discuss

### Reporting Bugs
Use [GitHub Issues](https://github.com/TechZeeLand/tzldashy/issues) with:
- Steps to reproduce
- Expected vs actual behaviour
- Your Docker / OS version

---

## 📄 License

```
GNU Affero General Public License v3 (AGPL-3.0)

Copyright (c) 2025 rayaz.org / TechZeeLand

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

<div align="center">

Built with ❤️ by **TechZeeLand** for **[rayaz.org](https://rayaz.org)**

⭐ Star this repo if TZLDashy is useful to you!

</div>
