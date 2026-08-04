# CDC_FONCTIONNEL — Cahier des charges du thème Shift Pilot

## Résumé

Le thème Shift Pilot est une couche de présentation minimaliste pour WordPress : il produit un document HTML (doctype, `<head>`, chrome en-tête/pied) autour du contenu natif de WordPress (posts/pages) via une boucle standard. Aucune logique métier, aucun modèle de données propre — le thème ne **consomme** que les API de rendu WordPress. Tout ce qui concerne le contenu réel, les plugins et la base de données relève du hors périmètre versionné ici.

**Périmètre thème** : rendu générique de contenu WordPress natif via un gabarit unique (`index.php`), sans différenciation par type de contenu. Nature métier réelle du site (blog, vitrine, e-commerce) non déterminable depuis ce dépôt seul.

**Cas hors périmètre** : e-commerce (WooCommerce), formulaires (Contact Form 7), SEO avancé (Yoast) — plugins hors dépôt. Intégration spécialisée par type de contenu — exigerait des templates `single.php`, `archive.php`, `woocommerce.php` absents du dépôt.

---

## Objectif métier & périmètre

| Aspect | Détail |
|---|---|
| **Objectif** | Fournir une couche de présentation HTML pour un site WordPress |
| **Acteurs** | Visiteur (HTTP) → WordPress (orchestration) → thème (rendu) |
| **Périmètre du thème** | Rendu du gabarit + déclaration des capacités WP + chargement des assets |
| **Périmètre exclu** | Contenu, base de données, plugins, cœur WordPress, SEO, e-commerce, authentification |

---

## Règles métier & fonctionnelles

### 1. Rendu du document HTML

**Règle** : Le thème produit un squelette HTML5 avec structure sémantique appelant WordPress pour les injections dynamiques (doctype → html → head → body → header/main/footer).

- **Preuve structurelle** : `header.php:1-9`, `index.php:1-13`, `footer.php:1-5` (`VÉRIFIÉ_CODE`)
- **Détail** :
  - Doctype HTML5 (`<!DOCTYPE html>`) — `PROUVÉ_CODE`
  - Charset UTF-8 via `<?php bloginfo('charset'); ?>` — appel WordPress présent (`PROUVÉ_CODE`), valeur lue depuis configuration WordPress (`HYPOTHÈSE_EFFET`)
  - Langue via `<?php language_attributes(); ?>` — appel WordPress présent (`PROUVÉ_CODE`), attribut généré par WordPress selon configuration site (`HYPOTHÈSE_EFFET`)
  - `<title>` déclaration `add_theme_support('title-tag')` (`functions.php:8`) — `PROUVÉ_CODE`, injection par WordPress via `wp_head()` (`header.php:5`) — `HYPOTHÈSE_EFFET`
  - `<body>` appel `body_class()` présent (`PROUVÉ_CODE`) — classes générées par WordPress selon type de page (`HYPOTHÈSE_EFFET`)
  - Sémantique HTML5 : balises `<header class="site-header">`, `<main class="site-content">`, `<footer class="site-footer">` — `PROUVÉ_CODE`

**Distinction critique** : le thème **déclare** et **appelle** les points d'injection WordPress ; le **contenu et l'effet** du rendu final (valeur du charset, contenu du `<title>`, classes contextuelles) relèvent du comportement du cœur WordPress, non du code du thème versionné.

---

### 2. Affichage du contenu via la boucle WordPress

**Règle** : Le contenu (posts/pages) est affiché via la boucle WordPress standard — une itération sur les résultats `have_posts()`, rendu de `the_title()` et `the_content()` pour chaque.

- **Preuve** : `index.php:3-10` (`VÉRIFIÉ_CODE`)
- **Détail** :
  ```php
  if ( have_posts() ) {
    while ( have_posts() ) {
      the_post();
      // Rendu : titre, contenu, classes post-spécifiques
    }
  } else {
    // Fallback si aucun post
    echo '<p>Aucun contenu.</p>';
  }
  ```
- **Contenu rendu** :
  - `<h2><?php the_title(); ?></h2>` — titre du post (filtré par WordPress)
  - `<div><?php the_content(); ?></div>` — corps du post (filtré, shortcodes exécutés)
  - Classes CSS `<?php post_class(); ?>` — générées par WordPress selon type/statut du post
