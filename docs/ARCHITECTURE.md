# Architecture du thème Adaptours

Document d'architecture technique du thème WordPress custom. Découle de la spec
fonctionnelle/technique `specifications-techniques-adaptours.md` (notamment §12.4 « Factorisation,
helpers & architecture » et §12.6 « Ordre de développement »). Il décrit **l'arborescence cible**,
la **convention des blocs**, l'**ordre de chargement** et les **conventions de code** — pour
démarrer vite et garder un code pérenne.

- Projet : Adaptours — thème custom, blocs Gutenberg natifs (`block.json` + `render.php`)
- Stack : WordPress · ACF Free (ACF Pro exclu, §4) · Contact Form 7 (+ Conditional Fields) · Flamingo · Polylang
- CPT & taxonomies : **déclarés en code** (`inc/cpt.php`, `inc/taxonomies.php`), pas de plugin CPT UI en prod (§4)
- SEO : **hand-rolled dans le thème** (`inc/seo.php`), pas de plugin SEO (§7)
- Périmètre versionné (§3) : **uniquement le thème** (`wp-content/themes/adaptours/`)

Légende : `[ACTÉ]` aligné sur la spec · `[RECOMMANDÉ]` choix d'implémentation par défaut, non bloquant ·
`[OUVERT]` dépend d'un point ouvert de la spec.

---

## 1. Arborescence du thème

```
wp-content/themes/adaptours/
├── style.css                     # en-tête de thème (métadonnées) + import build
├── theme.json                    # tokens Gutenberg : palette (orange highlight), typo, espacements
├── functions.php                 # bootstrap : require inc/* dans l'ordre de chargement (§5)
├── screenshot.png
│
├── *.php                         # hiérarchie de templates (§2)
│   ├── front-page.php            # homepage (§9.2) — blocs templateLock:all
│   ├── archive-destination.php   # archive CPT destination (§9.4) — figée, pas de Gutenberg
│   ├── single-destination.php    # single destination (§9.6) — hero/méta figés + zone modulaire
│   ├── template-devis.php        # page Devis (§9.7) — templateLock:all
│   ├── template-qui-sommes-nous.php  # QSN (§9.8) — templateLock:all
│   ├── template-contact.php      # Contact (§9.9) — templateLock:all
│   ├── template-page-modulaire.php   # page contenu riche (§9.10) — 100% modulaire
│   ├── page.php / index.php / 404.php
│
├── parts/                        # [PART] composants partagés (§10), non éditables Gutenberg
│   ├── header.php                # §10.1 (sticky + variante transparente single)
│   ├── prefooter.php             # §10.2 (masqué sur devis/contact/QSN)
│   └── footer.php                # §10.3 (4 colonnes + barre légale)
│
├── template-parts/               # partials de rendu réutilisés (§12.4)
│   ├── card-destination.php       # source UNIQUE d'une card destination (§9.4.3)
│   ├── card-numbered.php          # numéro 01..NN + pastille (process/practical/cards-numbered/recruitment)
│   └── block-hero.php             # socle commun des 5 héros
│
├── inc/                          # logique PHP, chargée par functions.php (§5)
│   ├── helpers.php               # adaptours_bichrome(), adaptours_show_prefooter(), utilitaires
│   ├── cpt.php                   # CPT destination, avis (§11.1, §11.2)
│   ├── taxonomies.php            # zone_geographique (§11.3)
│   ├── acf-config.php            # chargement Local JSON + page d'options (§10.4, §11)
│   ├── gallery-metabox.php       # métabox galerie → post meta _adaptours_gallery_ids (§11.1)
│   ├── polylang.php              # CPT/taxos traduisibles + pll_copy_post_metas (§11.7)
│   ├── roles.php                 # capability manage_adaptours_options + rôle cliente (§6, §12.4)
│   ├── icons.php                 # liste fermée d'icônes partagée (§9.6.5, §9.6.9)
│   ├── itinerary-tags.php        # options de tags du bloc itinerary (§9.6.7)
│   ├── options.php               # déclaration page d'options « Coordonnées & liens » (§10.4)
│   ├── menus.php                 # register_nav_menus : primary + 3 footer (§10.5)
│   ├── blocks.php                # enregistrement des blocs adaptours/* + allowedBlocks par template
│   ├── enqueue.php               # styles/scripts front + chargement conditionnel/lazy (§9.6.15, §9.7.15)
│   ├── seo.php                   # SEO maison : title, meta desc, OpenGraph, hreflang, schema.org (§7)
│   ├── rest-destinations.php     # endpoint REST filtres archive (§9.4.2)   [LOT 2]
│   ├── google-rating.php         # cron + transient note Google (§9.2.6)    [LOT 2]
│   └── cf7.php                   # hooks CF7 : honeypot, sujet mail, validations (§9.7, §9.9)
│
├── blocks/                       # un dossier par bloc adaptours/* (§12.1) — voir §3
│   ├── hero-home/
│   ├── kpi-bar/
│   ├── section-promise/
│   ├── destinations-grid/
│   ├── process/
│   ├── avis-grid/
│   ├── content-storytelling/
│   ├── team-intro/
│   ├── section-map/
│   ├── section-accessibility/
│   ├── destination-gallery/
│   ├── itinerary/
│   ├── avis-spotlight/
│   ├── section-practical/
│   ├── destinations-suggestions/
│   ├── hero-devis/
│   ├── devis-form/
│   ├── hero-qsn/
│   ├── founder-story/
│   ├── team-grid/
│   ├── recruitment/
│   ├── dual-cta/
│   ├── hero-contact/
│   ├── contact-form/
│   ├── legal-info/
│   ├── page-header/
│   ├── rich-text/
│   ├── media-text/
│   ├── media-full/
│   ├── quote/
│   ├── cards-numbered/
│   └── card-grid/
│
├── acf-json/                     # [RECOMMANDÉ] ACF Local JSON (versionné, sync inter-env)
│
├── assets/
│   ├── src/                      # sources (scss, js) — compilées en build
│   │   ├── scss/
│   │   └── js/
│   │       ├── devis-form.js          # steppers −/+ + sélecteur tel (§9.7.6, §9.7.7)
│   │       ├── gallery-lightbox.js    # lightbox galerie, lazy (§9.6.6)
│   │       └── destinations-filter.js # filtres AJAX archive (§9.4.2)         [LOT 2]
│   ├── build/                    # sortie de build (générée, gitignorée)
│   └── img/
│       └── collages/             # visuels décoratifs figés (§9.1)
│
├── languages/                    # .po/.mo FR/EN/ES (text-domain « adaptours », §7, §10.4)
│
├── package.json                  # scripts npm + dépendances build (§3 : npm run build)
└── (composer.json)               # [OUVERT #4] si gestion plugins via Composer/wpackagist
```

