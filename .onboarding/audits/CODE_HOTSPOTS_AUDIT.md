# Points chauds du code — Audit

> Confiance : high — tous les fichiers ont été lus intégralement. Le dépôt est suffisamment petit (~70 lignes effectives) pour qu'un inventaire exhaustif soit possible sans risque d'omission.

## Compréhension globale

Un thème WordPress de 5 fichiers (~70 lignes) n'a pas de points chauds au sens habituel (fichiers de 500 lignes, couplages forts, logique métier concentrée). Il existe néanmoins une ligne unique dans `functions.php` qui concentre le risque d'intégration le plus élevé du dépôt : `wp_deregister_script('jquery')` à la ligne 16. C'est le seul geste qui agit globalement sur l'environnement WordPress (suppression d'une ressource partagée par tous les plugins), avec des effets qui dépendent de l'ordre d'exécution des callbacks tiers.

## Résumé exécutif

Par sa taille, ce dépôt n'a pas de hotspot structurel (pas de classe God, pas de fichier 300+ lignes, pas de couplage circulaire). `functions.php` est le seul fichier avec de la logique (24 lignes) et est donc le point d'entrée naturel pour tout diagnostic. La ligne `wp_deregister_script('jquery')` (`functions.php:16`) est identifiée comme le point de risque unitaire le plus élevé : elle modifie l'état global de la file d'attente de scripts WordPress de manière irréversible dans la même requête, avant que les callbacks des plugins ne soient tous exécutés. `footer.php:1` représente un second point chaud de maintenabilité (mention codée en dur). Les templates (`index.php`, `header.php`, `footer.php`) sont trop courts pour constituer des hotspots.

## Constats détaillés

**`functions.php` — seul fichier avec logique, 24 lignes.** `functions.php` porte les deux hooks du thème et est le seul fichier non-template du dépôt (`VÉRIFIÉ_CODE`). Son rôle est central : toute modification de comportement du thème (ajout d'un support, changement d'asset, nouveau hook) passe par lui. Sa compacité le rend lisible, mais aussi fragile : une modification maladroite (mauvaise priorité de hook, ordre de désinscription/inscription incorrect) peut avoir des effets globaux sur tous les scripts du site.

**`functions.php:16` — `wp_deregister_script('jquery')` — hotspot prioritaire.** Cette ligne est le seul geste du thème qui agit sur l'état global partagé de WordPress (la file d'attente de scripts). Elle retire le jQuery bundlé WP (`VÉRIFIÉ_CODE` : `functions.php:16`), puis le réintroduit sous le même handle depuis un CDN externe (`functions.php:17-23`). Le risque : si la priorité par défaut (10) du callback du thème est identique à celle d'un plugin qui aurait déjà enqueued jQuery ou déclaré une dépendance sur ce handle, l'ordre d'exécution des callbacks détermine si le remplacement se fait avant ou après l'enqueue du plugin. Ce comportement dépend de l'ordre d'enregistrement des callbacks, qui dépend de l'ordre de chargement des plugins (hors dépôt) : `HYPOTHÈSE`. Dans un diagnostic de bug JavaScript, cette ligne sera toujours examinée en premier.

**`functions.php:13` — version du style codée en dur — hotspot de maintenabilité.** `wp_enqueue_style('shift-pilot-style', get_stylesheet_uri(), [], '1.0.2')` utilise `'1.0.2'` comme cache-buster (`VÉRIFIÉ_CODE`). Ce numéro doit être mis à jour manuellement à chaque modification de `style.css`, sous peine que les visiteurs reçoivent une version en cache. C'est un pattern courant en WordPress, mais il exige une discipline de versionnement qui n'est pas automatisée dans ce dépôt.

**`footer.php:1` — copyright en dur — hotspot de maintenabilité.** `<footer class="site-footer"><p>&copy; Prox-i</p></footer>` (`VÉRIFIÉ_CODE`) est la seule donnée éditoriale figée dans un template. Toute modification requiert une PR, là où `bloginfo()` ou une option WP permettrait de la piloter depuis l'admin.

**`index.php:3-10` — boucle WordPress unique pour tous les contenus.** La boucle (`have_posts()` → `the_post()` → `the_title()` → `the_content()`) est standard et correcte (`VÉRIFIÉ_CODE`). Elle n'est pas un hotspot de risque, mais elle est le point de rendu universel : toute évolution du thème (ajout d'un type de post, d'une sidebar, d'un bloc Gutenberg personnalisé) devra modifier ou entourer ce code.

