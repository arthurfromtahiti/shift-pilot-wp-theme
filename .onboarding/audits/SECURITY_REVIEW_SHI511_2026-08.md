# Revue sécurité thème PHP/JS — août 2026

**Issue :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-511  
**Date :** 2026-08-08  
**SHA audité :** 0743d8966e2c12e2ebdd79924ba9deee65487be3 (HEAD, branch fix/SHIAAAAAAAAAAAAAAAAAAAAAAAA-424-h1-archive-pages)  
**Agent :** Mainteneur sécurité (c5300d69)  
**Confiance :** high pour tout le périmètre versionné (code lu intégralement)

---

## Synthèse exécutive

Le thème présente une surface d'attaque structurellement réduite : pas de formulaire, pas d'endpoint AJAX, pas de logique d'authentification, aucune dépendance externe, aucun secret exposé. Deux écarts aux WordPress Coding Standards (WPCS) sont identifiés sur l'échappement des sorties en contexte HTML (`the_title()` et `bloginfo('name')`) — sévérité faible, exploitabilité très basse (accès éditeur/admin requis). Ces écarts ne s'appliquent pas dans les bornes de la maintenance légère ; ils sont remontés pour arbitrage en production.

**État de sortie : REPORT_ONLY** — aucune correction applicable proprement dans les bornes de la maintenance légère. Deux points à remonter en SHIFT Production si correction souhaitée.

---

## Périmètre audité

Fichiers versionnés lus intégralement (`VÉRIFIÉ_CODE`) :
- `functions.php`
- `header.php`
- `footer.php`
- `index.php`
- `assets/slider.js`
- `style.css`

Historique git complet parcouru pour les secrets dans les commits passés.

**Non couvert (explicitement) :**
- Cœur WordPress et plugins (gérés hors dépôt via FTP) — état inconnu en production
- En-têtes HTTP réellement servis (non mesurables depuis le dépôt seul)
- Test d'intrusion actif, audit d'infrastructure réseau, certification de conformité

---

## Tableau de constats

| Type | Constat (`VÉRIFIÉ_CODE`) | Sévérité | Correctif proposé | Cible | Statut |
|------|--------------------------|----------|-------------------|-------|--------|
| dépendance | Aucun gestionnaire de paquets (composer.json / package.json absents) — pas de lockfile, maintenance de dépendances non applicable | — | aucun, état normal | — | REPORT_ONLY |
| secret | Historique git complet : aucun secret commité (`grep` sur patterns password/key/token/auth) | — | aucun | — | OK |
| code | `functions.php` : `wp_enqueue_style` + `wp_enqueue_script` (slider.js, defer, in_footer) — pas de ressource CDN externe | — | aucun | front | OK |
| code | `header.php` : `esc_url()` sur href et src, `esc_attr()` sur l'alt du logo (depuis commit `1e96cf2`) | — | aucun | front | OK |
| code | `footer.php` : aucune sortie dynamique non contrôlée | — | aucun | front | OK |
| code | `assets/slider.js` : vanilla JS, `classList`/`querySelectorAll` uniquement, pas de `innerHTML`, `eval()`, `document.write()`, pas d'appel réseau | — | aucun | front | OK |
| **code** | **`header.php:11,13` — `bloginfo('name')` dans contexte HTML sans `esc_html()`** | **faible** | `esc_html(get_bloginfo('name'))` — WPCS `WordPress.Security.EscapeOutput` | front | NEEDS_PRODUCTION_SHIFT |
| **code** | **`index.php:6,8` — `the_title()` dans contexte HTML sans `esc_html()`** | **faible** | `esc_html(get_the_title())` — WPCS `WordPress.Security.EscapeOutput` | front | NEEDS_PRODUCTION_SHIFT |
| config | En-têtes de sécurité (CSP, X-Frame-Options, HSTS) : hors portée du thème, relèvent du serveur/plugin WP | faible | vérification côté hébergeur recommandée (déjà noté en SHI-324) | serveur | REPORT_ONLY |

---

## Analyse détaillée des deux écarts identifiés

### SEC-ESC-bloginfo-name-html — `bloginfo('name')` sans `esc_html()` (faible)

**Localisation** : `header.php:11` (`<h1>`) et `header.php:13` (texte du lien `<a>`).

**Comportement observé** (`VÉRIFIÉ_CODE`) : `bloginfo('name')` appelle `get_bloginfo('name', 'display')` qui applique `wptexturize()` et les filtres `bloginfo` — mais **pas** `esc_html()`. Le nom du blog est stocké via `update_option('blogname')` ; `sanitize_option('blogname')` applique `wp_kses_no_null()` (suppression des octets nuls), **non** `strip_tags()`. Des balises HTML sont donc techniquement persistables dans la base.

