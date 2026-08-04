# CARTOGRAPHIE_CODE — Structure & points critiques du thème Shift Pilot

## Vue d'ensemble

Le thème contient **5 fichiers** (~70 lignes effectives) organisés selon les conventions WordPress : un fichier `functions.php` pour les hooks, trois gabarits (`index.php`, `header.php`, `footer.php`) pour le rendu, une feuille de style (`style.css`) pour le branding.

```
shift-pilot-wp-theme/
├── style.css              (20 lignes) — métadonnées thème, CSS minimaliste
├── functions.php          (24 lignes) — hooks WordPress (amorçage, assets)
├── index.php              (13 lignes) — gabarit principal, boucle
├── header.php             (9 lignes)  — en-tête HTML, chrome
├── footer.php             (5 lignes)  — pied HTML
├── README.md              — documentation projet
└── .claude/settings.local.json — config locale (ignoré pour l'onboarding)
```

Aucun fichier PHP supplémentaire, aucune classe, aucun namespace. Toute la logique tient en 24 lignes.

---

## Domaines → Fichiers (matrice de traçabilité)

### Domaine : `rendu-gabarits-theme` (production du HTML)

| Responsabilité | Fichier | Lignes | Détail |
|---|---|---|---|
| **Structure HTML racine** | `header.php` | 1–2 | Doctype HTML5, balise `<html lang>` |
| **Head & meta-tags** | `header.php` | 4–5 | Charset, `wp_head()` (injection assets + meta) |
| **Body & chrome en-tête** | `header.php` | 7–8 | Ouverture `<body>` + classes, `<header>` + titre du site |
| **Gabarit principal** | `index.php` | 1, 12 | `get_header()` et `get_footer()` — structure générale |
| **Boucle de contenu** | `index.php` | 3–10 | `have_posts()` → `the_post()` → titre/contenu |
| **Fallback contenu vide** | `index.php` | 9 | Message `<p>Aucun contenu.</p>` |
| **Fermeture HTML** | `footer.php` | 3–4 | `</body></html>` |
| **Pied de page** | `footer.php` | 1 | Copyright + `<footer class="site-footer">` |
| **Injection scripts** | `footer.php` | 2 | `wp_footer()` (jQuery + scripts plugins) |

---

### Domaine : `amorcage-theme` (initialisation au chargement)

| Responsabilité | Fichier | Lignes | Détail |
|---|---|---|---|
| **Déclaration `title-tag`** | `functions.php` | 8 | WordPress gère la balise `<title>` |
| **Déclaration `post-thumbnails`** | `functions.php` | 9 | Active le champ image mise en avant (admin WP) |
| **Hook `after_setup_theme`** | `functions.php` | 7–10 | Fermeture anonyme — enregistrement des capacités |

---

### Domaine : `assets-front` (CSS + jQuery CDN)

| Responsabilité | Fichier | Lignes | Détail |
|---|---|---|---|
| **Enqueue feuille de style** | `functions.php` | 13 | CSS du thème, cache-buster `'1.0.2'` |
| **Désinscription jQuery bundlé** | `functions.php` | 16 | `wp_deregister_script('jquery')` |
| **Enqueue jQuery CDN** | `functions.php` | 17–23 | jQuery 1.12.4 depuis `code.jquery.com` |
| **Hook `wp_enqueue_scripts`** | `functions.php` | 12–24 | Fermeture anonyme — enregistrement des assets |
| **Métadonnées thème** | `style.css` | 1–10 | Version, requis PHP/WP, nom/description |
| **CSS utilisateurs** | `style.css` | 13–19 | 3 règles : body, `.site-header` |

---

## Fichiers critiques & points d'entrée

### 1. `functions.php` — CRITIQUE

**Rôle** : seul fichier avec logique métier du thème. Point d'entrée unique pour tout changement de comportement.

**Lignes critiques** :

