# Relecture — ARCHITECTURE_AUDIT.md

## Verdict global
À corriger — l'audit architecture est globalement solide, mais sa synthèse relève encore un comportement runtime WordPress au rang de fait établi. Le défaut est localisé, mais il touche le résumé exécutif, donc un endroit à forte portée.

## Problèmes bloquants
- `ARCHITECTURE_AUDIT.md:11` écrit que l'absence de templates spécialisés "signifie que WordPress se rabat sur [index.php] pour toutes les URLs, y compris les pages inexistantes". Or le dépôt prouve seulement l'absence de `single.php`, `page.php`, `archive.php`, `404.php` et la présence d'un fallback `<p>Aucun contenu.</p>` dans [index.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/index.php:8). Le comportement exact de la hiérarchie de templates et du runtime 404 relève du cœur WordPress hors dépôt ; tu le qualifies correctement en `HYPOTHÈSE` plus bas à `ARCHITECTURE_AUDIT.md:17,43`, mais pas ici. Cette incohérence de statut dans le résumé reste bloquante.

## Problèmes mineurs
- `ARCHITECTURE_AUDIT.md:15` invoque des "best practices WordPress" et "l'ordre d'exécution attendu par le cœur WP" sans source amont. Ce n'est pas faux, mais c'est une connaissance externe implicite ; à minima, signale-la comme telle si tu la gardes.

## Points vérifiés et corrects
- Le dépôt ne contient bien qu'un template principal versionné côté rendu : [index.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/index.php:1), [header.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/header.php:1), [footer.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/footer.php:1).
- Les constats sur `post-thumbnails`, le copyright codé en dur et l'absence de menus/widgets sont correctement sourcés dans [functions.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/functions.php:8) et [footer.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/footer.php:1).
- La distinction locale/hors dépôt est correctement tenue dans les sections détaillées `ARCHITECTURE_AUDIT.md:17,39,43-44`.

## Recommandations de correction
- Harmoniser le résumé exécutif avec les constats détaillés : absence de templates spécialisés = `VÉRIFIÉ_CODE`, comportement de fallback WordPress = `HYPOTHÈSE`.
- Éviter dans le résumé toute phrase causale qui semble exécuter WordPress "de tête" sans preuve supplémentaire.