**Aucun fichier volumineux, aucun couplage fort.** L'analyse de taille : `functions.php` (24 lignes), `index.php` (13 lignes), `header.php` (9 lignes), `footer.php` (5 lignes), `style.css` (20 lignes) (`VÉRIFIÉ_CODE`). Aucun fichier ne dépasse 25 lignes. Il n'y a pas de couplage entre fichiers au-delà des includes WordPress standards (`get_header()`, `get_footer()`).

## Forces

- **Aucun fichier « god »** : le découpage entre hooks (`functions.php`) et templates (`index.php`, `header.php`, `footer.php`) est propre. Chaque fichier a une responsabilité unique (`VÉRIFIÉ_CODE`).
- **Complexité cyclomatique quasi nulle** : la seule branche conditionnelle du thème est `if (have_posts())` dans `index.php:3`. Aucune logique imbriquée, aucun switch/case, aucune récursion.
- **Couplage minimal entre fichiers** : `index.php` appelle `get_header()`/`get_footer()` — ce sont des fonctions WordPress qui gèrent l'inclusion ; il n'y a pas de dépendance directe entre les fichiers du thème.

## Dettes techniques

- **Cache-buster manuel** : `functions.php:13` requiert une mise à jour manuelle de `'1.0.2'` à chaque modification de `style.css`. Sans automatisation (script de build, hook git pre-commit), ce numéro sera oublié.
- **Copyright hardcodé** : `footer.php:1` concentre en une ligne une donnée qui devrait être configurable. Toute modification passe par une PR au lieu de l'admin WP.

## Zones critiques

- **`functions.php:16`** — `wp_deregister_script('jquery')` : point de risque d'intégration maximal pour ce dépôt. Un senior diagnostiquant un bug JavaScript commencerait ici. L'effet est global, irréversible dans la requête, et dépend de l'ordre d'exécution des plugins. La preuve est `VÉRIFIÉ_CODE`, l'impact concret reste `HYPOTHÈSE` (plugins hors dépôt).
- **`functions.php:13`** — numéro de version du style : point de risque opérationnel récurrent. Sans processus garantissant la mise à jour de ce numéro, des bugs de cache CSS apparaîtront en production sans reproductibilité évidente.

## Risques

- **Bug JavaScript difficile à reproduire** : un conflit de priorité sur `wp_enqueue_scripts` entre le thème et un plugin pourrait produire un comportement différent selon l'ordre de chargement des plugins — non reproductible en isolation, visible seulement avec l'ensemble du site actif. Preuve locale : `functions.php:16` (`VÉRIFIÉ_CODE`) ; impact : `HYPOTHÈSE`.
- **Cache CSS silencieux** : si `style.css` est modifié sans mettre à jour `'1.0.2'` dans `functions.php:13`, les visiteurs sur des navigateurs avec cache agressif ou derrière un CDN intermédiaire verront l'ancienne version indéfiniment. Preuve : `functions.php:13`, `style.css:6` (`VÉRIFIÉ_CODE`).

## Recommandations priorisées

1. **Documenter explicitement la convention de versionnement** — ajouter une note dans `README.md` ou un commentaire dans `functions.php` rappelant que `'1.0.2'` doit être incrémenté à chaque modification de `style.css`. Alternative : automatiser via un script de déploiement ou la constante `SCRIPT_DEBUG` de WordPress.
2. **Surveiller l'ordre de priorité du hook `wp_enqueue_scripts`** — lors de l'ajout de plugins actifs, vérifier si leurs callbacks d'enqueue ont une priorité ≤ 10 et si leur dépendance à `jquery` est déclarée. Si un conflit est détecté, ajuster la priorité du callback du thème (`functions.php:12`) à une valeur plus basse (ex. 5) pour garantir l'exécution avant les plugins.
3. **Extraire le copyright vers une option WP** — remplacer `&copy; Prox-i` dans `footer.php:1` par `<?php echo get_bloginfo('name'); ?>` ou `<?php bloginfo('name'); ?>` si c'est l'entité propriétaire, ou par `get_option('shift_pilot_copyright')` si la mention doit être personnalisable indépendamment du nom du site.

## Questions ouvertes

- La priorité 10 (défaut WordPress) sur `wp_enqueue_scripts` (`functions.php:12`) a-t-elle provoqué des conflits avec les plugins en production ? Aucun historique d'incident visible depuis ce dépôt.
- Y a-t-il un processus de déploiement qui pourrait automatiser l'incrémentation de la version du style ?
