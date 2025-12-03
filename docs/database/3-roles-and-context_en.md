# Roles & Functional Context

## 1. What this platform is

This app is the **campaign hub** for a BDS list:

- Students log in with their **university email + 4-digit code**.
- They earn and spend **points** via:
    - daily / bonus **challenges** (questions, QCM, photos, “go see X”),
    - **allos** (small services / favors bookable in time slots),
    - manual points given by the staff during IRL activities.
- The list runs content around that:
    - events + photo galleries,
    - a fake “shop” (catalog only, no payment),
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
- No points, no allos, no challenges, nothing tied to a user.

### 2.2 Authenticated student (`ROLE_USER` by convention)

Once logged in with their uni email:

- Sees **their current points total**.
- Books **allos** in available time slots.
- Cancels their own allo before the deadline.
- Participates in **challenges**:
    - answers questions (TEXT or QCM),
    - declares a completed action (“I have the photo”, “I saw the treasurer”).
- Browses:
    - events & galleries,
    - fake shop catalog,
    - team by pôle.

This is the **baseline user**; they may or may not have admin roles on top.

---

## 3. Functional admin roles

All admin roles are **additive**: a user can have 0, 1 or many roles in `user_roles`.

### 3.1 `ROLE_GAMEMASTER`

Owns **points logic, challenges and allos**.

- **Points**
    - Gives / removes points via **manual `point_transactions`**:
        - IRL mini games,
        - ambiance, participation, etc.
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
- **Challenges**
    - Creates daily / bonus challenges:
        - `QUESTION_TEXT`, `QUESTION_MCQ`, `ACTION_PHOTO`, `ACTION_VISIT`.
    - For text / QCM:
        - defines correct answer(s) and points reward,
        - chooses whether it’s “bonus” or not (label).
    - Reviews attempts when needed (photos, visits, borderline cases).

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
| Allos               | book/cancel own (R/★) | C/U/D + handle usages (★)  | –                   | –                   | –          | full               |
| Challenges          | answer (C)            | C/U/D + review/auto rules  | –                   | –                   | –          | full               |
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
2. Sees **“Défi du jour”**.
3. Answers a question / QCM or clicks “I have the photo!”.
4. If it’s auto-checkable → points are granted immediately.  
   If not → attempt is **PENDING** until a gamemaster validates.
5. Books an allo for later in the week if they have enough points.

### 5.2 Gamemaster day

1. Checks **challenge attempts** list (photo proofs, visits, borderline answers).
2. Accepts / rejects attempts → points flow through `point_transactions`.
3. Creates a new **challenge** for the next day.
4. For allos, monitors **slots** and marks usages as **DONE** or **CANCELLED**.

This doc + the DB schema + the PlantUML is enough for someone technical to understand the whole system without ever talking to you.