---

## 2. Templates & hiérarchie WordPress

- `[ACTÉ]` La frontière éditable/figé est portée **par template** (§9.1) :
  - **Pages à structure figée** (`front-page.php`, `template-devis.php`, `template-qui-sommes-nous.php`,
    `template-contact.php`) → blocs insérés avec `templateLock: "all"`.
  - **Pages modulaires** (`single-destination.php`, `template-page-modulaire.php`) → zone Gutenberg
    `templateLock: false` + `allowedBlocks` restreint à `adaptours/*`.
  - **Archive** (`archive-destination.php`) → aucun bloc, tout figé en PHP + filtres AJAX.
- Les modèles de page (`template-*.php`) exposent un en-tête `Template Name:` pour être sélectionnables
  dans *Attributs de page > Modèle*.
- `adaptours_show_prefooter()` (dans `inc/helpers.php`) retourne `false` sur la blacklist §10.2,
  appelé dans `footer.php`.

---

## 3. Convention d'un bloc `adaptours/*`

`[ACTÉ]` Blocs natifs Gutenberg, un dossier de **source** par bloc sous `blocks/` ; l'enregistrement se
fait depuis la **sortie de build** `assets/build/blocks/` (pas depuis la source).

```
blocks/<nom-bloc>/                 # SOURCE (versionnée)
├── block.json        # métadonnées + attributs + supports (source de vérité du bloc)
├── render.php        # rendu serveur (dynamique) — lit get_block_wrapper_attributes()
├── index.js          # entrée de build : déclare l'édition ET importe le SCSS (cf. ci-dessous)
└── style.scss        # styles front — émis seulement s'il est importé dans index.js

assets/build/blocks/<nom-bloc>/    # BUILD (généré par `npm run build`, gitignoré)
├── block.json        # recopié, références d'assets résolues
├── render.php        # recopié tel quel
├── index.js          # compilé (édition)
├── index.asset.php   # manifeste de dépendances généré par wp-scripts
└── style-index.css   # SCSS compilé (+ style-index-rtl.css)
```

