#!/usr/bin/env python3


import requests
import sys

# ═══════════════════════════════════════════════════════════════
# CONFIGURATION — Modifie ces valeurs selon ton environnement
# ═══════════════════════════════════════════════════════════════

SOURCE_URL = "https://cidata.cilogistique.ci/EmuciServices_prod/getdata/ControlPayement/20260501/20260531"   # ← URL qui retourne ton JSON
API_URL    = "http://127.0.0.1:8000/api/relica/primo"
BEARER_TOKEN = "5|tiIzZxz17YP7yZoxIKXJQWxZ1RiR9fIdRoJeLO1n71afb213"

# Headers pour la requête source (si besoin d'authentification)
SOURCE_HEADERS = {
    # "Authorization": "Bearer xxx",
    # "X-API-Key": "ton-api-key",
    "Accept": "application/json"
}

# Timeout en secondes
TIMEOUT = 30

# ═══════════════════════════════════════════════════════════════
# FONCTIONS
# ═══════════════════════════════════════════════════════════════

def fetch_data(url, headers, timeout):
    """Récupère les données depuis l'URL source."""
    print(f"📡 Récupération des données depuis : {url}")
    try:
        response = requests.get(url, headers=headers, timeout=timeout)
        response.raise_for_status()
        data = response.json()
        print(f"✅ Données récupérées ({len(data)} enregistrement(s))")
        return data
    except requests.exceptions.HTTPError as e:
        print(f"❌ Erreur HTTP lors de la récupération : {e}")
        sys.exit(1)
    except requests.exceptions.ConnectionError:
        print(f"❌ Impossible de se connecter à {url}")
        sys.exit(1)
    except requests.exceptions.Timeout:
        print(f"❌ Délai d'attente dépassé pour {url}")
        sys.exit(1)
    except requests.exceptions.JSONDecodeError:
        print(f"❌ La réponse n'est pas un JSON valide.")
        sys.exit(1)


def send_to_api(item, api_url, headers, timeout):
    """Envoie un enregistrement vers l'API cible."""
    payload = {
        "NUMCHRONOCIL": item.get("NUMCHRONOCIL"),
        "NUMEROCHASISIS": item.get("NUMEROCHASISIS"),
        "IDENTITE_CLIENT": item.get("IDENTITE_CLIENT"),
        "GENRE": item.get("GENRE"),
        "DATEOUVERTUREDOSSIER": item.get("DATEOUVERTUREDOSSIER")
    }

    response = requests.post(api_url, headers=headers, json=payload, timeout=timeout)
    return response


# ═══════════════════════════════════════════════════════════════
# SCRIPT PRINCIPAL
# ═══════════════════════════════════════════════════════════════

if __name__ == "__main__":

    # 1) Récupération des données source
    data_source = fetch_data(SOURCE_URL, SOURCE_HEADERS, TIMEOUT)

    # Vérification que c'est bien une liste
    if not isinstance(data_source, list):
        print(f"⚠️  La réponse n'est pas une liste. Type reçu : {type(data_source).name}")
        sys.exit(1)

    # 2) Headers pour l'API cible
    target_headers = {
        "Authorization": f"Bearer {BEARER_TOKEN}",
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    # 3) Envoi des données
    print("=" * 60)
    print("ENVOI DES DOSSIERS VERS L'API RELICA/PRIMO")
    print("=" * 60)

    success_count = 0
    error_count = 0

    for idx, item in enumerate(data_source, start=1):
        num_chrono = item.get("NUMCHRONOCIL", f"ITEM_{idx}")

        try:
            r = send_to_api(item, API_URL, target_headers, TIMEOUT)

            if r.status_code in (200, 201, 202):
                print(f"✅ [{num_chrono}] Envoyé avec succès (HTTP {r.status_code})")
                success_count += 1
            else:
                print(f"❌ [{num_chrono}] Échec — HTTP {r.status_code}")
                print(f"   Réponse : {r.text[:200]}")
                error_count += 1

        except requests.exceptions.ConnectionError:
            print(f"❌ [{num_chrono}] Connexion refusée — vérifie que l'API tourne sur {API_URL}")
            error_count += 1
        except requests.exceptions.Timeout:
            print(f"❌ [{num_chrono}] Délai d'attente dépassé")
            error_count += 1
        except Exception as e:
            print(f"❌ [{num_chrono}] Exception : {e}")
            error_count += 1

    # 4) Résumé
    print("=" * 60)
    print(f"RÉSULTAT : {success_count} succès | {error_count} échecs sur {len(data_source)} total")
    print("=" * 60)