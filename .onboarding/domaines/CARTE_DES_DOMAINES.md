# Carte des domaines — shift-pilot-wp-theme

> **Confiance globale : `high` sur le périmètre versionné (le thème), `low` sur le site complet.**
> Les 6 fichiers du dépôt ont été lus intégralement (`style.css`, `functions.php`, `index.php`,
> `header.php`, `footer.php`, `README.md`) — ce qui rend le comportement du **thème** entièrement
> `VÉRIFIÉ_CODE`. Mais le cœur WordPress, la base de données et les plugins sont gérés **hors dépôt
> (FTP)** et ne sont donc **pas observables** ici : tout ce qui touche au site au-delà du thème est
> `INCONNU`. Voir la section **Hors périmètre** — elle est aussi importante que les domaines.
>
> Mode d'onboarding : **complet** (aucun artefact `.onboarding/` sur le distant — vérifié via
> `git ls-remote --heads origin onboarding/artifacts`, branche absente ; seule branche distante : `main`).

## Nature du projet

Ce dépôt ne contient **qu'un thème WordPress** (`Theme Name: Shift Pilot Theme`, `style.css:2`),
volontairement minimal : chrome de page (en-tête / pied), boucle de contenu standard et amorçage.
Il ne définit aucune entité, aucune route applicative ni aucune logique métier propre — il **consomme**
les API de rendu de WordPress (`have_posts()`, `the_content()`, `wp_head()`…). Le site réel (contenu,
e-commerce, formulaires, SEO) vit dans le **cœur WordPress, la base et les plugins gérés hors dépôt**
(`README.md:7-9`) : ce dépôt n'en est que la **couche de présentation versionnée**. La « nature du
projet » au sens métier n'est donc **pas déterminable depuis ce dépôt seul** — voir Incertitudes.

## Domaines

> **Cadrage de granularité.** Le seul artefact versionné est un thème minimal (6 fichiers). Les trois
> domaines ci-dessous ne sont **pas** trois pans applicatifs de même poids : ce sont **trois
> responsabilités techniques distinctes d'un même thème** (rendre, s'amorcer, charger ses assets).
> Il n'y a **aucun domaine métier porté par ce dépôt** — le contenu, le e-commerce, les formulaires et
> le SEO vivent hors dépôt (voir **Hors périmètre**). C'est volontaire : la règle de preuve interdit
> d'ériger en domaine ce que le code ne fait que **consommer** via les API natives de WordPress.

### Rendu des gabarits & structure du thème (`rendu-gabarits-theme`)
- **Catégorie** : technique
- **Priorité** : cœur
- **Confiance** : high
- **Description** : couche de présentation du site — assemblage du document HTML (doctype, `<head>`,
  chrome en-tête/pied) et boucle d'affichage du contenu. C'est la raison d'être du thème.
- **Entités** : aucune propre (le thème ne déclare aucun modèle) ; consomme les objets WordPress
  `post` via la boucle standard.
- **Routes / points d'entrée** : `index.php` sert de gabarit générique de la hiérarchie de templates
  WordPress (seul template présent — pas de `single.php`, `page.php`, `archive.php`, `404.php`).
- **Indices de rattachement** : appels d'API de template WordPress — `get_header()`/`get_footer()`
  (`index.php:1,12`), `wp_head()` (`header.php:5`), `wp_footer()` (`footer.php:2`),
  `bloginfo()`/`body_class()`/`language_attributes()`, chemins `*.php` à la racine du thème.
- **Types de workflows attendus** : rendu d'une page front (assemblage header → boucle → footer),
  affichage d'un article/page.
- **Preuves** : `index.php:1-13`, `header.php:1-9`, `footer.php:1-5` — tous `VÉRIFIÉ_CODE`.
- **Dépend de la base** : non. Aucun signal de builder maison (pas de renderer récursif, pas de champ
  `layout`/`blocks` personnalisé) → aucun artefact `CONTENU_*.md` à produire.
- **Dépendance externe (source du contenu — hors dépôt)** : la boucle `have_posts()`/`the_post()` puis
  `the_title()`/`the_content()` (`index.php:3-8`) et `add_theme_support('post-thumbnails')`
  (`functions.php:9`) montrent que ce domaine **consomme** du contenu WordPress natif (`post`/`page`).
  Ce contenu n'est **pas un domaine de ce dépôt** : son modèle et ses données sont définis par le cœur
  WordPress et sa base, **hors dépôt** (voir **Hors périmètre**). Statut de la nature/volume réels du
  contenu : `INCONNU`. C'est une **dépendance de rendu**, pas un domaine métier porté par ce code
  — la même règle de preuve que pour les plugins s'applique.

### Amorçage du thème & supports WordPress (`amorcage-theme`)
- **Catégorie** : technique
- **Priorité** : support
- **Confiance** : high
- **Description** : initialisation du thème au chargement — déclaration des fonctionnalités WordPress
  activées (balise `<title>` gérée par WP, images mises en avant).
- **Entités** : aucune.
- **Routes / points d'entrée** : hook `after_setup_theme` (`functions.php:7`).
- **Indices de rattachement** : `add_theme_support('title-tag')`, `add_theme_support('post-thumbnails')`,
  hook `after_setup_theme`.
