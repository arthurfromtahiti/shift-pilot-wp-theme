# Sécurité & Robustesse — Audit

> Confiance : high pour les constats locaux (code versionné lu intégralement) ; medium pour l'impact réel des vulnérabilités jQuery, dont la surface d'exploitation dépend des plugins actifs (hors dépôt).

## Compréhension globale

Le thème ne contient ni formulaire, ni authentification, ni route applicative propre — la surface d'attaque locale est structurellement faible. Le principal risque de sécurité est **externe** : jQuery 1.12.4 est chargé depuis un CDN tiers sans contrôle d'intégrité (`VÉRIFIÉ_CODE` : `functions.php:19`), et cette version est associée à des vulnérabilités connues dans les bases publiques (`CONNAISSANCE_EXTERNE` — non vérifiable depuis le dépôt seul). L'absence de CSP relève du hardening standard absent, non d'une vulnérabilité introduite par le thème.

## Résumé exécutif

Deux risques méritent une action avant mise en production : (1) l'absence de hash d'intégrité (SRI) sur la balise `<script>` jQuery injectée depuis `code.jquery.com` — si le CDN est compromis, du JavaScript arbitraire s'exécute sur toutes les pages sans aucune protection côté thème (`VÉRIFIÉ_CODE` : `functions.php:17-23`) ; (2) la version jQuery 1.12.4 est ancienne et associée à des vulnérabilités documentées pour XSS et prototype pollution dans les bases NVD/Mitre (`CONNAISSANCE_EXTERNE` — EOL et CVEs non vérifiables depuis le dépôt seul, exploitabilité sur ce site : `HYPOTHÈSE`). Le reste des constats (output WP correctement échappé, pas de secrets dans le code, pas de logique d'authentification dans le thème) est satisfaisant pour le périmètre versionné.

## Constats détaillés

**Absence de Subresource Integrity (SRI) sur jQuery CDN.** `functions.php:17-23` enregistre jQuery via `wp_enqueue_script()` vers l'URL `https://code.jquery.com/jquery-1.12.4.min.js` (`VÉRIFIÉ_CODE`). L'API native WordPress ne supporte pas nativement l'attribut `integrity` dans `wp_enqueue_script()` — il faudrait l'ajouter via un filtre `script_loader_tag`. Aucun tel filtre n'est présent dans le dépôt (`VÉRIFIÉ_CODE` : `functions.php` lu intégralement). Conséquence : si `code.jquery.com` est compromis ou si la réponse CDN est altérée en transit (attaque man-in-the-middle sur un réseau sans HSTS), le navigateur exécutera le script altéré sans pouvoir le détecter. La liaison HTTPS réduit le risque de transit, mais ne protège pas contre une compromission côté serveur CDN.

**jQuery 1.12.4 — version ancienne avec vulnérabilités connues.** `VÉRIFIÉ_CODE` : `functions.php:19` contient l'URL `https://code.jquery.com/jquery-1.12.4.min.js`, confirmant que la version 1.12.4 est chargée sur toutes les pages. `CONNAISSANCE_EXTERNE` (non vérifiable depuis le dépôt) : jQuery 1.x est en fin de vie et des CVE couvrant cette version sont documentés dans les bases NVD/Mitre — CVE-2019-11358 (prototype pollution via `$.extend(true, …)`, jQuery < 3.4.0), CVE-2020-11022 et CVE-2020-11023 (XSS via html() et manipulation DOM, jQuery < 3.5.0), CVE-2015-9251 (XSS cross-domain via Ajax, jQuery < 3.0). Que ces vecteurs soient exploitables sur ce site dépend des usages jQuery faits par les plugins actifs (WooCommerce, Contact Form 7 — hors dépôt) : le niveau de risque réel est une `HYPOTHÈSE`.

**Sortie WordPress correctement déléguée à l'API native.** `the_content()` (`index.php:6`), `the_title()` (`index.php:5`), `bloginfo('name')` (`header.php:8`), `bloginfo('charset')` (`header.php:4`), `language_attributes()` (`header.php:2`), `body_class()` (`header.php:7`), `post_class()` (`index.php:4`) — toutes sont des fonctions WordPress natives qui appliquent leur propre échappement ou filtrage (`VÉRIFIÉ_CODE`). Le thème n'effectue aucun `echo` direct de données non filtrées. Il n'y a pas de concaténation de chaînes dans les templates, pas de `$_GET`/`$_POST`/`$_SERVER` utilisés (`VÉRIFIÉ_CODE` — tous les fichiers PHP lus intégralement). Ce point est une force.

