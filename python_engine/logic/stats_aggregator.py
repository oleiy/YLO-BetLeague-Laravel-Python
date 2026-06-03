import pandas as pd
import os
import json


"""Skrypt analizuje historyczne dane meczowe z plików CSV
i generuje bazę statystyk drużyn wykorzystywaną
później przez silnik wyliczający kursy zdarzeń."""


from core.paths import DATA_DIR, PROCESSED_STATS_FILE

OUTPUT_STATS_JSON = PROCESSED_STATS_FILE

LEAGUE_FILES = ["E0.csv", "SP1.csv", "D1.csv", "F1.csv", "I1.csv"]


# Funkcja przetwarza dane z wielu lig,
# wylicza średnie statystyki drużyn i zapisuje je do pliku JSON.
def aggregate_all_stats():
    print("[INFO] Starting stats aggregation...")
    # Przechowuje statystyki wszystkich drużyn
    all_teams_data = {}
    # Średnie wartości statystyczne dla każdej ligi
    league_averages = {}

    for file in LEAGUE_FILES:
        path = os.path.join(DATA_DIR, file)
        if not os.path.exists(path):
            continue

        # Wczytanie danych meczowych z pliku CSV
        df = pd.read_csv(path, low_memory=False)

        # Obliczanie średnich ligowych potrzebnych do modelu Poissona
        league_averages[file] = {
            "avg_goals": (df["FTHG"].mean() + df["FTAG"].mean()),
            "avg_corners": (df["HC"].mean() + df["AC"].mean()),
            "avg_cards": (
                df["HY"].mean()
                + df["AY"].mean()
                + (df["HR"].mean() + df["AR"].mean()) * 2
            ),
            "avg_sot": (df["HST"].mean() + df["AST"].mean()),
        }

        # Pobranie unikalnych nazw drużyn z ligi
        teams = pd.concat([df["HomeTeam"], df["AwayTeam"]]).unique()

        for team in teams:
            # Ostatnie 10 meczów domowych i 10 wyjazdowych
            h_matches = df[df["HomeTeam"] == team].tail(10)
            a_matches = df[df["AwayTeam"] == team].tail(10)

            # Funkcja pomocnicza do wyliczania punktów kartek
            # (żółta = 1, czerwona = 2)
            def get_card_pts(df_sub, my_y, my_r):
                if df_sub.empty:
                    return 0.0
                return (df_sub[my_y] + (df_sub[my_r] * 2)).mean()

            # Statystyki drużyny w meczach domowych
            home_stats = {
                "goals_scored": h_matches["FTHG"].mean() if not h_matches.empty else 0,
                "goals_conceded": (
                    h_matches["FTAG"].mean() if not h_matches.empty else 0
                ),
                "sot_won": h_matches["HST"].mean() if not h_matches.empty else 0,
                "sot_lost": h_matches["AST"].mean() if not h_matches.empty else 0,
                "corners_won": h_matches["HC"].mean() if not h_matches.empty else 0,
                "corners_lost": h_matches["AC"].mean() if not h_matches.empty else 0,
                "cards_received": get_card_pts(h_matches, "HY", "HR"),
                "cards_opponent": get_card_pts(
                    h_matches, "AY", "AR"
                ),  # Kartki przeciwnika u nas
            }

            # Statystyki drużyny w meczach wyjazdowych
            away_stats = {
                "goals_scored": a_matches["FTAG"].mean() if not a_matches.empty else 0,
                "goals_conceded": (
                    a_matches["FTHG"].mean() if not a_matches.empty else 0
                ),
                "sot_won": a_matches["AST"].mean() if not a_matches.empty else 0,
                "sot_lost": a_matches["HST"].mean() if not a_matches.empty else 0,
                "corners_won": a_matches["AC"].mean() if not a_matches.empty else 0,
                "corners_lost": a_matches["HC"].mean() if not a_matches.empty else 0,
                "cards_received": get_card_pts(a_matches, "AY", "AR"),
                "cards_opponent": get_card_pts(
                    a_matches, "HY", "HR"
                ),  # Kartki gospodarza gdy my wpadamy
            }

            # Zapis pełnych danych drużyny do pamięci
            all_teams_data[team] = {
                "league_file": file,
                "home": home_stats,
                "away": away_stats,
            }

    # Zapis przetworzonych statystyk do pliku JSON
    # wykorzystywanego przez generator kursów
    with open(OUTPUT_STATS_JSON, "w") as f:
        json.dump(
            {"teams": all_teams_data, "league_averages": league_averages}, f, indent=4
        )

    print("[DONE] Stats aggregation completed")

if __name__ == "__main__":
    aggregate_all_stats()
