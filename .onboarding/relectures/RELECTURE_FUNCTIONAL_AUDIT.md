# Relecture — FUNCTIONAL_AUDIT.md

## Verdict global
À corriger — le cœur des constats est cohérent avec le dépôt, mais deux formulations restent plus affirmatives que la preuve disponible. L'audit mélange encore un jugement produit ("bloquant en production") et une généralité WooCommerce avec des constats de code.

## Problèmes bloquants
- `FUNCTIONAL_AUDIT.md:11` affirme que quatre manques "seront bloquants" en production réelle. Le dépôt prouve l'absence de `404.php`, de menu, de rendu de thumbnails et la présence d'un copyright codé en dur, mais pas leur criticité métier en production. Sans observation du site réel ni décision board, cette sévérité doit être reformulée en risque ou en `HYPOTHÈSE`, pas en fait.

## Problèmes mineurs
- `FUNCTIONAL_AUDIT.md:25` énonce que WooCommerce "attend habituellement un fichier `woocommerce.php` ou des hooks dédiés". C'est une connaissance générale externe, pas une preuve tirée du dépôt. Elle peut rester comme hypothèse d'intégration ou être sourcée, mais pas rester implicite.
- `FUNCTIONAL_AUDIT.md:17,48` sont désormais mieux qualifiés sur le cas 404. Rien de bloquant ici, mais veille à conserver partout la même discipline : absence de `404.php` = `VÉRIFIÉ_CODE`, comportement runtime exact = `HYPOTHÈSE`.

## Points vérifiés et corrects
- Le fallback `<p>Aucun contenu.</p>` est bien prouvé par [index.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/index.php:8).
- L'absence de menu déclaré est bien prouvée par [functions.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/functions.php:7) et l'en-tête minimal par [header.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/header.php:8).
- Le périmètre hors dépôt est rappelé correctement à partir de [README.md](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/README.md:7).

## Recommandations de correction
- Requalifier la phrase de synthèse sur les "bloquants" en formulation de risque conditionnel ou d'impact probable.
- Sourcer ou requalifier les généralisations WooCommerce qui ne sont pas démontrables depuis le seul thème versionné.
