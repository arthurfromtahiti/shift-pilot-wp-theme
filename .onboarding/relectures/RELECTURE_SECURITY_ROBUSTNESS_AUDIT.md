# Relecture — SECURITY_ROBUSTNESS_AUDIT.md

## Verdict global
À corriger — l'audit a bien requalifié les vulnérabilités jQuery en dépendances externes, mais il reste bloqué par un écart de méthode sur les statuts de preuve. Le socle n'autorise que `OBSERVÉ` / `VÉRIFIÉ_CODE` / `HYPOTHÈSE` / `INCONNU`, or l'artefact introduit `CONNAISSANCE_EXTERNE` comme cinquième statut et s'en sert pour des constats centraux.

## Problèmes bloquants
- `SECURITY_ROBUSTNESS_AUDIT.md:7,11,17,33,43` utilise `CONNAISSANCE_EXTERNE`, alors que la compétence de relecture et `socle-agence` n'autorisent que quatre statuts de preuve. Preuve de la règle : [SKILL.md](/paperclip/instances/default/companies/be2f6065-a710-4a1d-8bb7-531efdbc6f23/codex-home/skills/relire-audits--08252f2658/SKILL.md:21) et [SKILL.md](/paperclip/instances/default/companies/be2f6065-a710-4a1d-8bb7-531efdbc6f23/codex-home/skills/socle-agence--26c2c67045/SKILL.md:24). Tant que ces faits externes ne sont pas rabattus en `HYPOTHÈSE`/`INCONNU` ou accompagnés d'une qualification compatible avec le socle, le verdict ne peut pas passer.

## Problèmes mineurs
- `SECURITY_ROBUSTNESS_AUDIT.md:15` affirme que `wp_enqueue_script()` ne supporte pas nativement `integrity` et recommande `script_loader_tag`, mais cette connaissance n'est ni observable dans le dépôt ni sourcée vers une source amont. La recommandation peut rester, mais elle doit être explicitement qualifiée comme connaissance externe ou accompagnée d'une source amont.
- `SECURITY_ROBUSTNESS_AUDIT.md:47-48` recommande de migrer vers la "dernière version stable 3.x" et d'obtenir le hash SRI depuis des "outils officiels", sans source précise. Ce n'est pas bloquant pour le diagnostic, mais la recommandation gagnerait à citer la source amont consultée ou à rester plus générique.

## Points vérifiés et corrects
- La preuve locale du chargement CDN jQuery est correcte et bien sourcée : [functions.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/functions.php:16).
- Le commit `5d9b462` est bien traçable dans l'historique git avec le message cité par l'audit. Preuve de contrôle : `git log --oneline --grep='jQuery CDN épinglé 1.12.4'` relu pendant cette relecture.
- La distinction entre preuve locale (`functions.php`) et exploitabilité réelle dépendante des plugins hors dépôt est globalement mieux tenue qu'au tour précédent.

## Recommandations de correction
- Remplacer chaque `CONNAISSANCE_EXTERNE` par un statut admis par le socle, en explicitant la limite de preuve. Le plus simple ici est de garder `VÉRIFIÉ_CODE` pour la présence de `jquery-1.12.4` dans [functions.php](/paperclip/instances/default/projects/be2f6065-a710-4a1d-8bb7-531efdbc6f23/805323cb-4ff1-481e-bc7e-05f597695c48/shift-pilot-wp-theme/functions.php:19), puis de rabattre le reste en `HYPOTHÈSE` ou `INCONNU` selon le cas.
- Si tu conserves des faits externes précis (CVE, EOL, support SRI, hash), ajoute la source amont consultée dans le texte au lieu d'une catégorie ad hoc.
