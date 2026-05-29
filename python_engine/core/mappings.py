# ============================================
# DATA MAPPINGS
# ============================================
# Plik zawiera mapowania lig oraz nazw drużyn.
# Umożliwia dopasowanie danych z API i bazy danych
# do nazw używanych w plikach statystycznych CSV.

# Mapowanie ID lig na nazwy plików CSV zgodnie z bazą
LEAGUE_CSV_MAP = {
    17: "data/E0.csv",  # Premier League (API ID 17)
    8: "data/SP1.csv",  # La Liga (API ID 8)
    35: "data/D1.csv",  # Bundesliga (API ID 35)
    34: "data/F1.csv",  # Ligue 1 (API ID 34)
    23: "data/I1.csv",  # Serie A (API ID 23)
    7: "data/E0.csv",  # Champions League
}

# Słownik mapujący nazwy z bazy (klucze) na nazwy w plikach CSV (wartości)
TEAM_NAME_MAP = {
    # --- ANGLIA (E0.csv) ---
    "Manchester City": "Man City",
    "Manchester United": "Man United",
    "Brighton & Hove Albion": "Brighton",
    "Newcastle United": "Newcastle",
    "Nottingham Forest": "Nott'm Forest",
    "Tottenham Hotspur": "Tottenham",
    "West Ham United": "West Ham",
    "Wolverhampton": "Wolves",
    "Leeds United": "Leeds",
    "Sheffield United": "Sheffield United",
    "Luton Town": "Luton",
    # --- HISZPANIA (SP1.csv) ---
    "FC Barcelona": "Barcelona",
    "Real Madrid": "Real Madrid",
    "Villarreal": "Villarreal",
    "Atlético Madrid": "Ath Madrid",
    "Real Betis": "Betis",
    "Celta Vigo": "Celta",
    "Real Sociedad": "Sociedad",
    "Athletic Club": "Ath Bilbao",
    "Girona FC": "Girona",
    "Rayo Vallecano": "Vallecano",
    "Deportivo Alavés": "Alaves",
    "Levante UD": "Levante",
    "Espanyol": "Espanol",
    "Real Oviedo": "Oviedo",
    # --- NIEMCY (D1.csv) ---
    "FC Bayern München": "Bayern Munich",
    "Borussia Dortmund": "Dortmund",
    "RB Leipzig": "RB Leipzig",
    "VfB Stuttgart": "Stuttgart",
    "TSG Hoffenheim": "Hoffenheim",
    "Bayer 04 Leverkusen": "Leverkusen",
    "Eintracht Frankfurt": "Ein Frankfurt",
    "SC Freiburg": "Freiburg",
    "1. FSV Mainz 05": "Mainz",
    "1. FC Union Berlin": "Union Berlin",
    "FC Augsburg": "Augsburg",
    "Borussia M'gladbach": "M'gladbach",
    "SV Werder Bremen": "Werder Bremen",
    "1. FC Köln": "FC Koln",
    "FC St. Pauli": "St Pauli",
    "VfL Wolfsburg": "Wolfsburg",
    "1. FC Heidenheim": "Heidenheim",
    "Hamburger SV": "Hamburg",
    # --- FRANCJA (F1.csv) ---
    "Paris Saint-Germain": "Paris SG",
    "RC Lens": "Lens",
    "Olympique de Marseille": "Marseille",
    "AS Monaco": "Monaco",
    "Olympique Lyonnais": "Lyon",
    "Stade Rennais": "Rennes",
    "RC Strasbourg": "Strasbourg",
    "Stade Brestois": "Brest",
    "Nice": "Nice",
    "Le Havre": "Le Havre",
    "Toulouse": "Toulouse",
    "Lille": "Lille",
    "Auxerre": "Auxerre",
    "Angers": "Angers",
    "Paris FC": "Paris FC",
    # --- WŁOCHY (I1.csv) ---
    "Inter": "Inter",
    "Juventus": "Juventus",
    "Atalanta": "Atalanta",
    "Napoli": "Napoli",
    "AC Milan": "Milan",
    "Lazio": "Lazio",
    "Roma": "Roma",
    "Fiorentina": "Fiorentina",
    "Bologna": "Bologna",
    "Genoa": "Genoa",
    "Torino": "Torino",
    "Udinese": "Udinese",
    "Sassuolo": "Sassuolo",
    "Hellas Verona": "Verona",
    "Cagliari": "Cagliari",
}

def get_csv_team_name(db_name):
    """Zwraca nazwę z CSV na podstawie nazwy z bazy, lub bazową jeśli brak mapowania."""
    return TEAM_NAME_MAP.get(db_name, db_name)
