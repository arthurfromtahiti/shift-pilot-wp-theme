# Architecture — Audit

> Confiance : high — les 5 fichiers de code versionnés ont été lus intégralement (`VÉRIFIÉ_CODE`). Le cœur WordPress, la base de données et les plugins sont hors dépôt : tout ce qui touche au routage HTTP, à la hiérarchie de templates, et aux interactions plugin/thème reste à l'état `HYPOTHÈSE` ou `INCONNU`.

## Compréhension globale

Le dépôt contient un thème WordPress minimaliste de 5 fichiers PHP/CSS (~70 lignes effectives). L'architecture est celle d'un thème WordPress standard : un fichier `functions.php` pour les hooks d'amorçage et d'assets, trois fichiers de gabarit (`index.php`, `header.php`, `footer.php`) pour le rendu HTML, et `style.css` pour l'identité visuelle et les métadonnées du thème. Il n'existe aucune couche applicative propre — le thème délègue intégralement la logique au cœur WordPress (hors dépôt). Le projet est explicitement présenté comme un « pilote de test » (`README.md:3`) ; sa modestie structurelle est assumée.

## Résumé exécutif

Le thème respecte les conventions WordPress sans déviation notable : hooks standard, séparation gabarits/fonctions, aucune logique métier dans les templates. La principale dette architecturale est l'absence de tout template spécialisé (`single.php`, `page.php`, `archive.php`, `404.php`) : `index.php` est le seul point de rendu disponible dans ce dépôt (`VÉRIFIÉ_CODE`). La hiérarchie de templates WordPress implique qu'il servirait de fallback pour toutes les URLs, y compris les pages inexistantes (`HYPOTHÈSE` — comportement du cœur WP, hors dépôt ; ce même comportement est qualifié `HYPOTHÈSE` aux constats détaillés lignes 17 et 43). La mention de copyright est codée en dur dans `footer.php:1` plutôt que pilotée par la configuration WP. La dépendance à jQuery est gérée architecturalement par désinscription/ré-inscription sous le même handle, ce qui est un pattern WP légal mais à haut risque opérationnel (voir SECURITY_ROBUSTNESS_AUDIT et CODE_HOTSPOTS_AUDIT). Il n'y a ni menu de navigation, ni zone de widget, ni sidebar déclarés (`VÉRIFIÉ_CODE`) ; les plugins qui supposent ces zones n'y trouveront pas de point d'ancrage natif offert par le thème — leur comportement effectif dépend de la façon dont chaque plugin gère ce cas (`HYPOTHÈSE`, plugins hors dépôt). L'ensemble tient correctement pour un pilote à périmètre intentionnellement limité.

## Constats détaillés

**Découpage en deux hooks, propre et conforme.** `functions.php` centralise deux responsabilités distinctes via deux hooks WordPress séparés : `after_setup_theme` (ligne 7) pour les déclarations de capacités, et `wp_enqueue_scripts` (ligne 12) pour le chargement des assets. Cette séparation est conforme aux best practices WordPress et préserve l'ordre d'exécution attendu par le cœur WP. Les deux closures sont anonymes et directement attachées — aucune indirection, aucune sur-abstraction (`VÉRIFIÉ_CODE` : `functions.php:7-24`).

**Gabarit unique : `index.php` sert de fallback universel.** Le dépôt ne contient qu'un seul template : `index.php` (`VÉRIFIÉ_CODE` — inventaire complet du dépôt). La hiérarchie de templates WordPress se rabat sur `index.php` lorsqu'aucun template plus spécifique n'existe (`HYPOTHÈSE` — comportement du cœur WP, hors dépôt). Conséquence observable : il n'existe pas de rendu différencié pour les articles (`single.php`), les pages statiques (`page.php`), les archives (`archive.php`) ou les erreurs 404 (`404.php`). Un visiteur sur une URL inexistante pourrait voir le même rendu qu'une page sans contenu, soit `<p>Aucun contenu.</p>` si `have_posts()` retourne `false` (`index.php:9`, `VÉRIFIÉ_CODE` pour le code ; `HYPOTHÈSE` pour le comportement WP sur les 404 — hiérarchie de templates hors dépôt). Ce n'est pas un bug pour un pilote, mais c'est une limite architecturale documentée.

**Absence de zones déclarées (menus, widgets, sidebars).** Aucun appel à `register_nav_menus()`, `register_sidebar()`, ni `add_image_size()` dans `functions.php` (`VÉRIFIÉ_CODE` — fichier lu intégralement). Le thème déclare `add_theme_support('post-thumbnails')` (`functions.php:9`) mais aucun template ne consomme d'image mise en avant (`VÉRIFIÉ_CODE` : `index.php` lu intégralement, aucun appel à `the_post_thumbnail()`). L'architecture du thème n'offre donc aucun point d'extension natif WP (menus, widgets) que des plugins ou éditeurs pourraient utiliser.

