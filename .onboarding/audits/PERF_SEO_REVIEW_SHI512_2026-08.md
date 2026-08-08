# Revue performance et SEO des assets — août 2026

**Issue :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-512  
**Parent :** SHIAAAAAAAAAAAAAAAAAAAAAAAA-508 (Revue de maintenance — compatibilité et sécurité)  
**Date :** 2026-08-08  
**SHA audité :** `da64fcf35346e7e3c8a970f11ce245957ca29ae7` (origin/main)  
**Agent :** Mainteneur performance et SEO (15076aa9)  
**Confiance :** high pour le périmètre versionné (code lu intégralement) ; INCONNU pour toutes les métriques nécessitant un build servi

---

## En-tête

| Champ | Valeur |
|-------|--------|
| Projet | shift-pilot-wp-theme (`T-MAINT-WP · theme`) |
| URL du build mesuré | **INCONNUE** — site pilote sans URL publique documentée |
| SHA / version observée | `da64fcf` (origin/main) · version déployée : INCONNUE |
| Date | 2026-08-08 |
| Outils | code review (VÉRIFIÉ_CODE) ; Lighthouse : non exécutable (URL inaccessible) |
| Mode d'audit | COMPLET (perf + SEO) |

---

## Contrainte structurelle

Le site est un thème WordPress pilote déployé par FTP. **Aucune URL de staging ou de production n'est documentée ou accessible.** Cette contrainte est connue et inchangée depuis SHI-284 (annulation demandée 2026-08-06).

Conséquence : **toutes les métriques Lighthouse (LCP, CLS, TBT, TTFB, score Performance) sont à l'état `INCONNU`**, conformément à la règle d'airain du skill `auditer-perf-seo`. Ce rapport couvre ce qui peut être établi par lecture du code versionné (`VÉRIFIÉ_CODE`).

---

## Mesures de référence

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

| Axe | Constat | Sévérité | Impact estimé | Méthode | Correctif proposé | Cible | État |
|-----|---------|----------|---------------|---------|-------------------|-------|------|
| perf | `slider.js` — 487 octets, 14 lignes, non-minifié, IIFE + DOMContentLoaded. Chargé avec `strategy: 'defer'` + `in_footer: true` (`functions.php:14-20`) → aucun JS render-blocking | — | nul (taille négligeable) | `VÉRIFIÉ_CODE` | aucun — pas de pipeline de build ; taille < 500 oct. rend la minification sans bénéfice mesurable | front | ✅ OK |
| perf | `logo.png` versionné — 69 octets (placeholder 1×1 px) ; logo réel déployé par FTP hors dépôt | faible | INCONNU (logo réel non mesurable) | `VÉRIFIÉ_CODE` | logo de prod non observable depuis le dépôt | hors dépôt | `INCONNU` |
| perf | Dimensions logo déclarées `width="200" height="60"` (`header.php:15`) → anti-CLS | — | prévient CLS | `VÉRIFIÉ_CODE` | déjà appliqué (SHI-269) | front | ✅ OK |
| perf | `style.css` — 424 octets total (header WordPress inclus), ~100 oct. de règles CSS effectives, polices système uniquement (`Georgia, serif`). Chargé via `wp_head()` en `<head>` (comportement WordPress standard) | — | nul | `VÉRIFIÉ_CODE` | aucun — CSS minimal, pas de font web bloquante | front | ✅ OK |
| perf | `Requires at least: 6.3` dans `style.css:7` (commit `e12cd17`) — version WP formellement documentée. Garantit que `strategy: defer` sera effectif (passe de `HYPOTHÈSE` SHI-327 à `VÉRIFIÉ_CODE`) | — | positif | `VÉRIFIÉ_CODE` | déjà appliqué (SHI-373) | code | ✅ OK |
| perf | Pas de pipeline de build détecté (pas de `package.json`, `webpack.config.js`, `.babelrc`) — assets servis tels quels. Acceptable compte tenu du poids total : slider.js 487 oct. + style.css 424 oct. | faible | négligeable | `VÉRIFIÉ_CODE` | NEEDS_PRODUCTION_SHIFT si pipeline requis | — | `REPORT_ONLY` |
| seo | `add_theme_support('title-tag')` — `functions.php:8` → WordPress gère `<title>` | — | nul | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `<meta name="viewport" content="width=device-width, initial-scale=1">` — `header.php:5` | — | nul | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `language_attributes()` dans `<html>` — `header.php:2` → attribut `lang` présent | — | nul | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | Hiérarchie H1 : `<h1>` dans `<header>` sur `is_front_page()`, `<h2>` pour le titre article en homepage, `<h1>` pour le titre article sur pages internes (`header.php:9-14`, `index.php:5-8`) | — | nul | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `alt` logo — `esc_attr(get_bloginfo('name'))` — `header.php:15` ; présent et pertinent | — | nul | `VÉRIFIÉ_CODE` | déjà appliqué (SHI-362) | front | ✅ OK |
| seo | `alt` vignettes — `the_post_thumbnail('full', ['alt' => get_the_title()])` — `index.php:11` ; présent et pertinent | — | nul | `VÉRIFIÉ_CODE` | aucun | front | ✅ OK |
| seo | `meta description` : absente du thème — assurée par Yoast SEO (hors dépôt) | faible | `INCONNU` | `INCONNU` | vérifier Yoast actif et configuré | hors dépôt | `INCONNU` |
| seo | `canonical` : absent du thème — assuré par Yoast SEO (hors dépôt) | faible | `INCONNU` | `INCONNU` | vérifier Yoast actif | hors dépôt | `INCONNU` |
| seo | `robots.txt` : non versionné — généré par WordPress/Yoast (hors dépôt) | moyenne | `INCONNU` | URL inaccessible | confirmer `robots.txt` servi correctement | hors dépôt | `INCONNU` |
| seo | `sitemap.xml` : non versionné — généré par Yoast SEO (hors dépôt) | moyenne | `INCONNU` | URL inaccessible | confirmer sitemap servi et à jour | hors dépôt | `INCONNU` |