| Ligne(s) | Code | Criticité | Raison |
|---|---|---|---|
| **7–10** | Hook `after_setup_theme` + fermeture | Haute | Initialisation du thème — tout dysfonctionnement rend le thème inutile |
| **12–24** | Hook `wp_enqueue_scripts` + fermeture | Haute | Chargement CSS + jQuery — tout dysfonctionnement rend les ressources front invisibles |
| **8** | `add_theme_support('title-tag')` | Moyenne | Dépend du cœur WP — si absent, WordPress ne gère plus la balise `<title>` |
| **9** | `add_theme_support('post-thumbnails')` | Basse | Déclaration sans consommation — incohérence fonctionnelle, pas un risque technique |
| **13** | `wp_enqueue_style(..., '1.0.2')` | Moyenne | Cache-buster manuel — oubli = bugs de cache CSS silencieux en production |
| **16** | `wp_deregister_script('jquery')` | MAXIMAL | Désinscription de la ressource partagée WordPress — ordre d'exécution critique, dépend des plugins |
| **17–23** | `wp_enqueue_script('jquery', CDN, ...)` | Haute | Chargement jQuery 1.12.4 depuis CDN externe — vulnérabilités documentées, pas de SRI |

**Points de vigilance** :

- **Priorité du hook** : défaut (10). Si un plugin enregistré avec priorité < 10 sur `wp_enqueue_scripts` déclare une dépendance sur `'jquery'`, le résultat dépend de l'ordre d'exécution (non déterminé ici).
- **Syntax et erreurs** : une erreur PHP dans ce fichier provoque une page blanche en production (aucun linter pré-déploiement).
- **Aucun commentaire explicatif** : le code est auto-documenté mais sans contexte sur les **pourquoi** de chaque décision (jQuery CDN vs bundlé, priorité du hook, etc.).

---

### 2. `index.php` — PRINCIPAL (gabarit unique, fallback selon hiérarchie WordPress)

**Rôle** : seul template présent dans le dépôt. Peut être sélectionné pour différents types de contenu selon la hiérarchie WordPress (hors dépôt — `HYPOTHÈSE`). Sert de fallback si templates spécialisés (`single.php`, `archive.php`, etc.) absents du dépôt.

**Lignes critiques** :

| Ligne(s) | Code | Criticité | Raison |
|---|---|---|---|
| **1** | `<?php get_header(); ?>` | Haute | Inclut `header.php` — si absent, page sans en-tête ni `<head>` |
| **2** | `<main class="site-content">` | Moyenne | Sémantique HTML5 — pour SEO + accessibilité |
| **3–10** | Boucle `have_posts()` + `the_post()` | Haute | Point de rendu du contenu — utilisé pour chaque type de contenu qui sélectionne ce gabarit |
| **4** | `<?php post_class(); ?>` | Moyenne | Classes CSS contextuelles — générées par WordPress selon type/statut |
| **5** | `<?php the_title(); ?>` | Haute | Titre du post — sortie filtrée par WordPress |
| **6** | `<?php the_content(); ?>` | Haute | Contenu du post — sortie filtrée + shortcodes exécutés par WordPress |
| **9** | `<p>Aucun contenu.</p>` | Moyenne | Fallback si `have_posts()` = false — même message pour toutes les listes vides (`PROUVÉ_CODE`) ; distinction 404/vide dépend de sélection `index.php` par WordPress selon sa hiérarchie pour ces cas (hors dépôt — `HYPOTHÈSE`) |
| **12** | `<?php get_footer(); ?>` | Haute | Inclut `footer.php` — si absent, page sans pied ni `</html>` |

**Points de vigilance** :

- **Gabarit unique dans le dépôt** : l'absence de `single.php`, `page.php`, `archive.php` implique que ce gabarit est le seul candidat du dépôt (`PROUVÉ_CODE`). Que WordPress le sélectionne effectivement pour tous les types de contenu dépend de la hiérarchie WordPress (hors dépôt — `HYPOTHÈSE`). Autres templates spécialisés pourraient exister côté FTP hors dépôt.
- **Aucun appel à `the_post_thumbnail()`** : les images mises en avant (déclarées via `add_theme_support` en `functions.php:9`) ne sont jamais affichées (`PROUVÉ_CODE` — limitation).
- **Pas de menu, pas de sidebar** : aucun point d'ancrage natif pour les widgets ou menus WP (`PROUVÉ_CODE`).

