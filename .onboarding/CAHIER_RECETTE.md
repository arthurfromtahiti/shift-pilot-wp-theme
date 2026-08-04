# CAHIER_RECETTE — Parcours de test du thème Shift Pilot

## Introduction

Ce document liste les parcours à tester pour valider le bon fonctionnement du thème après déploiement ou modification. Aucun test automatisé n'existe (couverture 0 % — `README.md:13`, `TESTING_AUDIT.md`) — la recette est intégralement manuelle.

**Durée estimée** : 15–30 min selon le périmètre (vérification simple vs. diagnostic complet).

**Environnement requis** :
- WordPress ≥ 5.9 (`style.css:8` — `Requires at least: 5.9`)
- PHP ≥ 7.4 (`style.css:9` — `Requires PHP: 7.4`)
- Navigateur moderne avec DevTools (Firefox, Chrome, Safari)
- Accès admin WordPress (pour certains tests optionnels)

---

## Prérequis pour tous les tests

- [ ] Thème `shift-pilot-wp-theme` activé dans l'admin WordPress
- [ ] Au moins un article/page de contenu publié (pour vérifier le rendu)
- [ ] CDN `code.jquery.com` accessible depuis le navigateur (test optionnel si réseau restreint)

---

## Test #1 : Chargement de la page d'accueil

**Objectif** : vérifier que le thème produit un document HTML valide et que les assets CSS/jQuery se chargent.

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

4. **DevTools** → onglet **Network** :
   - [ ] `style.css` chargé avec statut HTTP 200 ou 304 (pas 404)
   - [ ] `jquery-1.12.4.min.js` chargé depuis `code.jquery.com` avec statut 200 ou 304 (pas 404)
   - [ ] Vérifier la chaîne `?ver=1.0.2` dans l'URL du CSS (cache-buster)
   - [ ] Vérifier la chaîne `?ver=1.12.4` dans l'URL de jQuery

5. **DevTools** → onglet **Inspector** / **Elements** (ou équivalent) — inspecter `<head>` :
   - [ ] Balise `<title>` présente (non vide, contient le titre de la page)
   - [ ] Meta charset UTF-8 : `<meta charset="utf-8">`
   - [ ] Balise `<link>` pour `style.css` présente
   - [ ] **Pas** de balise `<script>` pour jQuery en `<head>` (doit être en footer)

6. **DevTools** → inspecter `<footer>` :
   - [ ] Balise `<script>` de jQuery visible à la fin du `</body>` (avant la fermeture)

### Critères de passage

✅ **Succès** : page s'affiche, CSS appliqué, jQuery chargé en footer, aucune erreur console.

❌ **Échec** : page blanche, erreurs JavaScript, assets manquants (404).

---

## Test #2 : Article individuel

**Objectif** : vérifier que le rendu fonctionne pour un article et que les données sont correctement échappées.

### Prérequis

- Au moins un article publié dans WordPress

### Étapes

1. **Navigateur** : cliquer sur un article depuis la page d'accueil (ex. `/blog/mon-article/` — structure d'URL dépend de WordPress et du site réel, peut varier)
2. **Inspection visuelle** :
   - [ ] Page s'affiche sans erreur
   - [ ] Titre de l'article visible en `<h2>` (extrait du post)
   - [ ] Contenu de l'article visible (le corps du post)
   - [ ] Aucun caractère non échappé ni balise HTML cassée dans le contenu
   - [ ] Chrome (en-tête/pied) identique à la page d'accueil

3. **DevTools** → **Inspector** → éléments du contenu :
   - [ ] Balise `<h2>` contient le titre du post
   - [ ] Classe `post_class()` appliquée (ex. `<article class="post-123 type-post status-publish">` — les classes varient selon le contenu réel)
   - [ ] Contenu dans une `<div>` ou balise de section

4. **Observation** (si du contenu avec caractères spéciaux existe) :
   - [ ] Les accents (é, è, ê, etc.) s'affichent correctement (charset UTF-8 effectif)
   - [ ] Les guillemets, tirets, caractères spéciaux sont échappés (pas de `&lt;`, `&gt;` visibles pour du contenu normal)

### Critères de passage

✅ **Succès** : article s'affiche correctement, contenu intégral visible, pas d'erreur.

❌ **Échec** : article blanc ou tronqué, caractères mal échappés, erreurs console.

---

## Test #3 : Boucle vide (sans contenu)

**Objectif** : vérifier que le fallback `<p>Aucun contenu.</p>` s'affiche quand la boucle WordPress ne retourne aucun article/page (cas prouvé par `index.php:9`).

### Prérequis

