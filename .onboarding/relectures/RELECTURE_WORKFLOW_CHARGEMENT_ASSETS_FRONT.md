# Relecture — WORKFLOW_CHARGEMENT_ASSETS_FRONT.md

## Verdict global
À corriger — la séparation entre code du thème et comportement du cœur WordPress est nettement meilleure, mais il reste encore des affirmations externes formulées comme des constats. Le fond du workflow est juste ; la discipline de preuve n'est pas encore complètement tenue.

## Problèmes bloquants
- La question ouverte sur les plugins actifs continue d'embarquer une assertion temporelle externe non sourcée : "leur compatibilité avec 1.x n'est plus garantie par leurs mainteneurs depuis plusieurs années" dans `WORKFLOW_CHARGEMENT_ASSETS_FRONT.md:61`. Le dépôt prouve seulement que `README.md:7-9` cite `Contact Form 7`, `Yoast SEO` et `WooCommerce` hors dépôt, et que `functions.php:16-23` force `jquery-1.12.4.min.js`. L'état de compatibilité actuel ou historique de ces plugins relève d'une source amont absente ici.
- Le risque "`jQuery` chargé en footer" ferme encore un effet navigateur non prouvé localement : "tout script inline écrit avant `wp_footer()` ... provoquera une erreur `jQuery is not defined`" dans `WORKFLOW_CHARGEMENT_ASSETS_FRONT.md:58`. Le thème prouve bien `wp_enqueue_script(..., true)` (`functions.php:17-22`) et `wp_footer()` (`footer.php:2`), mais pas l'absence d'un autre chargement de jQuery ni l'exécution réelle d'un script inline donné. Cette phrase doit être abaissée en hypothèse ou reformulée sur le seul ordre d'enqueue visible.

## Problèmes mineurs
- La règle "cache-busting manuel" décrit le suffixe exact `?ver=1.0.2` comme un effet certain de WordPress dans `WORKFLOW_CHARGEMENT_ASSETS_FRONT.md:44`. `functions.php:13` prouve la version passée à `wp_enqueue_style`, mais la forme exacte de l'URL générée relève du cœur WordPress.

## Points vérifiés et corrects
- La callback de `wp_enqueue_scripts` et sa séquence locale sont exactes : enqueue de `shift-pilot-style` via `get_stylesheet_uri()` en version `1.0.2`, désinscription du handle `jquery`, puis ré-enregistrement vers `https://code.jquery.com/jquery-1.12.4.min.js` avec version `1.12.4` et chargement en footer (`functions.php:12-24`).
- Le lien entre la version du style enqueue et l'en-tête de `style.css` est bien prouvé : `Version: 1.0.2` dans `style.css:6`, cohérente avec `functions.php:13`.
- La preuve commit est correctement citée : `git log --oneline -n 5` montre bien `5d9b462 Seed thème WP pilote: thème seul versionné, jQuery CDN épinglé 1.12.4`.
- Les effets relevant du cœur WordPress sont globalement mieux bornés qu'à la version précédente : le document distingue maintenant correctement plusieurs dépendances externes au dépôt autour de `wp_head()`, `wp_footer()` et de la résolution des dépendances.

## Recommandations de correction
- Retirer la phrase sur la compatibilité "plus garantie depuis plusieurs années" ou la requalifier explicitement en `INCONNU`.
- Reformuler le risque sur les scripts inline pour qu'il décrive une conséquence possible, pas un échec certain non observé.
- Si tu gardes le détail `?ver=1.0.2`, présente-le comme comportement du cœur WordPress ou retire la forme exacte de l'URL.
