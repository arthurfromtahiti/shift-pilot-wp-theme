# Audit performance et SEO mensuel — août 2026

**Issue :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-327  
**Parent :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-318 (Cycle de maintenance mensuel)  
**Date :** 2026-08-06  
**SHA audité :** `d02aeab89e2d1d02337d87a47170950f19be4950` (origin/main)  
**Agent :** Mainteneur performance et SEO (15076aa9)  
**Confiance :** high pour le périmètre versionné (code lu intégralement) ; INCONNU pour toutes les métriques nécessitant un build servi

---

## En-tête

| Champ | Valeur |
|-------|--------|
| Projet | shift-pilot-wp-theme |
| URL du build mesuré | **INCONNUE** — site pilote sans URL publique documentée |
| SHA / version observée | `d02aeab` (origin/main) · version déployée : INCONNUE |
| Date | 2026-08-06 |
| Outils | code review (VÉRIFIÉ_CODE) ; Lighthouse : non exécutable (URL manquante) |
| Mode d'audit | COMPLET (perf + SEO) |

---

## Contrainte structurelle

Le site est un thème WordPress pilote déployé par FTP. **Aucune URL de staging ou de production n'est documentée ou accessible.** Cette contrainte est connue depuis le cycle précédent (SHI-284 — annulation demandée le 2026-08-06 précisément pour cette raison).

Conséquence directe : **toutes les métriques Lighthouse (LCP, CLS, TBT, TTFB, score Performance) sont à l'état `INCONNU`** pour ce cycle, conformément à la règle d'airain de `auditer-perf-seo` : « Si tu ne peux pas atteindre le build servi : statut INCONNU ». La demande d'URL reste ouverte (voir section Recommandations).

Ce rapport couvre ce qui peut être établi par lecture du code versionné uniquement (`VÉRIFIÉ_CODE`).

---

## Mesures de référence (« avant »)

### Labo (Lighthouse)

| Métrique | Valeur | Statut |
|----------|--------|--------|
| Score Performance mobile | — | `INCONNU` (URL inaccessible) |
| LCP labo mobile | — | `INCONNU` |
| CLS labo mobile | — | `INCONNU` |
| TBT labo mobile | — | `INCONNU` |
| TTFB labo mobile | — | `INCONNU` |
| Score Performance desktop | — | `INCONNU` |
| LCP labo desktop | — | `INCONNU` |
| CLS labo desktop | — | `INCONNU` |
| Poids de page total | — | `INCONNU` |

### Terrain (CrUX)

| Métrique | Valeur | Statut |
|----------|--------|--------|
| LCP p75 | — | `NON_DISPONIBLE` — URL non éligible CrUX |
| CLS p75 | — | `NON_DISPONIBLE` |
| INP p75 | — | `NON_DISPONIBLE` |

---

## Tableau de constats (VÉRIFIÉ_CODE)

| Axe | Constat | Sévérité | Méthode | Correctif proposé | Cible | État |
|-----|---------|----------|---------|-------------------|-------|------|
| perf | `strategy: defer` sur `slider.js` — `functions.php:14-20` | — | `VÉRIFIÉ_CODE` | déjà appliqué (SHI-269) | front | ✅ OK |
| perf | Dimensions `width="200" height="60"` sur le logo — `header.php:12` | — | `VÉRIFIÉ_CODE` | déjà appliqué (SHI-269) | front | ✅ OK |
| perf | Police système uniquement (`Georgia, serif`, `style.css:13`) — aucun chargement de police web | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| perf | CSS chargé via `wp_head()` — `functions.php:11`, ordre standard WordPress | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| perf | `logo.png` versionné : placeholder 1×1 px (69 octets) — logo réel FTP'd hors dépôt | faible | `VÉRIFIÉ_CODE` | logo de prod non observable ici ; confirmer avec déploiement réel | hors dépôt | `INCONNU` |
| perf | Version WordPress inconnue — `strategy: defer` requiert WP ≥ 6.3 | faible | `HYPOTHÈSE` | confirmer version WP réelle ; si < 6.3, defer ignoré | hors dépôt | `INCONNU` |
| seo | `add_theme_support('title-tag')` — `functions.php:8` → WordPress gère `<title>` | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `<meta name="viewport" content="width=device-width, initial-scale=1">` — `header.php:5` | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | H1 conditionnel : `<h1>` sur la page d'accueil (`is_front_page()`), `<h2>` sur les articles en homepage, `<h1>` sur les pages internes — `header.php:9-13`, `index.php:3-7` | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `alt` sur logo : `alt="<?php bloginfo('name'); ?>"` — `header.php:12` | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `alt` sur vignettes : `the_post_thumbnail('full', ['alt' => get_the_title()])` — `index.php:7` | — | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `meta description` : non présente dans le thème — assurée par plugin Yoast SEO (hors dépôt) | faible | `INCONNU` | vérifier Yoast actif et configuré en production | hors dépôt | `INCONNU` |
| seo | `canonical` : non présente dans le thème — assurée par Yoast SEO (hors dépôt) | faible | `INCONNU` | vérifier Yoast actif et configuré en production | hors dépôt | `INCONNU` |
| seo | `robots.txt` : non versionné — généré par WordPress/Yoast (hors dépôt) | moyenne | `INCONNU` | confirmer `robots.txt` servi correctement en production | hors dépôt | `INCONNU` |
| seo | `sitemap.xml` : non versionné — généré par Yoast SEO (hors dépôt) | moyenne | `INCONNU` | confirmer sitemap servi et à jour en production | hors dépôt | `INCONNU` |
| seo | Schema.org / Open Graph : non présents dans le thème — délégués à Yoast (hors dépôt) | faible | `INCONNU` | vérifier couverture Yoast en production | hors dépôt | `INCONNU` |