- **Limitation** : aucun appel à `the_post_thumbnail()` — images mises en avant ne sont jamais affichées malgré `add_theme_support('post-thumbnails')` (`functions.php:9`)

**Hypothèse** : `the_content()` applique déjà les filtres WordPress (echappement, exécution shortcodes, etc.) — le thème ne rééchappe pas. Comportement cohérent, pas d'XSS détectable dans le thème (`VÉRIFIÉ_CODE`).

---

### 3. Gabarit unique pour tous les types de contenu

**Règle** : Un seul template `index.php` est présent dans le dépôt — pas de `single.php`, `page.php`, `archive.php`, `404.php`.

- **Preuve** : inventaire complet du dépôt (`VÉRIFIÉ_CODE`)
- **Conséquence** : selon la hiérarchie de templates WordPress (non sourcée dans ce dépôt — `HYPOTHÈSE`), WordPress appelle `index.php` pour tous les types de contenu (articles, pages, archives, URLs inexistantes).
- **Impact conditionnelle** :
  - **Si WordPress appelle `index.php` pour une URL inexistante** (`HYPOTHÈSE`), aucune différenciation visuelle entre une page vide et une erreur 404 (tous deux affichent `<p>Aucun contenu.</p>`)
  - Pas de template d'erreur présent dans le dépôt, pas de lien vers l'accueil (`VÉRIFIÉ_CODE`)
  - Pas de template pour archives dans le dépôt (`VÉRIFIÉ_CODE`)
- **Recommandation pour un site réel** : créer `404.php` avec message et lien d'accueil pour différencier les erreurs 404 des listes vides

---

### 4. Chargement des assets (CSS + jQuery)

**Règle** : Deux ressources sont enregistrées et mises en file d'attente via les hooks WordPress natifs :

- **Feuille de style du thème** :
  - Enregistrement : `wp_enqueue_style('shift-pilot-style', get_stylesheet_uri(), [], '1.0.2')` (`functions.php:13`) — `PROUVÉ_CODE`
  - Injection : `wp_head()` appelé en `header.php:5` (`PROUVÉ_CODE`) ; WordPress émet la balise `<link rel="stylesheet" href="...?ver=1.0.2">` en réaction (`HYPOTHÈSE_EFFET`)
  - Cache-buster : version `'1.0.2'` (correspondant à `style.css:6`) — doit être mise à jour manuellement à chaque modification du fichier CSS
  - **Limitation** : versioning manuel non automatisé, risque d'oubli de mise à jour lors d'un changement CSS

- **jQuery depuis CDN externe** :
  - Désinscription : `wp_deregister_script('jquery')` (`functions.php:16`) — supprime le jQuery bundlé WordPress (`PROUVÉ_CODE`)
  - Enregistrement : `wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.12.4.min.js', [], '1.12.4', true)` (`functions.php:17-23`) — `PROUVÉ_CODE`
  - Version épinglée : `1.12.4` (non automatisée, montée de version manuelle uniquement)
  - Injection : `wp_footer()` appelé en `footer.php:2` (`PROUVÉ_CODE`) ; WordPress émet la balise `<script src="https://code.jquery.com/...?ver=1.12.4"></script>` en fin de `</body>` en réaction (`HYPOTHÈSE_EFFET`)
  - **Choix architectural documenté** : commit `5d9b462` mentionne « jQuery CDN épinglé 1.12.4 » — choix délibéré, à confirmer avant changement
  - **Limitations** :
    - Dépendance sur CDN externe sans fallback local (si `code.jquery.com` indisponible, jQuery ne charge pas)
    - Pas de Subresource Integrity (SRI) — aucune protection contre compromission CDN
    - Version 1.12.4 est EOL ; vulnérabilités documentées dans NVD (CVE-2019-11358, CVE-2020-11022/11023)

- **Ordre d'exécution** :
  - `wp_enqueue_scripts` est déclenché par WordPress à chaque rendu frontend (non-admin)
  - Callback du thème exécutée avec priorité par défaut (10)
  - **Risque** : si un plugin enregistré avec priorité < 10 déclare une dépendance sur `'jquery'` avant la désinscription du thème, le résultat est indéterminé (`HYPOTHÈSE` — plugins hors dépôt)

