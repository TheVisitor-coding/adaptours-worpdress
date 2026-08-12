#!/usr/bin/env python3
"""
Génère inc/block-default-strings.php : les appels _x() littéraux correspondant aux valeurs
par défaut éditoriales des blocs (attributes[].default des block.json).

`wp i18n make-pot` n'extrait jamais attributes[].default — il ne lit d'un block.json que
title/description/keywords/styles/variations. Sans ce fichier, les textes par défaut d'un
bloc (titres, accroches, libellés de bouton) n'entrent pas dans le .pot et s'affichent donc
en français sur les langues secondaires.

Le fichier généré n'est JAMAIS chargé par le thème : il n'existe que pour l'extraction. Le
rendu passe par le filtre render_block_data de inc/block-defaults-i18n.php, qui appelle
_x() avec le même contexte « <bloc>:<attribut> ».

À relancer après tout ajout/modification d'un attributes[].default, AVANT `wp i18n make-pot`.

Usage : python3 tools/gen-block-default-strings.py
"""
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.join(HERE, "..")
BLOCKS = os.path.join(THEME, "blocks")
OUT = os.path.join(THEME, "inc", "block-default-strings.php")

# Valeurs techniques (jetons de style, ancres) : ce sont des identifiants, pas du texte.
# Une heuristique de forme ne les distinguerait pas d'un vrai contenu (« section » est un
# défaut éditorial, « surface » un jeton) : la liste est donc explicite.
SKIP = {
    "adaptours/card-grid:background",
    "adaptours/cards-numbered:background",
    "adaptours/media-text:background",
    "adaptours/media-text:media_position",
    "adaptours/media-full:width",
    "adaptours/quote:background",
    "adaptours/rich-text:background",
    "adaptours/hero-qsn:cta_url",
    "adaptours/team-grid:cta_url",
}

HEADER = '''<?php
/**
 * Fichier GÉNÉRÉ par tools/gen-block-default-strings.py — ne pas éditer à la main.
 *
 * Rend extractibles par `wp i18n make-pot` les valeurs par défaut éditoriales des
 * block.json. Jamais requis à l'exécution : le rendu passe par inc/block-defaults-i18n.php.
 *
 * @package Adaptours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

'''


def php_double_quoted(value):
    """Chaîne PHP en guillemets doubles : les défauts multi-lignes doivent produire un vrai \\n."""
    out = value.replace("\\", "\\\\").replace('"', '\\"').replace("$", "\\$")
    return '"' + out.replace("\r\n", "\n").replace("\n", "\\n") + '"'


def main():
    lines = []
    skipped = []
    blocks = 0

    for slug in sorted(os.listdir(BLOCKS)):
        path = os.path.join(BLOCKS, slug, "block.json")
        if not os.path.isfile(path):
            continue

        with open(path, encoding="utf-8") as handle:
            data = json.load(handle)

        name = data.get("name", "")
        attributes = data.get("attributes") or {}
        emitted = 0

        for attr, schema in attributes.items():
            if not isinstance(schema, dict) or schema.get("type") != "string":
                continue
            default = schema.get("default")
            if not isinstance(default, str) or not default.strip():
                continue

            key = f"{name}:{attr}"
            if key in SKIP:
                skipped.append(key)
                continue

            lines.append(f'_x( {php_double_quoted(default)}, "{key}", "adaptours" );')
            emitted += 1

        if emitted:
            blocks += 1

    with open(OUT, "w", encoding="utf-8") as handle:
        handle.write(HEADER)
        handle.write("\n".join(lines))
        handle.write("\n")

    print(f"OK — {len(lines)} chaînes, {blocks} blocs, {len(skipped)} attributs exclus")
    for key in sorted(skipped):
        print(f"  exclu : {key}")

    unused = SKIP - set(skipped)
    if unused:
        print("ATTENTION — entrées d'exclusion sans correspondance (bloc ou attribut renommé ?) :", file=sys.stderr)
        for key in sorted(unused):
            print(f"  {key}", file=sys.stderr)
        return 1

    return 0


if __name__ == "__main__":
    sys.exit(main())
