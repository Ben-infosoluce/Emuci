#!/usr/bin/env python3

import requests
import sys
from datetime import datetime

# ═══════════════════════════════════════════════════════════════
# CONFIGURATION — À adapter selon l'environnement
# ═══════════════════════════════════════════════════════════════

# URL de l'API source des données PRIMO-ESPECE
SOURCE_URL = "https://votre-api-source.com/api/primo/espece/donnees"

# URL de l'API locale pour l'enregistrement des paiements
API_URL = "http://127.0.0.1:8000/api/primo/espece"

# Token d'authentification (à générer dans l'application)
BEARER_TOKEN = "votre_token_bearer_ici"

# Headers pour la requête source
SOURCE_HEADERS = {
    "Accept": "application/json",
    "Authorization": "Bearer votre_token_source_ici"  # Si nécessaire
}

# Timeout en secondes
TIMEOUT = 30

# ═══════════════════════════════════════════════════════════════
# FONCTIONS
# ═══════════════════════════════════════════════════════════════

def fetch_data(url, headers, timeout):
    """Récupère les données depuis l'URL source."""
    print(f"📡 Récupération des données PRIMO-ESPECE depuis : {url}")
    try:
        response = requests.get(url, headers=headers, timeout=timeout)
        response.raise_for_status()
        data = response.json()
        print(f"✅ Données récupérées ({len(data) if isinstance(data, list) else 1} enregistrement(s))")
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
    except Exception as e:
        print(f"❌ Erreur inattendue : {str(e)}")
        sys.exit(1)

def send_to_primo_espece(item, api_url, headers, timeout):
    """Envoie un enregistrement vers l'API PRIMO-ESPECE."""
    payload = {
        "NUMCHRONOCIL": item.get("NUMCHRONOCIL"),
        "NUMEROCHASISIS": item.get("NUMEROCHASISIS"),
        "IDENTITE_CLIENT": item.get("IDENTITE_CLIENT"),
        "MONTANT_TOTAL": item.get("MONTANT_TOTAL"),
        "DATE_PAIEMENT": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "DEVISE": "XOF",
        "MODE_PAIEMENT": "ESPECES",
        "REFERENCE_PAIEMENT": f"ESP-{datetime.now().strftime('%Y%m%d%H%M%S')}",
        "OBSERVATIONS": item.get("OBSERVATIONS", "")
    }
    
    try:
        response = requests.post(api_url, headers=headers, json=payload, timeout=timeout)
        return response
    except requests.exceptions.ConnectionError:
        print(f"❌ Connexion refusée — vérifie que l'API tourne sur {api_url}")
        return None
    except requests.exceptions.Timeout:
        print("❌ Délai d'attente dépassé lors de l'envoi")
        return None
    except Exception as e:
        print(f"❌ Erreur inattendue lors de l'envoi : {str(e)}")
        return None

# ═══════════════════════════════════════════════════════════════
# SCRIPT PRINCIPAL
# ═══════════════════════════════════════════════════════════════

if __name__ == "__main__":
    # 1) Récupération des données source
    print("=" * 70)
    print("DÉMARRAGE DU SCRIPT PRIMO-ESPECE")
    print("=" * 70)
    
    data_source = fetch_data(SOURCE_URL, SOURCE_HEADERS, TIMEOUT)

    if not isinstance(data_source, list):
        data_source = [data_source]

    # 2) Headers pour l'API cible
    target_headers = {
        "Authorization": f"Bearer {BEARER_TOKEN}",
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    # 3) Envoi des données
    print("=" * 70)
    print("ENVOI DES PAIEMENTS EN ESPÈCES VERS PRIMO-ESPECE")
    print("=" * 70)

    success_count = 0
    error_count = 0

    for idx, item in enumerate(data_source, start=1):
        num_chrono = item.get("NUMCHRONOCIL", f"ITEM_{idx}")

        try:
            print(f"\n🔹 Traitement du dossier {num_chrono} ({idx}/{len(data_source)})")
            
            response = send_to_primo_espece(item, API_URL, target_headers, TIMEOUT)

            if response is None:
                print(f"❌ [{num_chrono}] Erreur lors de l'envoi")
                error_count += 1
                continue

            if response.status_code in (200, 201, 202):
                result = response.json()
                if result.get('success'):
                    print(f"✅ [{num_chrono}] Paiement enregistré avec succès")
                    success_count += 1
                else:
                    print(f"⚠️  [{num_chrono}] Échec : {result.get('message', 'Aucun message d\'erreur')}")
                    error_count += 1
            else:
                print(f"❌ [{num_chrono}] Erreur HTTP {response.status_code}")
                print(f"   Réponse : {response.text[:200]}")
                error_count += 1

        except Exception as e:
            print(f"❌ [{num_chrono}] Exception : {str(e)}")
            error_count += 1

    # 4) Résumé
    print("\n" + "=" * 70)
    print("RÉSULTAT FINAL")
    print("=" * 70)
    print(f"✅ Succès : {success_count}")
    print(f"❌ Échecs : {error_count}")
    print(f"📊 Total  : {len(data_source)}")
    print("=" * 70)
    
    # Code de sortie en fonction du résultat
    if error_count > 0:
        sys.exit(1)
    else:
        sys.exit(0)
