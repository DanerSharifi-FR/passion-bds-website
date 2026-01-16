# Rôles & Contexte fonctionnel

## 1. C’est quoi cette plateforme ?

L’appli est le **hub de campagne** d’une liste BDS :

- Les étudiants se connectent avec leur **mail universitaire + code 4 chiffres**.
- Ils gagnent et dépensent des **points** via :
    - des **allos** (services / petits privilèges réservables sur des créneaux),
    - des **activités** (compétitions, jeux, tournois - individuels ou par équipe),
    - des points donnés **à la main** par les admins pendant les activités IRL.
- Autour de ça, la liste anime :
    - des évènements + galeries photos,
    - une “boutique” fictive (catalogue seulement, pas de paiement),
    - une présentation de l’équipe par **pôle**,
    - un panneau d’admin et des logs d’audit.

Tout le reste (modèle SQL, PlantUML, etc.) sert juste à structurer ça proprement.

---

## 2. Acteurs & rôles

On a deux niveaux :

1. Des **rôles implicites** (en fonction de la connexion).
2. Des **rôles explicites** dans les tables `roles` / `user_roles` (cumulables).

### 2.1 Visiteur (non connecté)

- Accès aux pages publiques (home, présentation, éventuellement quelques infos statiques).
- Pas de points, pas d'allos, pas d'activités, rien de lié à un compte.

### 2.2 Étudiant connecté (`ROLE_USER` par convention)

Une fois connecté avec son mail universitaire :

- Voit **son total de points**.
- Réserve des **allos** sur les créneaux disponibles.
- Peut annuler sa propre réservation avant la limite.
- Participe aux **activités** :
    - rejoint des activités individuelles ou est ajouté à des équipes,
    - gagne des points spécifiques à chaque activité (classements séparés),
    - voit les libellés de points spécifiques aux activités (victoires, secondes, kills, etc.).
- Consulte :
    - les évènements & galeries,
    - le catalogue de la fausse boutique,
    - l’équipe par pôle.

C’est l’utilisateur **de base** ; il peut ou non avoir des rôles admin en plus.

---

## 3. Rôles admin fonctionnels

Les rôles admin sont **additifs** : un même utilisateur peut avoir 0, 1 ou plusieurs rôles dans `user_roles`.

### 3.1 `ROLE_GAMEMASTER`

Responsable de la **mécanique de points, des allos et des activités**.

- **Points**
    - Ajoute / retire des points via des **`point_transactions` manuels** :
        - mini-jeux IRL,
        - ambiance, participation, etc.
    - Peut lier les points à des **activités** spécifiques (pour des classements par activité).
- **Activités**
    - Crée / modifie des **activités** (compétitions, tournois, jeux) :
        - définit le titre, slug, description,
        - définit le libellé de points (comment les points sont appelés : "victoires", "secondes", "kills", etc.),
        - choisit le mode : `INDIVIDUAL` ou `TEAM`,
        - assigne des admins d'activité (gamemasters pour cette activité spécifique),
        - ajoute des participants (utilisateurs ou équipes).
    - Gère les participants et équipes des activités.
    - Les points gagnés dans les activités peuvent être suivis séparément (classements spécifiques aux activités).
    - **Note** : Les admins d'activité (via `activity_admins`) peuvent gérer une activité spécifique même sans `ROLE_GAMEMASTER`, permettant la délégation à des gamemasters spécialisés par activité.
- **Allos**
    - Crée / modifie / désactive des allos.
    - Paramètre :
        - le coût en points,
        - la fenêtre de disponibilité,
        - la durée d'un créneau,
        - les admins assignés pour gérer l'allo.
    - Sur un allo donné :
        - ouvre / ferme,
        - voit les réservations,
        - gère chaque usage (accepter, marquer comme fait, annuler).

En résumé : ce rôle contrôle **comment les étudiants gagnent / dépensent leurs points**.

---

### 3.2 `ROLE_BLOGGER`

Responsable des **évènements et de la galerie**.

- Crée les **catégories d’événement** et les **évènements** (date, lieu, description…).
- Ajoute des photos / vidéos dans les **media items** liés aux évènements.
- Choisit quels médias sont visibles et dans quel ordre.
- Publie / dépublie des évènements.

C’est la personne “contenu / comm” pour la partie souvenirs/galerie du site.

---

### 3.3 `ROLE_TEAM`

Responsable de la **page équipe**.

- Gère les **pôles** (nom, description, ordre).
- Gère les **membres** :
    - nom / surnom,
    - rôle dans la liste,
    - photo et réseaux,
    - visibilité et ordre d’affichage.
