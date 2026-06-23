# Ajouter un bloc Gutenberg — procédure (Adaptours)

Procédure opérationnelle pour ajouter un bloc `adaptours/*`. Sources de vérité :
**`ARCHITECTURE.md §3`** (convention + chaîne build→enregistrement) et le skill
**`integration-front`** (standards d'intégration : conteneur 1280px, tokens `theme.json`, rem, BEM,
WCAG 2.1 AA, SEO). Ce fichier ne fait que les enchaîner.

## Cadence (du scaffold à la recette)

1. **Scaffold** — `npm run new-block -- <slug> [archetype]` (depuis le dossier thème).
   - `<slug>` : `^[a-z][a-z0-9-]*$` (minuscules / chiffres / tirets, commence par une lettre ;
     **pas** de `_` initial — nom de bloc invalide sinon).
   - `[archetype]` : `plat-texte` (défaut) · `media-texte` · `innerblocks` (cf. tableau ci-dessous).
   - Produit `blocks/<slug>/{block.json,index.js,render.php,style.scss}` pré-câblés.
2. **Lire la spec + la maquette** — section `§9.x` / `§12.3` de `specifications-techniques-adaptours.md`,
   puis le node Figma **via le skill `integration-front`** (`get_metadata` → `get_screenshot` →
   `get_design_context`, valeurs de référence jamais copiées telles quelles). La structure sémantique
   vient de la **spec**, pas des calques Figma.
3. **Remplir le bloc** :
   - `block.json` : déclarer les **attributs** (§12.3 — `title_part_*`, `cta_*`, `columns`, repeaters
     selon la méthode FP/IB/REL/GAL de `ARCHITECTURE §3.1`).
   - `index.js` : édition (RichText inline = ergonomie cliente ; attributs structurels non exposés).
   - `render.php` : markup BEM sémantique, `get_block_wrapper_attributes()` sur la racine, **échappement
     systématique en sortie** (`esc_html`/`esc_attr`/`esc_url`/`wp_kses_post`), réutilisation des partials
     `template-parts/`, `adaptours_bichrome()` pour les titres bichromes.
   - `style.scss` : `@use "abstracts" as *;`, `@include container`, **tokens `theme.json`** (zéro valeur en
     dur tokenisable), responsive desktop-first via `@include bp(...)`.
4. **Verrou** — ajouter l'entrée du bloc dans **`adaptours_lock_map()`** (`inc/blocks.php`) selon le
   contexte d'édition (page figée = `lock: all` + `template` ; page modulaire / single = `lock: false`
   + palette `adaptours`).
5. **Build** — `npm run build`. Le bloc n'apparaît dans l'éditeur **qu'après build** (enregistrement
   depuis `assets/build/blocks/*`, cf. `ARCHITECTURE §3`).
6. **Checklist skill** — `references/checklist-block.md` + `accessibilite-seo.md` (hiérarchie de titres,
   focus visible, **contraste AA** — texte normal ≥ 4,5:1, grand texte ≥ 3:1 ; `alt`, `aria-hidden` sur
   le décor, images responsives anti-CLS).
7. **Recette wp-env** — `npx @wordpress/env start` (racine), vérifier le rendu dans l'**éditeur Gutenberg
   ET sur le front**, en tenant compte du **verrou cliente** (templateLock / allowedBlocks).

## Classification par archétype (traiter par famille, pas un par un)

| Archétype | Quand | Édition | `save` / `render.php` | Repeater (§3.1) |
|---|---|---|---|---|
| **plat-texte** | titre + texte + CTA, structure fixe | RichText inline | `save: null`, markup depuis attributs | FP (attributs plats) |
| **hero** | en-tête de page, décor en absolu | RichText inline + média | `save: null` | FP |
| **media-texte** | image/illustration + bloc de texte (2 colonnes) | RichText + MediaUpload | `save: null` | FP |
| **innerblocks verrouillés** | liste de longueur variable (items ajoutés par la cliente) | `<InnerBlocks>` (`allowedBlocks` = 1 enfant `*-item`, `templateLock`) | `save: <InnerBlocks.Content />`, `render.php` echo `$content` | IB |
| **picker de relation** | sélection de posts existants (destinations, avis…) | `EntitySearchInput` / ACF Relationship | `save: null`, `render.php` requête par IDs | REL |

> Le générateur couvre `plat-texte` / `media-texte` / `innerblocks`. `hero` part de `plat-texte` (ajouter le
> décor en CSS) ; `picker de relation` part de `plat-texte` (remplacer l'édition par un sélecteur d'entités).
> Galeries (GAL) : métabox native → post meta `_adaptours_gallery_ids` (cf. `inc/gallery-metabox.php`).