**Copyright non paramétrable.** La mention `&copy; Prox-i` est codée en dur dans `footer.php:1` (`VÉRIFIÉ_CODE`). Elle n'est pas pilotée par `bloginfo()` ni par une option WP, ce qui signifie que toute modification nécessite une édition du fichier PHP. C'est une dette de maintenabilité prévisible si le thème est réutilisé ou si l'entité change de nom.

**Minimum WordPress requis déclaré mais non vérifiable.** `style.css:8` déclare `Requires at least: 5.9` et `Requires PHP: 7.4`. Ces contraintes sont lisibles par WordPress pour avertir en cas d'incompatibilité, mais la version réellement déployée est `INCONNU` (WP hors dépôt).

## Forces

- **Séparation claire functions/templates** : `functions.php` porte uniquement les hooks ; les gabarits n'ont aucune logique applicative — la boucle WP native (`index.php:3-10`) est la seule « logique » présente, et elle est standard (`VÉRIFIÉ_CODE`).
- **Conformité aux conventions WP** : hooks aux bons moments (`after_setup_theme` pour les capacités, `wp_enqueue_scripts` pour les assets), API natives utilisées correctement (`get_header()`/`get_footer()`, `wp_head()`/`wp_footer()`, `body_class()`, `language_attributes()`) (`VÉRIFIÉ_CODE`).
- **Minimalisme justifié** : pour un pilote de test, la taille du code (5 fichiers, ~70 lignes) rend l'ensemble entièrement auditable en une lecture.

## Dettes techniques

- **Gabarit unique pour toutes les URLs** : l'absence de `single.php`, `page.php`, `archive.php`, `404.php` (`VÉRIFIÉ_CODE`) rend tout rendu différencié (erreur 404, archive, article seul) impossible sans modification du dépôt. Fichier concerné : `index.php`.
- **Copyright codé en dur** : `footer.php:1` contient `&copy; Prox-i` sans paramétrage. Tout changement d'entité ou de mention légale exige une PR (`VÉRIFIÉ_CODE`).
- **`post-thumbnails` déclaré, jamais consommé** : `functions.php:9` déclare le support, mais aucun template n'appelle `the_post_thumbnail()` (`VÉRIFIÉ_CODE`). La déclaration n'est pas nuisible, mais elle signale soit une fonctionnalité prévue et non implémentée, soit une déclaration défensive sans usage.

## Zones critiques

- **`functions.php:16` — `wp_deregister_script('jquery')`** : ce point unique concentre le principal risque d'intégration de ce thème. Si des plugins (WooCommerce, Contact Form 7 — cités dans `README.md:8`) enregistrent ou déclarent des dépendances sur le handle `'jquery'` avant ce callback (selon leur priorité sur `wp_enqueue_scripts`), le comportement est indéterminé. Un senior regarderait ici en premier lors de tout diagnostic d'incompatibilité de scripts.

## Risques

- **Pas de template 404** : si WordPress fait appel à `index.php` pour les URLs inexistantes et que `have_posts()` retourne `false` (`HYPOTHÈSE` — comportement WP hors dépôt), le visiteur reçoit `<p>Aucun contenu.</p>` avec un code HTTP 404 géré par WordPress. Impact : expérience utilisateur dégradée, aucune page d'erreur utile (`index.php:9`, `VÉRIFIÉ_CODE`).
- **Extensibilité limitée** : l'absence de zones de widget et de menus (`VÉRIFIÉ_CODE`) prive les plugins qui passent par ces points d'ancrage WP natifs de zones d'affichage dans ce thème (`HYPOTHÈSE` selon les plugins utilisés, hors dépôt). Les shortcodes insérés dans le contenu WP restent fonctionnels indépendamment de ces zones.

## Recommandations priorisées

1. **Ajouter a minima un `404.php`** — pour distinguer les erreurs des pages normales et offrir un rendu utilisateur acceptable. Fichier cible : `404.php` à créer à la racine du thème.
2. **Piloter le copyright depuis WP** — remplacer `&copy; Prox-i` dans `footer.php:1` par `<?php bloginfo('name'); ?>` ou une option WP, pour rendre la mention éditable sans PR.
3. **Déclarer un menu de navigation** — `register_nav_menus()` dans `functions.php` permettrait d'activer la navigation WordPress native et d'offrir un point d'ancrage pour les menus gérés depuis l'admin WP. À ajouter dans le hook `after_setup_theme` (`functions.php:7-10`).

## Questions ouvertes

- D'autres templates (page.php, single.php, archive.php) existent-ils sur le serveur FTP hors dépôt, constituant le thème réel en production ? Le dépôt seul ne permet pas de répondre.
- La mention `&copy; Prox-i` désigne-t-elle l'entité finale du site, ou est-ce un placeholder pilote à remplacer ?
- `post-thumbnails` est-il déclaré en prévision d'un template futur, ou est-ce un résidu de copie-colle ?
