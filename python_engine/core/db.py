import sqlite3
import os
from pathlib import Path
from dotenv import load_dotenv

# ============================================
# LOAD ENV
# ============================================

load_dotenv()

# ============================================
# PROJECT ROOT DETECTION
# ============================================

BASE_DIR = Path(__file__).resolve().parents[2]

# ============================================
# DATABASE PATH (SAFE + PORTABLE)
# ============================================

# 1. próbujemy z ENV (najlepsze rozwiązanie)
DB_ENV_PATH = os.getenv("DB_PATH")

if DB_ENV_PATH:
    DB_PATH = (BASE_DIR / DB_ENV_PATH).resolve()
    print("PYTHON DB:", DB_PATH)
    print("EXISTS:", DB_PATH.exists())
else:
    # 2. fallback (domyślna struktura projektu)
    DB_PATH = (BASE_DIR / "web_app" / "database" / "database.sqlite").resolve()


# ============================================
# CONNECTION
# ============================================

def get_db_connection():

    if not DB_PATH.exists():
        raise FileNotFoundError(
            f"Nie znaleziono bazy SQLite:\n{DB_PATH}"
        )

    conn = sqlite3.connect(DB_PATH)

    # rekordy jako dict (kluczowe dla Twojego kodu!)
    conn.row_factory = sqlite3.Row

    return conn