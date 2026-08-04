# WORKFLOW_INITIALISATION_THEME — Initialisation du thème au chargement WordPress

## Classification
- **Type** : `technical_flow`
- **Sous-type** : enregistrement de capacités WordPress (hook d'initialisation)
- **Visibilité** : `technical`
- **Acteur principal** : moteur WordPress (déclenche le hook `after_setup_theme`)
- **Acteurs** : moteur WordPress ; `functions.php` (callback d'initialisation)
- **Criticité** : Modérée — `add_theme_support('title-tag')` est cohérent avec l'absence de `<title>` manuel dans `header.php` (prouvé) ; `add_theme_support('post-thumbnails')` est déclaré mais aucun template du dépôt ne consomme d'image mise en avant.
- **Confiance** : high pour les deux appels visibles dans `functions.php` ; les effets précis du cœur WordPress (comportement de `title-tag`, de `post-thumbnails`) sont des dépendances externes au dépôt.
- **Justification** : `functions.php` lu intégralement (`VÉRIFIÉ_CODE`). La callback est une closure anonyme enregistrée directement sur le hook — aucune indirection. Deux appels, deux effets, entièrement visibles dans le code. `header.php` lu intégralement : aucun `<title>` manuel (`VÉRIFIÉ_CODE`). `index.php` lu intégralement : aucun appel à `the_post_thumbnail()` (`VÉRIFIÉ_CODE`).

## Objectif
Déclarer au moteur WordPress, dès le chargement du thème, les fonctionnalités WordPress que ce thème supporte : gestion de la balise `<title>` et images mises en avant (thumbnails). Le thème fait ces deux déclarations via l'API `add_theme_support` ; leurs effets concrets dans WordPress relèvent du cœur WP (hors dépôt).

## Acteurs
- **Moteur WordPress** — déclenche le hook `after_setup_theme` après avoir chargé le thème actif
- **`functions.php`** — porte la closure anonyme enregistrée comme callback du hook

## Points d'entrée
- Hook WordPress `after_setup_theme` (`functions.php:7`) — déclenché par WordPress à chaque initialisation de la session PHP, après chargement du thème actif (avant que les templates ne soient inclus)

## Étapes principales
1. **Déclenchement du hook** — WordPress appelle tous les callbacks enregistrés sur `after_setup_theme` (comportement du cœur WP, hors dépôt)
2. **`add_theme_support('title-tag')`** (`functions.php:8`) — le thème déclare ce support. Aucun `<title>` n'est écrit manuellement dans les templates (`header.php:1-7` — `VÉRIFIÉ_CODE`), ce qui est cohérent avec l'usage de ce support. Le comportement précis du cœur WordPress (injection du `<title>` via `wp_head()`) est une dépendance externe au dépôt.
3. **`add_theme_support('post-thumbnails')`** (`functions.php:9`) — le thème déclare ce support. Aucun template du dépôt ne consomme d'image mise en avant (`index.php` ne contient aucun appel à `the_post_thumbnail()` ni équivalent — `VÉRIFIÉ_CODE`). L'effet concret (association de miniatures, portée sur les types de post) relève du cœur WordPress (hors dépôt).

## Règles métier
- **Aucun `<title>` manuel dans les templates** : `header.php` ne contient aucune balise `<title>` manuelle (`VÉRIFIÉ_CODE`), ce qui est cohérent avec la déclaration de `add_theme_support('title-tag')` (`functions.php:8`). Le mécanisme précis (WordPress prend en charge la balise `<title>`) est une dépendance externe au dépôt, non sourcée ici.
- **`post-thumbnails` déclaré mais non consommé par les templates** : `add_theme_support('post-thumbnails')` est appelé sans arguments (`functions.php:9`) ; la portée sur les types de post relève du cœur WordPress. Aucun template du dépôt ne consomme d'image mise en avant (`VÉRIFIÉ_CODE`).
- **Pas de menu de navigation, pas de sidebar, pas de zone de widget** : aucun `register_nav_menus()`, `register_sidebar()`, `add_image_size()` ni autre déclaration dans ce hook ou ailleurs dans `functions.php` (`VÉRIFIÉ_CODE` — fichier lu intégralement).

## Données
- Aucune donnée propre au thème n'est lue ou écrite par ce workflow. Les effets s'appliquent à la configuration globale de la session WordPress (en mémoire).

## Intégrations
- Aucune intégration externe. Ce workflow se limite à des appels d'API WordPress natifs (`add_action`, `add_theme_support`).

## Risques
- **Version WordPress requise non vérifiable depuis ce dépôt** : `style.css:6` indique `Requires at least: 5.9`. Les deux appels `add_theme_support` sont prouvés (`functions.php:7-9`). La version WP réellement installée est `INCONNU` (WP hors dépôt), tout comme la date d'introduction de ces API dans WordPress — informations non sourcées dans ce dépôt. Statut : `HYPOTHÈSE` sur la conformité réelle.
- **Capacités déclarées minimales** : avec seulement `title-tag` et `post-thumbnails`, des fonctionnalités supposées par certains plugins actifs pourraient manquer (non vérifiable — plugins hors dépôt). Impact : `HYPOTHÈSE`.

## Questions ouvertes
- Le thème ne déclare aucun menu de navigation (`register_nav_menus()`). Est-ce délibéré pour le pilote, ou un menu de navigation est-il prévu à terme ?
- Aucun `add_image_size()` n'est présent : les dimensions des images mises en avant sont celles des valeurs par défaut WordPress. Est-ce suffisant pour l'usage prévu ?
- La version WordPress réellement déployée est-elle ≥ 5.9 comme l'indique `style.css:6` (`Requires at least: 5.9`) ? Non vérifiable depuis ce dépôt.

## Preuves
- `functions.php:7-10` — lu intégralement : `VÉRIFIÉ_CODE`
- `header.php:1-9` — absence de `<title>` manuel confirmée par lecture complète : `VÉRIFIÉ_CODE`
- `functions.php` — absence de `register_nav_menus`, `register_sidebar`, `add_image_size` confirmée par lecture complète : `VÉRIFIÉ_CODE`
