# Multilingue (Polylang) — Adaptours

Le site est trilingue **FR (par défaut) + EN + ES**, géré par **Polylang (gratuit)**.

Structure d'URL : FR sans préfixe (`/contact/`), EN sous `/en/`, ES sous `/es/`.

## Mise en route d'un environnement

Après chaque (re)création de l'environnement wp-env, rejouer dans le conteneur CLI, **dans cet ordre** :

```bash
wp eval-file wp-content/themes/adaptours/tools/install-language-packs.php   # packs de langue WordPress (cœur + extensions)
wp eval-file wp-content/themes/adaptours/tools/setup-polylang.php           # crée les langues, FR par défaut, assigne le contenu existant
wp eval-file wp-content/themes/adaptours/tools/setup-site.php               # pages, menus par langue, zones géographiques traduites
ADAPTOURS_SEED_LANG=en wp eval-file wp-content/themes/adaptours/tools/seed-lang.php   # (optionnel) contenu de recette EN
ADAPTOURS_SEED_LANG=es wp eval-file wp-content/themes/adaptours/tools/seed-lang.php   # (optionnel) contenu de recette ES
wp eval-file wp-content/themes/adaptours/tools/seed-string-translations.php           # traductions Polylang des réglages
wp eval 'adaptours_cf7_ensure_contact_form(); adaptours_cf7_ensure_devis_form();'     # formulaires CF7 par langue
```

Tous ces scripts sont **idempotents**. `setup-polylang.php` est la source de vérité des langues
(slugs, locales, ordre, drapeaux) — éditer le tableau `$adaptours_languages` pour en ajouter.

`setup-site.php` gagne à être rejoué **après** les seeds : les items de menu pointant vers une
traduction encore inexistante sont ignorés au premier passage.

Recette : `ADAPTOURS_VERIFY_BASE=http://wordpress wp eval-file wp-content/themes/adaptours/tools/verify-langues.php`
(la variable n'est utile que sous wp-env, où le port publié sur l'hôte n'est pas joignable
depuis le conteneur CLI).

## Traduction de l'interface (thème, blocs, formulaires)

Les chaînes d'interface sont traduites via les fichiers de `languages/` :

- `adaptours.pot` — modèle (toutes les chaînes extraites).
- `en_US.po` / `en_US.mo`, `es_ES.po` / `es_ES.mo` — traductions. **Le `.mo` doit s'appeler
  `{locale}.mo`** (pas `adaptours-{locale}.mo`) : pour un dossier interne au thème, WordPress
  attend ce nommage, sinon le fichier n'est jamais chargé, sans erreur.
- `adaptours-en_US-adaptours-<bloc>-editor-script.json` — traductions de l'UI d'édition.