**Aucun secret dans le code.** Aucune clé d'API, token, mot de passe ou credential n'est présent dans les fichiers versionnés (`VÉRIFIÉ_CODE` — tous les fichiers PHP/CSS lus). La seule URL externe est `https://code.jquery.com/jquery-1.12.4.min.js` (`functions.php:19`), qui est publique.

**`wp_deregister_script('jquery')` — risque d'ordre d'exécution.** `functions.php:16` désactive le jQuery bundlé WordPress avant de le remplacer par la version CDN (`VÉRIFIÉ_CODE`). Si un plugin enregistré avec une priorité inférieure à 10 sur `wp_enqueue_scripts` avait déjà requis jQuery, la désinscription l'en prive. Cela peut provoquer des erreurs JavaScript silencieuses pour les plugins concernés. Impact : `HYPOTHÈSE` (ordre d'exécution des callbacks plugins inconnu, plugins hors dépôt). Ce n'est pas un risque de sécurité direct, mais un risque de robustesse fonctionnelle.

## Forces

- **Aucun traitement de données utilisateur dans le thème** : pas de formulaire, pas de `$_POST`, pas d'endpoint AJAX propre (`VÉRIFIÉ_CODE`). La surface d'attaque est structurellement limitée au rendu HTML.
- **Toutes les sorties passent par l'API WordPress** : aucun `echo` brut de variable non échappée (`VÉRIFIÉ_CODE`).
- **Aucun secret en clair** : le code ne contient ni token, ni clé, ni mot de passe (`VÉRIFIÉ_CODE`).

## Dettes techniques

- **jQuery 1.12.4** : version ancienne avec vulnérabilités documentées dans les bases NVD/Mitre (`CONNAISSANCE_EXTERNE`). Fichier : `functions.php:19`.
- **Absence de SRI** : aucun hash d'intégrité sur la ressource CDN externe. Fichier : `functions.php:17-23`.

## Zones critiques

- **`functions.php:17-23` — chargement CDN sans SRI** : c'est le seul point où une ressource externe est introduite dans toutes les pages. Un senior examinerait ici en priorité lors d'un audit sécurité, avant même de regarder les templates.

## Risques

- **Compromission de CDN sans SRI** : si `code.jquery.com` est compromis, tous les visiteurs du site exécutent le script malveillant. Aucune protection côté thème. Scénario : `VÉRIFIÉ_CODE` pour l'absence de SRI (`functions.php:17-23`) ; probabilité de compromission CDN : faible mais non nulle.
- **Exploitation de vulnérabilités jQuery connues** : des CVE couvrant la version 1.12.4 sont documentés dans les bases NVD/Mitre (`CONNAISSANCE_EXTERNE`). Leur exploitabilité dépend des usages faits par les plugins actifs (WooCommerce, Contact Form 7 — hors dépôt) : impact réel `HYPOTHÈSE`. Sévérité potentielle si exploités : XSS réfléchi ou stocké.

## Recommandations priorisées

1. **Mettre à jour jQuery vers 3.x** — migrer de `1.12.4` à la dernière version stable `3.x` pour éliminer les CVE documentés. Fichier : `functions.php:19`. La version CDN à utiliser est disponible sur `code.jquery.com`. Vérifier la compatibilité avec WooCommerce, Contact Form 7 et Yoast SEO (hors dépôt) avant déploiement.
2. **Ajouter un hash SRI sur la balise `<script>` jQuery** — via le filtre WordPress `script_loader_tag` dans `functions.php`, ajouter `integrity="sha384-..."` et `crossorigin="anonymous"` sur la balise émise. Le hash SRI de la version cible devra être calculé ou obtenu depuis les outils officiels de génération (non vérifiable depuis le dépôt). Cela protège contre la compromission CDN.
3. **Évaluer l'opportunité d'un CDN local ou bundlé** — si la disponibilité du CDN et la conformité des politiques de confidentialité (RGPD — chargement d'une ressource depuis un serveur tiers américain) sont des contraintes, envisager de versionner jQuery localement dans le thème.

## Questions ouvertes

- Le message de commit `5d9b462` mentionne « jQuery CDN épinglé 1.12.4 » (`VÉRIFIÉ_CODE` — git log). S'agit-il d'un choix délibéré de long terme ou d'une dette temporaire du pilote ?
- Un plugin de sécurité WordPress (Wordfence, iThemes Security, etc.) est-il actif sur l'installation ? S'il définit une CSP ou des en-têtes de sécurité, cela complèterait partiellement les lacunes du thème (INCONNU — hors dépôt).
- L'hébergement dispose-t-il d'un CDN ou d'un proxy intermédiaire (Cloudflare, etc.) capable d'injecter des en-têtes de sécurité ? Si oui, certaines mitigations pourraient être déjà en place sans modification du thème.
