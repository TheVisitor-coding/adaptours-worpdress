# syntax=docker/dockerfile:1

# --- Stage 1 : build des assets du thème (Sass + blocs Gutenberg) ---
FROM node:20 AS builder
WORKDIR /theme
COPY wp-content/themes/adaptours/package.json wp-content/themes/adaptours/package-lock.json ./
RUN npm ci
COPY wp-content/themes/adaptours/ ./
RUN npm run build

# --- Stage 2 : image WordPress ---
FROM wordpress:php8.3-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends curl unzip less; \
    rm -rf /var/lib/apt/lists/*

# wp-cli (commandes de migration/maintenance via `docker compose exec`)
RUN curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Plugins — mêmes sources que .wp-env.json (ACF figé en 6.8.4)
RUN set -eux; \
    plugins_dir=/usr/src/wordpress/wp-content/plugins; \
    for url in \
      https://downloads.wordpress.org/plugin/advanced-custom-fields.6.8.4.zip \
      https://downloads.wordpress.org/plugin/contact-form-7.zip \
      https://downloads.wordpress.org/plugin/cf7-conditional-fields.zip \
      https://downloads.wordpress.org/plugin/flamingo.zip \
      https://downloads.wordpress.org/plugin/polylang.zip ; do \
        curl -fsSL -o /tmp/plugin.zip "$url"; \
        unzip -q -o /tmp/plugin.zip -d "$plugins_dir"; \
    done; \
    rm -f /tmp/plugin.zip; \
    chown -R www-data:www-data "$plugins_dir"

# Thème custom + assets compilés (repris du stage builder)
COPY --chown=www-data:www-data wp-content/themes/adaptours /usr/src/wordpress/wp-content/themes/adaptours
COPY --from=builder --chown=www-data:www-data /theme/assets/build /usr/src/wordpress/wp-content/themes/adaptours/assets/build