---

### 3. `header.php` — SECONDAIRE (en-tête HTML)

**Rôle** : produit le doctype, head, et chrome en-tête.

**Lignes critiques** :

| Ligne(s) | Code | Criticité | Raison |
|---|---|---|---|
| **1–2** | Doctype + `<html>` + `language_attributes()` | Haute | Structure HTML racine, langue du site |
| **4** | `<meta charset="...">` via `bloginfo('charset')` | Moyenne | Encoding UTF-8 — mal configuré risque d'affichage garbled |
| **5** | `<?php wp_head(); ?>` | MAXIMAL | Point d'injection WordPress — tout CSS, meta, `<title>`, script en head passe ici |
| **7** | `<body>` + `body_class()` | Moyenne | Classes CSS contextuelles (accueil/single/archive/etc.) |
| **8** | `<h1><?php bloginfo('name'); ?></h1>` | Basse | Titre du site (lu depuis WordPress) — pas de lien vers accueil |

**Points de vigilance** :

- **Pas de `<title>` manuel** : conséquence volontaire de `add_theme_support('title-tag')` — si `wp_head()` n'injecte pas correctement, pas de titre du tout.
- **Pas de menu** : aucun `wp_nav_menu()` ni de lien de navigation.
- **Copyright hors du footer** : le titre du site en `<h1>` est typiquement accompagné d'un logo ou lien accueil — absent ici.

---

### 4. `footer.php` — SECONDAIRE (pied HTML)

**Rôle** : produit le pied de page et injection des scripts en footer.

**Lignes critiques** :

| Ligne(s) | Code | Criticité | Raison |
|---|---|---|---|
| **1** | `<footer>` + copyright `&copy; Prox-i` (en dur) | Moyenne | Mention légale hardcodée — non éditable depuis admin WP (limitation maintenabilité) |
| **2** | `<?php wp_footer(); ?>` | MAXIMAL | Point d'injection WordPress en footer — jQuery CDN + tout script en footer (plugins) |
| **3–4** | Fermeture `</body></html>` | Moyenne | Structure HTML | Si absent, document non fermé (grave) |

**Points de vigilance** :

- **Copyright en dur** : « Prox-i » n'est pas paramétrable — toute modification passe par une PR.
- **`wp_footer()` critique** : c'est où jQuery CDN est injecté. Si omis ou erroné, tous les scripts qui dépendent de jQuery échouent.

---

### 5. `style.css` — MÉTADONNÉES + CSS minimaliste

**Rôle** : métadonnées du thème + CSS principal du site.

**Lignes critiques** :

| Ligne(s) | Code | Criticité | Raison |
|---|---|---|---|
| **1–10** | En-tête métadonnées | Moyenne | Nom thème, version, requis PHP/WP — lu par WordPress pour affichage admin |
| **6** | `Version: 1.0.2` | MOYENNE | Cache-buster du CSS — doit correspondre à la valeur en `functions.php:13` |
| **8** | `Requires at least: 5.9` | Basse | Version WP minimale requise — version réelle déployée inconnue |
| **9** | `Requires PHP: 7.4` | Basse | Version PHP minimale requise — version réelle déployée inconnue |
| **13–19** | 3 règles CSS | Basse | Styles minimaux (`body`, `.site-header`) — peu de surface de régression |

**Points de vigilance** :

- **Versioning manuel** : `'1.0.2'` doit être synchronisé entre `style.css:6` et `functions.php:13` — oubli = cache CSS silencieux.
- **CSS minimaliste** : s'appuie sur les classes WordPress (`post_class()`, `body_class()`) — peu d'encapsulation de styles.

---

## Flux d'exécution (ordre d'appel au chargement)

