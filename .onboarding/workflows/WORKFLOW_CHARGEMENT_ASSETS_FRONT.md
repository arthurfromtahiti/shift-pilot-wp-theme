# WORKFLOW_CHARGEMENT_ASSETS_FRONT — Chargement des assets frontend (CSS + jQuery CDN)

## Classification
- **Type** : `technical_flow`
- **Sous-type** : enregistrement d'assets WordPress (enqueue)
- **Visibilité** : `technical`
- **Acteur principal** : moteur WordPress (déclenche le hook `wp_enqueue_scripts`)
- **Acteurs** : moteur WordPress ; `functions.php` (callback d'enqueue) ; CDN externe (URL : `https://code.jquery.com`)
- **Criticité** : Haute — la feuille de style conditionne le rendu visuel ; le désenregistrement et remplacement du jQuery bundlé est un choix délibéré documenté (`commit 5d9b462`). L'impact sur les scripts déclarant une dépendance sur le handle `'jquery'` dépend du cœur WordPress (hors dépôt).
- **Confiance** : high pour la séquence locale dans `functions.php` ; les effets de rendu effectifs dans le navigateur (émission des balises HTML) dépendent du cœur WordPress, hors dépôt.
- **Justification** : `functions.php:12-24` lu intégralement (`VÉRIFIÉ_CODE`). La séquence est linéaire et entièrement visible. Le choix de remplacer jQuery est documenté dans le message du commit de tête `5d9b462`. `wp_head()` (`header.php:5`) et `wp_footer()` (`footer.php:2`) sont confirmés comme points d'injection standard WordPress ; l'émission effective des balises `<link>` et `<script>` relève du cœur WP (hors dépôt).

## Objectif
Enregistrer et mettre en file d'attente les deux ressources que le thème déclare : sa propre feuille de style et une version figée de jQuery chargée depuis un CDN externe. Ce workflow remplace délibérément le jQuery bundlé de WordPress par une version CDN épinglée. Le périmètre de déclenchement (pages frontend, non-admin) relève du cœur WordPress (hors dépôt).

## Acteurs
- **Moteur WordPress** — déclenche le hook `wp_enqueue_scripts` (comportement du cœur WordPress, hors dépôt)
- **`functions.php`** — porte la closure anonyme enregistrée comme callback du hook (`functions.php:12`)
- **CDN externe** — URL `https://code.jquery.com/jquery-1.12.4.min.js` chargée par le navigateur du visiteur

## Points d'entrée
- Hook WordPress `wp_enqueue_scripts` (`functions.php:12`) — déclenché par le cœur WordPress lors du rendu des pages frontend (non-admin) ; le périmètre exact du déclenchement relève du cœur WP (hors dépôt).

## Étapes principales
1. **Déclenchement du hook** — WordPress appelle le callback enregistré sur `wp_enqueue_scripts`
2. **Enqueue de la feuille de style** (`functions.php:13`) :
   - Handle : `'shift-pilot-style'`
   - URL : `get_stylesheet_uri()` → pointe vers `style.css` dans le répertoire du thème
   - Version : `'1.0.2'` (cache-buster — correspond à `style.css:6` `Version: 1.0.2`)
   - Ajoutée à la file d'attente de styles WordPress ; `wp_head()` (`header.php:5`) est le point d'injection prévu — l'émission effective de la balise `<link>` relève du cœur WP (hors dépôt).
3. **Désinscription du jQuery bundlé** (`functions.php:16`) :
   - `wp_deregister_script('jquery')` — supprime le jQuery livré avec WordPress de la file d'attente
4. **Enqueue de jQuery CDN** (`functions.php:17-23`) :
   - Handle : `'jquery'` (même handle que le jQuery bundlé WordPress, réutilisé délibérément — `commit 5d9b462`)
   - URL : `'https://code.jquery.com/jquery-1.12.4.min.js'` — CDN externe, HTTPS
   - Version : `'1.12.4'`
   - Dépendances : `[]` (aucune)
   - Position : `true` → chargé **en footer** (`wp_footer()`)
5. **Points d'injection dans le HTML** : `wp_head()` est appelé en `header.php:5` et `wp_footer()` en `footer.php:2` — ce sont les points d'injection standard WordPress pour les assets enregistrés via l'API d'enqueue. L'émission effective des balises `<link>` et `<script>` relève du cœur WordPress (hors dépôt).

## Règles métier
- **Remplacement du jQuery WordPress par jQuery CDN** : le jQuery bundlé de WordPress est explicitement supprimé (`wp_deregister_script('jquery')`) avant d'enregistrer la version CDN sous le même handle `'jquery'` (`functions.php:16-23`). Choix documenté dans le commit `5d9b462` (message : « jQuery CDN épinglé 1.12.4 »). Que les scripts déclarant une dépendance sur le handle `'jquery'` reçoivent effectivement cette version CDN relève de la résolution de dépendances du cœur WordPress (`HYPOTHÈSE` — hors dépôt).
- **Version jQuery figée** : la version `1.12.4` est codée en dur (`functions.php:19`). Toute montée de version exige une modification manuelle du code.
- **jQuery chargé en footer** : le paramètre `$in_footer = true` (`functions.php:22`) fait que jQuery est demandé en footer via le handle `'jquery'`, injecté via `wp_footer()` et non via `wp_head()`. Cela réduit le blocage du rendu. La conséquence sur d'éventuels scripts inline appelant `jQuery` avant `wp_footer()` est une `HYPOTHÈSE` — le dépôt ne prouve ni l'absence d'un autre chargement de jQuery, ni l'exécution réelle d'un tel script (voir risque "jQuery en footer uniquement" ci-dessous).
- **Cache-busting manuel** : le numéro de version `'1.0.2'` dans `wp_enqueue_style` (`functions.php:13`) est transmis au cœur WordPress comme paramètre de version ; WordPress l'ajoute sous forme de suffixe d'URL de type `?ver=…` (comportement du cœur WP, hors dépôt). Toute modification de `style.css` sans mise à jour de ce numéro peut ne pas être visible pour les visiteurs dont le navigateur ou un CDN intermédiaire a mis en cache l'ancienne version.

## Données
- Aucune donnée propre au thème n'est lue ou écrite. Les effets sont l'ajout d'entrées dans les files d'attente WordPress (CSS et scripts), résolues en balises HTML lors du rendu.

## Intégrations
- **CDN externe** : URL `https://code.jquery.com/jquery-1.12.4.min.js` chargée par le navigateur du visiteur. Disponibilité du CDN non garantie par le thème.

## Risques
- **Dépendance CDN externe sans fallback** : si `code.jquery.com` est inaccessible (panne CDN, filtrage réseau, pare-feu), jQuery ne se charge pas côté visiteur. Tout JavaScript dépendant de jQuery (WooCommerce, Contact Form 7, scripts de plugins — hors dépôt) échouera silencieusement. Aucun fallback local défini dans le thème. Scénario : `VÉRIFIÉ_CODE` (`functions.php:17-23`) ; impact sur les plugins : `HYPOTHÈSE` (plugins hors dépôt).
- **jQuery en version figée** : la version `1.12.4` est codée en dur (`functions.php:19`) avec aucune mise à jour automatique possible. Le statut de maintenance de cette version (EOL, disponibilité de patches) est une information externe non sourcée dans ce dépôt (`INCONNU`). Le constat prouvé est que la version est fixe et ne peut évoluer que par modification manuelle du code. Scénario : `VÉRIFIÉ_CODE` (`functions.php:19`).
- **`wp_deregister_script('jquery')` avant que d'autres callbacks ne s'exécutent** : si un plugin enregistré avec une priorité inférieure à la valeur par défaut (10) sur `wp_enqueue_scripts` avait déjà enqueued jQuery, la désinscription peut l'en priver. Impact : `HYPOTHÈSE` (l'ordre d'exécution des callbacks de plugins est inconnu, plugins hors dépôt).
- **Cache-buster non automatique** : un fichier `style.css` modifié sans mise à jour du numéro `'1.0.2'` dans `functions.php:13` peut rester en cache chez les visiteurs et dans des CDN intermédiaires éventuels. Impact conditionnel : selon la configuration du navigateur ou d'un CDN intermédiaire, les modifications CSS risquent de ne pas être visibles en production avant l'expiration du cache.
- **jQuery en footer uniquement** : le thème prouve `in_footer = true` (`functions.php:22`) et `wp_footer()` dans `footer.php:2`. Tout script inline appelant `jQuery` avant `wp_footer()` (dans un template ou un plugin) pourrait provoquer une erreur `jQuery is not defined` — mais le thème ne peut pas prouver l'absence de tout autre chargement de jQuery ni l'exécution réelle d'un tel script inline. Scénario : `HYPOTHÈSE` (impact concret hors dépôt).

## Questions ouvertes
- Les plugins actifs (WooCommerce, Contact Form 7, Yoast SEO — cités dans `README.md:8`) sont-ils compatibles avec jQuery `1.12.4` ? Statut : `INCONNU` (plugins hors dépôt).
- Le site dispose-t-il d'un CDN intermédiaire (Cloudflare, etc.) qui peut également mettre en cache `style.css` ? Si oui, le cache-busting par `?ver=` peut être insuffisant selon la configuration du CDN. Statut : `INCONNU`.
- La mise à jour de jQuery (vers `3.x`) est-elle prévue, ou `1.12.4` est-il le choix de long terme pour ce pilote ?

## Preuves
- `functions.php:12-24` — lu intégralement : `VÉRIFIÉ_CODE`
- `header.php:5` — `wp_head()` confirmé comme point d'injection CSS : `VÉRIFIÉ_CODE`
- `footer.php:2` — `wp_footer()` confirmé comme point d'injection JS : `VÉRIFIÉ_CODE`
- `style.css:6` — `Version: 1.0.2` confirmé comme valeur du cache-buster : `VÉRIFIÉ_CODE`
- Commit `5d9b462` — message « jQuery CDN épinglé 1.12.4 » documenté dans `git log` : `VÉRIFIÉ_CODE`