---

## Delta depuis SHI-327 (SHA `d02aeab` → `da64fcf`)

| Commit | Description | Impact perf/SEO |
|--------|-------------|-----------------|
| `1e96cf2` | fix(security): `esc_attr()` sur alt logo (SHI-362) | Aucune régression — alt toujours présent et pertinent (`VÉRIFIÉ_CODE`) |
| `e12cd17` | fix(compat): `Requires at least: 5.9 → 6.3` (SHI-373) | Positif — `strategy: defer` désormais formellement garantie par le code versionné (`HYPOTHÈSE SHI-327 → VÉRIFIÉ_CODE`) |
| `da64fcf` | Merge PR #12 | Merge commit — pas d'impact direct |

Aucun commit entre ces trois n'introduit de régression perf/SEO dans le périmètre versionné.

---

## Synthèse

**État de sortie : `REPORT_ONLY`**

Tous les contrôles identifiables depuis le source versionné sont OK ou portent un statut `INCONNU` justifié par l'absence d'URL accessible. Aucune correction n'est applicable dans le périmètre thème versionné.

Le delta depuis SHI-327 est positif : la version WP minimale est maintenant formellement documentée à `6.3`, ce qui résout le point `HYPOTHÈSE` sur l'effectivité de `strategy: defer`.

Les éléments non mesurables (métriques Lighthouse, en-têtes HTTP, robots.txt, sitemap, Yoast) restent `INCONNU` ou `NON_DISPONIBLE`. Leur vérification requiert une URL de staging ou de production.

---

## Recommandations

### Sans URL requise

Aucune action de code supplémentaire. Tous les correctifs identifiables depuis le source sont en place.

### Conditionnelles (nécessitent URL ou accès production)

| Priorité | Action | Prérequis |
|----------|--------|-----------|
| Haute | Exécuter Lighthouse (5 runs mobile + 5 runs desktop) pour établir les métriques labo de référence | URL staging ou production accessible |
| Haute | Vérifier `robots.txt` servi (`GET /robots.txt`) et absence de `Disallow: /` involontaire | URL accessible |
| Haute | Vérifier `sitemap.xml` servi et référencé dans `robots.txt` | URL accessible |
| Moyenne | Confirmer Yoast SEO actif + `meta description` et `canonical` générés | URL accessible + accès admin WP |
| Faible | Confirmer en-têtes de cache et compression (gzip/brotli) côté serveur | URL accessible (`curl -I`) |
| Faible | Évaluer `fetchpriority="high"` sur le logo (`header.php:15`) si le logo réel est le LCP candidat | URL accessible + Lighthouse |

---

## Manifeste machine-readable

```json
{
  "schema_version": "shift.perf-seo.v1",
  "audit_id": "SHIAAAAAAAAAAAAAAAAAAAAAAAA-512",
  "generated_at": "2026-08-08T00:00:00Z",
  "source_git_sha": "da64fcf35346e7e3c8a970f11ce245957ca29ae7",
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
      "observed": "strategy:defer, in_footer:true",
      "expected": "strategy:defer, in_footer:true",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-CODE-slider-size",
      "check_type": "resource_weight",
      "resource_url": "assets/slider.js",
      "metric": "file_size_bytes",
      "before": 487,
      "scope": "code",
      "method": "VÉRIFIÉ_CODE — wc -c",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-CODE-logo-placeholder",
      "check_type": "resource_weight",
      "resource_url": "assets/logo.png",
      "metric": "file_size_bytes",
      "before": 69,
      "scope": "code",
      "method": "VÉRIFIÉ_CODE — placeholder 1x1px, logo réel hors dépôt",
      "status": "INCONNU",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-CODE-logo-dimensions",
      "check_type": "dom_attribute",
      "target_url": "header.php:15",
      "target": "img[width][height]",
      "observed": "width=200 height=60",
      "expected": "width=* height=*",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "PERF-CODE-wp-version-defer",
      "check_type": "dom_attribute",
      "target_url": "style.css:7",
      "target": "Requires at least",
      "observed": "6.3",
      "expected": ">=6.3 (pour strategy:defer)",
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
      "target_url": "header.php:9-14 + index.php:5-8",
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
      "target_url": "header.php:15",
      "target": "img[alt]",
      "observed": "alt=esc_attr(get_bloginfo('name'))",
      "expected": "alt présent et pertinent",
      "scope": "code",
      "method": "VÉRIFIÉ_CODE",
      "status": "OK",
      "raw_artifacts": []
    },
    {
      "finding_id": "SEO-CODE-alt-thumbnail",
      "check_type": "dom_attribute",
      "target_url": "index.php:11",
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
      "expected": "score >= 80, LCP <= 2500 ms, CLS <= 0.1, TBT <= 300 ms",
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