- **Chaîne de build** (`npm run build:blocks` = `wp-scripts build --webpack-src-dir=blocks
  --output-path=assets/build/blocks`) — trois points à respecter, sinon le bloc ne se charge pas :
  1. **`import './style.scss';` dans `index.js`** (en premier) : le SCSS n'est compilé en `style-index.css`
     que s'il est importé par l'entrée JS. Sans cet import, aucun CSS n'est émis.
  2. Le `block.json` source pointe les **fichiers compilés/recopiés** :
     `"style": "file:./style-index.css"`, `"editorScript": "file:./index.js"`, `"render": "file:./render.php"`.
  3. `@use "abstracts" as *;` ne résout que parce que `webpack.config.js` injecte
     `sassOptions.loadPaths → assets/src/scss` dans toutes les règles `sass-loader`.
- **Enregistrement** : `inc/blocks.php` boucle sur **`ADAPTOURS_DIR/assets/build/blocks/*`** (glob des
  dossiers contenant un `block.json`) et appelle `register_block_type( $dir )` sur la **sortie de build**.
  Pas d'enregistrement manuel un par un, et **pas** `register_block_type( __DIR__ )` sur la source.
- **Conséquence** : un bloc n'apparaît dans l'éditeur **qu'après `npm run build`** (l'ajout d'un dossier
  source seul ne suffit pas — il faut le compiler vers `assets/build/blocks/`).
- **Attributs** : déclarés dans `block.json` selon les conventions consolidées §12.3 —
  `title_part_1/2[/3]`, `cta_*`, `columns`, repeaters (figés vs libres), richtext limité/étendu.
- **`templateLock` / `allowedBlocks`** : posés côté template (pas dans le bloc) — figés pour les pages
  à structure figée, palette restreinte pour les pages modulaires (filtre `allowed_block_types_all`
  conditionné par `get_page_template_slug()`, §9.6.3 / §9.10.0).
- **Verrou cliente** : les attributs « structurels » (`columns` du kpi-bar, longueurs figées) sont posés
  à l'insertion et non exposés dans l'inspecteur.
- **Rendu partagé** : `render.php` inclut les partials communs (`card-destination.php`,
  `card-numbered.php`, `block-hero.php`) et le helper `adaptours_bichrome()` plutôt que de dupliquer
  le markup (§12.4).

### 3.1 « Repeaters » sans ACF Pro — `[ACTÉ]`

`[ACTÉ]` ACF Pro est **exclu (budget 0 €)** : pas de Repeater / Gallery / Flexible Content / ACF Blocks.
Les listes d'items (« repeaters ») sont implémentées selon **4 catégories** (source de vérité de la
méthode ; reportée par bloc dans la spec §12.1 et consolidée en §12.3) :

| Catégorie | Quand | Implémentation |
|---|---|---|
| **FP** — longueur figée | nombre d'items connu et fixe (kpi-bar ×4/5, destinations-grid ×5, process ×3, polaroïds storytelling/founder-story ×3, section-accessibility ×4, dual-cta ×2…) | **attributs plats** dans `block.json` (`kpi_1_value`, `kpi_1_label`…) ou champs ACF plats (`accessibility_card_1..4_*`). **Aucune UI de liste à coder**, aucun ajout/retrait côté cliente. |
| **IB** — longueur variable | nombre d'items libre (itinerary, section-practical, team-grid, recruitment, cards-numbered, card-grid) | **InnerBlocks verrouillés** : `allowedBlocks` = **un seul** bloc enfant `adaptours/*-item`, `templateLock` adapté (`insert`/`all` selon le besoin). La cliente ajoute/réordonne des enfants, sans accès au reste de la palette. |
| **REL** — relation de posts | sélection de posts existants (destinations-grid ×5, destinations-suggestions ×4, avis-grid, avis-spotlight ×1) | **sélecteur de recherche d'entités** : `core-data` (`useEntityRecords` / `EntitySearchInput`) côté bloc, ou champ **ACF Relationship / Post Object** (Free) côté CPT. Stocke des IDs, pas de duplication de contenu. |
| **GAL** — galerie | images d'une destination | **métabox native custom** → post meta `_adaptours_gallery_ids` (array d'IDs médiathèque), cf. §4 et `inc/gallery-metabox.php`. |

