# Tests — Audit

> Confiance : high — l'absence de tests est établie par lecture exhaustive du dépôt (VÉRIFIÉ_CODE) et confirmée explicitement par `README.md:13`. Il n'y a pas d'incertitude sur ce point.

## Compréhension globale

Le dépôt ne contient aucune suite de tests, aucun fichier de configuration de test, aucun CI/CD. C'est une absence documentée et assumée : `README.md:13` déclare explicitement « Pas de suite de tests automatisée. » Le projet est un pilote de test minimal (`README.md:3`) — l'absence de tests est cohérente avec son stade et sa taille. Elle représente néanmoins une zone blanche complète : tout changement de code est non vérifiable automatiquement.

## Résumé exécutif

Couverture automatisée : 0 %. Il n'y a pas de tests unitaires, pas de tests d'intégration, pas de tests de régression visuelle, pas de linter PHP configuré, pas de CI/CD. L'inventaire complet du dépôt confirme l'absence de tout fichier `phpunit.xml`, `jest.config.js`, `.travis.yml`, `.github/workflows/`, `Makefile` ou équivalent (`VÉRIFIÉ_CODE`). Pour un pilote de 5 fichiers, l'absence de tests n'est pas une anomalie critique, mais elle signifie que tout agent IA ou développeur qui modifie le code travaille sans filet. Les seules vérifications disponibles sont manuelles (charger le site dans un navigateur).

## Constats détaillés

**Absence documentée et confirmée par le README.** `README.md:13` : « Pas de suite de tests automatisée. » (`VÉRIFIÉ_CODE`). Cette déclaration est explicite et correspond à l'inventaire du dépôt.

**Aucun outillage de test dans le dépôt.** Inventaire complet des fichiers : `style.css`, `functions.php`, `index.php`, `header.php`, `footer.php`, `README.md`, `.claude/settings.local.json` (`VÉRIFIÉ_CODE` — liste de fichiers lue). Aucun fichier de configuration de test PHP (phpunit.xml, phpunit.xml.dist), JavaScript (jest.config.js, .babelrc, karma.conf.js), ni CSS (stylelint.config.js). Aucun dossier `tests/`, `spec/`, `__tests__/`.

**Aucun CI/CD configurable depuis le dépôt.** Aucun fichier `.github/workflows/`, `.travis.yml`, `.circleci/config.yml`, `.gitlab-ci.yml` ni `Dockerfile` (`VÉRIFIÉ_CODE`). Le déploiement se fait par FTP selon `README.md:13`, ce qui exclut toute vérification automatique pré-déploiement.

**Surface testable théorique — ce qui pourrait être couvert.** Malgré la taille minimale, plusieurs comportements seraient testables en théorie : (1) présence et valeur des déclarations `add_theme_support` dans `functions.php` (testable via WP_UnitTestCase ou Brain Monkey) ; (2) séquence `wp_deregister_script`/`wp_enqueue_script` et handle résultant (testable via les stubs WP des frameworks de test PHP pour WordPress) ; (3) rendu HTML produit par `index.php` (testable via un snapshot test avec un mock de la boucle WP). Aucun de ces tests n'est en place.

**Vérification manuelle uniquement.** La seule forme de vérification disponible est de charger le site dans un navigateur et d'observer le rendu. Cette méthode ne détecte pas les régressions subtiles (version jQuery, classes CSS manquantes, boucle silencieusement vide) sans un scénario de test explicitement joué par un humain.

## Forces

- **Taille minimale du code = risque de régression réduit** : 5 fichiers, ~70 lignes. Une revue humaine complète prend moins de 5 minutes. Le rapport coût/bénéfice des tests formels est faible pour un pilote de cette taille.
- **Comportement observable directement** : les effets du thème sont visibles dans le rendu HTML de chaque page — une vérification visuelle manuelle couvre l'essentiel sans infrastructure de test.

## Dettes techniques

- **Zéro couverture automatisée** : toute modification de `functions.php` (le seul fichier avec logique) est non vérifiée automatiquement. En particulier, le comportement de `wp_deregister_script`/`wp_enqueue_script` pourrait régresser silencieusement.
- **Pas de linter** : aucun outil de vérification de la qualité du code PHP ou CSS n'est configuré. Des erreurs de syntaxe dans `functions.php` ne seraient détectées qu'en production.
- **Déploiement par FTP sans CI** : `README.md:13` confirme un déploiement manuel direct sur le serveur. Aucune porte de contrôle qualité automatique avant la mise en ligne.

## Zones critiques

- **`functions.php` entier — aucune régression détectable automatiquement** : c'est le seul fichier avec logique et le seul où une erreur pourrait casser le site (erreur de syntaxe PHP = page blanche, conflit jQuery = panne JavaScript silencieuse). C'est la zone prioritaire si une couverture de test est mise en place.

## Risques

- **Régression silencieuse sur le chargement jQuery** : un changement dans `functions.php:16-23` qui rompt la séquence de désinscription/inscription de jQuery produirait une panne JavaScript invisible sans test automatisé. Détection uniquement par observation manuelle.
- **Erreur PHP de syntaxe non détectée avant déploiement** : sans linter ni CI, une faute de syntaxe dans `functions.php` ne serait visible qu'après dépôt FTP sur le serveur, ce qui peut produire une page blanche en production.
- **Absence de test de régression visuelle** : tout changement de `style.css` est non vérifié automatiquement. Aucun outil de snapshot CSS ou de test de régression visuelle n'est configuré.

## Recommandations priorisées

1. **Ajouter a minima un linter PHP** — `php -l functions.php` en pré-déploiement (ou via un hook git) détecterait les erreurs de syntaxe avant mise en ligne. Investissement minimal, protection contre la page blanche.
2. **Documenter une liste de vérification manuelle** — en l'absence de tests automatisés, une checklist de vérification (chargement de la page d'accueil, vérification du chargement jQuery dans les DevTools, vérification de la feuille de style) réduit le risque de régression lors des déploiements.
3. **Évaluer Brain Monkey ou WP_Mock pour `functions.php`** — si le thème évolue, des tests unitaires des hooks (`wp_enqueue_scripts`, `after_setup_theme`) avec Brain Monkey ou WP_Mock permettraient de vérifier la logique de `functions.php` sans instance WordPress réelle. Pour 24 lignes, l'investissement initial est faible.

## Questions ouvertes

- Un recettage manuel est-il documenté quelque part (hors dépôt) ? Si oui, sa formalisation dans un `CAHIER_RECETTE.md` serait utile pour la suite de l'onboarding.
- Le déploiement FTP est-il manuel ou semi-automatisé ? Un script de déploiement pourrait intégrer un contrôle `php -l` sans CI complet.
