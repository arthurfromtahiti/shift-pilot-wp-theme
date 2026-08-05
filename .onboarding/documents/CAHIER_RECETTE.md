# CAHIER_RECETTE — Parcours de test du thème Shift Pilot

## Introduction

Ce document liste les parcours de test pour valider le bon fonctionnement du thème après déploiement ou modification. Aucun test automatisé n'existe (couverture 0 % — `README.md:13`, `TESTING_AUDIT.md`) — la recette est intégralement manuelle.

**Importante distinction** : ce cahier teste **le code du thème versionné** (appels d'API WordPress, structure HTML, enregistrement des assets) et **observe** le comportement généré par WordPress et l'environnement réel (valeurs injectées, contenu filtré, hiérarchie des templates). Les tests marqués ✅ valident le thème ; ceux marqués 🔍 observent des effets WordPress hors du périmètre du thème.

**Environnement requis** :
- WordPress ≥ 5.9 (requis par `style.css:8` — `Requires at least: 5.9`)
- PHP ≥ 7.4 (requis par `style.css:9` — `Requires PHP: 7.4`)
- Navigateur avec DevTools (pour inspection du rendu)
- Accès admin WordPress (optionnel, pour certains tests)

---

## Prérequis pour tous les tests

- [ ] Thème `shift-pilot-wp-theme` activé dans l'admin WordPress
- [ ] Au moins un article/page de contenu publié (pour vérifier le rendu)
- [ ] CDN `code.jquery.com` accessible depuis le navigateur (test optionnel si réseau restreint)

---

## Test #1 : Chargement de la page d'accueil

**Objectif** : vérifier que le thème produit un squelette HTML correct et que les assets CSS/jQuery se chargent.

### Étapes

1. **Navigateur** : ouvrir la page d'accueil du site (`/` ou `/index.php`)
2. **Inspection visuelle** :
   - [ ] Page s'affiche sans erreur PHP (pas de page blanche, pas de "Fatal error")
   - [ ] Chrome en-tête visible (titre du site en `<h1>`)
   - [ ] Contenu affiché (articles/pages récentes si au moins une publiée)
   - [ ] Pied de page visible (copyright `© Prox-i`, si applicable)

3. **DevTools** (F12) → onglet **Console** :
   - [ ] Aucune erreur JavaScript (pas de `Uncaught ReferenceError: jQuery is not defined`, pas de 404 sur ressources)
   - [ ] Aucune erreur de réseau sur les assets

4. **DevTools** → onglet **Network** (vérification du chargement des assets enregistrés par le thème) :
   - [ ] `style.css` chargé avec statut HTTP 200 ou 304 (pas 404) — correspond à l'enqueue du thème (`functions.php:13` — `PROUVÉ_CODE`)
   - [ ] `jquery-1.12.4.min.js` chargé depuis `code.jquery.com` avec statut 200 ou 304 (pas 404) — correspond à l'enqueue du thème (`functions.php:17-23` — `PROUVÉ_CODE`)
   - [ ] Vérifier la chaîne `?ver=1.0.2` dans l'URL du CSS (cache-buster correspondant à `functions.php:13` — `PROUVÉ_CODE`)
   - [ ] Vérifier la chaîne `?ver=1.12.4` dans l'URL de jQuery (version enregistrée — `PROUVÉ_CODE`)

5. **DevTools** → onglet **Inspector** / **Elements** — observation de l'injection WordPress (non pas validation du thème) :
   - [ ] 🔍 **Observation** : Balise `<title>` présente (non vide) — le thème déclare `add_theme_support('title-tag')` (`functions.php:8` — `PROUVÉ_CODE`) ; le contenu et l'injection sont responsabilité de WordPress (`HYPOTHÈSE_EFFET`)
   - [ ] 🔍 **Observation** : Meta charset UTF-8 : `<meta charset="utf-8">` ou équivalent — injection WordPress (`HYPOTHÈSE_EFFET`)
   - [ ] ✅ Balise `<link>` pour `style.css` présente — validée dans Network au-dessus
   - [ ] ✅ **Pas** de balise `<script>` pour jQuery en `<head>` — le thème enregistre jQuery en footer (`functions.php:17-23`, `footer.php:2` — `PROUVÉ_CODE`)

6. **DevTools** → inspecter avant `</body>` (vérification que jQuery est injecté en footer comme enregistré) :
   - [ ] Balise `<script src="https://code.jquery.com/jquery-1.12.4.min.js..."></script>` visible avant la fermeture `</body>` — confirmé en Network au-dessus

### Critères de passage

✅ **Succès** : page s'affiche, CSS appliqué, jQuery chargé en footer, aucune erreur console.

❌ **Échec** : page blanche, erreurs JavaScript, assets manquants (404).

---

## Test #2 : Article individuel

**Objectif** : vérifier que le rendu fonctionne pour un article via les API WordPress.

### Prérequis

- Au moins un article publié dans WordPress

### Étapes

1. **Navigateur** : cliquer sur un article depuis la page d'accueil (ex. `/blog/mon-article/` — structure d'URL dépend de WordPress et du site réel, peut varier)
2. **Inspection visuelle** :
   - [ ] Page s'affiche sans erreur
   - [ ] 🔍 **Observation** : Titre de l'article visible en `<h1>` sur les pages intérieures, en `<h2>` sur la page d'accueil (rendu via `the_title()` — `index.php:5-9` — `PROUVÉ_CODE`) ; contenu du titre généré par WordPress
   - [ ] ✅ Contenu de l'article visible (via `the_content()` — `index.php:6` — `PROUVÉ_CODE` ; filtrage et shortcodes exécutés par WordPress — `HYPOTHÈSE_EFFET`)
   - [ ] 🔍 **Observation** : Caractères correctement échappés, aucune balise HTML cassée — le thème appelle l'API `the_content()` (`PROUVÉ_CODE`) ; les filtres d'échappement et de sécurité sont appliqués par WordPress (`HYPOTHÈSE_EFFET`)
   - [ ] ✅ Chrome (en-tête/pied) identique à la page d'accueil — structure de gabarit unique (`PROUVÉ_CODE`)