- Peut éventuellement lier un membre à un vrai `user` pour des features futures (badges, actions spéciales, etc.).

Objectif : garder la présentation de l’équipe à jour pendant toute la campagne.

---

### 3.4 `ROLE_SHOP` (optionnel mais propre)

Responsable de la **boutique fictive**.

- Crée les **catégories de boutique** et les **produits**.
- Choisit le style d’affichage (`CARD` vs `STICKER`) et les badges (“Nouveau”, “Edition limitée”…).
- Contrôle la visibilité et l’ordre.

Pas de paiement. C’est juste un **catalogue ludique** pour montrer le merch / les goodies que les points pourraient débloquer.

Si vous ne voulez pas de rôle séparé, ça peut être fusionné avec `ROLE_GAMEMASTER` ou donné à n’importe quel admin.

---

### 3.5 `ROLE_SUPER_ADMIN`

Le **mode dieu** de la plateforme.

- Gère les **comptes admin et les rôles** :
    - crée des utilisateurs admins,
    - ajoute / enlève des rôles dans `user_roles`.
- Possède **tous les droits** de :
    - `ROLE_GAMEMASTER`,
    - `ROLE_BLOGGER`,
    - `ROLE_TEAM`,
    - `ROLE_SHOP`.
- Accède aux **logs d’audit** :
    - filtre par acteur, action, entité, date, etc.
    - peut remonter qui a fait quoi et quand.
- Peut accéder à toutes les données, même si normalement limitées par module.

En pratique : 1–2 personnes max dans la liste, pour la sécurité et le debug.

---

## 4. Matrice Modules × Rôles (résumé)

Légende :
- **R** = lecture / liste
- **C/U/D** = création / mise à jour / suppression
- **★** = actions spécifiques

| Module              | Étudiant (`ROLE_USER`) | `ROLE_GAMEMASTER`           | `ROLE_BLOGGER`      | `ROLE_TEAM`         | `ROLE_SHOP` | `ROLE_SUPER_ADMIN` |
|---------------------|------------------------|-----------------------------|---------------------|---------------------|------------|--------------------|
| Auth / profil       | son profil (R)         | –                           | –                   | –                   | –          | full               |
| Solde de points     | le sien (R)            | C (ajouts/retraits manuels) | –                   | –                   | –          | full               |
| Activités           | participer (R/★)       | C/U/D + gestion participants| –                   | –                   | –          | full               |
| Allos               | réserver / annuler (R★)| C/U/D + gestion usages (★)  | –                   | –                   | –          | full               |
| Évènements          | R                      | R                           | C/U/D               | –                   | –          | full               |
| Galeries            | R                      | R                           | C/U/D médias        | –                   | –          | full               |
| Boutique (catalogue)| R                      | R (évent. C/U)              | –                   | –                   | C/U/D      | full               |
| Équipe & pôles      | R                      | R                           | –                   | C/U/D               | –          | full               |
| Logs d'audit        | –                      | –                           | –                   | –                   | –          | R (full)           |
| Admin & rôles       | –                      | pas de gestion des rôles    | pas de gestion      | pas de gestion      | –          | C/U/D              |

Vous ajustez cette matrice selon la réalité du projet, mais l’idée reste :  
**les rôles métier = “qui pilote quel module”**, et le super admin passe au-dessus de tout.

---

## 5. Scénarios typiques (pour situer)

### 5.1 Journée d'un étudiant

1. Se connecte avec son mail et son code.
2. Participe aux **activités** :
    - rejoint des activités individuelles ou est ajouté à des équipes,
    - gagne des points spécifiques aux activités (suivis séparément des points globaux).
3. Réserve un allo plus tard dans la semaine s'il a assez de points.
4. Consulte les évènements, galeries, catalogue boutique et pages équipe.

### 5.2 Journée d'un gamemaster

1. Gère les **activités** :
    - crée de nouvelles activités (tournois, compétitions),
    - ajoute des participants ou équipes,
    - assigne des admins spécifiques aux activités,
    - attribue des points liés aux activités (pour les classements par activité).
2. Ajoute / retire des points via des **`point_transactions` manuels** :
    - mini-jeux IRL,
    - ambiance, participation, etc.
3. Sur les allos, suit les **créneaux** et marque les usages en **DONE** ou **CANCELLED**.

Avec ce doc + le schéma SQL + les diagrammes PlantUML, n’importe quel dev comprend le système sans avoir besoin d’un long brief oral.