**Périmètre traduit : le front uniquement.** Le back-office (libellés ACF, page de réglages,
titres/descriptions de blocs, aides de l'Inspector) reste en français — décision cliente du
2026-07-01. Les `msgstr` vides du `.po` correspondant à ces chaînes sont donc normaux : gettext
retombe sur le français. Les JSON d'éditeur ne sont maintenus que pour l'anglais, par héritage.

### Valeurs par défaut des blocs

Les textes éditoriaux par défaut vivent dans `attributes[].default` des `block.json`, que
`wp i18n make-pot` **n'extrait jamais**. Ils sont rendus extractibles par
`inc/block-default-strings.php`, **fichier généré** par `tools/gen-block-default-strings.py`,
et traduits au rendu par le filtre `render_block_data` de `inc/block-defaults-i18n.php`.

Conséquence pratique : une page traduite dont la cliente n'a ressaisi aucun champ s'affiche
quand même dans sa langue. En revanche, **retaper le texte français dans un champ le fige** :
la valeur est alors sérialisée en base et le filtre ne s'applique plus à cet attribut.

### Régénérer après ajout/modification de chaînes

```bash
# 0. blocs compilés (si block.json ou render.php ont changé)
npm run build

# 1. chaînes des valeurs par défaut des blocs (AVANT make-pot)
python3 wp-content/themes/adaptours/tools/gen-block-default-strings.py

# 2. modèle
wp i18n make-pot wp-content/themes/adaptours wp-content/themes/adaptours/languages/adaptours.pot \
  --domain=adaptours --exclude=node_modules,assets/build,tools,vendor

# 3. fusion dans les .po (conserve les msgstr existants, ajoute les nouveaux vides)
wp i18n update-po wp-content/themes/adaptours/languages/adaptours.pot wp-content/themes/adaptours/languages/en_US.po
wp i18n update-po wp-content/themes/adaptours/languages/adaptours.pot wp-content/themes/adaptours/languages/es_ES.po
#    → compléter les msgstr des chaînes FRONT, retirer les marqueurs « #, fuzzy »

# 4. compilation .po -> .mo
wp i18n make-mo wp-content/themes/adaptours/languages/

# 5. JSON de l'éditeur (anglais uniquement)
python3 wp-content/themes/adaptours/tools/gen-editor-json.py en_US

# 6. contrôle
msgfmt --check --statistics -o /dev/null wp-content/themes/adaptours/languages/es_ES.po
```

## Traduction du contenu (côté cliente, en admin)

Le **contenu éditorial** (pages, fiches destinations, avis) se traduit dans l'admin WordPress :
sur chaque contenu, utiliser le bouton **« + »** de la colonne de la langue voulue.

- Les **blocs verrouillés** et le **template** de page sont conservés sur la traduction
  (le template est recopié, `_wp_page_template`).
- Les **formulaires Contact et Devis** existent dans les trois langues (créés par code, liés
  entre eux) ; ils s'affichent automatiquement dans la bonne langue — rien à faire.
- Les **libellés de menu** suivent le titre de la page traduite : traduire le titre suffit.
- Les **réglages traduisibles** (horaires, délai de réponse, chapô de l'archive) se traduisent
  dans **Langues → Traductions des chaînes**.

## Slugs

| FR                               | EN                    | ES                             |
|----------------------------------|-----------------------|--------------------------------|
| `/` (accueil)                    | `/en/`                | `/es/`                         |
| `/qui-sommes-nous/`              | `/en/about/`          | `/es/quienes-somos/`           |
| `/contact/`                      | `/en/contact/`        | `/es/contacto/`                |
| `/devis/`                        | `/en/quote/`          | `/es/presupuesto/`             |
| `/mentions-legales/`             | `/en/legal-notice/`   | `/es/aviso-legal/`             |
| `/cgv/`                          | `/en/terms/`          | `/es/condiciones-generales/`   |
| `/politique-de-confidentialite/` | `/en/privacy-policy/` | `/es/politica-de-privacidad/`  |
| `/destinations/`                 | `/en/destinations/`   | `/es/destinations/`            |

Les slugs sont posés par `tools/seed-lang.php` et restent modifiables en admin.

## Limites connues (Polylang gratuit)

- La **base d'URL des destinations** reste `/destinations/` dans toutes les langues : traduire
  la base de réécriture d'un CPT exige Polylang Pro.
- La page d'accueil traduite est aussi accessible via son permalink (`/en/home-en/`) en plus de
  `/en/` ; doublon SEO bénin (la redirection canonique est neutralisée pour servir la home à
  la racine de langue).

## Ajouter une langue

**L'ordre compte.** Livrer le `.mo` et les packs de langue **avant** d'activer la langue :
les formulaires CF7 sont construits sous `switch_to_locale()` et **figés en base**. Or
`switch_to_locale()` est refusé **en silence** tant que le pack de langue du cœur n'est pas
installé — les formulaires sortiraient alors intégralement en français, sans erreur.

1. **Traduction de l'interface** : `msginit --no-translator --locale=<locale> --input=adaptours.pot
   --output-file=<locale>.po`, aligner l'en-tête sur celui de `es_ES.po`, traduire les chaînes
   **front**, puis `wp i18n make-mo languages/` (nom attendu : `{locale}.mo`).
   Vérifier : `msgfmt --check`.
2. **Drapeau** : créer `assets/flags/<code>.svg` sur le modèle de `fr.svg` (`viewBox="0 0 18 13"`,
   `<rect>` uniquement). Le code est le basename du PNG livré par Polylang (`polylang/flags/`).
   Sans ce fichier, `adaptours_flag_svg()` renvoie une chaîne vide : drapeau invisible, sans erreur.
3. **Packs de langue** :
   `ADAPTOURS_LANG_LOCALES=fr_FR,<locale> wp eval-file tools/install-language-packs.php`
   (la variable est nécessaire tant que la langue n'existe pas encore dans Polylang).
4. **Activer la langue** : ajouter l'entrée dans `tools/setup-polylang.php`, relancer le script.
5. **Formulaires CF7** : `wp eval 'adaptours_cf7_ensure_contact_form(); adaptours_cf7_ensure_devis_form();'`
6. **Contenu et structure** : `ADAPTOURS_SEED_LANG=<slug> wp eval-file tools/seed-lang.php`,
   puis `wp eval-file tools/setup-site.php` (menus, zones géographiques), puis
   `wp eval-file tools/seed-string-translations.php` (après y avoir ajouté la langue).
7. **Recette** : `wp eval-file tools/verify-langues.php`.
8. **Traduction du contenu éditorial** : par la cliente, en admin.

### Pièges

- **Un formulaire CF7 est un instantané.** `adaptours_cf7_upsert_*()` sort immédiatement si
  l'option pointe déjà un formulaire valide : corriger le `.po` ne met **pas** à jour un
  formulaire existant. Rattrapage : supprimer le post et son option
  (`adaptours_contact_form_id_<slug>`, `adaptours_devis_form_id_<slug>`), puis rejouer.
- **Les trois libellés « Vous êtes » sont gelés à vie.** `adaptours_devis_statut_labels()`
  alimente à la fois les boutons radio et les `if_value` de `wpcf7cf_options` : modifier leur
  traduction après création du formulaire casse la logique conditionnelle **en silence**.
- **`pll_save_post_translations()` écrase le groupe de traductions.** Toujours fusionner avec
  `pll_get_post_translations()` avant d'enregistrer, sinon seeder une 3ᵉ langue délie les autres.
- **Polylang assigne d'office la langue par défaut à tout terme créé.** Poser la langue d'une
  traduction de terme exige un `pll_set_term_language()` **inconditionnel** ; un terme resté
  dans la mauvaise langue est filtré hors du front et le filtre « Continent » ressort vide.
