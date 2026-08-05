# WORKFLOW_CHARGEMENT_ASSETS_FRONT — Chargement des assets frontend (CSS + Slider)

## Classification
- **Type** : `technical_flow`
- **Sous-type** : enregistrement d'assets WordPress (enqueue)
- **Visibilité** : `technical`
- **Acteur principal** : moteur WordPress (déclenche le hook `wp_enqueue_scripts`)
- **Acteurs** : moteur WordPress ; `functions.php` (callback d'enqueue)
- **Criticité** : Haute — la feuille de style conditionne le rendu visuel ; le slider conditionne les interactions frontend.
- **Confiance** : high pour la séquence locale dans `functions.php` ; les effets de rendu effectifs dans le navigateur (émission des balises HTML) dépendent du cœur WordPress, hors dépôt.
- **Justification** : `functions.php:12-21` lu intégralement (`VÉRIFIÉ_CODE`). La séquence est linéaire et entièrement visible. `wp_head()` (`header.php:6`) et `wp_footer()` (`footer.php:2`) sont confirmés comme points d'injection standard WordPress ; l'émission effective des balises `<link>` et `<script>` relève du cœur WP (hors dépôt).

## Objectif
Enregistrer et mettre en file d'attente les deux ressources que le thème déclare : sa propre feuille de style et le script slider vanilla JS chargé en footer. Le périmètre de déclenchement (pages frontend, non-admin) relève du cœur WordPress (hors dépôt).

## Acteurs
- **Moteur WordPress** — déclenche le hook `wp_enqueue_scripts` (comportement du cœur WordPress, hors dépôt)
- **`functions.php`** — porte la closure anonyme enregistrée comme callback du hook (`functions.php:12`)

## Points d'entrée
- Hook WordPress `wp_enqueue_scripts` (`functions.php:12`) — déclenché par le cœur WordPress lors du rendu des pages frontend (non-admin) ; le périmètre exact du déclenchement relève du cœur WP (hors dépôt).

## Étapes principales
1. **Déclenchement du hook** — WordPress appelle le callback enregistré sur `wp_enqueue_scripts`
2. **Enqueue de la feuille de style** (`functions.php:13`) :
   - Handle : `'shift-pilot-style'`
   - URL : `get_stylesheet_uri()` → pointe vers `style.css` dans le répertoire du thème
   - Version : `'1.0.2'` (cache-buster — correspond à `style.css:6` `Version: 1.0.2`)
   - Ajoutée à la file d'attente de styles WordPress ; `wp_head()` (`header.php:6`) est le point d'injection prévu — l'émission effective de la balise `<link>` relève du cœur WP (hors dépôt).
3. **Enqueue du slider** (`functions.php:14-20`) :
   - Handle : `'shift-pilot-slider'`
   - URL : `get_template_directory_uri() . '/assets/slider.js'` — script local au thème
   - Version : `'1.0.0'`
   - Dépendances : `[]` (aucune — le slider est vanilla JS pur, vérifié par lecture de `assets/slider.js` : `document.addEventListener`, `querySelectorAll`, `classList` — aucune dépendance jQuery)
   - Position : `true` → chargé **en footer** (`wp_footer()`) — non bloquant pour le parsing HTML
4. **Points d'injection dans le HTML** : `wp_head()` est appelé en `header.php:6` et `wp_footer()` en `footer.php:2` — ce sont les points d'injection standard WordPress pour les assets enregistrés via l'API d'enqueue. L'émission effective des balises `<link>` et `<script>` relève du cœur WordPress (hors dépôt).

## Règles métier
- **Slider chargé en footer, sans dépendance jQuery** : `shift-pilot-slider` est déclaré avec `in_footer = true` et `deps = []`. Le script `assets/slider.js` est vanilla JS pur — aucune dépendance jQuery requise ou déclarée (`VÉRIFIÉ_CODE`).
- **Cache-busting manuel** : le numéro de version `'1.0.2'` dans `wp_enqueue_style` (`functions.php:13`) est transmis au cœur WordPress comme paramètre de version ; WordPress l'ajoute sous forme de suffixe d'URL de type `?ver=…` (comportement du cœur WP, hors dépôt). Toute modification de `style.css` sans mise à jour de ce numéro peut ne pas être visible pour les visiteurs dont le navigateur ou un CDN intermédiaire a mis en cache l'ancienne version.

## Données
- Aucune donnée propre au thème n'est lue ou écrite. Les effets sont l'ajout d'entrées dans les files d'attente WordPress (CSS et scripts), résolues en balises HTML lors du rendu.

## Risques
- **Cache-buster non automatique** : un fichier `style.css` modifié sans mise à jour du numéro `'1.0.2'` dans `functions.php:13` peut rester en cache chez les visiteurs et dans des CDN intermédiaires éventuels. Impact conditionnel : selon la configuration du navigateur ou d'un CDN intermédiaire, les modifications CSS risquent de ne pas être visibles en production avant l'expiration du cache.
- **Slider.js en footer uniquement** : tout code inline appelant les fonctionnalités du slider avant `wp_footer()` (dans un template ou un plugin) pourrait ne pas trouver le script initialisé. Scénario : `HYPOTHÈSE` (le thème ne contient aucun tel appel inline — `VÉRIFIÉ_CODE`).

## Questions ouvertes
- Le site dispose-t-il d'un CDN intermédiaire (Cloudflare, etc.) qui peut également mettre en cache `style.css` ? Si oui, le cache-busting par `?ver=` peut être insuffisant selon la configuration du CDN. Statut : `INCONNU`.

## Preuves
- `functions.php:12-21` — lu intégralement : `VÉRIFIÉ_CODE`
- `header.php:6` — `wp_head()` confirmé comme point d'injection CSS : `VÉRIFIÉ_CODE`
- `footer.php:2` — `wp_footer()` confirmé comme point d'injection JS : `VÉRIFIÉ_CODE`
- `style.css:6` — `Version: 1.0.2` confirmé comme valeur du cache-buster : `VÉRIFIÉ_CODE`
- `assets/slider.js` — vanilla JS pur, aucune dépendance jQuery : `VÉRIFIÉ_CODE`
