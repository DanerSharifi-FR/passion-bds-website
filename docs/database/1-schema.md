# Database schema

## Table of contents

- [1. Identity & Access](#1-identity--access)
    - [1.1 `users`](#11-users)
    - [1.2 `roles`](#12-roles)
    - [1.3 `user_roles`](#13-user_roles)
    - [1.4 `login_codes`](#14-login_codes)
- [2. Activities](#2-activities)
    - [2.1 `activities`](#21-activities)
    - [2.2 `activity_admins`](#22-activity_admins)
    - [2.3 `activity_participants`](#23-activity_participants)
    - [2.4 `activity_teams`](#24-activity_teams)
    - [2.5 `activity_team_members`](#25-activity_team_members)
- [3. Points & Allos](#3-points--allos)
    - [3.1 `point_transactions`](#31-point_transactions)
    - [3.2 `allos`](#32-allos)
    - [3.3 `allo_admins`](#33-allo_admins)
    - [3.4 `allo_slots`](#34-allo_slots)
    - [3.5 `allo_usages`](#35-allo_usages)
- [4. Events & Gallery](#4-events--gallery)
    - [4.1 `event_categories`](#41-event_categories)
    - [4.2 `events`](#42-events)
    - [4.3 `media_items`](#43-media_items)
- [5. Shop (Catalog)](#5-shop-catalog)
    - [5.1 `shop_categories`](#51-shop_categories)
    - [5.2 `products`](#52-products)
- [6. Team](#6-team)
    - [6.1 `poles`](#61-poles)
    - [6.2 `team_members`](#62-team_members)
- [7. Audit Logs](#7-audit-logs)
    - [7.1 `audit_logs`](#71-audit_logs)
- [8. Challenges & Daily Games](#8-challenges--daily-games)
    - [8.1 `challenges`](#81-challenges)
    - [8.2 `challenge_options`](#82-challenge_options)
    - [8.3 `challenge_attempts`](#83-challenge_attempts)

---

# 1. Identity & Access

## 1.1 `users`

#### Columns

| Column           | Type      | PK  | Nullability | Length |
|------------------|-----------|-----|-------------|--------|
| id               | BIGINT    | PK  | NOT NULL    | -      |
| university_email | VARCHAR   |     | NOT NULL    | 255    |
| display_name     | VARCHAR   |     | NULL        | 255    |
| avatar_url       | VARCHAR   |     | NULL        | 500    |
| is_active        | BOOLEAN   |     | NOT NULL    | -      |
| last_login_at    | TIMESTAMP |     | NULL        | -      |
| remember_token   | VARCHAR   |     | NULL        | 100    |
| created_at       | TIMESTAMP |     | NOT NULL    | -      |
| updated_at       | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                | Type   | Definition                        |
|---------------------|--------|-----------------------------------|
| pk_users            | PK     | PRIMARY KEY (id)                  |
| uq_users_univ_email | UNIQUE | UNIQUE (university_email)         |

#### Referenced by

- [`user_roles.user_id`](#13-user_roles)
- [`login_codes.user_id`](#14-login_codes)
- [`activities.created_by_id`](#21-activities)
- [`activity_admins.admin_id`](#22-activity_admins)
- [`activity_participants.user_id`](#23-activity_participants)
- [`activity_participants.created_by_id`](#23-activity_participants)
- [`activity_teams.created_by_id`](#24-activity_teams)
- [`activity_team_members.user_id`](#25-activity_team_members)
- [`activity_team_members.created_by_id`](#25-activity_team_members)
- [`point_transactions.user_id`](#31-point_transactions)
- [`point_transactions.created_by_id`](#31-point_transactions)
- [`allos.created_by_id`](#32-allos)
- [`allos.updated_by_id`](#32-allos)
- [`allo_admins.admin_id`](#33-allo_admins)
- [`allo_usages.user_id`](#35-allo_usages)
- [`allo_usages.handled_by_id`](#35-allo_usages)
- [`allo_usages.done_by_id`](#35-allo_usages)
- [`events.created_by_id`](#42-events)
- [`events.updated_by_id`](#42-events)
- [`media_items.uploader_id`](#43-media_items)
- [`team_members.user_id`](#62-team_members)
- [`audit_logs.actor_id`](#71-audit_logs)
- [`challenges.created_by_id`](#81-challenges)
- [`challenges.updated_by_id`](#81-challenges)
- [`challenge_attempts.user_id`](#83-challenge_attempts)
- [`challenge_attempts.reviewed_by_id`](#83-challenge_attempts)

---

## 1.2 `roles`

#### Columns

| Column      | Type      | PK  | Nullability | Length |
|-------------|-----------|-----|-------------|--------|
| id          | BIGINT    | PK  | NOT NULL    | -      |
| name        | VARCHAR   |     | NOT NULL    | 100    |
| description | TEXT      |     | NULL        | -      |
| created_at  | TIMESTAMP |     | NOT NULL    | -      |
| updated_at  | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name          | Type   | Definition       |
|---------------|--------|------------------|
| pk_roles      | PK     | PRIMARY KEY (id) |
| uq_roles_name | UNIQUE | UNIQUE (name)    |

#### Referenced by

- [`user_roles.role_id`](#13-user_roles)

---

## 1.3 `user_roles`

#### Columns

| Column   | Type   | PK  | Nullability | Length |
|----------|--------|-----|-------------|--------|
| user_id  | BIGINT | PK  | NOT NULL    | -      |
| role_id  | BIGINT | PK  | NOT NULL    | -      |

#### Constraints

| Name                        | Type | Definition                                             |
|-----------------------------|------|--------------------------------------------------------|
| pk_user_roles               | PK   | PRIMARY KEY (user_id, role_id)                        |
| fk_user_roles_user_id_users | FK   | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id) |
| fk_user_roles_role_id_roles | FK   | FOREIGN KEY (role_id) REFERENCES [roles](#12-roles)(id) |

---

## 1.4 `login_codes`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| user_id       | BIGINT    |     | NOT NULL    | -      |
| code_hash     | VARCHAR   |     | NOT NULL    | 255    |
| expires_at    | TIMESTAMP |     | NOT NULL    | -      |
| used_at       | TIMESTAMP |     | NULL        | -      |
| attempt_count | INT       |     | NOT NULL    | -      |
| ip_address    | VARCHAR   |     | NULL        | 45     |
| user_agent    | VARCHAR   |     | NULL        | 500    |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                         | Type  | Definition                                              |
|------------------------------|-------|---------------------------------------------------------|
| pk_login_codes               | PK    | PRIMARY KEY (id)                                        |
| fk_login_codes_user_id_users | FK    | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id) |
| chk_login_codes_attempt_min0 | CHECK | attempt_count >= 0                                      |
| chk_login_codes_expiry_after | CHECK | expires_at > created_at                                 |

---

# 2. Activities

## 2.1 `activities`

#### Columns

| Column         | Type      | PK  | Nullability | Length |
|----------------|-----------|-----|-------------|--------|
| id             | BIGINT    | PK  | NOT NULL    | -      |
| title          | VARCHAR   |     | NOT NULL    | 255    |
| slug           | VARCHAR   |     | NOT NULL    | 255    |
| points_label   | VARCHAR   |     | NOT NULL    | 50     |
| description    | TEXT      |     | NULL        | -      |
| mode           | VARCHAR   |     | NOT NULL    | 20     |
| is_active      | BOOLEAN   |     | NOT NULL    | -      |
| created_by_id  | BIGINT    |     | NULL        | -      |
| created_at     | TIMESTAMP |     | NOT NULL    | -      |
| updated_at     | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                      | Type   | Definition                                                              |
|---------------------------|--------|-------------------------------------------------------------------------|
| pk_activities             | PK     | PRIMARY KEY (id)                                                        |
| uq_activities_title       | UNIQUE | UNIQUE (title)                                                          |
| uq_activities_slug         | UNIQUE | UNIQUE (slug)                                                           |
| fk_activities_created_by_id_users | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)           |
| chk_activities_mode_valid  | CHECK  | mode IN ('INDIVIDUAL','TEAM')                                           |

#### Referenced by

- [`activity_admins.activity_id`](#22-activity_admins)
- [`activity_participants.activity_id`](#23-activity_participants)
- [`activity_teams.activity_id`](#24-activity_teams)
- [`point_transactions.activity_id`](#31-point_transactions)

---

## 2.2 `activity_admins`

#### Columns

| Column      | Type      | PK  | Nullability | Length |
|-------------|-----------|-----|-------------|--------|
| id          | BIGINT    | PK  | NOT NULL    | -      |
| activity_id | BIGINT    |     | NOT NULL    | -      |
| admin_id    | BIGINT    |     | NOT NULL    | -      |
| created_at  | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                              | Type   | Definition                                                              |
|-----------------------------------|--------|-------------------------------------------------------------------------|
| pk_activity_admins                 | PK     | PRIMARY KEY (id)                                                        |
| fk_activity_admins_activity_id_activities | FK     | FOREIGN KEY (activity_id) REFERENCES [activities](#21-activities)(id)   |
| fk_activity_admins_admin_id_users | FK     | FOREIGN KEY (admin_id) REFERENCES [users](#11-users)(id)                |
| uq_activity_admins_activity_admin  | UNIQUE | UNIQUE (activity_id, admin_id)                                         |

---

## 2.3 `activity_participants`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| activity_id   | BIGINT    |     | NOT NULL    | -      |
| user_id       | BIGINT    |     | NOT NULL    | -      |
| created_by_id | BIGINT    |     | NULL        | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                                    | Type   | Definition                                                              |
|-----------------------------------------|--------|-------------------------------------------------------------------------|
| pk_activity_participants                 | PK     | PRIMARY KEY (id)                                                        |
| fk_activity_participants_activity_id_activities | FK     | FOREIGN KEY (activity_id) REFERENCES [activities](#21-activities)(id)   |
| fk_activity_participants_user_id_users | FK     | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id)                  |
| fk_activity_participants_created_by_id_users | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)            |
| uq_activity_participants_activity_user  | UNIQUE | UNIQUE (activity_id, user_id)                                           |

---

## 2.4 `activity_teams`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| activity_id   | BIGINT    |     | NOT NULL    | -      |
| title         | VARCHAR   |     | NOT NULL    | 150    |
| created_by_id | BIGINT    |     | NULL        | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |
| updated_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                              | Type   | Definition                                                              |
|-----------------------------------|--------|-------------------------------------------------------------------------|
| pk_activity_teams                  | PK     | PRIMARY KEY (id)                                                        |
| fk_activity_teams_activity_id_activities | FK     | FOREIGN KEY (activity_id) REFERENCES [activities](#21-activities)(id)   |
| fk_activity_teams_created_by_id_users | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)            |
| uq_activity_teams_activity_title  | UNIQUE | UNIQUE (activity_id, title)                                             |

#### Referenced by

- [`activity_team_members.team_id`](#25-activity_team_members)

---

## 2.5 `activity_team_members`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| team_id       | BIGINT    |     | NOT NULL    | -      |
| user_id       | BIGINT    |     | NOT NULL    | -      |
| created_by_id | BIGINT    |     | NULL        | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                                    | Type   | Definition                                                              |
|-----------------------------------------|--------|-------------------------------------------------------------------------|
| pk_activity_team_members                 | PK     | PRIMARY KEY (id)                                                        |
| fk_activity_team_members_team_id_activity_teams | FK     | FOREIGN KEY (team_id) REFERENCES [activity_teams](#24-activity_teams)(id) |
| fk_activity_team_members_user_id_users | FK     | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id)                  |
| fk_activity_team_members_created_by_id_users | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)            |
| uq_activity_team_members_team_user     | UNIQUE | UNIQUE (team_id, user_id)                                               |

---

# 3. Points & Allos

## 3.1 `point_transactions`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| user_id       | BIGINT    |     | NOT NULL    | -      |
| amount        | INT       |     | NOT NULL    | -      |
| reason        | VARCHAR   |     | NOT NULL    | 255    |
| context_type  | VARCHAR   |     | NULL        | 50     |
| context_id    | BIGINT    |     | NULL        | -      |
| activity_id   | BIGINT    |     | NULL        | -      |
| created_by_id | BIGINT    |     | NULL        | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                      | Type  | Definition                                              |
|---------------------------|-------|---------------------------------------------------------|
| pk_point_transactions     | PK    | PRIMARY KEY (id)                                        |
| fk_pt_user_id_users       | FK    | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id) |
| fk_pt_activity_id_activities | FK    | FOREIGN KEY (activity_id) REFERENCES [activities](#21-activities)(id) |
| fk_pt_created_by_id_users | FK    | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id) |
| chk_pt_amount_not_zero    | CHECK | amount <> 0                                             |

---

## 3.2 `allos`

#### Columns

| Column                | Type      | PK  | Nullability | Length |
|-----------------------|-----------|-----|-------------|--------|
| id                    | BIGINT    | PK  | NOT NULL    | -      |
| title                 | VARCHAR   |     | NOT NULL    | 255    |
| slug                  | VARCHAR   |     | NULL        | 255    |
| description           | TEXT      |     | NULL        | -      |
| points_cost           | INT       |     | NOT NULL    | -      |
| status                | VARCHAR   |     | NOT NULL    | 20     |
| window_start_at       | TIMESTAMP |     | NOT NULL    | -      |
| window_end_at         | TIMESTAMP |     | NOT NULL    | -      |
| slot_duration_minutes | INT       |     | NOT NULL    | -      |
| created_by_id         | BIGINT    |     | NOT NULL    | -      |
| updated_by_id         | BIGINT    |     | NULL        | -      |
| created_at            | TIMESTAMP |     | NOT NULL    | -      |
| updated_at            | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                             | Type   | Definition                                                              |
|----------------------------------|--------|-------------------------------------------------------------------------|
| pk_allos                         | PK     | PRIMARY KEY (id)                                                        |
| uq_allos_slug                    | UNIQUE | UNIQUE (slug)                                                           |
| fk_allos_created_by_id_users     | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)           |
| fk_allos_updated_by_id_users     | FK     | FOREIGN KEY (updated_by_id) REFERENCES [users](#11-users)(id)           |
| chk_allos_points_cost_min0       | CHECK  | points_cost >= 0                                                        |
| chk_allos_window_end_after_start | CHECK  | window_end_at > window_start_at                                         |
| chk_allos_slot_duration_positive | CHECK  | slot_duration_minutes > 0                                               |
| chk_allos_status_valid           | CHECK  | status IN ('DRAFT','OPEN','CLOSED','DISABLED')                          |

#### Referenced by

- [`allo_admins.allo_id`](#33-allo_admins)
- [`allo_slots.allo_id`](#34-allo_slots)
- [`allo_usages.allo_id`](#35-allo_usages)

---

## 3.3 `allo_admins`

#### Columns

| Column     | Type      | PK  | Nullability | Length |
|------------|-----------|-----|-------------|--------|
| id         | BIGINT    | PK  | NOT NULL    | -      |
| allo_id    | BIGINT    |     | NOT NULL    | -      |
| admin_id   | BIGINT    |     | NOT NULL    | -      |
| created_at | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                          | Type   | Definition                                                     |
|-------------------------------|--------|----------------------------------------------------------------|
| pk_allo_admins                | PK     | PRIMARY KEY (id)                                               |
| fk_allo_admins_allo_id_allos  | FK     | FOREIGN KEY (allo_id) REFERENCES [allos](#22-allos)(id)        |
| fk_allo_admins_admin_id_users | FK     | FOREIGN KEY (admin_id) REFERENCES [users](#11-users)(id)       |
| uq_allo_admins_allo_admin     | UNIQUE | UNIQUE (allo_id, admin_id)                                     |

---

## 3.4 `allo_slots`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| allo_id       | BIGINT    |     | NOT NULL    | -      |
| slot_start_at | TIMESTAMP |     | NOT NULL    | -      |
| slot_end_at   | TIMESTAMP |     | NOT NULL    | -      |
| status        | VARCHAR   |     | NOT NULL    | 20     |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                         | Type   | Definition                                                      |
|------------------------------|--------|-----------------------------------------------------------------|
| pk_allo_slots                | PK     | PRIMARY KEY (id)                                                |
| fk_allo_slots_allo_id_allos  | FK     | FOREIGN KEY (allo_id) REFERENCES [allos](#32-allos)(id)         |
| uq_allo_slots_allo_start     | UNIQUE | UNIQUE (allo_id, slot_start_at)                                 |
| chk_allo_slots_status_valid  | CHECK  | status IN ('available', 'booked', 'blocked')                    |

#### Referenced by

- [`allo_usages.allo_slot_id`](#35-allo_usages)

---

## 3.5 `allo_usages`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| allo_id       | BIGINT    |     | NOT NULL    | -      |
| allo_slot_id  | BIGINT    |     | NOT NULL    | -      |
| slot_start_at | TIMESTAMP |     | NOT NULL    | -      |
| user_id       | BIGINT    |     | NOT NULL    | -      |
| points_spent  | INT       |     | NOT NULL    | -      |
| status        | VARCHAR   |     | NOT NULL    | 20     |
| handled_by_id | BIGINT    |     | NULL        | -      |
| done_by_id    | BIGINT    |     | NULL        | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |
| accepted_at   | TIMESTAMP |     | NULL        | -      |
| done_at       | TIMESTAMP |     | NULL        | -      |
| cancelled_at  | TIMESTAMP |     | NULL        | -      |

#### Constraints

| Name                                   | Type   | Definition                                                                                     |
|----------------------------------------|--------|------------------------------------------------------------------------------------------------|
| pk_allo_usages                         | PK     | PRIMARY KEY (id)                                                                               |
| fk_au_allo_id_allos                    | FK     | FOREIGN KEY (allo_id) REFERENCES [allos](#32-allos)(id)                                       |
| fk_au_allo_slot_id_allo_slots          | FK     | FOREIGN KEY (allo_slot_id) REFERENCES [allo_slots](#34-allo_slots)(id)                        |
| fk_au_user_id_users                    | FK     | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id)                                       |
| fk_au_handled_by_id_users              | FK     | FOREIGN KEY (handled_by_id) REFERENCES [users](#11-users)(id)                                 |
| fk_au_done_by_id_users                 | FK     | FOREIGN KEY (done_by_id) REFERENCES [users](#11-users)(id)                                    |
| uq_au_user_slotstart                   | UNIQUE | UNIQUE (user_id, slot_start_at)                                                                |
| chk_au_points_spent_min0               | CHECK  | points_spent >= 0                                                                              |
| chk_au_status_valid                    | CHECK  | status IN ('PENDING','ACCEPTED','DONE','CANCELLED')                                            |

---

# 4. Events & Gallery

## 4.1 `event_categories`

#### Columns

| Column      | Type      | PK  | Nullability | Length |
|-------------|-----------|-----|-------------|--------|
| id          | BIGINT    | PK  | NOT NULL    | -      |
| name        | VARCHAR   |     | NOT NULL    | 100    |
| slug        | VARCHAR   |     | NOT NULL    | 100    |
| description | TEXT      |     | NULL        | -      |
| created_at  | TIMESTAMP |     | NOT NULL    | -      |
| updated_at  | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                     | Type   | Definition         |
|--------------------------|--------|--------------------|
| pk_event_categories      | PK     | PRIMARY KEY (id)   |
| uq_event_categories_name | UNIQUE | UNIQUE (name)      |
| uq_event_categories_slug | UNIQUE | UNIQUE (slug)      |

#### Referenced by

- [`events.category_id`](#42-events)

---

## 4.2 `events`

#### Columns

| Column          | Type      | PK  | Nullability | Length |
|-----------------|-----------|-----|-------------|--------|
| id              | BIGINT    | PK  | NOT NULL    | -      |
| category_id     | BIGINT    |     | NULL        | -      |
| title           | VARCHAR   |     | NOT NULL    | 255    |
| slug            | VARCHAR   |     | NOT NULL    | 255    |
| description     | TEXT      |     | NULL        | -      |
| start_at        | TIMESTAMP |     | NOT NULL    | -      |
| end_at          | TIMESTAMP |     | NULL        | -      |
| location        | VARCHAR   |     | NULL        | 255    |
| cover_image_url | VARCHAR   |     | NULL        | 500    |
| is_published    | BOOLEAN   |     | NOT NULL    | -      |
| created_by_id   | BIGINT    |     | NOT NULL    | -      |
| updated_by_id   | BIGINT    |     | NULL        | -      |
| created_at      | TIMESTAMP |     | NOT NULL    | -      |
| updated_at      | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                            | Type   | Definition                                                                    |
|---------------------------------|--------|-------------------------------------------------------------------------------|
| pk_events                       | PK     | PRIMARY KEY (id)                                                              |
| fk_events_category_id_event_cat | FK     | FOREIGN KEY (category_id) REFERENCES [event_categories](#31-event_categories)(id) |
| fk_events_created_by_id_users   | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)                 |
| fk_events_updated_by_id_users   | FK     | FOREIGN KEY (updated_by_id) REFERENCES [users](#11-users)(id)                 |
| uq_events_slug                  | UNIQUE | UNIQUE (slug)                                                                 |
| chk_events_start_before_end     | CHECK  | (end_at IS NULL) OR (end_at > start_at)                                      |

#### Referenced by

- [`media_items.event_id`](#43-media_items)

---

## 4.3 `media_items`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| event_id      | BIGINT    |     | NOT NULL    | -      |
| uploader_id   | BIGINT    |     | NOT NULL    | -      |
| file_url      | VARCHAR   |     | NOT NULL    | 500    |
| thumbnail_url | VARCHAR   |     | NULL        | 500    |
| title         | VARCHAR   |     | NULL        | 255    |
| caption       | TEXT      |     | NULL        | -      |
| media_type    | VARCHAR   |     | NOT NULL    | 20     |
| position      | INT       |     | NOT NULL    | -      |
| is_visible    | BOOLEAN   |     | NOT NULL    | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |
| updated_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                             | Type   | Definition                                                          |
|----------------------------------|--------|---------------------------------------------------------------------|
| pk_media_items                   | PK     | PRIMARY KEY (id)                                                    |
| fk_media_items_event_id_events   | FK     | FOREIGN KEY (event_id) REFERENCES [events](#32-events)(id)          |
| fk_media_items_uploader_id_users | FK     | FOREIGN KEY (uploader_id) REFERENCES [users](#11-users)(id)         |
| chk_media_items_position_min0    | CHECK  | position >= 0                                                       |
| chk_media_items_media_type_valid | CHECK  | media_type IN ('IMAGE','VIDEO')                                     |

---

# 5. Shop (Catalog)

## 5.1 `shop_categories`

#### Columns

| Column      | Type      | PK  | Nullability | Length |
|-------------|-----------|-----|-------------|--------|
| id          | BIGINT    | PK  | NOT NULL    | -      |
| name        | VARCHAR   |     | NOT NULL    | 150    |
| slug        | VARCHAR   |     | NOT NULL    | 150    |
| description | TEXT      |     | NULL        | -      |
| position    | INT       |     | NOT NULL    | -      |
| is_visible  | BOOLEAN   |     | NOT NULL    | -      |
| created_at  | TIMESTAMP |     | NOT NULL    | -      |
| updated_at  | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                             | Type   | Definition         |
|----------------------------------|--------|--------------------|
| pk_shop_categories               | PK     | PRIMARY KEY (id)   |
| uq_shop_categories_name          | UNIQUE | UNIQUE (name)      |
| uq_shop_categories_slug          | UNIQUE | UNIQUE (slug)      |
| chk_shop_categories_position_min0| CHECK  | position >= 0      |

#### Referenced by

- [`products.category_id`](#52-products)

---

## 5.2 `products`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| category_id   | BIGINT    |     | NOT NULL    | -      |
| title         | VARCHAR   |     | NOT NULL    | 255    |
| slug          | VARCHAR   |     | NOT NULL    | 255    |
| description   | TEXT      |     | NULL        | -      |
| image_url     | VARCHAR   |     | NOT NULL    | 500    |
| display_style | VARCHAR   |     | NOT NULL    | 20     |
| badge_text    | VARCHAR   |     | NULL        | 50     |
| is_visible    | BOOLEAN   |     | NOT NULL    | -      |
| position      | INT       |     | NOT NULL    | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |
| updated_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                               | Type   | Definition                                                                           |
|------------------------------------|--------|--------------------------------------------------------------------------------------|
| pk_products                        | PK     | PRIMARY KEY (id)                                                                     |
| fk_products_category_id_shop_cat   | FK     | FOREIGN KEY (category_id) REFERENCES [shop_categories](#41-shop_categories)(id)      |
| uq_products_slug                   | UNIQUE | UNIQUE (slug)                                                                        |
| chk_products_position_min0         | CHECK  | position >= 0                                                                        |
| chk_products_display_style_valid   | CHECK  | display_style IN ('CARD','STICKER')                                                  |

---

# 6. Team

## 6.1 `poles`

#### Columns

| Column      | Type      | PK  | Nullability | Length |
|-------------|-----------|-----|-------------|--------|
| id          | BIGINT    | PK  | NOT NULL    | -      |
| name        | VARCHAR   |     | NOT NULL    | 150    |
| slug        | VARCHAR   |     | NOT NULL    | 150    |
| description | TEXT      |     | NULL        | -      |
| icon_name   | VARCHAR   |     | NULL        | 100    |
| position    | INT       |     | NOT NULL    | -      |
| is_visible  | BOOLEAN   |     | NOT NULL    | -      |
| created_at  | TIMESTAMP |     | NOT NULL    | -      |
| updated_at  | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                    | Type   | Definition         |
|-------------------------|--------|--------------------|
| pk_poles                | PK     | PRIMARY KEY (id)   |
| uq_poles_name           | UNIQUE | UNIQUE (name)      |
| uq_poles_slug           | UNIQUE | UNIQUE (slug)      |
| chk_poles_position_min0 | CHECK  | position >= 0      |

#### Referenced by

- [`team_members.pole_id`](#62-team_members)

---

## 6.2 `team_members`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| pole_id       | BIGINT    |     | NOT NULL    | -      |
| user_id       | BIGINT    |     | NULL        | -      |
| full_name     | VARCHAR   |     | NOT NULL    | 255    |
| nickname      | VARCHAR   |     | NULL        | 100    |
| bio           | TEXT      |     | NULL        | -      |
| photo_url     | VARCHAR   |     | NULL        | 500    |
| instagram_url | VARCHAR   |     | NULL        | 255    |
| position      | INT       |     | NOT NULL    | -      |
| is_visible    | BOOLEAN   |     | NOT NULL    | -      |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |
| updated_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                               | Type   | Definition                                                         |
|------------------------------------|--------|--------------------------------------------------------------------|
| pk_team_members                    | PK     | PRIMARY KEY (id)                                                   |
| fk_team_members_pole_id_poles      | FK     | FOREIGN KEY (pole_id) REFERENCES [poles](#51-poles)(id)            |
| fk_team_members_user_id_users      | FK     | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id)            |
| chk_team_members_position_min0     | CHECK  | position >= 0                                                      |

---

# 7. Audit Logs

## 7.1 `audit_logs`

#### Columns

| Column        | Type      | PK  | Nullability | Length |
|---------------|-----------|-----|-------------|--------|
| id            | BIGINT    | PK  | NOT NULL    | -      |
| actor_id      | BIGINT    |     | NULL        | -      |
| action        | VARCHAR   |     | NOT NULL    | 100    |
| entity_type   | VARCHAR   |     | NOT NULL    | 100    |
| entity_id     | BIGINT    |     | NULL        | -      |
| description   | VARCHAR   |     | NULL        | 255    |
| metadata_json | TEXT      |     | NULL        | -      |
| ip_address    | VARCHAR   |     | NULL        | 45     |
| user_agent    | VARCHAR   |     | NULL        | 500    |
| created_at    | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                           | Type  | Definition                                           |
|--------------------------------|-------|------------------------------------------------------|
| pk_audit_logs                  | PK    | PRIMARY KEY (id)                                     |
| fk_audit_logs_actor_id_users   | FK    | FOREIGN KEY (actor_id) REFERENCES [users](#11-users)(id) |
| chk_audit_logs_action_not_empty| CHECK | action <> ''                                         |
| chk_audit_logs_entity_type_not_empty | CHECK | entity_type <> ''                                  |

---

# 8. Challenges & Daily Games

## 8.1 `challenges`

#### Columns

| Column               | Type      | PK  | Nullability | Length |
|----------------------|-----------|-----|-------------|--------|
| id                   | BIGINT    | PK  | NOT NULL    | -      |
| title                | VARCHAR   |     | NOT NULL    | 255    |
| slug                 | VARCHAR   |     | NOT NULL    | 255    |
| description          | TEXT      |     | NULL        | -      |
| challenge_type       | VARCHAR   |     | NOT NULL    | 20     |
| label_tag            | VARCHAR   |     | NULL        | 50     |
| question_text        | TEXT      |     | NULL        | -      |
| action_target_label  | VARCHAR   |     | NULL        | 255    |
| expected_answer_text | VARCHAR   |     | NULL        | 255    |
| points_reward        | INT       |     | NOT NULL    | -      |
| requires_proof       | BOOLEAN   |     | NOT NULL    | -      |
| is_active            | BOOLEAN   |     | NOT NULL    | -      |
| starts_at            | TIMESTAMP |     | NULL        | -      |
| ends_at              | TIMESTAMP |     | NULL        | -      |
| created_by_id        | BIGINT    |     | NOT NULL    | -      |
| updated_by_id        | BIGINT    |     | NULL        | -      |
| created_at           | TIMESTAMP |     | NOT NULL    | -      |
| updated_at           | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                                         | Type   | Definition                                                                                               |
|----------------------------------------------|--------|----------------------------------------------------------------------------------------------------------|
| pk_challenges                                | PK     | PRIMARY KEY (id)                                                                                         |
| uq_challenges_slug                           | UNIQUE | UNIQUE (slug)                                                                                            |
| fk_challenges_created_by_id_users            | FK     | FOREIGN KEY (created_by_id) REFERENCES [users](#11-users)(id)                                            |
| fk_challenges_updated_by_id_users            | FK     | FOREIGN KEY (updated_by_id) REFERENCES [users](#11-users)(id)                                            |
| chk_challenges_type_valid                    | CHECK  | challenge_type IN ('QUESTION_TEXT','QUESTION_MCQ','ACTION_PHOTO','ACTION_VISIT')                         |
| chk_challenges_points_reward_min0            | CHECK  | points_reward >= 0                                                                                       |
| chk_challenges_dates_ok                      | CHECK  | (ends_at IS NULL) OR (starts_at IS NOT NULL AND ends_at > starts_at)                                    |
| chk_challenges_action_visit_requires_target  | CHECK  | (challenge_type <> 'ACTION_VISIT') OR (action_target_label IS NOT NULL AND action_target_label <> '')   |
| chk_challenges_photo_requires_proof          | CHECK  | (challenge_type <> 'ACTION_PHOTO') OR (requires_proof = TRUE)                                           |
| chk_challenges_text_requires_expected        | CHECK  | (challenge_type <> 'QUESTION_TEXT') OR (expected_answer_text IS NOT NULL AND expected_answer_text <> '') |

#### Referenced by

- [`challenge_options.challenge_id`](#82-challenge_options)
- [`challenge_attempts.challenge_id`](#83-challenge_attempts)

---

## 8.2 `challenge_options`

#### Columns

| Column       | Type      | PK  | Nullability | Length |
|--------------|-----------|-----|-------------|--------|
| id           | BIGINT    | PK  | NOT NULL    | -      |
| challenge_id | BIGINT    |     | NOT NULL    | -      |
| label        | VARCHAR   |     | NULL        | 10     |
| text         | VARCHAR   |     | NOT NULL    | 255    |
| is_correct   | BOOLEAN   |     | NOT NULL    | -      |
| position     | INT       |     | NOT NULL    | -      |
| created_at   | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                                  | Type   | Definition                                                            |
|---------------------------------------|--------|-----------------------------------------------------------------------|
| pk_challenge_options                  | PK     | PRIMARY KEY (id)                                                      |
| fk_challenge_options_challenge_id     | FK     | FOREIGN KEY (challenge_id) REFERENCES [challenges](#71-challenges)(id)|
| chk_challenge_options_position_min0   | CHECK  | position >= 0                                                         |

#### Referenced by

- [`challenge_attempts.selected_option_id`](#83-challenge_attempts)

---

## 8.3 `challenge_attempts`

#### Columns

| Column             | Type      | PK  | Nullability | Length |
|--------------------|-----------|-----|-------------|--------|
| id                 | BIGINT    | PK  | NOT NULL    | -      |
| challenge_id       | BIGINT    |     | NOT NULL    | -      |
| user_id            | BIGINT    |     | NOT NULL    | -      |
| selected_option_id | BIGINT    |     | NULL        | -      |
| answer_text        | TEXT      |     | NULL        | -      |
| proof_url          | VARCHAR   |     | NULL        | 500    |
| status             | VARCHAR   |     | NOT NULL    | 20     |
| auto_checked       | BOOLEAN   |     | NOT NULL    | -      |
| reviewed_by_id     | BIGINT    |     | NULL        | -      |
| reviewed_at        | TIMESTAMP |     | NULL        | -      |
| points_awarded     | INT       |     | NOT NULL    | -      |
| created_at         | TIMESTAMP |     | NOT NULL    | -      |

#### Constraints

| Name                                         | Type   | Definition                                                                                                  |
|----------------------------------------------|--------|-------------------------------------------------------------------------------------------------------------|
| pk_challenge_attempts                        | PK     | PRIMARY KEY (id)                                                                                            |
| fk_ca_challenge_id_challenges                | FK     | FOREIGN KEY (challenge_id) REFERENCES [challenges](#71-challenges)(id)                                     |
| fk_ca_user_id_users                          | FK     | FOREIGN KEY (user_id) REFERENCES [users](#11-users)(id)                                                     |
| fk_ca_selected_option_id_challenge_options   | FK     | FOREIGN KEY (selected_option_id) REFERENCES [challenge_options](#72-challenge_options)(id)                  |
| fk_ca_reviewed_by_id_users                   | FK     | FOREIGN KEY (reviewed_by_id) REFERENCES [users](#11-users)(id)                                             |
| uq_ca_challenge_user                         | UNIQUE | UNIQUE (challenge_id, user_id)                                                                              |
| chk_ca_status_valid                          | CHECK  | status IN ('PENDING','ACCEPTED','REJECTED')                                                                 |
| chk_ca_points_awarded_min0                   | CHECK  | points_awarded >= 0                                                                                         |
| chk_ca_non_pending_requires_reviewer_or_auto | CHECK  | (status = 'PENDING') OR (auto_checked = TRUE) OR (reviewed_by_id IS NOT NULL AND reviewed_at IS NOT NULL)  |