- **Preuve** : `functions.php:12-24` lu intégralement ; `header.php:5`, `footer.php:2` (`VÉRIFIÉ_CODE`)

---

### 5. Déclaration des capacités WordPress

**Règle** : Le thème déclare deux capacités WordPress au chargement, via le hook `after_setup_theme`.

- **`add_theme_support('title-tag')`** :
  - Effet : WordPress gère la balise `<title>` (aucun `<title>` manuel dans les templates)
  - Preuve : `functions.php:8` ; `header.php:1-9` ne contient aucun `<title>` (`VÉRIFIÉ_CODE`)
  - Cohérence : respectée

- **`add_theme_support('post-thumbnails')`** :
  - Déclaration : `functions.php:9` (`PROUVÉ_CODE`)
  - Effet attendu : WordPress active le champ « Image mise en avant » dans l'admin pour les éditeurs (`HYPOTHÈSE_EFFET` — comportement WP non sourcé ici)
  - Consommation dans le thème : **aucune** — aucun template n'appelle `the_post_thumbnail()` (`VÉRIFIÉ_CODE` — vérifiable par absence dans `index.php`)
  - **Incohérence documentée** : capacité déclarée mais jamais affichée dans le rendu frontend. La featured image mise en avant par un éditeur n'apparaîtra pas sur le site (le thème ne la consomme pas via `the_post_thumbnail()`). Note : les images insérées directement dans le contenu WordPress via `the_content()` apparaissent indépendamment.
  - **Correction à envisager** : soit retirer la déclaration (si pas d'usage prévu), soit ajouter un appel à `the_post_thumbnail()` dans `index.php` (si consommation souhaitée)

- **Absences notables** :
  - Aucun menu de navigation (`register_nav_menus()` absent)
  - Aucune sidebar/zone de widget (`register_sidebar()` absent)
  - Aucune taille d'image personnalisée (`add_image_size()` absent)

- **Preuve** : `functions.php:7-10` lu intégralement (`VÉRIFIÉ_CODE`)

---

### 6. Identité visuelle & chrome

**Règle** : En-tête et pied contiennent le chrome du site (branding minimaliste).

- **En-tête** (`header.php:8`) :
  - Titre du site via `<?php bloginfo('name'); ?>` (lu depuis configuration WordPress)
  - Rendu : `<header class="site-header"><h1>` + nom du site + `</h1></header>`
  - Pas de logo, pas de menu de navigation
  - **Limitation** : pas de lien vers l'accueil depuis les autres pages

- **Pied** (`footer.php:1`) :
  - Copyright en dur : `&copy; Prox-i`
  - **Limitation** : non configurable depuis l'admin WordPress, modification exige une PR
  - **Contexte** : acceptable pour un pilote, limitation pour multi-client

- **Feuille de style** (`style.css:13-19`) :
  - 3 règles CSS : `body` (font Georgia, couleur foncée), `.site-header` (bordure basse bleue)
  - Minimalisme voulu pour un pilote

- **Preuve** : `header.php`, `footer.php`, `style.css` lus intégralement (`VÉRIFIÉ_CODE`)

---

## Capacités déclarées (pour qui, quoi)

| Capacité | Acteur | Détail | Statut | Preuve |
|---|---|---|---|---|
| **Affichage contenu posts/pages** | Visiteur | Boucle WP + titre + corps filtrés, rendu générique sans différenciation par type de contenu | PROUVÉ | `index.php:3-10` |
| **Fallback vide** | Visiteur | Message `<p>Aucun contenu.</p>` si boucle retourne rien ; comportement 404 dépend de hiérarchie WP | PROUVÉ + HYPOTHÈSE | `index.php:9` + hierarchie WP |
| **Gestion du `<title>`** | WordPress | Thème déclare `add_theme_support('title-tag')` ; injection via `wp_head()` dépend du cœur WP | PROUVÉ_CODE + HYPOTHÈSE_EFFET | `functions.php:8`, `header.php` |
| **Images mises en avant** | Editeur (admin WP) | Capacité déclarée (`PROUVÉ_CODE`) ; champ admin et rendu dépendent du cœur WP (`HYPOTHÈSE`) | PROUVÉ_CODE + HYPOTHÈSE_EFFET | `functions.php:9` |
| **Personnalisation du nom du site** | Admin WP | Appel `bloginfo('name')` présent (`PROUVÉ_CODE`) ; valeur modifiable via Paramètres WP (`HYPOTHÈSE_EFFET`) | PROUVÉ_CODE + HYPOTHÈSE_EFFET | `header.php:8` |
| **Attribut `lang` & charset UTF-8** | Navigateur | Appels `language_attributes()` et `bloginfo('charset')` présents (`PROUVÉ_CODE`) ; injection et effet dépendent du cœur WP | PROUVÉ_CODE + HYPOTHÈSE_EFFET | `header.php:1-2,4` |
| **Classes CSS contextuelles** | Développeur (CSS) | Appels `post_class()`, `body_class()` présents (`PROUVÉ_CODE`) ; contenu généré par WordPress selon contexte (`HYPOTHÈSE_EFFET`) | PROUVÉ_CODE + HYPOTHÈSE_EFFET | `index.php:4`, `header.php:7` |

---

## Limitations & incertitudes

### Limitations avérées (dans le code du thème)

1. **Pas de template 404 ni d'archive** — seul `index.php` existe dans le dépôt ; la sélection réelle dépend de la hiérarchie WordPress hors dépôt (`HYPOTHÈSE` — voir Scenario 3, ligne 251)
2. **Fallback vide indistinct** — message `<p>Aucun contenu.</p>` utilisé pour toutes les listes vides (pages vides, archives vides, 404 si WordPress sélectionne `index.php`) ; impossible de distinguer les cas sans template `404.php` (`index.php:9` — `PROUVÉ_CODE` + `HYPOTHÈSE_WORDPRESS`)
3. **Images mises en avant inutilisées** — capacité déclarée (`functions.php:9`), jamais affichée via `the_post_thumbnail()` (`index.php` — `PROUVÉ_CODE`)
4. **Copyright non paramétrable** — codé en dur dans `footer.php:1` (`PROUVÉ_CODE`)
5. **jQuery ancien + dépendance CDN** — jQuery 1.12.4 sans fallback local, pas de SRI (`functions.php:17-23` — `PROUVÉ_CODE`)

### Incertitudes (HYPOTHÈSE)

- **Hiérarchie de templates WordPress** : que WordPress appelle `index.php` pour tous les types de contenus relève du cœur WP, non sourcé dans ce dépôt
- **Rendu des plugins cités** : WooCommerce, Contact Form 7, Yoast SEO — aucune intégration visible dans le thème ; leur fonctionnement réel est `INCONNU`
- **Composante e-commerce réelle** : WooCommerce est cité dans `README.md:8`, mais aucun `woocommerce.php` ni hook WC dans le thème (`VÉRIFIÉ_CODE`) — le site utilise-t-il réellement e-commerce ?
- **Code HTTP 404** : les URLs inexistantes retournent-elles le bon code HTTP 404, ou les plugins de SEO le gèrent-ils ? Non vérifiable depuis ce dépôt.

### Incertitudes (INCONNU)

- **Contenu réel du site** — modèle, volume, types de posts (`INCONNU` — base de données hors dépôt)
- **Plugins actifs** — compatibilité jQuery 1.12.4 avec WooCommerce, CF7, Yoast (`INCONNU` — plugins hors dépôt)
- **Configuration WordPress réelle** — version WP, extensions PHP, CDN intermédiaire (`INCONNU`)
- **Autres templates côté FTP** — `single.php`, `404.php`, `archive.php` existent-ils hors dépôt ? (`INCONNU`)

---

## Risques fonctionnels

| Risque | Preuve locale | Condition/Dépendance externe | Impact (si condition vraie) | Mitigation |
|---|---|---|---|---|
| **jQuery 1.12.4 — vulnérabilités documentées** | Version `1.12.4` épinglée (`functions.php:19`) — `PROUVÉ_CODE` | Exploitabilité dépend de plugins utilisant jQuery (`HYPOTHÈSE` — plugins hors dépôt) | XSS/prototype pollution si exploité via plugins | Montée vers jQuery 3.x avant production réelle |
| **CDN jQuery sans fallback** | Enregistrement sans fallback local (`functions.php:17-23`) — `PROUVÉ_CODE` | Panne CDN `code.jquery.com` (indépendant du dépôt) — `HYPOTHÈSE_RISQUE` | Absence de jQuery en production si CDN indisponible | Héberger jQuery localement ou ajouter fallback |
| **CDN jQuery sans SRI** | Pas de hash SRI dans l'enregistrement (`functions.php:17-23`) — `PROUVÉ_CODE` | Compromission CDN (risque externe) — `HYPOTHÈSE_RISQUE` | Exécution de code malveillant si CDN compromis | Ajouter filtre `script_loader_tag` + hash SRI |
| **Cache CSS oublié** | Versioning manuel (`functions.php:13`, `style.css:6`) — `PROUVÉ_CODE` | Développeur oublie d'incrémenter la version (risque de processus humain) | Modifications CSS invisibles en production (cache navigateur/CDN) | Automatiser ou documenter rigoureusement le versioning CSS |
| **Ordre d'exécution jQuery** | Désinscription avec priorité par défaut (`functions.php:16`, priorité 10) — `PROUVÉ_CODE` | Plugin enregistré avec priorité < 10 sur `wp_enqueue_scripts` déclare une dépendance sur `'jquery'` (hors dépôt — `HYPOTHÈSE`) | Dépendance jQuery du plugin non satisfaite ou comportement indéterminé | Tester avec plugins actifs ; abaisser priorité du callback thème ou vérifier ordre plugins |
| **Rendu indifférencié 404** | Seul `index.php` dans le dépôt (`VÉRIFIÉ_CODE`) | WordPress sélectionne `index.php` comme fallback pour 404 selon hiérarchie (hors dépôt — `HYPOTHÈSE`) | Mauvais signal SEO, UX dégradée pour les URLs invalides (pas de distinction 404/vide) | Créer `404.php` avec message distinct et lien d'accueil |
| **Incohérence `post-thumbnails`** | Capacité déclarée (`functions.php:9`), jamais affichée (`index.php` — `PROUVÉ_CODE` — aucun appel à `the_post_thumbnail()`) | N/A — limitation interne du thème | Attente utilisateur non satisfaite (éditeurs chargent images en admin, jamais affichées frontend) | Retirer la déclaration (`functions.php:9`) ou ajouter appel à `the_post_thumbnail()` dans `index.php` |

---

## Parcours d'utilisation

### Scénario 1 : Visiteur accède à la page d'accueil

1. **Requête HTTP** : Visiteur arrive sur `/?` (ou `/index.php`)
2. **Routing WordPress** : WordPress détermine que la page d'accueil affiche les posts récents (comportement WP, hors dépôt — `HYPOTHÈSE`)
3. **Sélection du template** : WordPress sélectionne `index.php` (seul template présent, `VÉRIFIÉ_CODE`). Que WordPress sélectionne effectivement ce template pour tous les types de contenu dépend de la hiérarchie WordPress (hors dépôt — `HYPOTHÈSE`)
4. **Rendu du gabarit** (`VÉRIFIÉ_CODE` pour la structure) :
   - `get_header()` → inclut `header.php` (`PROUVÉ_CODE`), produit doctype + head + `<body>` + en-tête
   - Boucle (`have_posts()` + `while` + `the_post()`) → pour chaque post : titre + contenu (appels `PROUVÉ_CODE` en `index.php:3-10`, contenu généré et filtré par WordPress — `HYPOTHÈSE_EFFET`)
   - Fallback vide : si aucun post (rare sur accueil), `<p>Aucun contenu.</p>` (`PROUVÉ_CODE`)
   - `get_footer()` → inclut `footer.php` (`PROUVÉ_CODE`), produit pied + fermeture `</body></html>`
5. **Assets injectés** (enregistrement du thème → injection WordPress) :
   - `wp_head()` → styles (`style.css` via `get_stylesheet_uri()` — `PROUVÉ_CODE` pour l'appel d'enqueue, injection comme balise `<link>` via WordPress — `HYPOTHÈSE_EFFET`), meta-tags (WordPress), `<title>` (déclaration `add_theme_support` — `PROUVÉ_CODE` en `functions.php:8`, contenu et injection via WordPress — `HYPOTHÈSE_EFFET`)
   - `wp_footer()` → jQuery CDN enregistré par thème (`PROUVÉ_CODE` en `functions.php:17-23`), injection comme balise `<script>` et rendu dans le flux HTML par WordPress (`HYPOTHÈSE_EFFET`)
6. **Réponse** : document HTML complet, rendu au navigateur. Contenu réel, filtrage, exécution de shortcodes, injection des assets et comportement plugins dépendent de WordPress et de l'environnement réel (hors dépôt — `HYPOTHÈSE` ou `INCONNU`).

---

### Scénario 2 : Visiteur accède à un article spécifique

1. **Requête HTTP** : Visiteur clique sur `/2026/08/mon-article/` (ou équivalent)
2. **Routing WordPress** : WordPress résout l'URL, charge le post correspondant dans `$post` global
3. **Sélection du template** : WordPress cherche `single.php` → absent dans ce dépôt (`VÉRIFIÉ_CODE`) → fallback à `index.php` (hiérarchie WP, `HYPOTHÈSE` — hors dépôt)
4. **Rendu** : identique au scénario 1 (gabarit unique)
   - Titre : `the_title()` = titre de l'article
   - Contenu : `the_content()` = corps de l'article
   - Aucune différenciation visuelle (pas de breadcrumb, pas de « Retour au blog »)

---

### Scénario 3 : Visiteur accède à une URL inexistante (404) — HYPOTHÈSE

**Prérequis** : ce scénario suppose que WordPress sélectionne `index.php` pour les URLs inexistantes (hiérarchie WP, hors dépôt, `HYPOTHÈSE`).

1. **Requête HTTP** : `/page-inexistante/`
2. **Routing WordPress** : WordPress détermine qu'aucun post ne correspond
3. **Sélection du template** : WordPress cherche `404.php` → absent dans ce dépôt (`VÉRIFIÉ_CODE`) → fallback à `index.php` selon hiérarchie WP (`HYPOTHÈSE`)
4. **Rendu** (si le fallback s'applique) :
   - Boucle : `have_posts()` = false (aucun contenu ne correspond)
   - Fallback : `<p>Aucun contenu.</p>` — même message que pour une page vide (`PROUVÉ_CODE`)
   - Code HTTP : probablement 404 (géré par WordPress, hors dépôt, `HYPOTHÈSE`) — mais rendu identique à une page vide

**Note** : plusieurs autres rendus sont possibles selon la configuration WordPress réelle (plugins SEO, configuration du site hors dépôt, etc.) — ce scénario dépend entièrement du cœur WordPress et des plugins, non du code versionné.

---

## Conformité & contrôle qualité

### Vérifications internes au thème

✅ **Sorties échappées** : tous les appels `the_*()` et `bloginfo()` sont des API WordPress qui appliquent leur filtre — pas d'`echo` brut de variable (`VÉRIFIÉ_CODE`)

✅ **Pas de secrets** : aucune clé API, token, mot de passe dans le code (`VÉRIFIÉ_CODE`)

✅ **Pas de `$_GET`/`$_POST`/`$_SERVER`** : le thème ne traite aucune entrée utilisateur directe (`VÉRIFIÉ_CODE`)

❌ **Tests automatisés** : couverture 0 % — aucune suite de tests PHP ou JS (`README.md:13` ; inventaire du dépôt, `VÉRIFIÉ_CODE`)

❌ **Linter PHP/CSS** : aucun outil configuré

---

## Questions pour le product owner

1. **Statut de production** : ce pilote remplacera-t-il le thème actuel, ou reste-t-il un test ?
2. **jQuery** : la version 1.12.4 est-elle définitive ou un placeholder à mettre à jour ?
3. **Templates supplémentaires** : `single.php`, `404.php`, `archive.php` existent-ils côté FTP hors dépôt ?
4. **WooCommerce** : le site a-t-il une vraie composante e-commerce ? Faut-il intégrer des templates WC au thème ?
5. **Localisation** : le site est-il multilingue ? Les templates supportent-elles les langues via `language_attributes()` + .po files ?
6. **Copyright** : `&copy; Prox-i` est-il l'entité finale, ou un placeholder ?
