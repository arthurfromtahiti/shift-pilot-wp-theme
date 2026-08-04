# WORKFLOW_RENDU_PAGE_FRONT — Rendu d'une page frontend WordPress

## Classification
- **Type** : `technical_flow`
- **Sous-type** : rendu de gabarit WordPress (template rendering)
- **Visibilité** : `external_user`
- **Acteur principal** : visiteur (requête HTTP) → WordPress (orchestration) → thème (rendu)
- **Acteurs** : visiteur ; moteur WordPress (hiérarchie de templates, boucle, API de rendu) ; `index.php`, `header.php`, `footer.php`
- **Criticité** : Haute — c'est la seule sortie visible du thème ; tout visiteur passe par là
- **Confiance** : medium — la chaîne de rendu interne au thème (`get_header()` → boucle → `get_footer()`) est entièrement visible dans le code (`VÉRIFIÉ_CODE`) ; le comportement de routage WordPress (sélection de `index.php` pour toutes les URLs) est une dépendance du cœur WP, hors dépôt.
- **Justification** : les 5 fichiers PHP/CSS ont été lus intégralement (`VÉRIFIÉ_CODE`). `index.php` est le seul template présent — pas de `single.php`, `page.php`, `archive.php`, `404.php` dans le dépôt (`VÉRIFIÉ_CODE`). Que WordPress se rabatte sur `index.php` pour toutes les URLs est un comportement présumé de la hiérarchie WP — comportement externe au dépôt, non sourcé ici (`HYPOTHÈSE`). La chaîne `get_header()` → boucle → `get_footer()` est entièrement visible.

## Objectif
Produire le document HTML lorsque `index.php` est invoqué par WordPress. Le thème assemble la structure de la page (doctype, head, chrome en-tête/pied) autour du contenu natif WordPress via la boucle. `index.php` est le seul template présent dans le dépôt ; que WordPress le sélectionne en fallback pour toutes les URLs (articles, pages, archives, 404) relève de la hiérarchie de templates WordPress — comportement externe au dépôt (`HYPOTHÈSE`) — suggéré par l'absence de tout autre template dans le dépôt (`VÉRIFIÉ_CODE`).

## Acteurs
- **Visiteur** — envoie une requête HTTP vers n'importe quelle URL du site
- **Moteur WordPress** — résout la requête, interroge la base de données, alimente la boucle (comportement du cœur WP, hors dépôt) ; sélection de `index.php` via la hiérarchie de templates (`HYPOTHÈSE` — suggéré par l'absence de tout autre template dans le dépôt)
- **`index.php`** — gabarit générique unique : orchestre `header.php` et `footer.php`, exécute la boucle
- **`header.php`** — produit l'en-tête HTML (doctype, head, corps de la balise `<body>`, chrome en-tête)
- **`footer.php`** — produit le pied de page et ferme le document HTML

## Points d'entrée
- Invocation de `index.php` par WordPress. Ce template est le seul présent dans le dépôt — pas de `single.php`, `page.php`, `archive.php`, `404.php` (`VÉRIFIÉ_CODE`). Que WordPress l'utilise en fallback universel est un comportement de la hiérarchie WP (`HYPOTHÈSE` — hors dépôt).

## Étapes principales
1. **Sélection du template** — WordPress parcourt sa hiérarchie de templates et, n'en trouvant aucun de spécialisé dans le thème, sélectionne `index.php` (le seul présent). *(Comportement WordPress natif, `HYPOTHÈSE` — suggéré par l'absence de tout autre template dans le dépôt.)*
2. **Appel `get_header()`** (`index.php:1`) — WordPress inclut `header.php` :
   - Doctype HTML5, balise `<html>` avec `language_attributes()` (`header.php:1-2`)
   - `<head>` avec charset (`bloginfo('charset')`, `header.php:4`) et `wp_head()` (`header.php:5`) — point d'injection des assets (CSS, titre, meta, scripts en head)
   - Fermeture `</head>`, ouverture `<body>` avec `body_class()` (`header.php:7`)
   - Chrome en-tête : `<header class="site-header"><h1>` avec `bloginfo('name')` — nom du site WP (`header.php:8`)
3. **Ouverture `<main class="site-content">`** (`index.php:2`)
4. **Boucle WordPress** (`index.php:3-10`) :
   - `have_posts()` (`index.php:3`) — WordPress vérifie si des posts correspondent à la requête courante
   - **Si des posts existent** : pour chaque post, `the_post()` → rendu d'un `<article>` avec :
     - `post_class()` (`index.php:4`) — classes CSS générées par WordPress selon le type/statut du post
     - `<h2>` + `the_title()` (`index.php:5`) — titre du post
     - `<div>` + `the_content()` (`index.php:6`) — corps du post (HTML filtré par WordPress)
   - **Si aucun post** : affichage de `<p>Aucun contenu.</p>` (`index.php:9`)