- URL valide mais sans contenu : ex. `/category/vide/` si catégorie vide, ou toute archive sans articles
- ℹ️ Cette URL **existe et est gérée par WordPress**, contrairement aux URLs inexistantes (Test #4)

### Étapes

1. **Navigateur** : accéder à une URL d'archive valide mais vide (ex. catégorie vide, archive par auteur sans articles)
2. **Inspection visuelle** :
   - [ ] Message `<p>Aucun contenu.</p>` s'affiche
   - [ ] Chrome du site toujours visible (pas de page blanche)
   - [ ] ⚠️ **Note** : ce message s'affiche aussi pour les URLs inexistantes (404) via le fallback WordPress — voir Test #4 pour cette distinction

3. **DevTools** → **Console** :
   - [ ] Aucune erreur PHP, aucune erreur JavaScript

### Critères de passage

✅ **Succès** : fallback s'affiche, structure HTML intacte.

❌ **Échec** : page blanche, erreur PHP.

---

## Test #4 : URL inexistante (404 — LIMITATION CONNUE, HORS PÉRIMÈTRE)

**Objectif** : observer le comportement du thème sur URLs inexistantes (limité — pas de `404.php`).

**⚠️ NOTE IMPORTANTE** : le code HTTP 404 et son envoi au navigateur sont gérés par le cœur WordPress, **hors du périmètre versionné de ce thème**. Ce test ne valide que le rendu du thème, pas la couche HTTP.

### Prérequis

- URL inexistante (ex. `/page-qui-nexiste-pas/`)

### Étapes

1. **Navigateur** : accéder à l'URL inexistante
2. **Inspection visuelle** :
   - [ ] Page s'affiche sans erreur 500 (rendu thème en place)
   - [ ] Message affiché : `<p>Aucun contenu.</p>` (identique au Test #3)
   - [ ] ⚠️ **LIMITATION DOCUMENTÉE** : pas de message distinct, pas de lien vers l'accueil — c'est une limitation du thème, pas une erreur

3. **DevTools** → **Network** → premier élément (la page elle-même) :
   - [ ] Observation optionnelle : code HTTP réponse (200, 404, etc.)
   - [ ] ℹ️ Le thème ne contrôle pas ce code — c'est WordPress qui l'envoie

4. **Remarque** : code 404 réel, traitement Yoast SEO, comportement plugins — tout cela est **hors dépôt**, non vérifiable depuis ce thème seul.

### Critères de passage

✅ **Succès** : page rendue sans erreur, fallback `<p>Aucun contenu.</p>` visible, aucune erreur thème.

⚠️ **Limitation acceptée** : pas de `404.php` — le thème applique `index.php` à tous les types de contenu. Amélioration recommandée pour la production.

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

4. **Console JavaScript** (F12 → Console tab) :
   - [ ] Taper `jQuery` ou `$` et appuyer sur Entrée
   - [ ] Résultat : une fonction jQuery doit s'afficher (ex. `ƒ (selector, context)`) — jQuery est chargé et accessible
   - [ ] ❌ Si `ReferenceError: jQuery is not defined` → problème de chargement

5. **Comportement des plugins** (observation optionnelle — hors périmètre thème) :
   - ℹ️ Aucune erreur `jQuery is not defined` ou `$ is not defined` en console = le thème charge jQuery correctement
   - ℹ️ Tous les comportements plugins jQuery-dépendants (formulaires, paniers, etc.) restent de la responsabilité des plugins, pas du thème

### Critères de passage

✅ **Succès** : jQuery chargé en footer, accessible en console, aucune erreur dépendance.

❌ **Échec** : jQuery manquant (404), chargé en head, ou `jQuery is not defined` en console.

---

## Test #7 : Title-tag (balise `<title>`)

**Objectif** : vérifier que WordPress gère la balise `<title>` (déclaration `add_theme_support('title-tag')`).

### Étapes

1. **DevTools** → **Elements** → inspecter `<head>` :
   - [ ] Balise `<title>` présente
   - [ ] Contenu non vide (ex. `<title>Accueil — Mon Site WordPress</title>`)

2. **Navigateur** → onglet du site :
   - [ ] Titre de l'onglet affiche le contenu de la balise `<title>`

3. **Changer de page** (aller à un article) :
   - [ ] Onglet change : affiche le titre de l'article
   - [ ] `<title>` dans DevTools est mis à jour

4. **Comportement attendu** (généré par WordPress, hors dépôt — observation seulement) :
   - [ ] Accueil : titre de l'accueil ou nom du site (WordPress)
   - [ ] Article : titre de l'article (WordPress)
   - [ ] Page vide/404 : title dépend de WordPress et des plugins — non vérifiable depuis ce thème seul

### Critères de passage

✅ **Succès** : `<title>` dynamique, changé selon le contexte de page.

❌ **Échec** : `<title>` absent, vide ou statique (ne change pas de page en page).

---

## Test #8 : Images mises en avant (BONUS — limitation actuelle)

**Objectif** : vérifier le statut de `add_theme_support('post-thumbnails')` (déclaré, mais non utilisé par le thème).

### Prérequis

- Au moins un article avec une image mise en avant

### Étapes

1. **Admin WordPress** → Modifier un article :
   - [ ] Champ « Image mise en avant » visible (activé par `add_theme_support('post-thumbnails')`)
   - [ ] Image associée existante

2. **Frontend** → article avec image mise en avant :
   - [ ] Chercher l'image dans le rendu HTML : aucun appel à `the_post_thumbnail()` n'existe dans `index.php` (`VÉRIFIÉ_CODE`)
   - [ ] ✅ Image **ne s'affiche pas** — c'est attendu (limitation documentée)

3. **DevTools** → inspecter le contenu de l'article :
   - [ ] Aucun tag `<img>` pour la vignette (on la cherche, on ne la trouve pas)

### Critères de passage

✅ **Comportement actuel (documenté)** : image mises en avant déclarée mais non affichée.

⚠️ **Améliorations futures** : soit consommer `the_post_thumbnail()` dans `index.php`, soit retirer la déclaration de `add_theme_support('post-thumbnails')`.

---

## Test #9 : Langue & charset (International)

**Objectif** : vérifier que la langue et le charset sont correctement déclarés.

### Étapes

1. **DevTools** → **Elements** → inspecter `<html>` :
   - [ ] Attribut `lang` présent : ex. `<html lang="fr-FR">` (varie selon la configuration WordPress)
   - [ ] Valeur cohérente avec la langue du site (vérifiable en admin WordPress → Paramètres → Langue)

2. **DevTools** → **Elements** → inspecter `<head>` :
   - [ ] Balise `<meta charset="utf-8">` présente (ou équivalent)

3. **Pages avec contenu multilingue** (si applicable) :
   - [ ] Accents (é, è, ê, ü, ñ, etc.) s'affichent correctement
   - [ ] Caractères spéciaux («, », —, …) non garbled

### Critères de passage

✅ **Succès** : charset UTF-8, langue déclarée, caractères spéciaux lisibles.

❌ **Échec** : charset absent, caractères garbled, langue manquante.

---

## Test #10 : Classe CSS contextuelles

**Objectif** : vérifier que `post_class()` et `body_class()` appliquent des classes contextuelles utilisables en CSS.

### Étapes

1. **Page d'accueil** → DevTools → inspecter `<body>` :
   - [ ] Classes présentes (ex. `<body class="home blog logged-in ...">` — classe exacte dépend de WordPress et configuration du site)
   - [ ] Classe `home` ou équivalent présente (spécifique à l'accueil)

2. **Article** → DevTools → inspecter `<body>` :
   - [ ] Classes indicatrices de type de page (ex. `single`, `single-post`) — générées par WordPress selon le contexte
   - [ ] Classe statut visible (ex. `status-publish`) — dépend du post

3. **Article** → DevTools → inspecter `<article>` :
   - [ ] Attribut `class` présent avec classes générées par `post_class()` (ex. `post-123 type-post status-publish` — le numéro et les classes varient selon le contenu)
   - [ ] Classe unique par article (ex. `post-123`, où 123 est l'ID du post)

4. **CSS** (si utilisé pour styliser des types de posts différemment) :
   - [ ] Classes appliquées permettent une stylisation contextualisée (ex. `body.single .site-header` vs `body.home .site-header`)

### Critères de passage

✅ **Succès** : classes contextuelles présentes, différentes selon le type de page/article.

❌ **Échec** : aucune classe, ou classes invariantes (même sur toutes les pages).

---

## Test #11 : Performance (DevTools Lighthouse — optionnel)

**Objectif** : baseline de performance — utile pour détecter les régressions après modifications.

### Étapes

1. **DevTools** → onglet **Lighthouse** (ou **PageSpeed Insights**) :
   - [ ] Lancer une audit de performance
   - [ ] Documenter le score initial (ex. 78/100)

2. **Points d'attention** :
   - [ ] jQuery CDN chargé en footer ne bloque pas le rendu (bon)
   - [ ] CSS minimaliste — peu d'impact sur le score

3. **Après modification du thème** :
   - [ ] Relancer Lighthouse
   - [ ] Comparer au score de baseline
   - [ ] Alerte si score baisse de > 10 points

### Critères de passage

✅ **Succès** : score stable ou amélioré après modification.

⚠️ **À surveiller** : dégradation de performance liée au thème (pas à la base/plugins).

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

**Hors périmètre thème** — vérification optionnelle si plugins actifs (comportement non garanti par ce dépôt) :
- [ ] WooCommerce (si actif) : page boutique s'affiche, panier fonctionne (e-commerce = hors dépôt)
- [ ] Contact Form 7 (si actif) : formulaire s'affiche, soumission fonctionne (plugin = hors dépôt)
- [ ] Yoast SEO (si actif) : aucune alerte majeure sur le SEO (plugin = hors dépôt)

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
