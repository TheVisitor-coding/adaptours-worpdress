# Cahier des charges — Refonte du site Adaptours

Adaptours est une agence de voyages spécialisée dans les **séjours accessibles et sur mesure**
(personnes à mobilité réduite, besoins médicaux ou d'accompagnement spécifiques). Ce document
présente le cadrage fonctionnel et technique de la refonte de son site, réalisée dans le cadre
du Workshop client réel (Master 2).

---

## 1. Contexte et objectifs

Le site existant ne valorise ni le positionnement « voyage accessible » de l'agence, ni la
richesse de ses destinations, et n'offre pas à la cliente l'autonomie nécessaire pour gérer
ses contenus au quotidien. La refonte vise trois objectifs :

- **Convertir** : amener le visiteur vers la demande de devis, point d'entrée commercial.
- **Rassurer** : mettre en avant l'expertise accessibilité (conditions détaillées par destination,
  matériel et accompagnement, témoignages).
- **Rendre la cliente autonome** : édition des contenus en toute sécurité, sans pouvoir casser
  la structure ni toucher à la technique.

**Indicateurs de succès visés** : Lighthouse Performance et Accessibilité > 90, First Contentful
Paint < 1,5 s, conformité WCAG 2.1 AA.

---

## 2. Cadrage fonctionnel

### Pages

| Page | Rôle | Édition |
|---|---|---|
| **Accueil** | Vitrine : promesse, chiffres clés, destinations, processus, avis, équipe | Structure figée, contenus éditables |
| **Destinations** (archive) | Listing filtrable (continent, budget, recherche) | Template figé, données issues des fiches |
| **Destination** (fiche) | Hero, carte, accessibilité, galerie, itinéraire, infos pratiques, suggestions | Sections modulaires réordonnables |
| **Devis** | Formulaire détaillé multi-profils (particulier, agence, partenaire) | Structure figée |
| **Contact** | Coordonnées + formulaire | Structure figée |
| **Qui sommes-nous** | Présentation de l'agence | Structure figée |

### Autonomie de la cliente

La cliente peut, **sans intervention technique** :

- créer et composer des pages à partir d'une palette de blocs de contenu riche ;
- créer des destinations et les classer par zone géographique (qui alimente les filtres) ;
- éditer les fiches destination en composant avec les blocs dédiés ;
- modifier les contenus de la page d'accueil ;
- consulter les demandes reçues via les formulaires (devis et contact).

Le **verrouillage est technique, pas seulement contractuel** : un rôle sur mesure retire l'accès
aux extensions, thèmes et mises à jour ; les structures de page sont figées (`templateLock`) pour
éviter toute désorganisation involontaire. L'installation et les mises à jour restent réservées
au développeur.

---

## 3. Cadrage technique

### Stack

- **WordPress**, thème **custom classique** (templates PHP + `template-parts`).
- **Blocs Gutenberg natifs** (`block.json` + `render.php`), sans page builder propriétaire.
- **ACF Free** pour les champs structurés (ACF Pro exclu, voir §4).
- **Contact Form 7** + **CF7 Conditional Fields** (formulaire de devis à champs conditionnels)
  + **Flamingo** (persistance et consultation des soumissions).
- **Polylang** (multilingue FR / EN / ES).
- Build des assets via **`@wordpress/scripts`** (blocs) et **Sass** (styles globaux).

### Modèle de contenu

- **CPT `destination`** (avec galerie), **CPT `avis`**, **taxonomie `zone_geographique`**.
- CPT et taxonomies **déclarés en code** (`inc/cpt.php`, `inc/taxonomies.php`) : source de vérité
  unique, versionnée et reproductible — pas d'extension de type CPT UI en production.

### Référencement (SEO)

SEO **intégré au thème** (`<title>`, meta description, OpenGraph, `hreflang`, schema.org) plutôt
qu'un plugin lourd (Yoast / Rank Math exclus), pour rester cohérent avec l'implémentation
multilingue et garder la maîtrise du balisage.

### Hébergement et environnements

- Hébergement **Infomaniak** (offre avec accès SSH, SSL Let's Encrypt, sauvegardes quotidiennes).
- Trois environnements : **local** (Docker, image miroir de la prod), **préprod** (sous-domaine
  du même hébergement, protégée par `noindex` + htpasswd) et **production**.

### Déploiement

- **CI/CD GitHub**, déploiement par SSH avec build des assets (`npm run build`) avant publication.
- **Périmètre versionné = le thème uniquement**. Le cœur WordPress, la base de données et
  `wp-content/uploads` restent hors dépôt (pas d'écrasement du contenu saisi par la cliente).

### Performance

- OPcache au niveau PHP ; object cache (Redis) et cache de page selon les capacités de l'offre.
- Polices **auto-hébergées** (pas d'appel à Google Fonts : performance + RGPD).

### Multilingue

- FR (langue principale), EN, ES via Polylang.
- Traduction **manuelle** en v1 (relecture humaine obligatoire), DeepL utilisé en externe comme
  aide à la rédaction.
- Synchronisation des métas indépendantes de la langue (prix, images, codes) entre traductions ;
  les contenus textuels sont saisis par langue.

---

## 4. Choix et arbitrages

### ACF Free imposé (budget 0 €)

ACF Pro étant exclu, aucune fonctionnalité Pro n'est utilisée (Repeater, Gallery, Flexible
Content, ACF Blocks). Les besoins de type « répéteur » sont couverts par des méthodes natives :
champs plats de longueur fixe, métabox native pour la galerie, ou **blocs InnerBlocks**
(un bloc parent + un bloc enfant répétable) pour les listes à longueur variable (itinéraire,
informations pratiques).

### Accessibilité vs fidélité maquette

L'intégration suit les maquettes au plus près. En cas de conflit avec un critère WCAG AA (par
exemple un contraste insuffisant), la valeur est ajustée pour respecter l'AA et l'écart est
documenté plutôt que laissé silencieux.

### Priorisation en deux lots

Le périmètre est priorisé en deux lots ; le lot 2 est conditionnel et se coupe en dernier
recours si le délai l'impose. **Migration, recette, formation de la cliente et traduction ne
sont jamais sacrifiées.**

- **Lot 1** — homepage complète, fiche destination, archive avec filtres (rechargement de page),
  pages Devis / Contact / Qui sommes-nous, palette de contenu riche, note Google en saisie manuelle.
- **Lot 2** — filtres d'archive en AJAX, automatisation des avis Google, et autres améliorations
  de confort.

---

## 5. Architecture du thème

L'organisation du thème (arborescence, conventions de code, ordre de chargement, convention
d'un bloc) est détaillée dans **[`ARCHITECTURE.md`](ARCHITECTURE.md)**. La procédure d'ajout d'un
bloc Gutenberg est décrite dans **[`CONTRIBUTING-blocks.md`](CONTRIBUTING-blocks.md)**.
