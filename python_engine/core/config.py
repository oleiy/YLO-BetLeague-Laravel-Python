import os
from dotenv import load_dotenv

# ============================================
# APPLICATION CONFIG
# ============================================
# Plik konfiguracyjny aplikacji.
# Odpowiada za ładowanie ustawień API
# oraz konfigurację SQLite.

# Załadowanie zmiennych środowiskowych
load_dotenv()

# ============================================
# API CONFIG
# ============================================

BASE_URL = "https://sofascore.p.rapidapi.com"

RAPIDAPI_KEY = os.getenv("X_RAPIDAPI_KEY")
RAPIDAPI_HOST = os.getenv("X_RAPIDAPI_HOST")

HEADERS = {
    "x-rapidapi-key": RAPIDAPI_KEY,
    "x-rapidapi-host": RAPIDAPI_HOST,
    "Content-Type": "application/json",
}

# ============================================
# SQLITE CONFIG
# ============================================

# Ścieżka do bazy SQLite Laravel
# przykład:
# D:/Programowanie/YLO_BetLeague_sqlite/web_app/database/database.sqlite

BASE_DIR = os.path.dirname(os.path.dirname(__file__))

SQLITE_PATH = os.path.join(
    BASE_DIR,
    "web_app",
    "database",
    "database.sqlite"
)