5. **Fermeture `</main>`** (`index.php:11`)
6. **Appel `get_footer()`** (`index.php:12`) — WordPress inclut `footer.php` :
   - `<footer class="site-footer"><p>&copy; Prox-i</p></footer>` (`footer.php:1`) — copyright en dur
   - `wp_footer()` (`footer.php:2`) — point d'injection des scripts JS (dont jQuery CDN, chargé en footer)
   - Fermeture `</body></html>` (`footer.php:3-4`)

## Règles métier
- **Gabarit unique dans le dépôt** : le dépôt ne contient aucun template spécialisé (`VÉRIFIÉ_CODE`). Que WordPress appelle `index.php` quelle que soit la nature de l'URL relève de la hiérarchie de templates WordPress (`HYPOTHÈSE` — comportement présumé du cœur WP, hors dépôt).
- **Fallback "aucun contenu"** : si `have_posts()` retourne `false`, le gabarit affiche `<p>Aucun contenu.</p>` — c'est la seule gestion d'erreur/fallback visible dans le thème (`index.php:9`). Il n'y a pas de template `404.php` dédié dans le dépôt (`VÉRIFIÉ_CODE`).
- **Copyright en dur** : la mention `&copy; Prox-i` est codée en dur dans `footer.php:1`, non paramétrable depuis l'administration WordPress.
- **Nom du site** : `bloginfo('name')` (`header.php:8`) lit le titre du site depuis la configuration WordPress (table `wp_options` hors dépôt) — modifiable depuis l'administration WP, pas dans ce code.

## Données
- **`post` (objet WordPress natif)** — titre (`the_title()`), corps (`the_content()`), classes CSS (`post_class()`) — fourni par le cœur WordPress depuis la base de données (hors dépôt)
- **Nom du site** — lu via `bloginfo('name')` depuis la configuration WordPress (hors dépôt)
- **Charset** — lu via `bloginfo('charset')` depuis la configuration WordPress (hors dépôt)

## Intégrations
- **API de rendu WordPress** (native) : `get_header()`, `get_footer()`, `have_posts()`, `the_post()`, `the_title()`, `the_content()`, `post_class()`, `wp_head()`, `wp_footer()`, `bloginfo()`, `language_attributes()`, `body_class()` — toutes des fonctions du cœur WordPress, hors dépôt
- Aucune intégration externe propre au thème (pas d'appel API tiers, pas de shortcode, pas de bloc Gutenberg personnalisé visible dans les fichiers du dépôt)

## Risques
- **Absence de templates spécialisés** : aucun `404.php`, `archive.php`, `single.php` dans le dépôt (`VÉRIFIÉ_CODE`). Si WordPress sélectionne `index.php` pour une URL inexistante et si `have_posts()` retourne `false` (comportement WP pour une 404 — `HYPOTHÈSE`), le visiteur verrait `<p>Aucun contenu.</p>` (`index.php:9`). Impact : expérience utilisateur non différenciée selon le contexte. Le code HTTP de réponse est géré par WordPress (hors dépôt).
- **Copyright en dur dans `footer.php`** : toute modification du nom de l'entité requiert une édition du code PHP, non configurable depuis l'interface d'administration WP. Impact : dette de maintenabilité. Scénario : `VÉRIFIÉ_CODE` (`footer.php:1`).
- **Thème minimal — aucune zone de widget, aucun menu de navigation** : aucun `register_sidebar()`, `register_nav_menus()` dans `functions.php` (vérifié par lecture complète). Si un plugin (ex. Contact Form 7, WooCommerce) suppose l'existence de zones de widget, il ne pourra pas les afficher via le thème. Impact : `HYPOTHÈSE` (plugins hors dépôt).

## Questions ouvertes
- D'autres templates (`page.php`, `single.php`, `archive.php`, `404.php`) existent-ils côté FTP hors dépôt, et constituent-ils le thème réel en production ? Le dépôt n'en contient aucun.
- La mention `&copy; Prox-i` (`footer.php:1`) reflète-t-elle l'entité qui exploitera le site en production, ou est-ce un placeholder du pilote ?
- Si WordPress sélectionne `index.php` pour les URLs inexistantes et si `have_posts()` retourne `false`, le visiteur verrait `<p>Aucun contenu.</p>` (`index.php:9`). Ce comportement pour le pilote est-il souhaité ou à compléter par un template `404.php` ?

## Preuves
- `index.php` — lu intégralement (13 lignes) : `VÉRIFIÉ_CODE`
- `header.php` — lu intégralement (9 lignes) : `VÉRIFIÉ_CODE`
- `footer.php` — lu intégralement (5 lignes) : `VÉRIFIÉ_CODE`
- `style.css` — lu intégralement (20 lignes) : `VÉRIFIÉ_CODE`
- `functions.php` — lu intégralement (24 lignes) : `VÉRIFIÉ_CODE`
- Absence de templates spécialisés vérifiée par lecture de la liste des fichiers du dépôt (`ls` confirmé)