3. **DevTools** → **Inspector** → éléments du contenu :
   - [ ] ✅ Balise `<h1>` contient le titre du post sur les pages intérieures (balise `<h2>` sur la page d'accueil) — validé au-dessus
   - [ ] 🔍 **Observation** : Classe `post_class()` appliquée (ex. `<article class="post-123 type-post status-publish">`) — le thème appelle `post_class()` (`index.php:4` — `PROUVÉ_CODE`) ; contenu des classes généré par WordPress selon contexte (`HYPOTHÈSE_EFFET`)
   - [ ] ✅ Contenu rendu par la boucle WordPress (via `have_posts()` + `the_post()` — `index.php:3-10` — `PROUVÉ_CODE`)

4. **Observation** (vérification que l'encodage UTF-8 fonctionne) :
   - [ ] 🔍 Les accents (é, è, ê, etc.) s'affichent correctement — le thème appelle `bloginfo('charset')` (`header.php:4` — `PROUVÉ_CODE`) ; valeur et effet = WordPress (`HYPOTHÈSE_EFFET`)
   - [ ] 🔍 Les guillemets, tirets, caractères spéciaux sont échappés (pas de `&lt;`, `&gt;` visibles pour du contenu normal) — filtrage par `the_content()` API WordPress

### Critères de passage

✅ **Succès** : article s'affiche correctement, contenu intégral visible, pas d'erreur.

❌ **Échec** : article blanc ou tronqué, caractères mal échappés, erreurs console.

---

## Test #3 : Boucle vide (sans contenu) — OBSERVATION CONDITIONNELLE

**Objectif** : observer que le fallback `<p>Aucun contenu.</p>` s'affiche quand la boucle WordPress ne retourne aucun article/page.

**⚠️ CONDITION** : cette observation s'applique **seulement si** WordPress route une URL valide mais sans contenu vers `index.php` (comportement dépendant de la configuration WordPress hors dépôt — `HYPOTHÈSE`). Cette condition n'est vérifiable que si le site réel possède une archive vide (ex. catégorie vide) accessible.

### Prérequis

- URL valide sans contenu : ex. `/category/vide/` si catégorie vide, ou toute archive sans articles publiés
- ℹ️ Si aucune archive vide n'existe sur le site réel, ce test n'est pas applicable

### Étapes

1. **Vérification locale du code** (toujours applicable) :
   - [ ] Lire `index.php:9` : fallback `<p>Aucun contenu.</p>` présent (`PROUVÉ_CODE`) pour le cas `have_posts() = false`
   - [ ] Lire `index.php:3-10` : la boucle et le fallback sont la seule logique de rendu (`PROUVÉ_CODE`)
   - **Conclusion** : le thème déclare bien le fallback pour les listes vides.

2. **Si une archive vide peut être créée et testée** : accéder à une URL d'archive valide mais vide
3. **Observation visuelle** (conditionnelle) :
   - [ ] 🔍 Message `<p>Aucun contenu.</p>` observé (si WordPress sélectionne `index.php` pour cette URL — `HYPOTHÈSE`)
   - [ ] 🔍 Chrome du site visible (en-tête/pied) — la structure du thème reste intacte
   - ⚠️ **Note** : le même message s'affiche aussi pour d'autres cas où la boucle ne retourne aucun contenu — voir Test #4 pour les URLs inexistantes (404)

4. **DevTools** → **Console** (si test s'applique) :
   - [ ] Aucune erreur PHP, aucune erreur JavaScript

### Statut de validation

✅ **Code du thème valide** : fallback présent (`index.php:9` — `PROUVÉ_CODE`).

⚠️ **Observation frontend** : son apparition dépend de WordPress qui retourne une boucle vide et sélectionne `index.php` (hors dépôt — `HYPOTHÈSE`). Test observable seulement si archive vide disponible et routable via `index.php`.

---

## Test #4 : URL inexistante (404 — OBSERVATION CONDITIONNELLE, HORS PÉRIMÈTRE THÈME)

**Objectif** : observer le rendu du thème sur URLs inexistantes (limitation connue — pas de `404.php` dans le dépôt).

**⚠️ NOTES ESSENTIELLES** :
1. Le code HTTP 404 et son envoi au navigateur sont gérés par le cœur WordPress (**hors du périmètre versionné de ce thème** — `HYPOTHÈSE` ou `INCONNU`)
2. Le rendu de la page (ce que voit le visiteur) dépend de la hiérarchie WordPress qui sélectionne `index.php` en fallback (`HYPOTHÈSE`)
3. Ce test ne valide rien du thème — il documente seulement une limitation connue

### Prérequis

- URL inexistante valide (ex. `/page-qui-nexiste-pas/`)

### Étapes

1. **Vérification locale du code** (toujours applicable) :
   - [ ] Lire inventaire du dépôt : aucun `404.php` présent (`VÉRIFIÉ_CODE`)
   - [ ] Lire `index.php` : seul template pour tous les types de contenus (`PROUVÉ_CODE`)
   - **Conclusion** : le thème n'offre **pas** de page d'erreur personnalisée — c'est une limitation volontaire.

2. **Si WordPress sélectionne `index.php` pour les 404** (hiérarchie WP — `HYPOTHÈSE`) — navigateur accède à l'URL inexistante
3. **Observation visuelle** (conditionnelle) :
   - [ ] 🔍 Page s'affiche sans erreur 500 (rendu thème en place, pas d'exception PHP)
   - [ ] 🔍 Message affiché : `<p>Aucun contenu.</p>` (identique au fallback du Test #3 — même message pour vide et 404)
   - ⚠️ **LIMITATION DOCUMENTÉE** : pas de message distinct « Page introuvable », pas de lien vers l'accueil — c'est une limitation du thème (`index.php` — `PROUVÉ_CODE`)

4. **DevTools** → **Network** → premier élément (la page elle-même) (optionnel) :
   - [ ] 🔍 Observation optionnelle : code HTTP réponse (200, 404, etc.)
   - [ ] ℹ️ Le thème ne contrôle pas ce code — c'est WordPress qui l'envoie (hors dépôt)

5. **Hors périmètre observé depuis ce thème** :
   - [ ] ℹ️ Le code HTTP 404 réel est généré par WordPress, non par le thème
   - [ ] ℹ️ Traitement Yoast SEO, redirection, comportement plugins — tout est hors dépôt

### Statut de validation

✅ **Code du thème validé** : pas de `404.php` — limitation connue documentée (`PROUVÉ_CODE`).

⚠️ **Observation frontend** : le rendu du 404 dépend de la hiérarchie WordPress qui sélectionne `index.php` pour les URLs inexistantes (hors dépôt — `HYPOTHÈSE`). Cette observation est utile pour comprendre le comportement global, mais ne valide rien du thème lui-même.

💡 **Amélioration recommandée** : pour un site en production, créer `404.php` avec message distinct et lien vers l'accueil.

---

## Test #5 : CSS appliqué

**Objectif** : vérifier que la feuille de style se charge et applique les styles.

### Étapes

1. **DevTools** → **Elements** → inspecter les éléments stylisés :
   - [ ] En-tête (`<header class="site-header">`) : bordure basse visible (règle CSS `.site-header` de `style.css:18`)
   - [ ] `<body>` : police Georgia, couleur foncée (règles CSS de `style.css:14-16`)

2. **DevTools** → onglet **Styles** (ou équivalent) :
   - [ ] Sélectionner l'en-tête et vérifier que la règle `.site-header` s'applique (`border-bottom: 2px solid #0d3b66;`)
   - [ ] Sélectionner le body et vérifier que la font-family est Georgia

3. **Cache-buster** :
   - [ ] DevTools → **Network** → chercher `style.css`
   - [ ] URL doit contenir `?ver=1.0.2` (cache-buster)
   - [ ] Après modification de `style.css`, vérifier que **la version est incrémentée** dans `functions.php:13`

### Critères de passage

✅ **Succès** : CSS appliqué visuellement et via DevTools, cache-buster présent.

❌ **Échec** : page non stylisée, cache-buster absent.

---

## Test #6 : jQuery chargé en footer

**Objectif** : vérifier que jQuery est bien chargé depuis le CDN et injecté en footer (pas en head).

### Étapes

1. **DevTools** → **Network** :
   - [ ] Chercher `jquery-1.12.4.min.js`
   - [ ] Vérifier l'URL : `https://code.jquery.com/jquery-1.12.4.min.js?ver=1.12.4`
   - [ ] Statut HTTP : **200 ou 304** (pas 404). 304 = contenu en cache côté navigateur, valide.
   - [ ] Taille chargée > 0 (ex. ~30 KB minifié)

2. **DevTools** → **Elements** → inspecter `<head>` :
   - [ ] ✅ Aucune balise `<script>` pour jQuery en `<head>` — c'est voulu (chargement asynchrone en footer)

3. **DevTools** → **Elements** → inspecter avant `</body>` (fin du document) :
   - [ ] Balise `<script src="https://code.jquery.com/jquery-1.12.4.min.js?ver=1.12.4"></script>` présente
   - [ ] Avant la fermeture `</body>`

4. **Console JavaScript** (F12 → Console tab) — **VÉRIFICATION OPTIONNELLE** :
   - ℹ️ Vérification supplémentaire : taper `jQuery` ou `$` et appuyer sur Entrée
   - ℹ️ Résultat attendu : une fonction jQuery doit s'afficher (ex. `ƒ (selector, context)`) — indique que le site déployé a chargé jQuery correctement
   - ⚠️ Ce test valide l'environnement WordPress réel, pas directement le thème (le thème enqueue et injecte jQuery en footer — `PROUVÉ_CODE`). Aucune erreur en console = environnement WordPress correctement configuré.

5. **Comportement des plugins** (observation du site déployé — hors périmètre thème) :
   - ℹ️ Tous les comportements plugins jQuery-dépendants (formulaires, paniers, etc.) restent de la responsabilité des plugins, pas du thème

### Critères de passage

✅ **Succès** : jQuery chargé en footer, accessible en console, aucune erreur dépendance.

❌ **Échec** : jQuery manquant (404), chargé en head, ou `jQuery is not defined` en console.

---

## Test #7 : Title-tag (balise `<title>`) — OBSERVATION WORDPRESS

**Objectif** : vérifier que le thème déclare correctement `add_theme_support('title-tag')` — la présence et génération de la balise `<title>` dépend ensuite du cœur WordPress.

**⚠️ NOTE CRITIQUE** : le thème **déclare** seulement que WordPress doit gérer le `<title>` (`functions.php:8` — `PROUVÉ_CODE`). La balise est **injectée par WordPress**, pas par le thème (`header.php` ne contient aucun `<title>` manuel — `PROUVÉ_CODE`). Le contenu exact du `<title>` est généré par le cœur WordPress (hors dépôt — `HYPOTHÈSE_EFFET`).

### Étapes

1. **Vérification locale du code** :
   - [ ] Lire `functions.php:8` : déclare bien `add_theme_support('title-tag')` (`PROUVÉ_CODE`)
   - [ ] Lire `header.php:1-9` : aucun `<title>` manuel présent (`PROUVÉ_CODE`)
   - **Conclusion locale** : le thème délègue correctement la gestion du `<title>` à WordPress.

2. **Observation frontend** (ne pas valider comme succès du thème — observation de l'environnement WordPress) :
   - [ ] DevTools → **Elements** → inspecter `<head>` : si une balise `<title>` est présente (non absente), cela signifie que WordPress a injecté la balise (observation)
   - [ ] Contenu non vide : observation que WordPress a généré un titre (contenu = responsabilité WordPress)
   - [ ] Navigateur → onglet du site affiche un titre : observation supplémentaire que le navigateur a reçu la balise

3. **Changer de page** (si possible) :
   - [ ] Observation optionnelle : onglet change (si WordPress met à jour le titre dynamiquement)
   - ℹ️ Ce comportement dépend de WordPress et peut varier selon la configuration du site

### Statut de validation

✅ **Code du thème valide** : déclaration présente, pas de `<title>` manuel — le thème fait sa part.

⚠️ **Observation frontend** : présence/contenu du `<title>` = responsabilité du cœur WordPress, non du thème. Ne compte pas comme critère de passage pour le thème.

ℹ️ **Contenu exact** : vérifié via l'admin WordPress (Paramètres → Titre du site, ou plugins SEO) — hors périmètre versionné de ce thème.

---

## Test #8 : Images mises en avant — LIMITATION DOCUMENTÉE

**Objectif** : documenter l'incohérence entre la capacité déclarée et son absence de consommation dans le thème.

**⚠️ LIMITATION CONNUE** : le thème déclare `add_theme_support('post-thumbnails')` mais ne l'utilise jamais dans le rendu (`PROUVÉ_CODE`). Les images mises en avant chargées via l'admin WordPress ne s'afficheront pas sur le site.

### Prérequis (optionnel)

- Au moins un article avec une image mise en avant (pour observer l'incohérence)

### Étapes

1. **Vérification locale du code** :
   - [ ] Lire `functions.php:9` : déclare bien `add_theme_support('post-thumbnails')` (`PROUVÉ_CODE`)
   - [ ] Lire `index.php` : aucun appel à `the_post_thumbnail()` présent (`PROUVÉ_CODE` — vérifiable par recherche)
   - **Conclusion locale** : l'incohérence est confirmée dans le code.

2. **Observation frontend** (optionnelle, si article avec image existe) :
   - [ ] Admin WordPress → article avec image mise en avant : le champ « Image mise en avant » est visible (effet de la déclaration)
   - [ ] Frontend → article : chercher une balise `<img>` pour la vignette — aucune ne s'affiche (effet de l'absence d'appel à `the_post_thumbnail()`)
   - ℹ️ Les images chargées par les éditeurs ne sont jamais affichées aux visiteurs

3. **Observation de conflit d'attentes** :
   - ℹ️ Éditeurs voient le champ et ajoutent des images → attente d'affichage
   - ℹ️ Les images ne s'affichent jamais → déception UX

### Statut de validation

⚠️ **Incohérence documentée** : c'est une limitation intentionnelle ou un oubli (à clarifier avec le product owner).

✅ **Résolution attendue** :
   - **Option 1** : retirer `add_theme_support('post-thumbnails')` de `functions.php:9` (si images jamais affichées)
   - **Option 2** : ajouter un appel à `the_post_thumbnail()` dans `index.php` (si consommation souhaitée)

ℹ️ Voir questions pour le product owner (CDC_FONCTIONNEL.md) pour clarifier l'intention.

---

## Test #9 : Langue & charset (International) — OBSERVATION WORDPRESS

**Objectif** : vérifier que le thème appelle correctement les API WordPress pour langue et charset.

**⚠️ NOTE CRITIQUE** : le thème **appelle** seulement `language_attributes()` et `bloginfo('charset')` (API WordPress). Les **valeurs réelles** sont générées par WordPress selon la configuration du site (hors dépôt — `HYPOTHÈSE_EFFET`). Le thème ne contrôle pas la langue ni le charset.

### Étapes

1. **Vérification locale du code** :
   - [ ] Lire `header.php:1-2` : appels `<?php language_attributes(); ?>` et `<?php bloginfo('charset'); ?>` présents (`PROUVÉ_CODE`)
   - **Conclusion locale** : le thème délègue correctement la gestion de la langue et du charset à WordPress.

2. **Observation frontend** (ne pas valider comme succès du thème — observation de l'environnement WordPress) :
   - [ ] DevTools → **Elements** → inspecter `<html>` : si attribut `lang` est présent (ex. `lang="fr-FR"`), c'est WordPress qui l'a injecté (observation)
   - [ ] Valeur observée : ex. `fr-FR` — vérifie que WordPress a lu la configuration (hors dépôt)
   - [ ] DevTools → **Elements** → inspecter `<head>` : si balise `<meta charset="utf-8">` est présente, c'est WordPress qui l'a injectée (observation)

3. **Contenu réel** (si contenu multilingue existe dans WordPress) :
   - [ ] Observation optionnelle : accents (é, è, ê, ü, ñ, etc.) lisibles — indique que le charset a pris effet (dépend de WordPress et de la configuration du navigateur)
   - [ ] Caractères spéciaux («, », —, …) : observation que l'encodage UTF-8 fonctionne (hors dépôt)

### Statut de validation

✅ **Code du thème valide** : appels aux API WordPress présents — le thème fait sa part.

⚠️ **Observation frontend** : présence/valeur de `lang` et `charset` = responsabilité du cœur WordPress, non du thème. Ne compte pas comme critère de passage pour le thème.

ℹ️ **Configuration de langue** : vérifiée via l'admin WordPress (Paramètres → Langue du site) — hors périmètre versionné de ce thème.

---

## Test #10 : Classes CSS contextuelles — OBSERVATION WORDPRESS

**Objectif** : vérifier que le thème appelle correctement `post_class()` et `body_class()`.

**⚠️ NOTE CRITIQUE** : le thème **appelle** seulement les API WordPress. Le **contenu exact des classes** est généré par WordPress selon le contexte (type de post, statut du site, page affichée, etc. — hors dépôt — `HYPOTHÈSE_EFFET`). Le thème ne contrôle pas les classes.

### Étapes

1. **Vérification locale du code** :
   - [ ] Lire `header.php:7` : appel `<?php body_class(); ?>` présent (`PROUVÉ_CODE`)
   - [ ] Lire `index.php:4` : appel `<?php post_class(); ?>` présent (`PROUVÉ_CODE`)
   - **Conclusion locale** : le thème appelle correctement les API WordPress pour les classes.

2. **Observation frontend** (ne pas valider comme succès du thème — observation de l'environnement WordPress) :
   - [ ] DevTools → inspecter `<body>` : si attribut `class` est présent (ex. `class="home blog logged-in ..."`), c'est WordPress qui l'a généré (observation)
   - [ ] Attribut `class` observé sur `<body>` (dépend de la page affichée et de la configuration WordPress)
   - [ ] DevTools → inspecter `<article>` (sur une page article) : si attribut `class` présent (ex. `class="post-123 type-post status-publish"`), c'est WordPress qui l'a généré (observation)

3. **Observation d'adaptation au contexte** (optionnelle) :
   - [ ] Si possible, naviguer entre l'accueil et un article et observer que les classes `<body>` changent (ex. accueil : `home`, article : `single-post`)
   - ℹ️ Ces changements reflètent que WordPress s'adapte au type de page (comportement dépendant de WordPress, hors dépôt)

### Statut de validation

✅ **Code du thème valide** : appels aux API WordPress présents — le thème fait sa part.

⚠️ **Observation frontend** : présence/contenu des classes = responsabilité du cœur WordPress, non du thème. Ne compte pas comme critère de passage pour le thème.

ℹ️ **Contenu exact** : déterminé par WordPress selon le contexte (type de post, statut, page) — non configurable par le thème.

---

## Test #11 : Performance (DevTools Lighthouse — OPTIONNEL ET PÉRIPHÉRIQUE)

**Objectif** : baseline de performance du site déployé — diagnostic utile pour détecter les régressions après modifications du thème.

**⚠️ IMPORTANCE RÉDUITE** : ce test est **OPTIONNEL** et **PÉRIPHÉRIQUE** par rapport aux parcours de validation du thème lui-même (rendu gabarit, assets, boucle). Il valide le site déployé et son environnement WordPress global, pas directement le thème. À exécuter comme vérification complémentaire ou optionnelle, pas comme critère de passage obligatoire.

### Prérequis optionnel

- Chrome ou Chromium avec DevTools (Firefox aussi possible)

### Étapes (à effectuer après validation des tests critiques)

1. **DevTools** → onglet **Lighthouse** (ou **PageSpeed Insights**) :
   - [ ] Lancer un audit de performance
   - [ ] Documenter le score initial (ex. 78/100)

2. **Points d'observation** :
   - [ ] jQuery CDN chargé en footer ne bloque pas le rendu (bon — le thème fait sa part : `functions.php:17-23`, `footer.php:2` — `PROUVÉ_CODE`)
   - [ ] CSS minimaliste — peu d'impact sur le score

3. **Après modification du thème** (surveillance de régression) :
   - [ ] Relancer Lighthouse
   - [ ] Comparer au score de baseline documenté
   - [ ] ⚠️ Alerte si score baisse de > 10 points (signe d'une modification du thème qui pénalise la perf — implique une correction)

### Statut de validation

⚠️ **OPTIONNEL** : ce test n'est pas obligatoire avant validation du thème. Le thème lui-même (rendu, assets, hooks) n'est pas affecté par Lighthouse.

✅ **Utile pour surveillance** : comme baseline de régression après modifications futures.

⚠️ **Hors périmètre direct** : les scores Lighthouse dépendent aussi de WordPress, de la base, des plugins, et du contenu — pas uniquement du thème.

---

## Test #12 : Erreur PHP — Linter (optionnel mais recommandé)

**Objectif** : détecter les erreurs de syntaxe PHP avant déploiement.

### Prérequis

- Shell/terminal avec `php` disponible

### Étapes

1. **Linter** sur chaque fichier PHP :
   ```bash
   php -l functions.php
   php -l index.php
   php -l header.php
   php -l footer.php
   ```

2. **Résultat attendu** :
   ```
   No syntax errors detected in functions.php
   ```

3. **En cas d'erreur** :
   ```
   Parse error: syntax error, ... in functions.php on line 16
   ```
   → Corriger avant déploiement (erreur = page blanche en production)

### Critères de passage

✅ **Succès** : aucune erreur de syntaxe PHP sur tous les fichiers.

❌ **Échec** : erreur rapportée = risque de page blanche en production.

---

## Test #13 : Modifications & Cache-buster (après édition du thème)

**Objectif** : vérifier que le versioning CSS est bien synchronisé après une modification.

### Prérequis

- Modification du fichier `style.css` (ex. changement de couleur)

### Étapes

1. **Modification** :
   - [ ] Éditer `style.css` (ex. changer `.site-header` color ou border)
   - [ ] **CRITIQUE** : incrémenter la version en `functions.php:13` ET `style.css:6`
     - Exemple : `1.0.2` → `1.0.3`

2. **Déploiement** :
   - [ ] Téléverser les deux fichiers modifiés via FTP

3. **Vérification frontend** :
   - [ ] **Forcer le rechargement** du navigateur (Ctrl+Shift+R ou Cmd+Shift+R pour vider le cache)
   - [ ] Nouvelle modification visible (couleur, border, etc.)
   - [ ] DevTools → Network → `style.css` URL contient `?ver=1.0.3` (ancienne : `?ver=1.0.2`)

4. **Scénario d'oubli** (test de risque) :
   - [ ] Si la version n'a pas été incrémentée, modifier `style.css` à nouveau, déployer, visiter la page sans rechargement forcé
   - [ ] ❌ Ancienne version du CSS visible (stockée en cache) — ceci documente le risque du cache-buster manuel

### Critères de passage

✅ **Succès** : modification CSS visible après rechargement, version incrémentée et présente dans DevTools.

❌ **Échec** : modification CSS non visible (cache), version non incrémentée.

---

## Checklist rapide (5 min)

Pour une vérification minimale avant déploiement :

- [ ] Page d'accueil s'affiche (pas de page blanche)
- [ ] CSS appliqué (en-tête avec bordure bleue visible)
- [ ] jQuery chargé (console : `jQuery` retourne une fonction)
- [ ] DevTools Console : aucune erreur rouge
- [ ] DevTools Network : CSS et jQuery statut 200, pas 404

---

## Checklist complète (30 min)

Pour une recette rigoureuse (avant mise en production) :

- [ ] Tests #1–7 (chargement, articles, CSS, jQuery, title-tag)
- [ ] Tests #9–10 (charset, classes CSS)
- [ ] Test #12 (linter PHP)
- [ ] Test #13 (cache-buster si modification)
- [ ] Logs serveur vérifiés (aucune erreur PHP, aucune erreur 500)

**Hors périmètre thème** — vérifications optionnelles d'environnement si plugins actifs (non du dépôt, pas critères de passage pour le thème) :
- ℹ️ WooCommerce (si actif) : page boutique s'affiche-t-elle ? Panier fonctionne-t-il ? (e-commerce = géré hors dépôt)
- ℹ️ Contact Form 7 (si actif) : formulaire s'affiche-t-il ? Soumission fonctionne-t-elle ? (plugin = géré hors dépôt)
- ℹ️ Yoast SEO (si actif) : alertes majeures détectées ? (plugin = géré hors dépôt)

---

## Dépannage — Erreurs courantes

### Symptôme : Page blanche en production

**Causes possibles** :
- Erreur PHP de syntaxe en `functions.php` (oubli `;`, parenthèse manquante)
- Version PHP trop ancienne (< 7.4)
- Mémoire PHP insuffisante (rare avec ce thème minimaliste)

**Dépannage** :
1. Vérifier les logs du serveur (`error_log`, `/var/log/php-errors.log`, ou via l'admin FTP)
2. Lancer le linter PHP : `php -l functions.php`
3. Réduire la déclaration `add_theme_support` si suspicion : retirer temporairement les deux lignes de `functions.php:8-9`, redéployer, vérifier

---

### Symptôme : jQuery manquant (`$ is not defined` en console)

**Causes possibles** :
- CDN `code.jquery.com` indisponible (panne CDN, filtrage réseau, HTTPS bloqué)
- `wp_deregister_script('jquery')` exécuté après un plugin qui déclare `'jquery'` comme dépendance (ordre d'exécution)
- `wp_footer()` manquant dans `footer.php:2` (retiré par erreur)

**Dépannage** :
1. Vérifier DevTools Network : jQuery CDN URL retourne 200 ou 404 ?
2. Si 404 : CDN panne, héberger jQuery localement
3. Si ordre d'exécution : abaisser priorité du hook thème (`functions.php:12`) de 10 à 5
4. Vérifier que `footer.php:2` contient `<?php wp_footer(); ?>`

---

### Symptôme : CSS ne change pas (cache navigateur)

**Causes possibles** :
- Cache navigateur appliqué (pas de cache-buster ou ancien cache-buster)
- CDN intermédiaire (Cloudflare, Akamai) met en cache l'ancienne version

**Dépannage** :
1. Forcer rechargement : Ctrl+Shift+R (Windows) ou Cmd+Shift+R (Mac)
2. Vérifier cache-buster : DevTools Network → `style.css` URL doit contenir `?ver=` avec un numéro
3. Synchroniser version : `style.css:6` et `functions.php:13` doivent être identiques
4. Si CDN intermédiaire : invalider le cache CDN (ex. via Cloudflare dashboard) ou attendre expiration (TTL)

---

## Questions pour le développeur après test

1. Tous les tests passent-ils sans intervention ?
2. Quel test a pris le plus de temps ou requiert le plus de clarification ?
3. Avez-vous rencontré une erreur non documentée ici ?
4. Le site réel (avec plugins WooCommerce/CF7/Yoast actifs) a-t-il comportements non testables depuis ce dépôt seul ?

---

## Liens vers la documentation interne

- `PROJECT_CONTEXT.md` — résumé exécutif, points d'attention globaux
- `CDC_FONCTIONNEL.md` — règles métier, capacités, limites
- `CARTOGRAPHIE_CODE.md` — structure fichiers, hotspots, dépendances
- `ARCHITECTURE_AUDIT.md` — audit complet de l'architecture
- `SECURITY_ROBUSTNESS_AUDIT.md` — risques sécurité (jQuery 1.12.4, SRI, CDN)
