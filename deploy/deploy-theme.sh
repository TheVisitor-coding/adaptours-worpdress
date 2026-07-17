#!/usr/bin/env bash
#
# Déploiement manuel du thème Adaptours vers un hébergement Infomaniak mutualisé.
# Build local (npm) puis rsync-over-SSH du SEUL dossier du thème.
#
# Usage :
#   deploy/deploy-theme.sh [--dry-run] [--no-build]
#
# Config : deploy/.env (voir deploy/.env.example) — jamais commité.
#   SSH_HOST, SSH_USER, DEPLOY_PATH (obligatoires) ; SSH_PORT, SSH_KEY (optionnels).
#
set -euo pipefail

DRY_RUN=0
DO_BUILD=1
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --no-build) DO_BUILD=0 ;;
    *) echo "Argument inconnu : $arg" >&2; exit 2 ;;
  esac
done

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="$REPO_ROOT/wp-content/themes/adaptours"
ENV_FILE="$REPO_ROOT/deploy/.env"

[ -f "$ENV_FILE" ] || { echo "Manquant : $ENV_FILE (copier deploy/.env.example)"; exit 1; }
# shellcheck disable=SC1090
set -a; . "$ENV_FILE"; set +a

: "${SSH_HOST:?SSH_HOST manquant dans deploy/.env}"
: "${SSH_USER:?SSH_USER manquant dans deploy/.env}"
: "${DEPLOY_PATH:?DEPLOY_PATH manquant dans deploy/.env}"
SSH_PORT="${SSH_PORT:-22}"

SSH_OPTS="-p $SSH_PORT -o StrictHostKeyChecking=accept-new"
[ -n "${SSH_KEY:-}" ] && SSH_OPTS="$SSH_OPTS -i $SSH_KEY -o IdentitiesOnly=yes"

if [ "$DO_BUILD" -eq 1 ]; then
  echo "▶ Build ($THEME_DIR)…"
  ( cd "$THEME_DIR" && npm run build )
fi

[ -f "$THEME_DIR/assets/build/main.css" ] || { echo "assets/build/main.css absent — build requis (retirer --no-build)."; exit 1; }

RSYNC_ARGS=(
  -rltvz --delete --delete-after
  --exclude='node_modules/'
  --exclude='.git/' --exclude='.github/' --exclude='.gitignore'
  --exclude='CC-Session-Logs/'
  --exclude='_*.php'
  --exclude='*.map'
  --exclude='.DS_Store'
  -e "ssh $SSH_OPTS"
  "$THEME_DIR/"
  "$SSH_USER@$SSH_HOST:$DEPLOY_PATH/"
)

echo "▶ rsync (dry-run) vers $SSH_USER@$SSH_HOST:$DEPLOY_PATH/"
rsync -n "${RSYNC_ARGS[@]}"

if [ "$DRY_RUN" -eq 1 ]; then
  echo "✔ Dry-run terminé (aucune écriture). Relancer sans --dry-run pour déployer."
  exit 0
fi

echo "▶ rsync (réel)…"
rsync "${RSYNC_ARGS[@]}"
echo "✔ Déploiement terminé."