---

## Synthèse

**État de sortie : `REPORT_ONLY`**

Toutes les corrections perf/SEO identifiées lors des cycles précédents (SHI-73, SHI-269) sont en place dans le code versionné. Aucune action supplémentaire n'est applicable dans le périmètre thème versionné.

Les éléments non mesurables depuis le code (métriques Lighthouse, en-têtes HTTP, robots.txt, sitemap, Yoast) restent à l'état `INCONNU` ou `NON_DISPONIBLE`. Leur vérification requiert une URL de staging ou de production avec accès réseau.

**Delta depuis le cycle précédent (commits depuis la dernière analyse perf/SEO) :**

| Commit | Description | Impact perf/SEO |
|--------|-------------|-----------------|
| `d02aeab` | fix(perf) : dimensions logo + defer slider (SHI-269) | ✅ anti-CLS logo, defer slider.js effectif |
| `ff13774` | fix(seo) : viewport, alt vignette, slider.js via wp_enqueue_scripts (SHI-73) | ✅ viewport, alt, enqueue correct |
| `8fbfbbf` | docs(securite) : audit sécurité mensuel août 2026 — REPORT_ONLY (SHI-324) | — (documentation seule) |

Aucun commit entre ces trois n'introduit de régression perf/SEO dans le périmètre versionné (`VÉRIFIÉ_CODE`).

---

## Recommandations

### Immédiat (sans URL requise)

Aucune action de code immédiate. Tous les correctifs identifiables depuis le source sont appliqués.

### Conditionnel (nécessite URL ou accès production)

| Priorité | Action | Prérequis |
|----------|--------|-----------|
| Haute | Exécuter Lighthouse (5 runs mobile + 5 runs desktop) pour établir les métriques labo de référence | URL staging ou production accessible |
| Haute | Vérifier `robots.txt` servi (`GET /robots.txt`) et absence de `Disallow: /` involontaire | URL accessible |
| Haute | Vérifier `sitemap.xml` servi et référencé dans `robots.txt` | URL accessible |
| Moyenne | Confirmer Yoast SEO actif + `meta description` et `canonical` générés sur pages d'exemple | URL accessible + accès admin WP |
| Faible | Confirmer version WordPress ≥ 6.3 pour que `strategy: defer` soit effectif | Accès admin WP ou sonde d'état |
| Faible | Confirmer en-têtes de cache et compression (gzip/brotli) côté serveur | URL accessible (`curl -I`) |

---

## Manifeste machine-readable

