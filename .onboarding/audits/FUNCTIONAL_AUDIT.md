# Fonctionnel — Audit

> Confiance : high pour le périmètre versionné (thème seul) ; low pour les fonctionnalités réelles du site (WooCommerce, Contact Form 7, Yoast SEO — entièrement hors dépôt et non observables).

## Compréhension globale

Le thème déclare être un « pilote de test » (`README.md:3`) et sa fonctionnalité est cohérente avec cette définition : il rend du contenu WordPress natif avec un chrome minimal (en-tête, pied, boucle de contenu) et charge deux ressources front (une feuille de style et jQuery). Il ne propose ni navigation, ni gestion d'erreurs différenciée, ni zones d'extension (menus, widgets, sidebars). La cohérence fonctionnelle interne est bonne ; les manques sont assumés par nature de pilote, mais méritent d'être documentés explicitement pour anticiper les évolutions.

## Résumé exécutif

Le thème accomplit sa fonction déclarée : afficher du contenu WordPress via un gabarit HTML valide, avec une identité visuelle minimale et jQuery chargé en footer. Quatre manques fonctionnels sortent du cadre strictement « pilote » et constituent des risques opérationnels probables si le thème est utilisé en production réelle (`HYPOTHÈSE` — criticité métier non observable depuis le seul dépôt versionné) : (1) absence de template `404.php` — en l'absence de template dédié, WordPress pourrait sélectionner `index.php` comme fallback et afficher `<p>Aucun contenu.</p>` sans distinction visuelle (`HYPOTHÈSE` — comportement WP hors dépôt) ; (2) absence de navigation (aucun menu de navigation WP déclaré — `VÉRIFIÉ_CODE`) ; (3) `add_theme_support('post-thumbnails')` déclaré mais jamais rendu ; (4) copyright `&copy; Prox-i` non configurable depuis l'admin WP. La cohérence avec les trois plugins cités (`README.md:8`) est entièrement inconnue — le thème ne les intègre pas, ne définit pas de zones pour eux, et leur compatibilité jQuery 1.12.4 n'est pas vérifiée.

## Constats détaillés

**Le thème remplit sa fonction déclarée de pilote.** `index.php` fournit une structure de gabarit cohérente avec un rendu WordPress minimal : doctype, charset, langue, puis `wp_head()` pour les balises meta, boucle de contenu, `wp_footer()` en pied (`VÉRIFIÉ_CODE` : `index.php:1-13`, `header.php:1-9`, `footer.php:1-5`). La structure squelette HTML est cohérente (doctype → html → head → body → header → main → footer → /body → /html). Le contenu effectif injecté par `wp_head()` et `wp_footer()` (méta-tags, scripts, styles) provient du cœur WP et des plugins — hors dépôt, non observable depuis cette lecture statique. Le flux de rendu documenté dans `WORKFLOW_RENDU_PAGE_FRONT.md` est validé par le code versionné.

**Fallback « aucun contenu » fonctionnel mais non différencié.** `index.php:9` affiche `<p>Aucun contenu.</p>` si `have_posts()` retourne `false` (`VÉRIFIÉ_CODE`). Ce message couvre le cas vide (pas de posts dans la requête courante) mais s'affiche aussi pour les URLs inexistantes, si WordPress sélectionne `index.php` comme fallback 404 (`HYPOTHÈSE` — comportement WP). Il n'y a ni template `404.php`, ni message d'erreur distinguant une page vide d'une page introuvable, ni redirection vers l'accueil.

**Aucune navigation déclarée.** Le thème ne déclare pas de menu de navigation WordPress (`register_nav_menus()` absent — `VÉRIFIÉ_CODE`). L'en-tête produit par `header.php` contient uniquement `<h1><?php bloginfo('name'); ?></h1>` — le nom du site (`header.php:8`). Il n'y a pas de lien vers d'autres pages, pas de fil d'Ariane, pas de barre de navigation dans ce thème (`VÉRIFIÉ_CODE`). Un utilisateur arrivant sur une page de contenu ne dispose d'aucune navigation fournie par le thème lui-même ; une navigation peut néanmoins être assurée par un plugin, un widget ou des liens dans le contenu (`HYPOTHÈSE` — hors dépôt).

