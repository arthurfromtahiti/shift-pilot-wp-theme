# PROJECT_CONTEXT — Thème WordPress Shift Pilot

## Résumé exécutif

Ce dépôt contient un **thème WordPress minimal** conçu comme pilote de test (`style.css:2` — « Shift Pilot Theme », version 1.0.2). Le thème rend du contenu WordPress natif via un gabarit unique (`index.php`) avec chrome en-tête/pied minimaliste, charge une feuille de style et déclare jQuery depuis un CDN externe. Il ne porte aucune logique métier, aucun modèle de données propre, et ne s'intègre pas explicitement aux plugins cités (WooCommerce, Contact Form 7, Yoast SEO — tous hors dépôt).

**Nature du projet** : pilote technique de présentation, volontairement borné au rendu de contenu WordPress standard.

**Périmètre versionné** : seul le thème (6 fichiers versionnés : `style.css`, `functions.php`, `index.php`, `header.php`, `footer.php`, `README.md` ; ~70 lignes effectives). Le cœur WordPress, la base de données et tous les plugins sont gérés hors dépôt (FTP) et ne sont donc **pas observables** ici — toute affirmation les concernant est une `HYPOTHÈSE` ou reste `INCONNU`.

---

## Domaines techniques

Le thème porte trois responsabilités techniques distinctes, sans hiérarchie métier :

| Domaine | Description | Criticité | Confiance | Points d'attention |
|---|---|---|---|---|
| **rendu-gabarits-theme** | Assemblage du document HTML, boucle de contenu WordPress | Cœur | high | Gabarit unique pour toutes les URLs — pas de `404.php`, `single.php`, `archive.php` |
| **amorcage-theme** | Déclaration des capacités WordPress (`title-tag`, `post-thumbnails`) | Support | high | `post-thumbnails` déclaré, jamais consommé par les templates |
| **assets-front** | Chargement feuille de style + jQuery 1.12.4 depuis CDN externe | Support | high | jQuery old + CDN externe sans SRI ; cache-buster manuel sur la feuille de style |

---

## Points d'attention prioritaires

### 1. jQuery 1.12.4 — version ancien avec vulnérabilités documentées
- **Preuve** : `functions.php:19` (`VÉRIFIÉ_CODE`)
- **Impact** : jquery 1.x est EOL ; CVE-2019-11358, CVE-2020-11022/11023 documentées dans NVD (exploitabilité hors dépôt — plugins)
- **Recommandation** : mettre à jour vers jQuery 3.x avant mise en production réelle
- **Contexte** : choix épinglé intentionnellement dans commit `5d9b462` — vérifier avec le product owner avant changement

### 2. Absence de SRI (Subresource Integrity) sur jQuery CDN
- **Preuve** : `functions.php:17-23` — pas d'intégrité sur `code.jquery.com` (`VÉRIFIÉ_CODE`)
- **Impact** : si le CDN est compromis, du JavaScript arbitraire s'exécute sans détection
- **Recommandation** : ajouter un filtre `script_loader_tag` pour injecter `integrity=` sur la balise `<script>` émise

### 3. Gabarit unique sans différenciation d'erreur
- **Preuve** : inventaire complet du dépôt — pas de `404.php`, `single.php`, `archive.php` (`VÉRIFIÉ_CODE`)
- **Impact** : si WordPress appelle `index.php` comme fallback pour les URLs inexistantes, le visiteur voit `<p>Aucun contenu.</p>` sans distinction (`HYPOTHÈSE` — comportement WP hors dépôt)
- **Recommandation** : créer `404.php` minimal avec message distinct et lien d'accueil

