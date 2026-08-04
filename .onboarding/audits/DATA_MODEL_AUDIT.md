# Modèle de données — Audit

> Confiance : high pour l'absence de modèle propre (VÉRIFIÉ_CODE — tous les fichiers lus intégralement). Confiance low sur tout ce qui concerne la base de données et le schéma WordPress réel : ces éléments sont entièrement hors dépôt et non observables ici.

## Compréhension globale

Le thème ne définit aucun modèle de données propre. Il n'y a ni Custom Post Type, ni taxonomie personnalisée, ni table `$wpdb` custom, ni champ ACF/métadonnée enregistrée. Le thème est un pur consommateur du modèle natif WordPress (`post`/`page`) via la boucle standard. Toute la persistance, le schéma et le contenu relèvent du cœur WordPress et de sa base de données, entièrement hors dépôt.

## Résumé exécutif

Il n'y a rien à auditer dans ce dépôt au sens du modèle de données : le thème ne crée, ne lit directement, ni ne modifie aucune donnée. Les appels `have_posts()`, `the_post()`, `the_title()`, `the_content()` (`index.php:3-8`) sont des abstractions de rendu WordPress qui encapsulent les requêtes SQL — le thème ne connaît pas le schéma sous-jacent. La base de données et son schéma (tables `wp_posts`, `wp_options`, `wp_postmeta`, etc.) sont gérés par WordPress hors dépôt. Le seul signal de « donnée » dans le thème est `bloginfo('name')` (`header.php:8`) qui lit le titre du site depuis `wp_options` — mais via l'API WordPress, sans accès direct à la base.

## Constats détaillés

**Absence totale de modèle propre au thème.** Une recherche dans les 5 fichiers PHP révèle : aucun `register_post_type()`, aucun `register_taxonomy()`, aucun `$wpdb->` (accès direct à la base), aucun `get_post_meta()`, aucun `update_post_meta()`, aucun `add_option()` ni `get_option()` propre au thème (`VÉRIFIÉ_CODE` — `functions.php`, `index.php`, `header.php`, `footer.php` lus intégralement). Le thème n'étend pas le modèle de données WordPress : il le consomme tel quel.

**Consommation exclusive de l'API WordPress de haut niveau.** Les seuls accès aux données sont : `have_posts()` et `the_post()` qui itèrent sur la WP_Query courante (`index.php:3-4`) ; `the_title()` et `the_content()` qui lisent le post en cours (`index.php:5-6`) ; `post_class()` qui génère des classes CSS depuis les métadonnées du post (`index.php:4`) ; `bloginfo('name')` et `bloginfo('charset')` qui lisent la configuration WordPress (`header.php:4,8`). Tous ces appels délèguent au cœur WP sans que le thème ne connaisse ou ne manipule le schéma sous-jacent (`VÉRIFIÉ_CODE`).

**`add_theme_support('post-thumbnails')` — déclaration sans consommation.** `functions.php:9` déclare le support des images mises en avant, ce qui indique à WordPress d'activer l'association de miniatures aux posts. Aucun template du dépôt n'appelle ensuite `the_post_thumbnail()`, `has_post_thumbnail()` ou équivalent (`VÉRIFIÉ_CODE` — `index.php` lu intégralement). Cette déclaration n'affecte pas le schéma de la base (WordPress gère les miniatures via `wp_postmeta` nativement), mais signale une capacité déclarée et non consommée côté template.

**Base de données — hors périmètre, non observable.** Le schéma réel de la base (tables WP standard, éventuelles tables ajoutées par WooCommerce, Contact Form 7 ou Yoast SEO), le contenu des posts, les options de configuration et les données de formulaire sont entièrement inconnus depuis ce dépôt. Statut : `INCONNU`. Aucun dump, aucun accès lecture seule n'a été fourni à cette étape (cohérent avec la carte des domaines qui marque tous les domaines `Dépend de la base : non` pour le code versionné).

## Forces

- **Aucun accès direct à la base** : le thème n'utilise pas `$wpdb` ni de requêtes SQL brutes (`VÉRIFIÉ_CODE`). Il n'est pas possible d'introduire une injection SQL depuis ce code.
- **Séparation nette entre modèle et présentation** : le thème ne connaît pas le schéma, ne le duplique pas, n'en crée pas d'abstraction maison. La frontière est propre.

## Dettes techniques

- Aucune dette technique liée au modèle de données dans le périmètre versionné. Le thème ne définit rien qui pourrait se dégrader ou créer une dette de schéma.

## Zones critiques

- Aucune zone critique dans le périmètre versionné. Si une dette de modèle de données existe (tables WooCommerce, métadonnées CF7, configuration Yoast), elle est entièrement hors dépôt.

## Risques

- **Opacité du schéma réel** : sans accès à la base, il est impossible de vérifier si des plugins actifs (WooCommerce, CF7) ont ajouté des tables non standard, ou si le contenu stocké en `wp_postmeta` est compatible avec l'unique template de rendu (`index.php`). Statut : `INCONNU`. Ce risque ne concerne pas le thème lui-même, mais les interactions entre le thème et le contenu réel.
- **`post-thumbnails` déclaré, jamais affiché** : si des éditeurs associent des images mises en avant à leurs contenus (via l'admin WP), ces images ne seront jamais rendues par le thème en l'état. Pas un risque de données, mais une déconnexion entre capacité déclarée et rendu effectif.

## Recommandations priorisées

1. **Documenter le schéma réel de la base** — si le projet évolue au-delà du pilote, un accès lecture seule à la base permettrait d'inventorier les plugins actifs, leurs tables, et de vérifier la compatibilité avec le thème. Cette étape est hors périmètre de ce dépôt seul.
2. **Supprimer ou consommer `post-thumbnails`** — soit retirer `add_theme_support('post-thumbnails')` de `functions.php:9` si les images mises en avant ne sont pas utilisées, soit ajouter un appel à `the_post_thumbnail()` dans `index.php` si elles doivent être affichées.

## Questions ouvertes

- Des tables custom ont-elles été créées par WooCommerce, Contact Form 7 ou d'autres plugins actifs ? Aucun signal visible depuis ce dépôt.
- Des métadonnées personnalisées (`wp_postmeta`) sont-elles utilisées par le contenu réel du site ? Le thème ne les lit pas, mais elles peuvent exister.
- Un accès lecture seule à la base sera-t-il fourni pour compléter l'onboarding, ou le périmètre reste-t-il volontairement borné au thème ?
