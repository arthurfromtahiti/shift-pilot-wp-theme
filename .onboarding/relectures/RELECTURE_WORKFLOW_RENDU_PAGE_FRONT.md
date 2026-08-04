# Relecture — WORKFLOW_RENDU_PAGE_FRONT.md

## Verdict global
Bon — les points bloquants soulevés au tour précédent ont été corrigés. Le document distingue désormais correctement le squelette de rendu prouvé par le thème et les comportements du cœur WordPress laissés en hypothèse.

## Problèmes bloquants
- Aucun.

## Problèmes mineurs
- La formule "le dépôt le confirme a contrario par l'absence de tout autre template" autour de la hiérarchie WordPress reste un peu trop forte dans `WORKFLOW_RENDU_PAGE_FRONT.md:18`. L'absence de templates spécialisés prouve le thème minimal, pas le fallback WordPress lui-même. Ce n'est plus bloquant car le comportement est bien marqué `HYPOTHÈSE`.

## Points vérifiés et corrects
- La chaîne de rendu effectivement visible dans le thème est exacte : `get_header()` (`index.php:1`), ouverture de `<main>` (`index.php:2`), boucle WordPress (`index.php:3-10`), fermeture de `<main>` (`index.php:11`), puis `get_footer()` (`index.php:12`).
- La description de `header.php` est correcte et prouvée par le fichier : doctype, `language_attributes()`, `bloginfo('charset')`, `wp_head()`, `body_class()` et affichage du nom du site via `bloginfo('name')` (`header.php:1-8`).
- La description de `footer.php` est correcte et prouvée : footer HTML statique `&copy; Prox-i`, `wp_footer()`, fermeture de `</body></html>` (`footer.php:1-4`).
- Le constat "pas de template spécialisé dans le dépôt versionné" est correct au vu de l'inventaire réel du dépôt.
- La confiance `medium` est désormais cohérente avec ce partage entre preuve locale et comportements WordPress externes.

## Recommandations de correction
- Si tu repasses dessus, remplace juste "confirmé a contrario" par une formulation plus sobre du type "suggéré par l'absence de templates spécialisés".