- **Types de workflows attendus** : activation du thème, déclaration d'une nouvelle capacité de thème.
- **Preuves** : `functions.php:7-10` (`VÉRIFIÉ_CODE`).
- **Dépend de la base** : non.

### Dépendances & assets front (CSS / jQuery CDN) (`assets-front`)
- **Catégorie** : technique
- **Priorité** : support
- **Confiance** : high
- **Description** : chargement des ressources front — feuille de style du thème et **jQuery épinglé
  sur un CDN externe** (`code.jquery.com`, version `1.12.4`, figée). Point de vigilance opérationnel :
  dépendance à une version ancienne et à un domaine tiers, servie en front sur toutes les pages.
- **Entités** : aucune.
- **Routes / points d'entrée** : hook `wp_enqueue_scripts` (`functions.php:12`).
- **Indices de rattachement** : `wp_enqueue_style`, `wp_enqueue_script`, `wp_deregister_script('jquery')`,
  `get_stylesheet_uri()`, URL `code.jquery.com`, versions `1.0.2` (style) / `1.12.4` (jQuery).
- **Types de workflows attendus** : ajout/mise à jour d'un asset front, changement de version jQuery,
  cache-busting via le numéro de version de la feuille de style.
- **Preuves** : `functions.php:12-24` (`VÉRIFIÉ_CODE`) ; `style.css:6` (`Version: 1.0.2`). Le commit de
  tête `5d9b462` documente explicitement l'épinglage jQuery — c'est le seul geste technique tracé.
- **Dépend de la base** : non.

## Hors périmètre — géré hors dépôt (FTP), à NE PAS supposer couvert

Documenté explicitement à la demande du cadrage (issue CLA-155). Ces éléments **existent** (nommés dans
`README.md:7-9`) mais **aucun code ne les représente dans ce dépôt** : ils ne sont donc **pas** des
domaines de cette carte (règle de preuve — un domaine sans preuve concrète n'existe pas). Statut :
`HYPOTHÈSE`/`INCONNU`, non observable ici.

| Élément | Statut ici | Preuve du hors-périmètre |
|---|---|---|
| **Cœur WordPress** (moteur, hiérarchie de templates, admin) | INCONNU — fournit les API que le thème appelle, mais son code n'est pas versionné | `README.md:7-9`, `functions.php:4` |
| **Base de données** (contenu, config, utilisateurs) | INCONNU — aucun accès fourni à cette étape | `README.md:7` |
| **Contenu éditorial** (articles/pages `post`/`page`, titres, corps, images mises en avant) | INCONNU — modèle et données définis par le cœur WP hors dépôt ; le thème ne fait que le **consommer** via la boucle native (dépendance de rendu de `rendu-gabarits-theme`, **pas** un domaine du dépôt) | `index.php:3-8`, `functions.php:9` (consommation) ; `README.md:7` (source hors dépôt) |
| **Plugin Contact Form 7** (formulaires) | INCONNU — nommé, aucun code/hook dans le thème | `README.md:8` |
| **Plugin Yoast SEO** (référencement) | INCONNU — nommé, aucun code/hook dans le thème | `README.md:8` |
| **Plugin WooCommerce** (e-commerce) | INCONNU — nommé ; **ne pas conclure à un site e-commerce** : aucun `woocommerce.php`, aucun hook WC, aucune entité produit/commande dans le thème | `README.md:8` |

**Conséquence pour la suite de la chaîne** : les workflows/audits ne peuvent porter que sur le thème.
Tout workflow métier (commande WooCommerce, envoi d'un formulaire CF7, indexation SEO) nécessiterait
un accès aux plugins et à la base — à demander au board avant, pas à supposer.

## Incertitudes

- **Nature métier réelle du site** : WooCommerce est cité (`README.md:8`) mais **aucune trace** dans le
  thème. S'agit-il d'une boutique, d'un site vitrine, d'un blog ? Non déterminable depuis ce dépôt.
  → Question board : peut-on obtenir un accès (lecture) à l'installation WP ou à la base pour cartographier
  le site réel, ou l'onboarding reste-t-il volontairement borné au seul thème ?
- **Thème enfant ou complet ?** Aucun `Template:` dans l'en-tête `style.css` → thème **complet** (pas
  enfant). Mais `index.php` est le **seul** template : la hiérarchie WordPress se rabattra dessus pour
  tout (single, page, archive, 404). Est-ce l'état voulu du pilote, ou d'autres templates existent-ils
  côté FTP hors dépôt ? À confirmer.
- **jQuery 1.12.4 épinglé sur CDN externe** (`functions.php:19`) : choix assumé (commit `5d9b462`) ou
  dette à traiter ? Relève d'un futur audit sécurité/robustesse, pas de cette carte.
- **Accès base non fourni** à cette étape : la détection « contenu piloté par la base » n'a donc tourné
  que sur deux de ses trois signaux (entité étendue, code exécutable) — aucun trouvé. Le signal schéma
  n'a pas pu être évalué. Conclusion (aucun builder maison) tenue avec confiance `high` **pour ce dépôt**,
  mais ne préjuge rien de plugins hors dépôt.
