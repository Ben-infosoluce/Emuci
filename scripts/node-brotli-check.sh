#!/usr/bin/env bash
#
# node-brotli-check.sh
# Vérifie l'exposition à CVE-2026-111497 (décodeur Brotli de Node.js).
#
# Lecture seule : ne modifie rien, n'envoie aucune requête.
# Compatible bash 3.2 (macOS).
#
# Codes de sortie :
#   0  version corrigée
#   2  version vulnérable ou branche EOL
#   3  node introuvable
#
# Usage :
#   bash scripts/node-brotli-check.sh
#   bash scripts/node-brotli-check.sh --quiet
#
set -uo pipefail

QUIET=0
while [ $# -gt 0 ]; do
  case "$1" in
    --quiet) QUIET=1 ;;
    -h|--help) sed -n '2,20p' "$0"; exit 0 ;;
  esac
  shift
done

say() { [ "$QUIET" -eq 1 ] || printf '%s\n' "$*"; }

# verdict <version> -> FIXED | VULN | EOL | UNKNOWN
verdict() {
  local ver maj min pat
  ver="${1#v}"
  ver="${ver%%-*}"
  ver="${ver%%+*}"
  maj=$(printf '%s' "$ver" | cut -d. -f1)
  min=$(printf '%s' "$ver" | cut -d. -f2)
  pat=$(printf '%s' "$ver" | cut -d. -f3)

  case "$maj" in ''|*[!0-9]*) echo "UNKNOWN"; return ;; esac
  case "$min" in ''|*[!0-9]*) min=0 ;; esac
  case "$pat" in ''|*[!0-9]*) pat=0 ;; esac

  case "$maj" in
    22)
      if [ "$min" -gt 23 ] || { [ "$min" -eq 23 ] && [ "$pat" -ge 4 ]; }; then
        echo "FIXED"
      else
        echo "VULN"
      fi
      ;;
    24)
      if [ "$min" -gt 22 ] || { [ "$min" -eq 22 ] && [ "$pat" -ge 1 ]; }; then
        echo "FIXED"
      else
        echo "VULN"
      fi
      ;;
    26)
      if [ "$min" -gt 12 ] || { [ "$min" -eq 12 ] && [ "$pat" -ge 1 ]; }; then
        echo "FIXED"
      else
        echo "VULN"
      fi
      ;;
    *)
      # 20 et antérieurs, 23 et 25 : branches EOL, sans correctif amont.
      # 27+ : publiées après le correctif.
      if [ "$maj" -gt 26 ]; then
        echo "FIXED"
      else
        echo "EOL"
      fi
      ;;
  esac
}

say ""
say "node-brotli — CVE-2026-111497"
say "Minimal corrigé : 22.23.4 | 24.22.1 | 26.12.1"
say "-----------------------------------------------------------------------"

if ! command -v node >/dev/null 2>&1; then
  say "$(printf '%-34s %-16s %s' 'local' '-' 'node introuvable')"
  say ""
  exit 3
fi

NODE_VER=$(node -p 'process.versions.node' 2>/dev/null)
if [ -z "$NODE_VER" ]; then
  say "$(printf '%-34s %-16s %s' 'local' '-' 'version illisible')"
  say ""
  exit 3
fi

STATE=$(verdict "$NODE_VER")
DISPLAY_STATE="$STATE"
case "$STATE" in
  EOL) DISPLAY_STATE="EOL (aucun correctif amont)" ;;
esac

say "$(printf '%-34s %-16s %s' 'local (node)' "v$NODE_VER" "$DISPLAY_STATE")"
say "-----------------------------------------------------------------------"

if [ "$STATE" = "FIXED" ]; then
  say "OK — Node.js n'est pas vulnérable à CVE-2026-111497."
  say ""
  exit 0
fi

say "ACTION REQUISE — viser Node.js 22.23.4 / 24.22.1 / 26.12.1."
say ""
exit 2
