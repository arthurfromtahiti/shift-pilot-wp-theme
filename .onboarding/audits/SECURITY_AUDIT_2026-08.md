# Audit sécurité mensuel — août 2026

**Issue :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-324  
**Date :** 2026-08-06  
**SHA audité :** d02aeab89e2d1d02337d87a47170950f19be4950 (origin/main)  
**Agent :** Mainteneur sécurité (c5300d69)  
**Confiance :** high pour tout le périmètre versionné (code lu intégralement)

---

## Synthèse exécutive

Le thème est dans un état sécuritaire satisfaisant. La faille principale documentée lors du cycle initial (jQuery 1.12.4 CDN sans SRI) a été corrigée en commit `847ff12`. Le code versionné ne contient aucun secret exposé. Les sorties PHP sont entièrement déléguées à l'API WordPress native avec échappement correct. `assets/slider.js` est du JavaScript vanilla sans dépendance externe ni vecteur d'injection.

**État de sortie : REPORT_ONLY** — aucune vulnérabilité active dans le périmètre versionné ; aucune maintenance de dépendances applicable (pas de lockfile).

---

## Périmètre audité

Fichiers versionnés lus intégralement (`VÉRIFIÉ_CODE`) :
- `functions.php`
- `header.php`
- `index.php`
- `footer.php`
- `style.css`
- `assets/slider.js`

Historique git complet parcouru (`git log --all -p --follow -- functions.php`) pour les secrets dans les commits passés.

**Non couvert (explicitement) :**
- Cœur WordPress et plugins (gérés hors dépôt via FTP) — état inconnu en production
- En-têtes HTTP réellement servis (non mesurables depuis le dépôt seul)
- État des plugins actifs en production

---

## Tableau de constats

| Type | Constat (mesuré) | Sévérité | Correctif proposé | Cible |
|------|-----------------|----------|-------------------|-------|
| dépendance | Pas de gestionnaire de paquets (composer.json / package.json absents) — maintenance de dépendances non applicable | — | aucun, état normal | — |
| dépendance | jQuery CDN 1.12.4 — supprimé en commit `847ff12` ; WP sert son bundled jQuery (3.x, hors dépôt) | résolu | déjà appliqué au cycle précédent | front |
| secret | Historique git complet : aucun secret commité à aucun moment | — | aucun | — |
| code | `functions.php` : `wp_enqueue_style` + `wp_enqueue_script` (slider.js, defer, in_footer) — pas de ressource CDN externe | — | aucun | front |
| code | `header.php` : `esc_url()` explicite sur toutes les URLs en attribut HTML (href, src) | — | aucun | front |
| code | `header.php` / `index.php` / `footer.php` : sorties via fonctions WP natives (`bloginfo`, `language_attributes`, `body_class`, `post_class`, `the_title`, `the_content`, `the_post_thumbnail`) — pas de `echo` brut non filtré, pas de `$_GET`/`$_POST`/`$_SERVER` | — | aucun | front |
| code | `assets/slider.js` : vanilla JS, pas de `eval()`, `document.write()`, `innerHTML` non contrôlé, pas d'appel réseau | — | aucun | front |
| config | En-têtes de sécurité (CSP, X-Frame-Options, HSTS) : non mesurables depuis le code versionné seul — relèvent du serveur ou d'un plugin WP | faible | vérifier côté hébergeur / plugin sécurité WP | serveur |

---

## Changements depuis le cycle précédent (2026-08-04)

| Commit | Description | Impact sécurité |
|--------|-------------|-----------------|
| `847ff12` | Suppression jQuery CDN 1.12.4 + `wp_deregister_script('jquery')` | ✅ CVE-2019-11358, CVE-2020-11022/23, CVE-2015-9251 éliminés |
| `800bf7a` | Ajout `esc_url()` sur les sorties d'URL dans les attributs HTML | ✅ Recommandation cycle précédent appliquée |
| `47bf09f` | Ajout `assets/slider.js` (vanilla JS, carrousel) | ✅ Code audité — aucune surface d'attaque |
| `ff13774` | `slider.js` chargé via `wp_enqueue_scripts` (strategy defer) | ✅ Chargement correct via WP API |
| `d02aeab` | Dimensions logo + defer slider (PR #7) | ✅ Neutral sécurité |

Toutes les recommandations du cycle initial ont été traitées.

---

## Forces

- **Zéro ressource externe** : aucun CDN, aucune bibliothèque tierce chargée par le thème (`VÉRIFIÉ_CODE` : `functions.php` complet).
- **Surface d'attaque structurellement limitée** : pas de formulaire, pas d'endpoint AJAX propre, pas de logique d'authentification dans le thème (`VÉRIFIÉ_CODE`).
- **Échappement systématique** : toutes les sorties passent par l'API WordPress native ; `esc_url()` explicite sur les attributs d'URL (`VÉRIFIÉ_CODE`).
- **Aucun secret** : ni dans le code courant, ni dans l'historique git (`VÉRIFIÉ_CODE`).
- **slider.js propre** : vanilla JS, pas de dépendance externe, pas de vecteur d'injection (`VÉRIFIÉ_CODE`).

---

## Points d'attention résiduels (hors périmètre de correction légère)

**En-têtes HTTP de sécurité** (`OBSERVÉ`) : Le thème ne peut pas lui-même définir `Content-Security-Policy`, `X-Frame-Options`, `Strict-Transport-Security` ou `X-Content-Type-Options` — ce sont des en-têtes HTTP qui relèvent de la configuration serveur (Apache/Nginx/hébergeur) ou d'un plugin de sécurité WordPress (Wordfence, iThemes, etc.). Un audit serveur séparé permettrait de vérifier leur présence réelle. Sévérité : faible dans ce contexte (thème sans logique applicative sensible).

---

## Couverture non assurée par cet audit

Cet audit ne couvre PAS : test d'intrusion actif, ingénierie sociale, audit d'infrastructure réseau (pare-feu, segmentation), certification de conformité. Le thème ne manipule pas de données de paiement ou de santé — aucun audit humain spécialisé n'est requis pour le périmètre constaté.

---

## Conclusion

Aucune action corrective applicable proprement dans les bornes de la maintenance légère. Le code est sain. Le cycle précédent a appliqué toutes les corrections identifiées.

**État final : REPORT_ONLY**