**Exploitabilité** : très basse. Seul un administrateur WordPress peut modifier le nom du site. Un administrateur compromis dispose d'accès bien plus larges (installation de plugin, création d'utilisateur). Ce scénario constitue un XSS stocké par un utilisateur privilégié — catégorie admise par WordPress comme hors-périmètre de défense du thème (`wp_kses_post` ne couvre pas cette option).

**Correctif WPCS** : `esc_html(get_bloginfo('name'))` — deux occurrences dans `header.php`.

**Note sur le cycle précédent** : l'audit SHIAAAAAAAAAAAAAAAAAAAAAAAA-324 (2026-08-06) avait qualifié `bloginfo('name')` de « correctement échappé » car utilisé via l'API WP native. C'est techniquement inexact au sens strict des WPCS : l'API WP native ne garantit pas `esc_html()` pour toutes les fonctions d'affichage — seules les fonctions prefixées `esc_*` ou `wp_kses_*` le garantissent. Ce point n'invalide pas l'état global du thème (exploitabilité très basse) mais est noté pour cohérence.

---

### SEC-ESC-the-title-html — `the_title()` sans `esc_html()` (faible)

**Localisation** : `index.php:6` (`<h2>`) et `index.php:8` (`<h1>`).

**Comportement observé** (`VÉRIFIÉ_CODE`) : `the_title()` appelle `get_the_title()` qui applique `wptexturize()`, `convert_chars()`, `capital_P_dangit()` et le filtre `the_title` — mais **pas** `esc_html()`. Les titres de publication peuvent contenir du HTML en fonction du rôle de l'auteur (les éditeurs et administrateurs ne sont pas soumis à KSES pour les titres).

**Exploitabilité** : basse. Nécessite un compte éditeur ou administrateur compromis pour insérer du HTML dans un titre. Un éditeur avec accès pourrait insérer `<img src=x onerror=…>` pour un XSS stocké ciblant les autres utilisateurs connectés. C'est un vecteur réel mais à probabilité très faible sur un site pilote à accès contrôlé.

**Correctif WPCS** : `esc_html(get_the_title())` — deux occurrences dans `index.php`. Note : `the_post_thumbnail('full', ['alt' => get_the_title()])` est safe car `the_post_thumbnail()` applique `esc_attr()` en interne sur l'alt — pas d'action requise ici.

---

## Delta depuis l'audit précédent (SHIAAAAAAAAAAAAAAAAAAAAAAAA-324, 2026-08-06)

| Commit | Description | Impact sécurité |
|--------|-------------|-----------------|
| `1e96cf2` / `595ec93` | `esc_attr()` sur l'alt du logo (`header.php:15`) — fix SHIAAAAAAAAAAAAAAAAAAAAAAAA-362 | ✅ Echappement attribut HTML corrigé |
| `e12cd17` | Requires at least 6.3 (compatibilité) | ✅ Neutre sécurité |
| `c98dcd5` | `is_front_page()` → `!is_singular()` (a11y fix) | ✅ Neutre sécurité |
| `0743d89` | Mise à jour doc onboarding | ✅ Neutre sécurité |

La recommandation sur l'alt du logo (implicite dans l'audit précédent) a bien été appliquée.

---

## Ce qui reste OK (confirmé sur ce SHA)

- **Aucun secret** : ni dans les fichiers courants, ni dans l'historique git (`VÉRIFIÉ_CODE`).
- **Aucune dépendance externe** : zéro CDN, zéro bibliothèque tierce (`VÉRIFIÉ_CODE` : `functions.php`).
- **Surface d'attaque minimale** : pas de formulaire, pas d'endpoint AJAX, pas d'authentification dans le thème (`VÉRIFIÉ_CODE`).
- **slider.js propre** : vanilla JS pur, pas de `innerHTML`, `eval()`, `document.write()`, pas d'appel réseau (`VÉRIFIÉ_CODE`).
- **URLs correctement échappées** : `esc_url()` sur `home_url('/')` et `get_template_directory_uri()`, `esc_attr()` sur l'alt du logo (`VÉRIFIÉ_CODE`).

---

## Couverture non assurée par cette revue

Cet audit ne couvre pas : test d'intrusion actif, ingénierie sociale, audit d'infrastructure réseau, certification de conformité. Le thème ne manipule pas de données de paiement ou de santé — pas d'audit humain spécialisé requis sur le périmètre constaté.

---

## Conclusion

Le thème est dans un état sécuritaire globalement satisfaisant. Les deux écarts WPCS identifiés (sévérité faible) ne sont pas corrigeables proprement dans les bornes de la maintenance légère (modifications de code PHP hors liste des ajustements autorisés). Ils sont documentés pour une éventuelle correction en SHIFT Production.

**État final : REPORT_ONLY**  
**Corrections optionnelles à remonter en production :** `esc_html(get_bloginfo('name'))` × 2 dans `header.php`, `esc_html(get_the_title())` × 2 dans `index.php`.
