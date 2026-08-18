#!/usr/bin/env python3

import pandas as pd
import requests
import sys
import os

# ═══════════════════════════════════════════════════════════════
# CONFIGURATION — Modifie ces valeurs selon ton environnement
# ═══════════════════════════════════════════════════════════════

# EXCEL_FILE = "df_final.xlsx"   # ← Fichier Excel local
EXCEL_FILE = "df_final_production.xlsx" 

# API_URL    = "http://127.0.0.1:8000/api/importer/primo/relica/caisse"
# BEARER_TOKEN = "5|tiIzZxz17YP7yZoxIKXJQWxZ1RiR9fIdRoJeLO1n71afb213"

#production
API_URL    = "https://post.digimmat.ci/api/importer/primo/relica/caisse"
BEARER_TOKEN = "15695|V8NC9yi9j2LNHBtJYeUoneyc46yzSOKQ5INvNA6P4283c046"

# Timeout en secondes
TIMEOUT = 30

#  ═══════════════════════════════════════════════════════════════
# FONCTIONS
# ═══════════════════════════════════════════════════════════════

def load_excel_data(file_path):
    """Charge les données depuis un fichier Excel."""
    print(f"📂 Chargement des données depuis : {file_path}")
    
    if not os.path.exists(file_path):
        print(f"❌ Le fichier {file_path} n'existe pas.")
        sys.exit(1)
    
    try:
        # Lecture du fichier Excel
        df = pd.read_excel(file_path)
        
        # Convertir les colonnes de date en chaînes de caractères
        # Format attendu par Laravel : 'Ymd' (ex: '20260501')
        if 'DATEOUVERTUREDOSSIER' in df.columns:
            df['DATEOUVERTUREDOSSIER'] = pd.to_datetime(df['DATEOUVERTUREDOSSIER'], errors='coerce').dt.strftime('%Y%m%d')
        
        # Conversion en liste de dictionnaires
        data = df.to_dict(orient='records')
        
        # Remplacer les NaN par None pour éviter les problèmes JSON
        for item in data:
            for key, value in item.items():
                if pd.isna(value):
                    item[key] = None
        
        print(f"✅ Données chargées ({len(data)} enregistrement(s))")
        return data
        
    except Exception as e:
        print(f"❌ Erreur lors de la lecture du fichier Excel : {e}")
        sys.exit(1)


def send_to_api(item, api_url, headers, timeout):
    """Envoie un enregistrement vers l'API cible."""
    payload = {
        "NUMCHRONOCIL": str(item.get("NUMCHRONOCIL", "")),
        "NUMEROCHASISIS": str(item.get("NUMEROCHASISIS", "")),
        "IDENTITE_CLIENT": str(item.get("IDENTITE_CLIENT", "")),
        "GENRE": str(item.get("GENRE", "")),
        "DATEOUVERTUREDOSSIER": str(item.get("DATEOUVERTUREDOSSIER", "")),
        "flag_column": int(item.get("flag_column", 2))  # Défaut à 2 (RELICA-PRIMO) si non fourni
    }

    response = requests.post(api_url, headers=headers, json=payload, timeout=timeout)
    return response


# ═══════════════════════════════════════════════════════════════
# SCRIPT PRINCIPAL
# ═══════════════════════════════════════════════════════════════

if __name__ == "__main__":

    # 1) Chargement des données depuis Excel
    data_source = load_excel_data(EXCEL_FILE)

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
        flag_value = item.get("flag_column", 2)
        type_label = "PRIMO-ESPECE" if flag_value == 1 else "RELICA-PRIMO"

        try:
            r = send_to_api(item, API_URL, target_headers, TIMEOUT)

            if r.status_code in (200, 201, 202):
                print(f"✅ [{num_chrono}] {type_label} — Envoyé avec succès (HTTP {r.status_code})")
                success_count += 1
            else:
                print(f"❌ [{num_chrono}] {type_label} — Échec — HTTP {r.status_code}")
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