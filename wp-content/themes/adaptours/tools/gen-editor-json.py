#!/usr/bin/env python3
"""
Génère les fichiers de traduction JS de l'éditeur Gutenberg (format Jed), un par bloc,
nommés d'après le handle du script d'édition :

    languages/adaptours-<locale>-adaptours-<slug>-editor-script.json

WordPress (load_script_textdomain) teste ce nom basé sur le handle EN PRIORITÉ, avant le
nom basé sur md5(chemin) — on évite ainsi le piège du hash (md5 du chemin *buildé* ≠ md5 du
chemin *source* produit par `wp i18n make-json`).

Entrées :
  - languages/adaptours.pot  → références (#: blocks/<slug>/index.js) = quelle chaîne va dans quel bloc.
  - languages/<locale>.po     → traductions.

À relancer après tout `npm run build` qui modifie les chaînes __() des blocks/*/index.js, puis
après avoir mis à jour le .pot (make-pot) et le .po :  python3 tools/gen-editor-json.py en_US

Usage : python3 tools/gen-editor-json.py [locale]   (défaut : en_US)
"""
import re, json, os, sys

LOCALE = sys.argv[1] if len(sys.argv) > 1 else "en_US"
DOMAIN = "adaptours"
HERE = os.path.dirname(os.path.abspath(__file__))
LANG = os.path.join(HERE, "..", "languages")

def parse_po(path):
    entries = []
    cur = {"ctx": None, "msgid": None, "msgstr": None, "refs": []}
    field = None
    def flush():
        nonlocal cur
        if cur["msgid"] is not None:
            entries.append(cur)
        cur = {"ctx": None, "msgid": None, "msgstr": None, "refs": []}
    for raw in open(path, encoding="utf-8"):
        line = raw.rstrip("\n")
        if line.strip() == "":
            flush(); field = None; continue
        if line.startswith("#:"):
            cur["refs"].extend(line[2:].strip().split()); continue
        if line.startswith("#"):
            continue
        m = re.match(r'^msgctxt "(.*)"$', line)
        if m: cur["ctx"] = m.group(1); field = "ctx"; continue
        m = re.match(r'^msgid "(.*)"$', line)
        if m: cur["msgid"] = m.group(1); field = "msgid"; continue
        if re.match(r'^msgid_plural "(.*)"$', line): field = "plural"; continue
        m = re.match(r'^msgstr(?:\[0\])? "(.*)"$', line)
        if m: cur["msgstr"] = m.group(1); field = "msgstr"; continue
        if re.match(r'^msgstr\[1\] "(.*)"$', line): field = None; continue
        m = re.match(r'^"(.*)"$', line)
        if m and field in ("ctx", "msgid", "msgstr"):
            cur[field] = (cur[field] or "") + m.group(1); continue
    flush()
    return entries

def unescape(s):
    return s.replace('\\"', '"').replace("\\n", "\n").replace("\\t", "\t").replace("\\\\", "\\")

pot = parse_po(os.path.join(LANG, "adaptours.pot"))
po  = parse_po(os.path.join(LANG, f"{LOCALE}.po"))

trans = {(e["ctx"], e["msgid"]): (e["msgstr"] or "") for e in po if e["msgid"]}

by_slug = {}
ref_re = re.compile(r'^blocks/([^/]+)/index\.js')
for e in pot:
    if not e["msgid"]:
        continue
    slugs = {m.group(1) for ref in e["refs"] for m in [ref_re.match(ref)] if m}
    if not slugs:
        continue
    en = trans.get((e["ctx"], e["msgid"]))
    if en is None:
        continue
    msgid = unescape(e["msgid"])
    key = (unescape(e["ctx"]) + "" + msgid) if e["ctx"] else msgid
    for slug in slugs:
        by_slug.setdefault(slug, {})[key] = [unescape(en)]

written = 0
for slug, msgs in sorted(by_slug.items()):
    messages = {"": {"domain": "messages", "lang": LOCALE, "plural-forms": "nplurals=2; plural=(n != 1);"}}
    messages.update(msgs)
    data = {
        "translation-revision-date": "2026-06-23 00:00+0000",
        "generator": "adaptours/tools/gen-editor-json.py",
        "domain": "messages",
        "locale_data": {"messages": messages},
    }
    fname = f"{DOMAIN}-{LOCALE}-{DOMAIN}-{slug}-editor-script.json"
    json.dump(data, open(os.path.join(LANG, fname), "w", encoding="utf-8"), ensure_ascii=False)
    written += 1

print(f"OK [{LOCALE}] — {written} fichiers JSON, {sum(len(v) for v in by_slug.values())} clés")
