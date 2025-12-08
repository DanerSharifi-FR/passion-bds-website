# passion-bds-website

Source code and CI/CD for **passion-bds.fr**, website of the sports student association (BDS) of **École Mines-Télécom Atlantique (IMT Atlantique)**.

---

## ✨ Overview

This repo contains:

- The **Laravel 12** backend in `/server` (PHP 8.4)
- A **MySQL 8.0** database container for the app
- A **Dockerized stack**:
    - PHP-FPM 8.4 (Alpine)
    - Nginx 1.27 (Alpine)
- Wiring for an **external Caddy reverse proxy** in production (shared Docker network).

> ℹ️ **Infra scope**  
> This repository ships the **application code** and its **local/dev Docker stack** (Nginx + PHP-FPM + MySQL).  
> All **Caddy / DNS / TLS / global infra** configuration lives outside this repo.

---

## 🧱 Tech stack

- **Language:** PHP 8.4
- **Framework:** Laravel 12.x
- **Web server:** Nginx 1.27 (Alpine)
- **Runtime:** PHP-FPM 8.4 (Alpine)
- **Database:** MySQL 8.0 (container `mysql_bds`)
- **Containers:** Docker + Docker Compose
- **Reverse proxy (prod):** Caddy (external stack on network `app_default`)

---

## 📁 Project structure (backend)

> Backend + containers only, not the whole project.

| Path                      | Description                                                |
|---------------------------|------------------------------------------------------------|
| `/server`                 | Laravel application root                                  |
| `/server/public`          | Web root served by Nginx                                  |
| `/server/.env`            | Laravel env config (APP_KEY, DB, session, etc.)           |
| `/server/storage`         | Logs, cache, sessions, compiled views                     |
| `/server/bootstrap/cache` | Framework cache files                                     |
| `/Dockerfile`             | PHP-FPM 8.4 image (Alpine + required extensions)          |
| `/compose.yaml`           | Docker Compose stack (`php_bds`, `passion_bds`, `mysql_bds`) |
| `/nginx.conf`             | Nginx config pointing to `server/public`                  |
| `/docs/…`                 | Database / functional documentation                       |

---

## 🧰 Prerequisites

### Local dev (Docker)

- Docker
- Docker Compose plugin

### Optional (WSL / host dev comfort)

If you want to run Artisan/Composer directly on the host (WSL):

- PHP 8.4 CLI (`php -v` → 8.4.x)
- Composer (using that PHP 8.4)
- Node.js + npm (later for assets, if needed)

---

## 🚀 First-time Laravel setup (host)

From the project root:

```bash
cd server

# Create local env file
cp .env.example .env

# Generate app key
php artisan key:generate
```

Then in `server/.env`:

```env
SESSION_DRIVER=file
```

> ⚠️ This keeps sessions on the filesystem instead of SQLite and avoids missing-driver issues during early dev.

---

## 🐳 Docker environment

### Services (`compose.yaml`)

#### `php_bds` — PHP-FPM / Laravel

- Builds from local `Dockerfile` (target: `dev`)
- Mounts project root into `/var/www/html`
- Runs PHP-FPM 8.4 with extensions:
    - `pdo_mysql`
    - `pdo_sqlite`
    - `mbstring`
- Depends on: `mysql_bds`

#### `mysql_bds` — MySQL 8.0

- Image: `mysql:8.0`
- Internal port: `3306`
- Host port: `3307` (for Adminer / DBeaver / CLI on host)
- Env (default dev credentials):

  ```env
  MYSQL_ROOT_PASSWORD=root
  MYSQL_DATABASE=passion_bds
  MYSQL_USER=passion_bds
  MYSQL_PASSWORD=passion_bds
  ```

- Data volume: `mysql_data:/var/lib/mysql`
- Network: `internal`

#### `passion_bds` — Nginx

- Image: `nginx:1.27-alpine`
- Depends on `php_bds`
- Uses `nginx.conf` from project root
- Web root: `/var/www/html/server/public`
- Proxies PHP to `php_bds:9000`
- Ports:
    - `80` inside container
    - `8080:80` mapped on host (local dev)

### Networks

```yaml
networks:
  internal:
    driver: bridge

  caddy:
    external: true
    name: app_default
```

- `internal` → private Nginx ↔ PHP-FPM ↔ MySQL network
- `caddy` → external Docker network shared with the Caddy/Caddyfile stack in prod

### Volumes

```yaml
volumes:
  mysql_data:
```

---

## 🗄️ Database configuration (Laravel)

In `server/.env`, the DB section is wired to the `mysql_bds` container:

```env
DB_CONNECTION=mysql
DB_HOST=mysql_bds
DB_PORT=3306
DB_DATABASE=passion_bds
DB_USERNAME=passion_bds
DB_PASSWORD=passion_bds
```

This lets Laravel talk directly to the MySQL container over the `internal` network.

---

## ▶️ Run the stack locally

From the project root:

```bash
cd ~/passion-bds-website
```

### 1️⃣ Create external network (once)

```bash
docker network create app_default || true
```

### 2️⃣ Build PHP image

```bash
docker compose build php_bds
```

### 3️⃣ Start containers

```bash
docker compose up -d
```

You should now have at least:

- `passion-bds-website-php_bds-1`
- `passion-bds-website-passion_bds-1`
- `passion-bds-website-mysql_bds-1`

### 4️⃣ Fix Laravel permissions (dev-only shortcut)

```bash
chmod -R 777 server/storage server/bootstrap/cache
# or inside the container:
# docker compose exec php_bds sh -lc 'chmod -R 777 server/storage server/bootstrap/cache'
```

### 5️⃣ Run migrations (inside containers, against MySQL)

From project root:

```bash
docker compose exec php_bds php server/artisan migrate
```

Answer `yes` if asked to create the database tables.

### 6️⃣ Open in browser

```text
http://localhost:8080
```

You should see the **Laravel welcome page** backed by the MySQL container.

---

## 🌐 Production / infra view

In production, the idea is:

```text
Internet
   ↓
 Caddy (external stack on Docker network app_default)
   ↓
 Nginx (passion_bds container)
   ↓
 PHP-FPM (php_bds container + Laravel in /server)
   ↓
 MySQL (mysql_bds container)
```

- Caddy handles:
    - Domains (e.g. `passion-bds.fr`)
    - HTTPS / certificates
    - Reverse proxy to `passion_bds:80` on `app_default`
- This repo:
    - ✅ Laravel app
    - ✅ Nginx + PHP-FPM + MySQL Docker setup ready to plug into that infra

---

## 🧾 Handy commands (dev)

From project root:

```bash
# Start stack
docker compose up -d

# Stop stack
docker compose down

# Rebuild PHP image
docker compose build php_bds

# Enter PHP container
docker compose exec php_bds sh

# Run Artisan (e.g. migrations, tinker, etc.)
docker compose exec php_bds php server/artisan migrate
docker compose exec php_bds php server/artisan tinker

# MySQL CLI from host (if mysql client installed)
mysql -h 127.0.0.1 -P 3307 -u passion_bds -p

# Tail logs
docker compose logs -f
```
