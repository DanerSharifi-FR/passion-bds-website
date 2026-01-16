# Roles & Functional Context

## 1. What this platform is

This app is the **campaign hub** for a BDS list:

- Students log in with their **university email + 4-digit code**.
- They earn and spend **points** via:
    - **allos** (small services / favors bookable in time slots),
    - **activities** (competitions, games, tournaments - individual or team-based),
    - manual points given by the staff during IRL activities.
- The list runs content around that:
    - events + photo galleries,
    - a fake "shop" (catalog only, no payment),
    - team presentation by **pôle**,
    - admin tools and audit logs.

Everything else (DB, PlantUML, etc.) is just plumbing to make this work cleanly.

---

## 2. Actors & roles

There are two levels:

1. **Implicit roles** (by login status).
2. **Explicit roles** in the `roles` / `user_roles` tables (cumulative).

### 2.1 Visitor (not logged in)

- Can access public marketing pages (home, basic presentation, maybe static events / team teaser).
- No points, no allos, no activities, nothing tied to a user.

### 2.2 Authenticated student (`ROLE_USER` by convention)

Once logged in with their uni email:

- Sees **their current points total**.
- Books **allos** in available time slots.
- Cancels their own allo before the deadline.
- Participates in **activities**:
    - joins individual or team-based activities,
    - earns points specific to each activity (separate leaderboards),
    - sees activity-specific points labels (wins, seconds, kills, etc.).
- Browses:
    - events & galleries,
    - fake shop catalog,
    - team by pôle.

This is the **baseline user**; they may or may not have admin roles on top.

---

## 3. Functional admin roles

All admin roles are **additive**: a user can have 0, 1 or many roles in `user_roles`.

### 3.1 `ROLE_GAMEMASTER`

Owns **points logic, allos and activities**.

- **Points**
    - Gives / removes points via **manual `point_transactions`**:
        - IRL mini games,
        - ambiance, participation, etc.
    - Can link points to specific **activities** (for activity-specific leaderboards).
- **Activities**
    - Creates / edits **activities** (competitions, tournaments, games):
        - sets title, slug, description,
        - defines points label (what points are called: "wins", "seconds", "kills", etc.),
        - chooses mode: `INDIVIDUAL` or `TEAM`,
        - assigns activity admins (gamemasters for this specific activity),
        - adds participants (users or teams).
    - Manages activity participants and teams.
    - Points earned in activities can be tracked separately (activity-specific leaderboards).
    - **Note**: Activity admins (via `activity_admins`) can manage a specific activity even without `ROLE_GAMEMASTER`, allowing delegation to specialized gamemasters per activity.
- **Allos**
    - Creates / edits / disables allos.
    - Sets:
        - points cost,
        - availability window,
        - slot duration,
        - assigned admins for handling the allo.
    - Manages a specific allo:
        - opens / closes,
        - sees the bookings,
        - handles each allo usage (accept, mark as done, cancel).

In short: this role controls **how students earn/spend points**.

---

### 3.2 `ROLE_BLOGGER`

Owns **events and gallery**.

- Creates **event categories** and **events** (date, location, description…).
- Uploads photos / videos in **media items** linked to events.
- Chooses which media are visible and in which order.
- Publishes/unpublishes events.

This is the content / comms manager for the “memories” part of the website.

---

### 3.3 `ROLE_TEAM`

Owns the **team page**.

- Manages **pôles** (name, description, order).
- Manages **team members**:
    - full name / nickname,
    - role in the list,
    - photo and social links,
    - visibility and display order.
- Can optionally link a team member to a real `user` for future features (badges, special actions, etc.).

Goal: keep the team section up to date as the campaign moves.

---

### 3.4 `ROLE_SHOP` (optional but recommended)

Owns the **fake shop catalog**.

- Creates **shop categories** and **products**.
- Chooses display style (`CARD` vs `STICKER`) and badge texts (“New”, “Limited”, etc.).
- Controls visibility and order.

No payments. It’s just a **fun catalog** to showcase merch / goodies that points could theoretically unlock.

If you don’t want a separate role, this can be merged into `ROLE_GAMEMASTER` or handled by any admin.

---

### 3.5 `ROLE_SUPER_ADMIN`

The **god mode** of the platform.

- Manages **admin accounts and roles**:
    - creates users with admin roles,
    - adds/removes roles in `user_roles`.
- Has **all permissions** from:
    - `ROLE_GAMEMASTER`,
    - `ROLE_BLOGGER`,
    - `ROLE_TEAM`,
    - `ROLE_SHOP`.
- Accesses the **audit logs**:
    - filters by actor, action, entity, date, etc.
    - can trace who changed what and when.
- Can access any data even if it’s normally module-scoped.

Used sparingly: usually 1–2 people in the list, mainly for safety and debugging.

---

## 4. Modules × roles matrix (summary)

Legend:
- **R** = read / list
- **C/U/D** = create / update / delete
- **★** = special actions

| Module              | Student (`ROLE_USER`) | `ROLE_GAMEMASTER`          | `ROLE_BLOGGER`      | `ROLE_TEAM`         | `ROLE_SHOP` | `ROLE_SUPER_ADMIN` |
|---------------------|-----------------------|----------------------------|---------------------|---------------------|------------|--------------------|
| Auth / profile      | own profile (R)       | –                          | –                   | –                   | –          | full               |
| Points balance      | own (R)               | C (manual grants)          | –                   | –                   | –          | full               |
| Activities          | participate (R/★)     | C/U/D + manage participants| –                   | –                   | –          | full               |
| Allos               | book/cancel own (R/★) | C/U/D + handle usages (★)  | –                   | –                   | –          | full               |
| Events              | R                     | R                          | C/U/D               | –                   | –          | full               |
| Galleries           | R                     | R                          | C/U/D media         | –                   | –          | full               |
| Shop catalog        | R                     | R (optional C/U)           | –                   | –                   | C/U/D      | full               |
| Team & pôles        | R                     | R                          | –                   | C/U/D               | –          | full               |
| Audit logs          | –                     | –                          | –                   | –                   | –          | R (full)           |
| Admin & roles       | –                     | cannot manage roles        | cannot manage roles | cannot manage roles | –          | C/U/D              |

You can adjust this matrix to reality, but the idea is clear:  
**business roles = “who runs which module”**, super admin just overrides everything.

---

## 5. Typical flows (for context)

### 5.1 Student day

1. Logs in with uni email + 4-digit code.
2. Participates in **activities**:
    - joins individual activities or gets added to teams,
    - earns activity-specific points (tracked separately from global points).
3. Books an allo for later in the week if they have enough points.
4. Browses events, galleries, shop catalog, and team pages.

### 5.2 Gamemaster day

1. Manages **activities**:
    - creates new activities (tournaments, competitions),
    - adds participants or teams,
    - assigns activity-specific admins,
    - awards points linked to activities (for activity leaderboards).
2. Gives / removes points via **manual `point_transactions`**:
    - IRL mini games,
    - ambiance, participation, etc.
3. For allos, monitors **slots** and marks usages as **DONE** or **CANCELLED**.

This doc + the DB schema + the PlantUML is enough for someone technical to understand the whole system without ever talking to you.
