# Multilingue (Polylang) — Adaptours

Le site est bilingue **FR (par défaut) + EN**, géré par **Polylang (gratuit)**.
L'espagnol est anticipé mais pas encore activé (voir « Ajouter une langue »).

Structure d'URL : FR sans préfixe (`/contact/`), EN sous `/en/` (`/en/contact/`).

## Mise en route d'un environnement

Après chaque (re)création de l'environnement wp-env, rejouer dans le conteneur CLI :

```bash
wp eval-file wp-content/themes/adaptours/tools/setup-polylang.php   # crée les langues FR/EN, FR par défaut, assigne le contenu existant au FR
wp eval-file wp-content/themes/adaptours/tools/seed-en.php          # (optionnel) crée un échantillon de contenu EN pour la recette
```

Les deux scripts sont **idempotents**. `setup-polylang.php` est la source de vérité des langues
(slugs, locales, ordre, URL) — éditer le tableau `$adaptours_languages` pour en ajouter.

## Traduction de l'interface (thème, blocs, formulaires)

Les chaînes d'interface sont traduites via les fichiers de `languages/` :

- `adaptours.pot` — modèle (toutes les chaînes extraites).
- `en_US.po` / `en_US.mo` — traduction anglaise. **Le `.mo` doit s'appeler `en_US.mo`** (pas
  `adaptours-en_US.mo`) : pour un dossier interne au thème, WordPress attend `{locale}.mo`.
- `adaptours-en_US-adaptours-<bloc>-editor-script.json` — traductions de l'UI d'édition des blocs.

Régénérer après ajout/modification de chaînes `__()` :

```bash
# 1. modèle (inclut les titres/descriptions des block.json)
wp i18n make-pot wp-content/themes/adaptours wp-content/themes/adaptours/languages/adaptours.pot \
  --domain=adaptours --exclude=node_modules,assets/build,tools,vendor
# 2. compléter en_US.po (msgstr), puis compiler (en_US.po -> en_US.mo)
wp i18n make-mo wp-content/themes/adaptours/languages/
# 3. JSON de l'éditeur (régénère depuis .pot + en_US.po)
python3 wp-content/themes/adaptours/tools/gen-editor-json.py en_US
```

## Traduction du contenu (côté cliente, en admin)

Le **contenu éditorial** (pages, fiches destinations, avis) se traduit dans l'admin WordPress :
sur chaque contenu, utiliser le bouton **« + »** de la colonne EN pour créer la version anglaise.

- Les **blocs verrouillés** et le **template** de page sont conservés sur la traduction
  (le template est recopié, `_wp_page_template`).
- Les **formulaires Contact et Devis** ont déjà leur version EN (créée par code, liée à la version
  FR) ; ils s'affichent automatiquement dans la bonne langue sur les pages traduites — rien à faire.

## Slugs

| FR                  | EN                    |
|---------------------|-----------------------|
| `/` (accueil)       | `/en/`                |
| `/qui-sommes-nous/` | `/en/about/`          |
| `/contact/`         | `/en/contact/`        |
| `/devis/`           | `/en/quote/`          |
| `/destinations/`    | `/en/destinations/`   |

Les slugs EN se saisissent à la création de chaque traduction.

## Limites connues (Polylang gratuit)

- La **base d'URL des destinations** reste `/destinations/` en anglais (`/en/destinations/…`) :
  la traduction de la base de réécriture des CPT exige Polylang Pro.
- La page d'accueil EN est aussi accessible via son permalink (`/en/home-en/`) en plus de `/en/` ;
  doublon SEO bénin (la redirection canonique est neutralisée pour servir la home à `/en/`).

## Ajouter une langue (ex. espagnol)

1. Décommenter l'entrée `es` dans `tools/setup-polylang.php`, relancer le script.
2. Créer `languages/es_ES.po` (depuis `adaptours.pot`), le traduire, compiler en `es_ES.mo`.
3. `python3 tools/gen-editor-json.py es_ES`.
4. Recharger l'admin : les formulaires CF7 ES sont créés et liés automatiquement.
5. Traduire le contenu en admin.
