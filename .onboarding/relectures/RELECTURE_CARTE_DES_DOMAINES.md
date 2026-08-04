# Relecture — CARTE_DES_DOMAINES.md

## Verdict global
À corriger — la carte est sérieuse sur le périmètre technique du thème et documente bien le hors-périmètre, mais elle laisse passer un domaine métier qui n'est pas réellement prouvé par le dépôt versionné. Le dépôt montre un thème WordPress minimal qui rend du contenu via les API natives de WordPress ; il ne montre pas, à lui seul, un domaine autonome `contenu-editorial-wp`.

## Problèmes bloquants
- `contenu-editorial-wp` est présenté comme un domaine du dépôt alors que la carte établit elle-même que le modèle, les données et la gouvernance de ce contenu vivent hors dépôt. Les seules preuves citées sont des appels de rendu natifs `have_posts()`, `the_post()`, `the_title()`, `the_content()` dans `index.php:3-8` et `add_theme_support('post-thumbnails')` dans `functions.php:9`. Cela prouve que le thème consomme la boucle WordPress, pas qu'il existe ici un domaine métier distinct porté par ce code. La section `Hors périmètre` applique déjà la bonne règle de preuve aux plugins et au cœur WP (`README.md:5-9`) ; le même standard doit être appliqué à ce pseudo-domaine.

## Problèmes mineurs
- La frontière entre `rendu-gabarits-theme`, `amorcage-theme` et `assets-front` tient, mais elle est à la limite basse de granularité pour un dépôt de 5 fichiers PHP/CSS. Les indices sont réels et disjoints (`index.php:1-12`, `header.php:1-7`, `footer.php:1-3`, `functions.php:7-24`), donc ce n'est pas bloquant ; en revanche la version corrigée gagnerait à expliciter que ces trois domaines sont des sous-ensembles techniques d'un même thème minimal, pour éviter qu'on les lise comme trois pans applicatifs de même poids.

## Points vérifiés et corrects
- Le cadrage de périmètre est correct et prouvé : seul le thème est versionné, le cœur WordPress, la base et les plugins sont hors dépôt selon `README.md:5-9` et `style.css:5`.
- Le domaine `rendu-gabarits-theme` est réel et bien sourcé : assemblage `get_header()`/`get_footer()` dans `index.php:1,12`, boucle et rendu HTML dans `index.php:3-8`, structure du document et hooks WordPress dans `header.php:1-7` et `footer.php:1-3`.
- Le domaine `amorcage-theme` est réel et bien sourcé : hook `after_setup_theme` puis `add_theme_support('title-tag')` et `add_theme_support('post-thumbnails')` dans `functions.php:7-10`.
- Le domaine `assets-front` est réel et bien sourcé : hook `wp_enqueue_scripts`, enregistrement de la feuille de style et remplacement de jQuery par une version CDN figée dans `functions.php:12-24`, cohérent avec `style.css:6`.
- Je n'ai pas trouvé d'omission fonctionnelle ou technique majeure dans le dépôt versionné : l'inventaire complet (`functions.php`, `index.php`, `header.php`, `footer.php`, `style.css`, `README.md`) retombe bien sur le thème minimal décrit par la carte.

## Recommandations de correction
- Retirer `contenu-editorial-wp` de la liste des domaines, ou le requalifier explicitement en dépendance externe / contrainte de rendu WordPress hors périmètre au lieu d'un domaine de la carte.
- Conserver le constat utile associé : le thème affiche du contenu WordPress natif via la boucle standard, mais déplacer ce constat soit dans `Nature du projet`, soit dans `Hors périmètre`, soit comme note de dépendance du domaine `rendu-gabarits-theme`.
- Si tu gardes 3 domaines techniques, ajoute une phrase de cadrage indiquant qu'ils découpent le seul thème versionné en trois responsabilités techniques distinctes, afin de verrouiller la granularité retenue.