**Support `post-thumbnails` déclaré, non consommé.** `functions.php:9` active le support des images mises en avant. Aucun appel à `the_post_thumbnail()` ou `get_the_post_thumbnail()` dans aucun template du dépôt (`VÉRIFIÉ_CODE`). Cette déclaration est anodine fonctionnellement (elle n'empêche rien), mais crée une attente : les éditeurs WP voient le champ « Image mise en avant » dans l'admin et peuvent associer des images à leurs contenus — images qui ne seront jamais affichées par le thème en l'état.

**Copyright non paramétrable — déconnexion admin/front.** `footer.php:1` contient `&copy; Prox-i` codé en dur (`VÉRIFIÉ_CODE`). La modification de cette mention (changement d'entité légale, internationalisation, personnalisation client) requiert une PR plutôt qu'une action dans l'admin WP. Pour un pilote de test, c'est acceptable ; pour une utilisation en production avec une clientèle variable, c'est une limitation fonctionnelle concrète.

**Cohérence avec les plugins cités — entièrement inconnue.** `README.md:8` mentionne Contact Form 7, Yoast SEO et WooCommerce comme plugins actifs (`VÉRIFIÉ_CODE` — mention dans README). Le thème ne déclare aucun hook, shortcode, template dédié ou zone de widget pour ces plugins (`VÉRIFIÉ_CODE` — `functions.php` lu intégralement). Leur présence sur le site ne signifie pas qu'ils fonctionnent avec ce thème : la compatibilité de rendu, les zones d'affichage WooCommerce, les formulaires CF7 et les balises Yoast sont entièrement `INCONNU` depuis ce dépôt. En particulier, aucun fichier `woocommerce.php` ni hook WooCommerce dédié n'est présent dans le thème (`VÉRIFIÉ_CODE`). La convention selon laquelle WooCommerce s'attend à un tel fichier ou à des hooks dédiés pour son rendu relève de la documentation WooCommerce externe, non vérifiable depuis ce seul dépôt (`HYPOTHÈSE`).

**CSS minimal — identité visuelle cohérente avec le pilote.** `style.css` définit 3 règles CSS : `body` (font Georgia, couleur foncée), `.site-header` (bordure basse bleue) (`VÉRIFIÉ_CODE` : `style.css:13-19`). C'est suffisant pour un pilote ; les templates s'appuient sur les classes générées par WordPress (`post_class()`, `body_class()`) sans les styler dans la feuille de style du thème.

## Forces

- **Cohérence interne du thème** : les hooks déclarés dans `functions.php` sont cohérents avec les points d'injection dans les templates (`wp_head()` en `header.php:5`, `wp_footer()` en `footer.php:2`). Aucune incohérence entre ce qui est déclaré et ce qui est utilisé.
- **Fallback explicite pour contenu vide** : `index.php:9` gère le cas sans contenu avec un message visible plutôt qu'une page blanche. C'est mieux qu'une erreur PHP.
- **Structure HTML sémantique minimale** : `<header>`, `<main>`, `<footer>`, `<article>`, `<h1>`, `<h2>` sont utilisés correctement selon leur sémantique HTML5 (`VÉRIFIÉ_CODE`).

## Dettes techniques

- **Absence de template 404** : `index.php` sert de fallback universel sans différenciation d'erreur. Fichier à créer : `404.php`.
- **Navigation absente** : aucun menu WP, aucun lien de navigation dans les templates. Fonctionnalité à ajouter si le site sort du mode pilote.
- **Image mise en avant déclarée mais non rendue** : `functions.php:9` vs absence de `the_post_thumbnail()` dans `index.php`. Incohérence à résoudre dans un sens ou dans l'autre.
- **Copyright non paramétrable** : `footer.php:1` — dette de maintenabilité prévisible.

## Zones critiques

- **Intégration WooCommerce** : si WooCommerce est actif (cité dans `README.md:8`) et que le site a une composante e-commerce, l'absence de `woocommerce.php` et de hooks WC dans le thème (`VÉRIFIÉ_CODE`) implique que WooCommerce activerait son propre mécanisme de fallback pour le rendu de ses pages (`HYPOTHÈSE` — comportement WooCommerce hors dépôt). Le rendu réel des pages boutique est inconnu depuis ce dépôt.

## Risques

- **Rendu 404 indistinguable** : une URL inexistante et une page vide produisent le même rendu `<p>Aucun contenu.</p>` (`index.php:9`). Pour les visiteurs et pour le SEO (si Yoast est actif), cette indistinction peut nuire (`HYPOTHÈSE` — comportement WP pour les 404 inconnu depuis ce dépôt).
- **WooCommerce sans template dédié** : si WooCommerce est actif, ses pages (catalogue, produit, panier, checkout) utilisent ses propres templates de fallback — leur rendu avec ce thème minimal est imprévisible sans test sur le site réel (`HYPOTHÈSE`, hors dépôt).
- **Plugins supposant des zones de widget** : Contact Form 7 permet l'insertion de formulaires via shortcodes (dans le contenu WP) ou widgets. Si des widgets CF7 sont placés dans des zones non déclarées par le thème, ils ne s'afficheront pas (`HYPOTHÈSE`, hors dépôt).

## Recommandations priorisées

1. **Créer `404.php`** — template d'erreur minimal avec un message distinct (ex. « Page introuvable ») et un lien vers l'accueil. Prioritaire si le thème sort du mode pilote.
2. **Déclarer un menu de navigation** — `register_nav_menus(['primary' => __('Menu principal', 'shift-pilot')])` dans `functions.php:7-10` et `wp_nav_menu(['theme_location' => 'primary'])` dans `header.php` après `<h1>`. Nécessaire pour tout site réel.
3. **Vérifier la cohérence avec WooCommerce sur le site réel** — tester les pages WooCommerce (liste produits, fiche produit) avec ce thème actif pour évaluer si le fallback WC est acceptable ou si un template `woocommerce.php` est nécessaire.
4. **Résoudre l'incohérence `post-thumbnails`** — soit consommer `the_post_thumbnail()` dans `index.php`, soit retirer `add_theme_support('post-thumbnails')` de `functions.php:9`.

## Questions ouvertes

- Le site est-il en production avec WooCommerce actif ? Si oui, quel rendu est-il produit sur les pages boutique actuellement ?
- Contact Form 7 est-il utilisé via shortcode dans le contenu WP (compatible avec ce thème) ou via des widgets dans des zones non déclarées (incompatible) ?
- Le pilote est-il destiné à être utilisé en production, ou sera-t-il remplacé par un thème plus complet à terme ?