---

## 4. Données : CPT, ACF, options

- `[ACTÉ]` CPT & taxonomies déclarés en code (`inc/cpt.php`, `inc/taxonomies.php`) — versionnés,
  reproductibles, **source de vérité unique** (§11.6). **CPT UI non installé en prod** : la définition
  est 100 % en code (résout l'ancienne contradiction §4 ↔ archi).
- `[ACTÉ]` **ACF Free uniquement — ACF Pro exclu** (budget 0 €) : pas de Repeater / Gallery /
  Flexible Content / ACF Blocks. Méthode de remplacement des « repeaters » → §3.1.
- `[RECOMMANDÉ]` **ACF Local JSON** (`acf-json/`) pour les groupes de champs : versionné dans le thème
  et synchronisable entre environnements (répond au besoin de sync inter-env, point ouvert #5).
- `[ACTÉ]` La page d'options (§10.4) est enregistrée **en natif via la Settings API** dans
  `inc/options.php` (UNE option tableau `adaptours_options`), protégée par la capability
  `manage_adaptours_options`. ⚠️ **PAS `acf_add_options_page()`** : c'est une fonction ACF **Pro**,
  exclue du projet.
- **Galerie** : métabox native custom (`inc/gallery-metabox.php`) → post meta `_adaptours_gallery_ids`
  (array d'IDs médiathèque), consommée par le bloc `destination-gallery` (§9.6.6, §11.1).
- **Exception type** : `prix_a_partir_de` en `number` (filtre budget) ; tous les autres « chiffres »
  en `string` (§9.1).

---

## 5. Ordre de chargement (`functions.php`)

`[ACTÉ]` Séquence imposée par les dépendances (§12.4) — les blocs qui consomment un CPT s'enregistrent
**après** les CPT :

```php
require 'inc/helpers.php';        // utilitaires + adaptours_bichrome()
require 'inc/roles.php';          // capability manage_adaptours_options
require 'inc/cpt.php';            // CPT destination, avis
require 'inc/taxonomies.php';     // zone_geographique
require 'inc/gallery-metabox.php';
require 'inc/polylang.php';       // CPT/taxos traduisibles + pll_copy_post_metas (§11.7)
require 'inc/icons.php';
require 'inc/itinerary-tags.php';
require 'inc/acf-config.php';     // Local JSON
require 'inc/options.php';        // page d'options (après ACF)
require 'inc/menus.php';
require 'inc/blocks.php';         // enregistrement blocs (après CPT/taxos)
require 'inc/cf7.php';
require 'inc/seo.php';            // SEO maison : head tags, hreflang, schema.org (§7)
require 'inc/rest-destinations.php';  // [LOT 2]
require 'inc/google-rating.php';      // [LOT 2]
require 'inc/enqueue.php';        // assets front
```

`after_setup_theme` : supports thème (title-tag, post-thumbnails, html5, align-wide), `load_theme_textdomain('adaptours', .../languages)`. Les hooks `init` (CPT/taxos/blocs) se déclenchent dans l'ordre des `require`.

---

## 6. Assets & build

- `[ACTÉ]` Build des assets en CI avant déploiement (`npm run build`, §3).
- `[RECOMMANDÉ]` **`@wordpress/scripts`** (`wp-scripts`) comme outil de build — alignement natif avec
  les blocs `block.json`, compilation JSX/SCSS, `wp-scripts build` + `start`. Sortie dans `assets/build/`
  (gitignorée). Alternative possible (Vite) non bloquante.
- **Chargement conditionnel** : `inc/enqueue.php` charge `devis-form.js` uniquement sur les templates
  devis/contact, la lightbox en lazy (IntersectionObserver / au 1er clic, §9.6.15), les filtres AJAX
  sur l'archive seulement.
- **Styles de blocs** : déclarés via la clé `style` du `block.json` → chargés à la demande quand le bloc
  est présent (`should_load_separate_core_block_assets`).
- **Tokens** : `theme.json` centralise la palette (dont l'orange highlight), la typo et les espacements,
  consommés par les blocs et le CSS.

---

## 7. i18n & multilingue (Polylang)

- `[ACTÉ]` Toutes les chaînes du thème via `__()` / `_e()`, text-domain `adaptours` ; fichiers `.po/.mo`
  FR/EN/ES dans `languages/` (gérés par Mattéo, §7).
- `[ACTÉ]` Contenus de blocs sérialisés dans `post_content` → traduction **per-post** (chaque version de
  langue est un post distinct, §9.6.13, §9.8.9, §9.10.10).
- `[ACTÉ]` CF7 : 3 formulaires distincts (FR/EN/ES) liés via Polylang ; le bloc form appelle
  `pll_get_post()` (§9.7.9, §9.9.5).
- `[ACTÉ]` CPT `destination`/`avis` et taxonomies activés dans Polylang (§11.7).
- `[ACTÉ]` **Synchro des métas** entre traductions via le filtre `pll_copy_post_metas` (spec §7, §11.7) :
  copiées (`prix_a_partir_de`, `_adaptours_gallery_ids`, `coup_de_coeur`) vs traduites
  par langue (`ville`, `duree`). `is_featured` (CPT `avis`) sera ajouté avec le groupe `avis` (§11.2).
- `[ACTÉ]` **Traduction v1 manuelle** (Polylang free) ; DeepL utilisé hors WordPress (aide à la
  rédaction, relecture humaine) ; pas de synchro continue FR→EN/ES. Polylang Pro + DeepL = `[FUTUR]`.
- `[ACTÉ]` **SEO hand-rolled** dans `inc/seo.php` : `<title>`, meta description, OpenGraph, balises
  `hreflang` (cohérentes avec la structure d'URL multilingue) et schema.org. Pas de plugin SEO (spec §7).

---

## 8. Rôles & sécurité

- `[ACTÉ]` Rôle cliente sur mesure (base Éditeur) **sans** accès Extensions/Thèmes/Mises à jour (§6).
- `[ACTÉ]` Capability custom `manage_adaptours_options` créée dans `inc/roles.php` et attribuée au rôle
  cliente → seul accès à la page d'options (§9.2.12, §10.4, §12.4).
- `[ACTÉ]` Anti-spam formulaires : honeypot + RGPD (CF7), pas de captcha visible v1 (§9.7.11).

---

## 9. Conventions de code

- Préfixe global : `adaptours_` (fonctions), `_adaptours_` (post meta), namespace blocs `adaptours/`.
- PHP : un fichier `inc/` = une responsabilité ; pas de logique dans les templates au-delà de l'appel
  de partials/helpers.
- Pas de HTML inline pour les highlights → toujours `adaptours_bichrome()` (§12.3).
- Sécurité de sortie : `esc_html` / `esc_attr` / `esc_url` / `wp_kses_post` (richtext) systématiques
  dans les `render.php`.
- Partials = source unique : ne jamais redéfinir le markup d'une card destination ou d'une card
  numérotée ailleurs que dans `template-parts/` (§12.4).

---

## 10. Points d'infra encore ouverts

Non tranchés dans la spec, à acter avant/pendant le dev (renvois « Points ouverts (synthèse) ») :

- #1 version PHP cible · #2 Redis object cache · #4 gestion plugins (Composer/wpackagist
  vs manuel — conditionne `composer.json`) · #5 sync DB inter-env + `search-replace` · #6 structure
  d'URL multilingue · #7 reprise de l'existant vs reconstruction.
  - **Clos** : ~~#3 plugin SEO~~ → **SEO hand-rolled** dans le thème (`inc/seo.php`, §7).
- Côté blocs/pages : libs (#36 lightbox, #43 steppers, #49 tel), slugs multilingues (#55, #57),
  etc. — aucun n'est bloquant pour démarrer selon l'ordre de dev §12.6.
- **Reclassés en LOT 2** (voir spec « Périmètre de livraison ») : endpoint/SEO archive AJAX (#19, #20),
  Google Places API (#9).

---

## 11. Ordre de mise en œuvre

Suivre §12.6 de la spec : **fondations** (CPT/taxos/ACF/roles/options/icons/tags) → **composants
partagés** (header/prefooter/footer + helpers/partials) → **blocs partagés** (`kpi-bar`, `avis-grid`,
`card-destination`) → **blocs page par page** → **formulaires CF7** → **archive AJAX** →
**intégrations** (note Google, lightbox, tel/steppers).
