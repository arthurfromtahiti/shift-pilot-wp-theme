# Relecture — WORKFLOW_INITIALISATION_THEME.md

## Verdict global
À corriger — le document revient bien au code du thème pour l'essentiel, mais il reste une règle de preuve non tenue dans la section des risques. Le workflow local est correctement lu ; une ou deux affirmations externes doivent encore être nettoyées.

## Problèmes bloquants
- Le premier risque énonce comme des faits historiques externes non sourcés que `add_theme_support('title-tag')` "existe depuis WP 4.1" et `post-thumbnails` "depuis WP 2.9", puis conclut à une compatibilité "sans risque" dans `WORKFLOW_INITIALISATION_THEME.md:40`. Le dépôt prouve seulement `Requires at least: 5.9` dans `style.css:7` et les deux appels `add_theme_support` dans `functions.php:7-9`. L'historique d'introduction des APIs WordPress n'est pas prouvé ici.

## Problèmes mineurs
- Le bloc de risques contient deux puces quasi redondantes sur des "capacités déclarées minimales" dans `WORKFLOW_INITIALISATION_THEME.md:41-42`. Ça nuit à la netteté sans ajouter de preuve.

## Points vérifiés et corrects
- Le point d'entrée local est correctement identifié : hook `after_setup_theme` avec une closure anonyme dans `functions.php:7-10`.
- L'absence de `<title>` manuel dans le template est correctement prouvée par `header.php:1-7`.
- Le support `post-thumbnails` est maintenant correctement borné : `functions.php:9` le déclare, tandis que `index.php:1-12` ne consomme aucune miniature. Le document ne le présente plus comme critique pour toutes les pages.
- Le constat "pas de menu de navigation, pas de sidebar, pas de zone de widget" est correctement ancré dans l'absence de `register_nav_menus`, `register_sidebar` et `add_image_size` dans `functions.php`.
- Le périmètre "aucune intégration externe" est cohérent avec les fichiers du dépôt.

## Recommandations de correction
- Retirer les numéros de versions WordPress historiques ou les qualifier explicitement comme informations externes non vérifiées dans ce dépôt.
- Conserver l'analyse centrée sur ce que montre le thème : deux déclarations de support, absence de `<title>` manuel, absence de consommation locale des miniatures.
- Fusionner les deux risques redondants sur les capacités minimales.
