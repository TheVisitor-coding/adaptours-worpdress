# Adaptours

Thème WordPress custom pour **Adaptours**, agence de voyages spécialisée dans les séjours
accessibles et sur mesure. Projet réalisé dans le cadre du Workshop client réel (Master 2).

Le thème est construit autour de **blocs Gutenberg natifs**, d'un **design system centralisé**
(`theme.json`) et d'une **édition verrouillée** pour rendre la cliente autonome sans risque pour
la structure du site.

- 📄 Cadrage du projet : [`CAHIER-DES-CHARGES.md`](CAHIER-DES-CHARGES.md)
- 🏗️ Architecture & conventions : [`ARCHITECTURE.md`](ARCHITECTURE.md)
- 🧩 Ajouter un bloc : [`CONTRIBUTING-blocks.md`](CONTRIBUTING-blocks.md)

## Stack

| Domaine | Choix |
|---|---|
| CMS | WordPress (thème classique custom) |
| Éditeur | Blocs Gutenberg natifs (`block.json` + `render.php`) |
| Champs | ACF Free (ACF Pro exclu) |
| Formulaires | Contact Form 7 + CF7 Conditional Fields + Flamingo |
| Multilingue | Polylang (FR / EN / ES) |
| Build | `@wordpress/scripts` (blocs) + Sass (styles globaux) |
| Dev local | `wp-env` (Docker) |

## Arborescence

```
.
├── CAHIER-DES-CHARGES.md          # Cadrage fonctionnel & technique
├── ARCHITECTURE.md                # Arborescence du thème & conventions
├── CONTRIBUTING-blocks.md         # Procédure d'ajout d'un bloc
├── .wp-env.json                   # Environnement de dev (Docker + plugins)
└── wp-content/themes/adaptours/   # Le thème (périmètre versionné)
    ├── functions.php              # Bootstrap + chargement des modules
    ├── theme.json                 # Design tokens (couleurs, typo, espacements)
    ├── inc/                       # Couche données (CPT, taxonomies, options, CF7…)
    ├── blocks/                    # Blocs Gutenberg adaptours/*
    ├── parts/                     # header / prefooter / footer partagés
    ├── template-parts/            # Partials réutilisables (cards, logo…)
    └── assets/                    # SCSS, JS, polices, icônes (build dans assets/build/)
```

## Installation

Prérequis : [Docker](https://www.docker.com/) et [Node.js](https://nodejs.org/) (≥ 18).

```bash
# 1. Construire les assets du thème
cd wp-content/themes/adaptours
npm install
npm run build

# 2. Lancer l'environnement WordPress local (depuis la racine du dépôt)
cd ../../..
npx @wordpress/env start
```

Le site est alors disponible sur `http://localhost:8888` (administration sur
`http://localhost:8888/wp-admin`, identifiants `admin` / `password`). `wp-env` installe et
active automatiquement les extensions requises (ACF, Contact Form 7, CF7 Conditional Fields,
Flamingo) déclarées dans `.wp-env.json`.

## Développement

```bash
npm run build        # Compile styles + blocs (sortie dans assets/build/)
npm run watch:css    # Recompile les styles globaux à la volée
npm run start:blocks # Recompile les blocs à la volée
npm run lint:css     # Lint des feuilles SCSS
npm run new-block    # Génère le squelette d'un nouveau bloc
```

> Les blocs ne sont enregistrés qu'**après un build** : `register_block_type()` cible la sortie
> compilée `assets/build/blocks/`. Pensez à lancer `npm run build` après toute modification.

## Licence

Distribué sous licence **GPL-2.0-or-later**.