```json
{
  "schema_version": "shift.perf-seo.v1",
  "audit_id": "SHIAAAAAAAAAAAAAAAAAAAAAAAA-327",
  "generated_at": "2026-08-06T00:00:00Z",
  "source_git_sha": "d02aeab89e2d1d02337d87a47170950f19be4950",
  "deployment_version": "INCONNUE",
  "page_url": "INCONNUE",
  "tool": "code-review (Lighthouse non exécutable — URL inaccessible)",
  "audit_mode": "COMPLET",
  "scope": {
    "seed_urls": ["INCONNUE"],
    "source": "code_review_only",
    "max_pages": 0,
    "included_locales": ["fr"],
    "excluded_patterns": []
  },
  "profiles": [
    {
      "name": "mobile",
      "runs": 0,
      "aggregation": "N/A",
      "lab_metrics": {
        "performance_score": null,
        "lcp_ms": null,
        "cls": null,
        "tbt_ms": null,
        "ttfb_ms": null,
        "transfer_size_bytes": null,
        "request_count": null,
        "js_transfer_size_bytes": null,
        "css_transfer_size_bytes": null,
        "status": "INCONNU"
      },
      "field_metrics": {
        "status": "NON_DISPONIBLE",
        "lcp_p75_ms": null,
        "cls_p75": null,
        "inp_p75_ms": null
      },
      "raw_artifacts": []
    },
    {
      "name": "desktop",
      "runs": 0,
      "aggregation": "N/A",
      "lab_metrics": {
        "performance_score": null,
        "lcp_ms": null,
        "cls": null,
        "tbt_ms": null,
        "ttfb_ms": null,
        "transfer_size_bytes": null,
        "request_count": null,
        "js_transfer_size_bytes": null,
        "css_transfer_size_bytes": null,
        "status": "INCONNU"
      },
      "field_metrics": {
        "status": "NON_DISPONIBLE",
        "lcp_p75_ms": null,
        "cls_p75": null,
        "inp_p75_ms": null
      },
      "raw_artifacts": []
    }
  ],
  "findings": [
    {
      "finding_id": "PERF-CODE-slider-defer",
      "check_type": "dom_attribute",
      "target_url": "functions.php:14-20",
      "target": "wp_enqueue_script strategy:defer in_footer:true",
      "observed": "strategy:defer,in_footer:true",
      "expected": "strategy:defer,in_footer:true",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-CODE-logo-dimensions",
      "check_type": "dom_attribute",
      "target_url": "header.php:12",
      "target": "img[width][height]",
      "observed": "width=200 height=60",
      "expected": "width=* height=*",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-viewport",
      "check_type": "dom_attribute",
      "target_url": "header.php:5",
      "target": "meta[name=viewport]",
      "observed": "content=width=device-width,initial-scale=1",
      "expected": "present",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-title-tag",
      "check_type": "dom_attribute",
      "target_url": "functions.php:8",
      "target": "add_theme_support(title-tag)",
      "observed": "present",
      "expected": "present",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-h1-conditionnel",
      "check_type": "dom_count",
      "target_url": "header.php:9-13 + index.php:3-7",
      "target": "h1",
      "observed": "conditionnel is_front_page() + page interne",
      "expected": "un seul h1 par page",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-alt-logo",
      "check_type": "dom_attribute",
      "target_url": "header.php:12",
      "target": "img[alt]",
      "observed": "alt=bloginfo(name)",
      "expected": "alt présent et pertinent",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-alt-thumbnail",
      "check_type": "dom_attribute",
      "target_url": "index.php:7",
      "target": "the_post_thumbnail alt",
      "observed": "alt=get_the_title()",
      "expected": "alt présent et pertinent",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-LIVE-robots-txt",
      "check_type": "robots_rule",
      "target_url": "INCONNUE/robots.txt",
      "observed": null,
      "expected": "Disallow: absent ou vide",
      "scope": "live",
      "method": "curl — non exécutable (URL inaccessible)",
      "status": "INCONNU",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-LIVE-sitemap",
      "check_type": "sitemap_entry",
      "target_url": "INCONNUE/sitemap.xml",
      "observed": null,
      "expected": "sitemap XML valide, référencé dans robots.txt",
      "scope": "live",
      "method": "curl — non exécutable (URL inaccessible)",
      "status": "INCONNU",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-LIVE-lighthouse-mobile",
      "check_type": "lighthouse_metric",
      "target_url": "INCONNUE",
      "observed": null,
      "expected": "score ≥ 80, LCP ≤ 2500 ms, CLS ≤ 0.1, TBT ≤ 300 ms",
      "scope": "labo",
      "method": "Lighthouse CLI — non exécutable (URL inaccessible)",
      "status": "INCONNU",
      "raw_artifacts": []
    }
  ],
  "lab_budget": {
    "status": "NON_ÉVALUABLE",
    "reason": "URL inaccessible — métriques labo non mesurées",
    "proposed_thresholds": {
      "performance_score_min": 80,
      "lcp_ms_max": 2500,
      "cls_max": 0.1,
      "tbt_ms_max": 300,
      "ttfb_ms_max": 600
    }
  },
  "field_budget": {
    "status": "NON_DISPONIBLE",
    "reason": "Pas de données CrUX — URL non éligible"
  },
  "final_status": "REPORT_ONLY"
}
```
