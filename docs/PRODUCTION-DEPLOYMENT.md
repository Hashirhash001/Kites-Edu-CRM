# Production Deployment — Kites Edu CRM

Summary of production infrastructure, deployment layout, and local vs server sync status.  
**Last reviewed:** October 6, 2026

> **Cursor rule (local only, not committed):** Copy or sync from this doc into `.cursor/rules/production-deployment.mdc` on your machine. The `.cursor/` folder is gitignored.

**Live URL:** [https://crm.ajkadm.com/login](https://crm.ajkadm.com/login)

---

## VPS Overview

| Field | Value |
|-------|--------|
| Provider | Hostinger (KVM 1) |
| Hostname | `srv1396328.hstgr.cloud` |
| Public IP | `72.60.204.220` |
| OS | Ubuntu 22.04.5 LTS |
| Disk | 49 GB (~9% used) |
| RAM | 3.8 GB |
| SSL expiry | Nov 17, 2026 (Let's Encrypt) |
| Renewal / plan | Running until 2028-02-18 |

**DNS:** `crm.ajkadm.com` → `72.60.204.220`

---

## Architecture

```
Internet
   │
   ▼
Nginx (80 → 443 redirect, SSL)
   │
   ▼
PHP 8.2-FPM (unix:/var/run/php/php8.2-fpm.sock)
   │
   ▼
Laravel 10.49.1  (/var/www/Kites-Edu-CRM)
   │
   ▼
MySQL 8.0 (127.0.0.1:3306 only — not public)
```

---

## Source Code & Paths

| Purpose | Path |
|---------|------|
| Live application root | `/var/www/Kites-Edu-CRM/` |
| Nginx document root | `/var/www/Kites-Edu-CRM/public/` |
| Nginx vhost config | `/etc/nginx/sites-available/edu-crm` (enabled) |
| Server backup snapshot | `/var/www/Kites-Edu-CRM-backup-2026-06-19/` |
| Unused deploy dirs | `/var/www/releases/`, `/var/www/shared/` |

**Deploy method:** Code was uploaded/rsynced to the server. There is **no git repository** on the VPS.

**Local repository:** `git@github.com:smlingesh/Kites-Edu-CRM.git`

---

## Stack & Services

| Component | Status / version |
|-----------|------------------|
| Nginx | Active |
| PHP | 8.2.30 (FPM active) |
| Laravel | 10.49.1 |
| MySQL | 8.0 (active, localhost only) |
| Redis | Not running |
| Supervisor | Not running |
| Queue driver | `sync` (no background workers) |
| phpMyAdmin | Installed at `/phpmyadmin` on same domain |
| Docker | Not used |

**Cron (root):**

```cron
* * * * * cd /var/www/Kites-Edu-CRM && php artisan schedule:run >> /dev/null 2>&1
```

---

## Database

| Setting | Production value |
|---------|------------------|
| Engine | MySQL 8.0 |
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `edu_crm` |
| User | `crmuser` |
| Password | `********` *(stored in server `.env` only)* |
| Approx. size | ~13 MB |
| Tables | 16 |

**Largest tables (approx. row counts):**

| Table | Rows |
|-------|------|
| `edu_leads` | ~12,282 |
| `edu_lead_followups` | ~11,246 |
| `edu_lead_status_histories` | ~2,150 |
| `edu_lead_notes` | ~1,925 |
| `users` | 15 |

---

## Production `.env` (non-secret keys)

```env
APP_NAME=Kites-Edu-CRM
APP_ENV=production
APP_KEY=base64:********
APP_DEBUG=true
APP_URL=https://crm.kiteseds.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edu_crm
DB_USERNAME=crmuser
DB_PASSWORD=********

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

> **Note:** `APP_URL` still points to `crm.kiteseds.com` while the live domain is `crm.ajkadm.com`.  
> **Note:** `APP_DEBUG=true` in production can expose stack traces — consider disabling in a maintenance window.

---

## Local vs Production — Sync Status

**Reviewed after commit `1d7abff` (Oct 6, 2026):** *Sync production hotfixes for lead validation and code generation.*

### Application source code

Compared **218 files** under `app/`, `config/`, `routes/`, `resources/`, `database/`, and `public/` (PHP, Blade, JS, CSS, JSON).

| Check | Result |
|-------|--------|
| Identical content | **218 / 218** |
| Different source files | **0** |
| `composer.json` / `composer.lock` | Identical checksums |

**Conclusion:** Deployable application source in git matches the live server.

### Expected differences (not drift)

| Layer | In sync? | Notes |
|-------|----------|-------|
| Application source | Yes | Git matches `/var/www/Kites-Edu-CRM/` |
| Production `.env` | No | By design — not committed to git |
| Local `.env` | No | Dev settings (`APP_ENV=local`, local DB) |
| `vendor/` | No | Installed on server via Composer |
| `storage/` / `bootstrap/cache/` | No | Runtime logs, uploads, compiled views |

### Local `.env` vs production (non-secret)

| Key | Local | Production |
|-----|-------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_URL` | `https://crm.ajkadm.com` | `https://crm.kiteseds.com` |
| `APP_NAME` | `Kites Edu CRM` | `Kites-Edu-CRM` |
| `DB_DATABASE` | `laravel` | `edu_crm` |
| `DB_USERNAME` | `root` | `crmuser` |
| `DB_PASSWORD` | `********` | `********` |
| `APP_KEY` | `base64:********` | `base64:********` |

---

## Server Hotfixes (synced to git)

On **June 18, 2026**, two files were edited directly on the production server and were **not** in git until synced on Oct 6, 2026.

### 1. `app/Http/Controllers/EduLeadController.php`

Improved duplicate-key (`1062`) error handling on lead create/update for:

- `whatsapp_number`
- `lead_code`
- `phone` (fallback)

### 2. `app/Models/EduLead.php`

Improved `generateLeadCode()` logic — parses the numeric suffix after `-` and supports sequences beyond 4 digits.

These changes are now committed locally on branch `pre-lead` (`1d7abff`).

---

## Server Backup Folder

`/var/www/Kites-Edu-CRM-backup-2026-06-19/` is a snapshot from June 19, 2026.

- It **does not** reflect current live code after the Jun 18 hotfixes.
- It contains some old files no longer in the current codebase (e.g. old migrations/seeders).
- Use only as a historical reference, not as the source of truth.

---

## Known Issues & Observations

1. **Export memory limit** — `/edu-leads/export` hits PHP 128 MB limit (PhpSpreadsheet). Errors appear in `storage/logs/laravel.log` and nginx error log. Does not stop the rest of the app.
2. **`APP_DEBUG=true` on production** — security/UX risk; disable when convenient.
3. **`APP_URL` mismatch** — may affect generated URLs and emails.
4. **phpMyAdmin on public domain** — ensure strong credentials; consider IP restriction.
5. **No git on server** — manual deploys make drift harder to detect; prefer git- or rsync-based deploy from CI.
6. **No Redis / queue workers** — all jobs run synchronously (`QUEUE_CONNECTION=sync`).

---

## How to Detect Drift (read-only)

Run these on the **server** without affecting the running instance.

### Compare live vs backup

```bash
diff -qr /var/www/Kites-Edu-CRM-backup-2026-06-19 /var/www/Kites-Edu-CRM \
  --exclude=vendor --exclude=storage --exclude=bootstrap/cache --exclude=.env
```

### Find source files modified after a date

```bash
find /var/www/Kites-Edu-CRM -type f -name '*.php' \
  ! -path '*/vendor/*' ! -path '*/storage/*' \
  -newermt '2026-06-19' -ls
```

### Generate checksum manifest (compare with local)

**On server:**

```bash
cd /var/www/Kites-Edu-CRM
find app config routes resources database public -type f \
  \( -name '*.php' -o -name '*.blade.php' -o -name '*.js' -o -name '*.css' -o -name '*.json' \) \
  | sort | xargs md5sum > /tmp/prod-manifest.txt
```

**Locally:**

```bash
find app config routes resources database public -type f \
  \( -name '*.php' -o -name '*.blade.php' -o -name '*.js' -o -name '*.css' -o -name '*.json' \) \
  | sort | xargs md5sum > /tmp/local-manifest.txt
```

Compare the two manifest files; any line mismatch is a content drift.

---

## SSH Access (local only)

VPS credentials are stored in the local `.env` file (gitignored):

```env
VPS_HOST=72.60.204.220
VPS_USER=root
VPS_PASSWORD=********
VPS_PORT=22
```

> All secrets (`VPS_PASSWORD`, `DB_PASSWORD`, `APP_KEY`, etc.) are kept in the local `.env` file only. Use `********` in docs — never paste real values.

**Never commit** `.env` or production secrets to git.

When inspecting production:

- Use read-only commands (`cat`, `ls`, `grep`, `find`, `diff`, `md5sum`).
- Do **not** restart services, run migrations, or clear caches unless in a planned maintenance window.

---

## Quick Reference

| Item | Value |
|------|--------|
| Live URL | https://crm.ajkadm.com |
| Server IP | 72.60.204.220 |
| App path | /var/www/Kites-Edu-CRM |
| Web root | /var/www/Kites-Edu-CRM/public |
| PHP | 8.2-FPM |
| Laravel | 10.49.1 |
| Database | edu_crm @ 127.0.0.1 |
| Git branch (local) | pre-lead |
| Last sync commit | 1d7abff |