```
Requête HTTP
    ↓
WordPress charge le thème actif
    ↓
functions.php évalué
    ↓
Hooks WordPress enregistrés :
  - after_setup_theme (priorité défaut 10)
  - wp_enqueue_scripts (priorité défaut 10)
    ↓
WordPress déclenche after_setup_theme
  ├─ add_theme_support('title-tag')
  └─ add_theme_support('post-thumbnails')
    ↓
WordPress détermine le template (hiérarchie)
  └─ Sélectionne index.php (seul disponible)
    ↓
index.php exécuté
  ├─ get_header() → header.php
  │   ├─ Doctype, <html>, charset, wp_head() [1]
  │   └─ <body>, <header>, titre du site
  ├─ Boucle have_posts() / the_post()
  │   ├─ post_class(), the_title(), the_content()
  │   └─ Fallback si empty
  └─ get_footer() → footer.php
      ├─ wp_footer() [2] ← jQuery CDN injecté ici
      └─ </body></html>

[1] wp_head() : jQuery n'y est pas (chargé en footer)
[2] wp_footer() : jQuery CDN + tout script en footer (priorité 10+)
```

---

## Dépendances externes

### Dépendances WordPress (non versionnées, hors dépôt)

| Dépendance | Fourni par | Détail | Preuve |
|---|---|---|---|
| **API de template** | Cœur WordPress | `have_posts()`, `the_post()`, `the_title()`, `the_content()`, `get_header()`, `get_footer()`, etc. | Standard WP, `HYPOTHÈSE` |
| **API d'assets** | Cœur WordPress | `wp_enqueue_script()`, `wp_enqueue_style()`, `wp_deregister_script()` | Standard WP, `HYPOTHÈSE` |
| **Hiérarchie templates** | Cœur WordPress | Sélection de `index.php` comme fallback | Standard WP, `HYPOTHÈSE` — hors dépôt |
| **Hooks `after_setup_theme`, `wp_enqueue_scripts`** | Cœur WordPress | Déclenchement au bon moment | Standard WP, `HYPOTHÈSE` |
| **Filtrage sortie** | Cœur WordPress | `the_content()` applique les filtres + shortcodes | Standard WP, `HYPOTHÈSE` |

---

### Dépendances externes (Internet)

| Dépendance | Détail | Criticité | Fallback |
|---|---|---|---|
| **`code.jquery.com`** | jQuery 1.12.4 minifié | Haute | Aucun — site sans jQuery si CDN indisponible |
| **Charset & language packs (WP)** | UTF-8 + traductions `.po` | Basse | WordPress fait défaut |

---

### Dépendances versionnées manquantes

| Élément | Statut | Impact |
|---|---|---|
| **Composer/PHP packages** | Aucun utilisé | Simplicité (pas de dépendance transitive) |
| **npm/webpack** | Aucun utilisé | CSS/JS minifiés manuellement ou non minifiés |
| **Babel/transpiling** | Non utilisé | Pas de syntaxe ES6+ (compatible tous navigateurs) |

---

## Zones de risque & hotspots

### Hotspot #1 : `functions.php:16` — `wp_deregister_script('jquery')`

**Sévérité** : MAXIMAL

**Raison** : ligne unique qui modifie l'état global de WordPress de manière irréversible et dépend de l'ordre d'exécution des plugins (hors dépôt).

**Conséquences possibles** :
- Plugin enregistré avec priorité < 10 sur `wp_enqueue_scripts` déclare une dépendance sur `'jquery'` → détournement de la dépendance
- Après désinscription, le handle `'jquery'` n'existe plus → plugins tardifs qui tentent de l'utiliser voient `'jquery undefined'`
- Comportement non reproductible : dépend de l'ensemble des plugins actifs et de leur ordre de chargement

**Résolution** :
- Tester avec l'ensemble réel des plugins actifs sur le site
- Si conflit détecté, abaisser priorité du callback du thème (`functions.php:12`) à 5 pour exécution avant les plugins
- Documenter explicitement ce risque

---

