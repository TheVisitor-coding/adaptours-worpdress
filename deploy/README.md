# Déploiement démo — Adaptours (VPS Docker + Nginx Proxy Manager)

Hébergement **temporaire** du thème Adaptours sur un VPS, derrière Nginx Proxy Manager (NPM),
en attendant l'achat de l'offre Infomaniak. Le but : montrer le site en HTTPS pour une présentation,
avec une base de données **réutilisable telle quelle** lors de la vraie mise en production.

> ⚠️ Ce n'est pas la préprod « officielle » de la spec (§2 = sous-domaine sur Infomaniak).
> C'est un hébergement de démo ad hoc. Voir *Reprise vers Infomaniak* en bas.

## Ce que contient la stack

| Élément | Où | Persistance |
|---|---|---|
| WordPress + thème `adaptours` + 5 plugins | dans l'image `adaptours-wp:demo` | immuable (rebuild) |
| Assets compilés (`assets/build/`) | buildés dans l'image (stage Node) | immuable (rebuild) |
| Base de données (MariaDB) | volume `db_data` | persistant |
| Médias (`wp-content/uploads`) | volume `uploads` | persistant |

Plugins figés dans l'image (mêmes sources que `.wp-env.json`) : ACF **6.8.4**, Contact Form 7,
CF7 Conditional Fields, Flamingo, Polylang.

## Prérequis (VPS)

- Docker + Docker Compose v2.
- Nginx Proxy Manager déjà en service, sur un réseau Docker externe nommé **`proxy`**.
- Un sous-domaine (ex. `demo-adaptours.mondomaine.fr`) avec un enregistrement DNS **A** pointant vers l'IP du VPS.

---

## 1. Configuration

```bash
cd deploy
cp .env.example .env
# éditer .env : WP_DOMAIN (le vrai sous-domaine) + DB_PASSWORD (mot de passe fort)
```

## 2. Build + démarrage

```bash
docker compose build          # stage 1 compile les assets, stage 2 assemble WordPress
docker compose up -d
docker compose ps             # db "healthy", wordpress "running"
```

Le conteneur WordPress ne publie **aucun port** : NPM l'atteint par son nom sur le réseau `proxy`.

## 3. Nginx Proxy Manager

Dans l'UI NPM → **Add Proxy Host** :

