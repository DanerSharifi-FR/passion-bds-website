# passion-bds-website

Source code and Docker stack for **passion-bds.fr**, the website of the sports student association (BDS) of **IMT Atlantique**.

This repo contains:

- The **public landing page** and the **Laravel 12 backend**
- A **local/dev Docker stack** (Nginx + PHP-FPM + MySQL + phpMyAdmin)
- CI/CD glue used by the remote server

> **Note**  
> The **Caddy reverse-proxy and global infrastructure stack live outside this repository** (on the server under `/opt/app` and other ops repos).  
> This repo focuses on: **app code + local/dev containers + production app stack**.

---

## Table of contents

- [Architecture overview](#architecture-overview)
- [Tech stack](#tech-stack)
- [Docker services](#docker-services)
- [File / folder layout](#file--folder-layout)
- [Local development](#local-development)
  - [Initial setup](#initial-setup)
  - [Running the stack](#running-the-stack)
  - [Artisan, Composer & migrations](#artisan-composer--migrations)
- [Database & schema](#database--schema)
- [phpMyAdmin (dev & prod, safely)](#phpmyadmin-dev--prod-safely)
- [Deployment pipeline (production)](#deployment-pipeline-production)
- [Security notes](#security-notes)
- [Useful commands](#useful-commands)

---

## Architecture overview

High level:

- **Public traffic** hits **Caddy** (outside this repo).
- Caddy reverse-proxies to the **Nginx container** from this repo.
- Nginx serves:
  - The **Laravel app** (PHP-FPM in `php_bds`), including the custom landing view.
- A **MySQL 8** container stores application data.
- **phpMyAdmin** is available:
  - Locally: at `http://localhost:8081`
  - On the server: bound to `127.0.0.1:8081`, reachable only via SSH tunnel.

---

## Tech stack

- **Backend**
  - PHP **8.4** (FPM, Alpine)
  - **Laravel 12.x**
- **Web server**
  - **Nginx 1.27** (Alpine)
- **Database**
  - **MySQL 8.0**
- **Admin tooling**
  - **phpMyAdmin** (latest, Docker image)
- **Containerization**
  - Docker & Docker Compose
  - External Docker network: `app_default` (shared with Caddy stack on the server)
- **CI/CD**
  - GitHub Actions (`.github/workflows/deploy.yml`)
  - Remote deploy script on server: `/opt/deploy/deploy-passion-bds-website.sh`

---

## Docker services

From `compose.yaml`:

- `php_bds`
  - PHP 8.4 FPM for the Laravel app in `server/`
  - Mounted code: `./:/var/www/html`
- `passion_bds`
  - Nginx serving the app
  - Exposes port **8080** on host (Caddy proxies to this on prod)
- `mysql_bds`
  - MySQL 8.0
  - Exposes port **3307** on host (for internal / ops use)
- `phpmyadmin`
  - phpMyAdmin UI bound to **127.0.0.1:8081**
  - Local dev: reachable directly on your machine
  - Prod: reachable **only via SSH tunnel** (no direct public exposure)

Networks:

- `internal` – bridge network for app + DB + phpMyAdmin
- `caddy` – external network (`app_default`) used to connect to the Caddy reverse-proxy stack

---

## File / folder layout

Relevant bits:

```text
.
├─ index.php              # Legacy static landing (now ported into Laravel landing view)
├─ compose.yaml           # Docker services for app + DB + phpMyAdmin
├─ Dockerfile             # PHP-FPM + extensions (pdo_mysql, pdo_sqlite, mbstring, etc.)
├─ nginx.conf             # Nginx config for the Laravel app
├─ docs/                  # Documentation: DB schema, modelisation, roles/context, ...
└─ server/                # Laravel 12 application
   ├─ app/
   ├─ bootstrap/
   ├─ config/
   ├─ database/
   │   ├─ migrations/     # Laravel migrations matching 1-schema.md
   ├─ public/
   │   └─ index.php       # Front controller
   ├─ resources/
   │   └─ views/
   │       └─ landing.blade.php  # Landing page view plugged into Laravel
   ├─ routes/
   │   └─ web.php         # Routes ("/" → landing view, etc.)
   ├─ storage/
   ├─ .env                # Laravel environment (NOT committed)
   └─ ...
```

---

## Local development

### Initial setup

Clone repo:

```bash
git clone git@github.com:DanerSharifi-FR/passion-bds-website.git
cd passion-bds-website
```

Copy env file for Laravel:

```bash
cd server
cp .env.example .env
```

Edit `server/.env`:

```env
APP_NAME="Passion BDS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql_bds
DB_PORT=3306
DB_DATABASE=passion_bds
DB_USERNAME=passion_bds
DB_PASSWORD=passion_bds   # dev-only default, can be changed locally
```

Then, still in `server/`:

```bash
composer install
php artisan key:generate
```

(or you can run those via `docker compose exec php_bds`, see below).

### Running the stack

From repo root:

```bash
cd ~/passion-bds-website
docker compose up -d
```

You should get:

- App: `http://localhost:8080`
- phpMyAdmin (local): `http://localhost:8081`

### Artisan, Composer & migrations

From repo root, using the PHP container:

```bash
# Install dependencies from inside the container (optional if already done on host)
docker compose exec php_bds composer install --working-dir=server

# Generate app key (first time)
docker compose exec php_bds php server/artisan key:generate

# Run migrations
docker compose exec php_bds php server/artisan migrate

# Reset + migrate fresh (local only)
docker compose exec php_bds php server/artisan migrate:fresh
```

---

## Database & schema

The conceptual database schema is documented in `docs/` (including `1-schema.md` and the PlantUML modelisation).

Core domains:

1. **Identity & Access**  
   `users`, `roles`, `user_roles`, `login_codes`
2. **Activities**  
   `activities`, `activity_admins`, `activity_participants`, `activity_teams`, `activity_team_members`
3. **Points & Allos**  
   `point_transactions`, `allos`, `allo_admins`, `allo_slots`, `allo_usages`
4. **Events & Gallery**  
   `event_categories`, `events`, `media_items`
5. **Shop (Catalog)**  
   `shop_categories`, `products`
6. **Team**  
   `poles`, `team_members`
7. **Audit Logs**  
   `audit_logs`

Migrations in `server/database/migrations/` mirror this structure and enforce:

- Primary keys / foreign keys
- Unique constraints (e.g. `uq_roles_name`, `uq_allos_slug`)
- Check constraints where relevant (MySQL 8+)

---

## phpMyAdmin (dev & prod, safely)

### Local (dev machine)

- URL: `http://localhost:8081`
- Server: `mysql_bds`
- User/pass (by default):

```text
Username: passion_bds
Password: passion_bds
```

(Or whatever you set in `server/.env` + `compose.yaml` for local.)

### Production (server)

On the server, phpMyAdmin container is bound to:

```text
127.0.0.1:8081
```

It is **not** directly exposed publicly.

To access it from your laptop:

1. Open SSH tunnel from your dev machine (WSL):

   ```bash
   ssh -L 9001:127.0.0.1:8081 root@YOUR_SERVER_IP
   ```

   Keep this session **open**.

2. In your browser on your laptop:

  - Go to: `http://localhost:9001`

3. Login:

  - Server: `mysql_bds`
  - Username: `passion_bds`
  - Password: the **strong prod DB password** stored in `/opt/passion-bds/server/.env` (`DB_PASSWORD`)

This keeps phpMyAdmin accessible only via SSH, not the open internet.

---

## Deployment pipeline (production)

### Remote layout

On the server:

- Repo checkout: `/opt/passion-bds`
- Deploy scripts: `/opt/deploy/`
  - `/opt/deploy/deploy-passion-bds-website.sh`

### GitHub Actions

Workflow: `.github/workflows/deploy.yml`

Trigger:

- On `push` to `main`
- Manual `workflow_dispatch`

The job:

1. Checks out the repo.
2. Starts an SSH agent with the deployment key (`SSH_PRIVATE_KEY` secret).
3. Verifies SSH to the server.
4. Calls the remote script:

   ```bash
   ssh "$SSH_USER@$SSH_HOST"      "SERVER_NAME='$SERVER_NAME' /opt/deploy/deploy-passion-bds-website.sh"
   ```

### Remote deploy script

`/opt/deploy/deploy-passion-bds-website.sh` (summary):

- `cd /opt/passion-bds`
- `git fetch` + `git reset --hard origin/main`
- Ensures `SERVER_NAME` is written into `.env` (app-level, not Caddy)
- Runs **Composer install in `server/`** using the official `composer:2` Docker image
- Ensures external network `app_default` exists
- `docker compose up -d --remove-orphans`
- Shows `docker compose ps`

Result: updated code + dependencies + containers, without touching infra (Caddy stack is separate).

---

## Security notes

- **Laravel `.env` files are never committed**:
  - Dev: `server/.env` on your laptop
  - Prod: `/opt/passion-bds/server/.env` on the server
- **DB credentials**:
  - MySQL user: `passion_bds`
  - Prod password: strong random string, set in:
    - `server/.env` (`DB_PASSWORD`)
    - MySQL via `ALTER USER 'passion_bds'@'%' IDENTIFIED BY '...';`
- **phpMyAdmin**:
  - Local: bound to `127.0.0.1:8081`
  - Prod: bound to `127.0.0.1:8081` and only reachable via SSH tunnel
  - Never expose it directly on a public IP/port.
- **File permissions**:
  - `storage/` and `bootstrap/cache/` must be writable by the PHP-FPM user (container has this handled; on server we adjusted with `chmod -R 775` / `777` during setup).
- **Caddy / TLS / domain config** lives in infra repos and must enforce:
  - HTTPS only
  - HSTS
  - Proper proxy headers

---

## Useful commands

From repo root (`~/passion-bds-website`):

```bash
# Start stack (dev)
docker compose up -d

# View logs
docker compose logs -f
docker compose logs -f php_bds
docker compose logs -f passion_bds

# Run artisan
docker compose exec php_bds php server/artisan migrate
docker compose exec php_bds php server/artisan migrate:fresh
docker compose exec php_bds php server/artisan tinker

# Run composer inside container
docker compose exec php_bds composer install --working-dir=server

# Stop and remove containers
docker compose down
```

---

If you change anything major in the Docker stack, DB schema, or deployment process, update this README so it stays the single source of truth for future you (and future teammates).