### Hotspot #2 : `functions.php:13` — cache-buster CSS manuel

**Sévérité** : MOYENNE

**Raison** : version codée en dur `'1.0.2'` doit être synchronisée avec `style.css:6`. Oubli = modifications CSS invisibles en production.

**Conséquences** :
- Développeur modifie `style.css`, oublie de mettre à jour `functions.php:13`
- Visiteurs voient l'ancienne version du CSS en cache (navigateur, CDN intermédiaire)
- Bug non reproductible en dev (cache local nettoyé)

**Résolution** :
- Documenter explicitement la convention de versioning
- Envisager automatisation (script de déploiement, hook git pre-commit, ou utiliser `SCRIPT_DEBUG` en dev)

---

### Hotspot #3 : `functions.php:19` — jQuery 1.12.4 CDN

**Sévérité** : HAUTE

**Raison** : version EOL, vulnérabilités documentées (CVE-2019-11358, CVE-2020-11022/11023), pas de SRI, dépendance externe sans fallback.

**Conséquences** :
- Exploitation XSS/prototype pollution via jQuery
- CDN inaccessible = panne JavaScript silencieuse
- CDN compromis = exécution de code malveillant sans détection

**Résolution** :
1. Montée jQuery vers 3.x avant production réelle
2. Ajouter SRI via filtre `script_loader_tag`
3. Héberger jQuery localement ou ajouter fallback

---

### Hotspot #4 : `footer.php:1` — copyright en dur

**Sévérité** : BASSE (maintenabilité, pas sécurité)

**Raison** : `&copy; Prox-i` ne peut pas être modifié depuis l'admin WordPress.

**Conséquences** :
- Changement d'entité ou mention légale exige une PR
- Non scalable pour un thème multi-client

**Contexte** :
- Pour un pilote, acceptable ; pour production multi-client, externaliser via les options WordPress serait une amélioration recommandée

---

### Hotspot #5 : `index.php` — gabarit unique

**Sévérité** : MOYENNE (limitation fonctionnelle, pas bug)

**Raison** : aucun `404.php`, `single.php`, `archive.php` — l'absence de différenciation 404/vide est une limitation volontaire pour un pilote.

**Conséquences** :
- URLs inexistantes affichent `<p>Aucun contenu.</p>` (même message que pages vides)
- Mauvais signal SEO, UX dégradée
- Pas de lien vers l'accueil en cas d'erreur

**Résolution** :
- Créer `404.php` avec message distinct (« Page introuvable ») et lien d'accueil
- Optionnel pour un pilote, prioritaire pour production

---

## Checklist de modification sûre

Avant de modifier ce thème :

- [ ] Lire `functions.php` en entier (24 lignes, < 5 min)
- [ ] Vérifier l'ordre de priorité des hooks WordPress si ajout d'un nouveau hook
- [ ] Si modification de `style.css` : **synchroniser la version** en `functions.php:13` (et `style.css:6`)
- [ ] Si modification de `functions.php:16-23` (jQuery) : tester la chaîne complète de désinscription/enqueue avec les plugins actifs
- [ ] Si addition d'une capacité (`add_theme_support`) : vérifier que le template correspondant la consomme
- [ ] Linter PHP sur le fichier modifié (`php -l functions.php`)
- [ ] Tester dans un navigateur (charger une page, vérifier CSS + jQuery dans les DevTools)

---

## Questions pour l'équipe technique

1. **Qui maintient ce thème ?** — pour savoir à qui alerter en cas de régression jQuery
2. **Y a-t-il un processus de déploiement documenté ?** — pour automatiser le versioning CSS ou les vérifications
3. **Des plugins jQuery (WooCommerce, CF7) sont-ils actifs ?** — pour évaluer le risque de priorité sur `wp_enqueue_scripts`
4. **Le site sera-t-il multilingue ?** — les templates supportent `language_attributes()` mais l'équipe doit fournir les .po files
5. **Un linter PHP est-il configuré pré-déploiement ?** — pour éviter les erreurs de syntaxe en production