- **Domain Names** : `demo-adaptours.mondomaine.fr` (= `WP_DOMAIN`)
- **Scheme** : `http`
- **Forward Hostname / IP** : `adaptours-wp`
- **Forward Port** : `80`
- Cocher **Block Common Exploits** et **Websockets Support**
- Onglet **SSL** : *Request a new SSL Certificate* (Let's Encrypt) + **Force SSL** + **HTTP/2 Support**

NPM et le conteneur partagent déjà le réseau `proxy` (déclaré `external` dans la compose), donc la
résolution par nom `adaptours-wp` fonctionne. À ce stade, `https://WP_DOMAIN/` répond (site vide tant
que la base n'est pas importée).

---

## 4. Migration du contenu (poste local → VPS)

Le contenu déjà saisi dans wp-env (pages FR/EN, destinations, avis, formulaires, options) est
transféré une seule fois. **Aucune ressaisie.**

### 4.1 Export depuis le poste local

Les conteneurs wp-env portent un hash dans leur nom (`wp-env-adaptours-<hash>-…`). Le récupérer :

```bash
CLI=$(docker ps --format '{{.Names}}' | grep -E 'wp-env-adaptours-.*-cli-1$' | head -1)
WPC=$(docker ps --format '{{.Names}}' | grep -E 'wp-env-adaptours-.*-wordpress-1$' | head -1)

# Dump de la base
docker exec "$CLI" wp db export /tmp/adaptours.sql --allow-root
docker cp "$CLI":/tmp/adaptours.sql ./adaptours.sql

# Médias
docker cp "$WPC":/var/www/html/wp-content/uploads ./uploads-dump
```

> Si `wp db export` échoue (mysqldump absent du conteneur cli), dumper depuis le conteneur MariaDB :
> `MYS=$(docker ps --format '{{.Names}}' | grep -E 'wp-env-adaptours-.*-mysql-1$' | head -1)`
> puis `docker exec "$MYS" sh -c 'exec mysqldump -uroot -ppassword wordpress' > ./adaptours.sql`

Transférer sur le VPS :

```bash
scp adaptours.sql user@vps:/chemin/adaptours/deploy/
scp -r uploads-dump user@vps:/chemin/adaptours/deploy/
```

### 4.2 Import sur le VPS (dans `deploy/`)

```bash
# 1) Base (mariadb:lts : le client s'appelle `mariadb`, pas `mysql`)
docker compose exec -T db sh -c 'exec mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < adaptours.sql

# 2) Réécriture de l'URL (serialization-aware : gère blocs Gutenberg + métas ACF sérialisées)
docker compose exec -u www-data wordpress \
  wp search-replace 'http://localhost:8888' 'https://demo-adaptours.mondomaine.fr' \
  --all-tables --precise --skip-plugins --skip-themes

# 3) Médias
docker cp ./uploads-dump/. adaptours-wp:/var/www/html/wp-content/uploads/
docker compose exec wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads

# 4) Permaliens
docker compose exec -u www-data wordpress wp rewrite flush
```

> ❗ Ne **jamais** faire de `sed` / SQL brut pour changer l'URL : ça casse les longueurs des
> tableaux PHP sérialisés (blocs Gutenberg, champs ACF). Toujours `wp search-replace`.

Polylang, ACF, CF7, rôle client : **rien à relancer**, leur état est dans la base importée
(langues = termes + options, formulaires = posts `wpcf7_contact_form`, ACF = déclaré en PHP au chargement).

## 5. Vérification

```bash
docker compose logs --tail=50 wordpress          # pas d'erreur fatale
docker compose exec wordpress cat /var/www/html/wp-content/debug.log 2>/dev/null   # propre
```

- `https://WP_DOMAIN/` → 200, cadenas HTTPS, pas de mixed-content (console navigateur).
- Pages : `/`, `/qui-sommes-nous/`, `/contact/`, `/devis/`, `/destinations/`, une single destination, `/en/`.
- `/wp-admin` : thème actif, 5 plugins actifs, langues FR/EN, formulaires Contact/Devis rendus.
- Soumettre un formulaire → entrée visible dans **Flamingo** (le mail peut ne pas partir, cf. Notes).

---

## Reprise vers Infomaniak (mise en production réelle)

Infomaniak Hébergement Web = mutualisé (pas de Docker). Même procédure, **une dernière fois**,
sans ressaisir le contenu :

1. Déployer le **thème** (git/rsync + `npm run build` en CI, cf. spec §3) et installer les **5 plugins** (mêmes versions).
2. Activer le thème (recrée rôle client, CPT, taxonomies, groupes ACF, formulaires CF7).
3. Exporter la base **du VPS** (`wp db export`) — pas celle du local, pour capturer les ajustements de recette — puis `wp db import` sur Infomaniak.
4. `wp search-replace 'https://demo-adaptours.mondomaine.fr' 'https://adaptours.fr' --all-tables --precise`
5. Copier `wp-content/uploads/` (SFTP).
6. `wp rewrite flush`.

MariaDB → MariaDB, versions PHP/WP à aligner sur l'offre Infomaniak (spec §4, à confirmer).

---

## Opérations courantes

```bash
docker compose exec -u www-data wordpress wp <commande>   # wp-cli dans le conteneur
docker compose exec -T db sh -c 'exec mariadb-dump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup-$(date +%F).sql   # sauvegarde
docker compose down                # stop (conserve les volumes db_data / uploads)
docker compose build && docker compose up -d   # redéploiement après modif du thème
```

## Notes / limites

- **Emails** : pas de SMTP → `wp_mail()` échouera probablement, mais **Flamingo enregistre quand même** les soumissions (démo OK). Ajouter un plugin SMTP si l'envoi réel est nécessaire.
- **Nettoyage démo** (optionnel avant présentation/prod) : supprimer « Sample Page », « Hello World », « Contenu riche (démo) », la destination « Test » et les doublons EN seedés non traduits.
- **Version** : image en PHP 8.3 (= wp-env). À réaligner sur la version Infomaniak avant la vraie prod.
- **Réseau `proxy`** : `docker compose up` échoue s'il n'existe pas — il est fourni par la stack NPM (déclaré `external`).
