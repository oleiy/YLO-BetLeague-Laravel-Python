import os

# ============================================
# PATH CONFIGURATION
# ============================================
# Plik zawiera ścieżki do katalogów oraz plików
# wykorzystywanych przez system analizy danych i aplikację webową.

# Główny katalog projektu
BASE_DIR = os.path.dirname(os.path.dirname(__file__))

# Katalog z plikami danych i statystyk
DATA_DIR = os.path.join(BASE_DIR, "data")

# Plik JSON zawierający przetworzone statystyki drużyn
PROCESSED_STATS_FILE = os.path.join(
    DATA_DIR,
    "processed_stats.json"
)


# Ścieżka do aplikacji webowej Laravel
WEB_APP_DIR = os.path.join(
    BASE_DIR,
    "..",
    "web_app"
)

# Publiczny katalog aplikacji webowej
PUBLIC_DIR = os.path.join(
    WEB_APP_DIR,
    "public"
)

# Katalog przechowujący logotypy drużyn
TEAM_LOGOS_DIR = os.path.join(
    PUBLIC_DIR,
    "uploads",
    "teams"
)