### 4. Configuration externalisée et non-externalisée
- **Lue depuis WordPress** : titre du site via `<?php bloginfo('name'); ?>` dans `header.php:8` — modifiable en admin WP (`VÉRIFIÉ_CODE` pour l'appel ; valeur lue = `HYPOTHÈSE_EFFET`)
- **Codée en dur** : copyright `&copy; Prox-i` hardcodé dans `footer.php:1` — modification exige une PR (`VÉRIFIÉ_CODE`)
- **Impact** : les modifications du titre du site passent par l'admin WordPress ; le copyright nécessite toujours une modification du dépôt
- **Contexte** : acceptable pour un pilote, limitation pour production multi-client

### 5. Absence de tests automatisés
- **Preuve** : `README.md:13` — « Pas de suite de tests automatisée » ; inventaire du dépôt confirme l'absence de config test (`VÉRIFIÉ_CODE`)
- **Impact** : tout changement non vérifiable automatiquement ; risque de régression sur `functions.php` (seul fichier avec logique)
- **Recommandation** : ajouter au minimum une vérification syntaxe PHP pre-déploiement

---

## Hors périmètre documenté

Ces éléments **existent** sur le site réel mais ne sont **pas versionné** dans ce dépôt — statut : `INCONNU` ou `HYPOTHÈSE`, non observable ici :

| Élément | Raison |
|---|---|
| **Cœur WordPress** | Moteur, hiérarchie de templates, admin — code non versionné |
| **Base de données** | Contenu, config, schéma — accès non fourni |
| **Contenu éditorial** | Posts/pages, images mises en avant — modèle/données WP natifs hors dépôt |
| **Plugins cités** | WooCommerce, Contact Form 7, Yoast SEO — nommés dans `README.md:8`, aucun code/hook du thème |
| **Intégration e-commerce** | Pas de `woocommerce.php` ni hook WC dans le thème (`VÉRIFIÉ_CODE`) |

---

## Confiance et incertitudes

**Confiance haute** sur le périmètre versionné : tous les fichiers lus intégralement (`VÉRIFIÉ_CODE`), taille auditable exhaustivement (6 fichiers).

**Confiance basse** sur :
- Comportement réel de WordPress (hiérarchie templates, routing) — hors dépôt
- Compatibilité plugins (WooCommerce, CF7) — hors dépôt
- Exploitabilité des vulnérabilités jQuery — dépend des plugins actifs

**Incertitudes clés** :
- Le site est-il réellement en production avec WooCommerce actif, ou le thème reste-t-il un pilote ?
- D'autres templates (`single.php`, `404.php`) existent-ils côté FTP hors dépôt ?
- Yoast SEO génère-t-il le bon code HTTP 404, malgré le `<p>Aucun contenu.</p>` du thème ?

---

## Déploiement & intégration

- **Méthode** : FTP manuel (pas de CI/CD) selon `README.md:13`
- **Versionning** : Git (`main` branche seule)
- **Dépendances opérationnelles** :
  - CDN externe `code.jquery.com` (pas de fallback local)
  - WordPress ≥ 5.9 (requis par `style.css:8` — `Requires at least: 5.9`)
  - PHP ≥ 7.4 (requis par `style.css:9` — `Requires PHP: 7.4`) ; note : dépendances réelles du déploiement sont inconnues (`INCONNU`)
- **Configuration** : titre du site (`header.php:8` — `<?php bloginfo('name'); ?>`) lue depuis WordPress et modifiable en admin — `PROUVÉ_CODE` pour l'appel, valeur = `HYPOTHÈSE_EFFET` ; copyright (`footer.php:1`) codé en dur — modification nécessite une PR (`PROUVÉ_CODE`)

---

## Prochaines étapes recommandées

1. **Clarifier le statut de production** : le pilote va-t-il remplacer un thème actuel, ou compléter le site réel ?
2. **Auditer la compatibilité WooCommerce** : si e-commerce actif, tester les pages boutique avec ce thème
3. **Mettre à jour jQuery** : avant toute mise en production au-delà du pilote, évaluer la montée vers 3.x
4. **Ajouter `404.php`** : pour un site en production
5. **Documenter le recettage manuel** : en l'absence de CI, formaliser la checklist de vérification (Voir `CAHIER_RECETTE.md`)
