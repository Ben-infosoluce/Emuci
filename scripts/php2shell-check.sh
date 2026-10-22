#!/usr/bin/env bash
#
# php2shell-check.sh
# Vérifie l'exposition à CVE-2026-109618 et CVE-2026-109619 (RCE critique PHP).
#
# Lecture seule : ne modifie aucune configuration, n'envoie aucune requête.
# Compatible bash 3.2 (macOS) : pas de tableaux associatifs.
#
# Codes de sortie :
#   0  toutes les cibles sont corrigées
#   2  au moins une cible vulnérable ou en fin de vie
#   3  aucune cible inspectable
#
# Usage :
#   bash scripts/php2shell-check.sh              # local + conteneurs Docker
#   bash scripts/php2shell-check.sh --quiet      # code de sortie seulement
#   bash scripts/php2shell-check.sh --no-containers
#   bash scripts/php2shell-check.sh --ssh user@host
#
set -uo pipefail

# Versions minimales corrigées (avis php.net du 21/10/2026)
# 8.2.35 | 8.3.36 | 8.4.27 | 8.5.12
FIXED_MAJOR=9        # 9.0+ est postérieur aux correctifs
FIXED_MINOR_8=6      # 8.6+ est postérieur aux correctifs

QUIET=0
CHECK_CONTAINERS=1
SSH_TARGET=""

while [ $# -gt 0 ]; do
  case "$1" in
    --quiet)          QUIET=1 ;;
    --no-containers)  CHECK_CONTAINERS=0 ;;
    --ssh)            SSH_TARGET="${2:-}"; shift ;;
    -h|--help)        sed -n '2,22p' "$0"; exit 0 ;;
  esac
  shift
done

VULN=0
EOL=0
OK=0
SEEN=0

say() { [ "$QUIET" -eq 1 ] || printf '%s\n' "$*"; }

# min_patch <maj.min> -> patch minimal corrigé, ou vide si branche inconnue
min_patch() {
  case "$1" in
    8.2) echo 35 ;;
    8.3) echo 36 ;;
    8.4) echo 27 ;;
    8.5) echo 12 ;;
    *)   echo "" ;;
  esac
}

# verdict <version> -> FIXED | VULN | EOL | UNKNOWN
verdict() {
  local ver maj min pat need
  ver="${1%%-*}"                       # 8.3.36-alpine -> 8.3.36
  maj=$(printf '%s' "$ver" | cut -d. -f1)
  min=$(printf '%s' "$ver" | cut -d. -f2)
  pat=$(printf '%s' "$ver" | cut -d. -f3)

  # Tout doit être numérique
  case "$maj" in ''|*[!0-9]*) echo "UNKNOWN"; return ;; esac
  case "$min" in ''|*[!0-9]*) min=0 ;; esac
  case "$pat" in ''|*[!0-9]*) pat=0 ;; esac

  if [ "$maj" -ge "$FIXED_MAJOR" ]; then echo "FIXED"; return; fi
  if [ "$maj" -eq 8 ] && [ "$min" -ge "$FIXED_MINOR_8" ]; then echo "FIXED"; return; fi

  need=$(min_patch "${maj}.${min}")
  if [ -n "$need" ]; then
    if [ "$pat" -ge "$need" ]; then echo "FIXED"; else echo "VULN"; fi
    return
  fi

  # 5.x, 6.x, 7.x, 8.0, 8.1 : plus de correctif upstream
  if [ "$maj" -lt 8 ] || { [ "$maj" -eq 8 ] && [ "$min" -le 1 ]; }; then
    echo "EOL"; return
  fi

  echo "UNKNOWN"
}

record() {
  local label="$1" ver="$2" v
  SEEN=$((SEEN + 1))
  v=$(verdict "$ver")
  case "$v" in
    FIXED) OK=$((OK + 1)) ;;
    VULN)  VULN=$((VULN + 1)) ;;
    EOL)   EOL=$((EOL + 1)); v="EOL (aucun correctif amont)" ;;
  esac
  say "$(printf '%-34s %-16s %s' "$label" "$ver" "$v")"
}

say ""
say "php2shell — CVE-2026-109618 / CVE-2026-109619"
say "Minimal corrigé : 8.2.35 | 8.3.36 | 8.4.27 | 8.5.12"
say "-----------------------------------------------------------------------"
say "$(printf '%-34s %-16s %s' 'CIBLE' 'VERSION' 'ÉTAT')"
say "-----------------------------------------------------------------------"

# --- 1. PHP local -----------------------------------------------------------
if command -v php >/dev/null 2>&1; then
  record "local ($(php -r 'echo PHP_SAPI;' 2>/dev/null || echo cli))" \
         "$(php -r 'echo PHP_VERSION;' 2>/dev/null)"
else
  say "$(printf '%-34s %-16s %s' 'local' '-' 'php introuvable')"
fi

# --- 2. PHP distant via SSH --------------------------------------------------
if [ -n "$SSH_TARGET" ]; then
  REMOTE_VER=$(ssh -o BatchMode=yes -o ConnectTimeout=5 "$SSH_TARGET" \
    'php -r "echo PHP_VERSION;"' 2>/dev/null)
  if [ -n "$REMOTE_VER" ]; then
    record "ssh:$SSH_TARGET" "$REMOTE_VER"
  else
    say "$(printf '%-34s %-16s %s' "ssh:$SSH_TARGET" '-' 'injoignable')"
  fi
fi

# --- 3. Conteneurs Docker ----------------------------------------------------
if [ "$CHECK_CONTAINERS" -eq 1 ] && command -v docker >/dev/null 2>&1 \
   && docker info >/dev/null 2>&1; then
  FOUND=0
  while IFS= read -r cname; do
    [ -z "$cname" ] && continue
    FOUND=1
    CVER=$(docker exec "$cname" php -r 'echo PHP_VERSION;' 2>/dev/null)
    if [ -n "$CVER" ]; then
      record "docker:$cname" "$CVER"
    else
      IMG=$(docker inspect --format '{{.Config.Image}}' "$cname" 2>/dev/null)
      record "docker:$cname (sans php)" "${IMG:--}"
    fi
  done < <(docker ps --format '{{.Names}}' 2>/dev/null)
  [ "$FOUND" -eq 0 ] && say "$(printf '%-34s %-16s %s' 'docker' '-' 'aucun conteneur actif')"
else
  say "$(printf '%-34s %-16s %s' 'docker' '-' 'daemon indisponible — non inspecté')"
fi

say "-----------------------------------------------------------------------"
if [ "$SEEN" -eq 0 ]; then
  say "Aucune cible inspectable."
  say ""
  exit 3
fi

say "Corrigées : $OK | Vulnérables : $VULN | Fin de vie : $EOL"
say ""

if [ "$VULN" -gt 0 ] || [ "$EOL" -gt 0 ]; then
  [ "$QUIET" -eq 0 ] && say "ACTION REQUISE — viser 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12."
  [ "$QUIET" -eq 0 ] && say ""
  exit 2
fi

[ "$QUIET" -eq 0 ] && say "OK — aucune version vulnérable détectée."
[ "$QUIET" -eq 0 ] && say ""
exit 0
