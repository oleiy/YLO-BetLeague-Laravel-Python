from time import sleep

from core.db import get_db_connection
from core.api import make_request

"""
Skrypt odpowiedzialny za pobieranie statystyk zakończonych meczów z API SofaScore
oraz zapisywanie ich do tabeli fixture_statistics w bazie danych.
Importowane są m.in. gole, kartki, rzuty rożne i strzały celne z podziałami na drużyny.
"""

# ============================================
# Pobiera podstawowe informacje o meczu:
# status oraz końcowy wynik gospodarzy i gości
# ============================================
def fetch_match_details(api_id):
    data = make_request(
        "match/details",
        {"match_id": str(api_id)}
    )

    if not data:
        return None

    print(data)

    # Pobranie statusu meczu
    status = data.get("status", {})

    if isinstance(status, dict):
        status_type = status.get("type")
    else:
        status_type = None

    # Pobranie wyniku gospodarzy
    home_score = data.get("homeScore", {})

    if isinstance(home_score, dict):
        home_goals = home_score.get("current", 0)
    else:
        home_goals = 0

    # Pobranie wyniku gości
    away_score = data.get("awayScore", {})

    if isinstance(away_score, dict):
        away_goals = away_score.get("current", 0)
    else:
        away_goals = 0

    return {
        "status": status_type,
        "home_goals": home_goals,
        "away_goals": away_goals
    }


# Konwersja wartości API na liczbę całkowitą.
# Zabezpiecza przed błędnymi lub pustymi danymi.

def safe_int(value):
    try:
        return int(float(value))
    except Exception:
        return 0

# Pobiera szczegółowe statystyki meczu z API
# i mapuje je na strukturę używaną w bazie danych.
def fetch_match_statistics(api_id):
    stats = {
        "home_corners": 0,
        "away_corners": 0,
        "home_yellow_cards": 0,
        "away_yellow_cards": 0,
        "home_red_cards": 0,
        "away_red_cards": 0,
        "home_shots_on_goal": 0,
        "away_shots_on_goal": 0
    }

    data = make_request(
        "match/statistics",
        {"match_id": str(api_id)}
    )

    # SofaScore zwraca listę statystyk
    if not data or not isinstance(data, list):
        return stats

    # Pobranie pełnych statystyk meczu
    all_period = next(
        (period for period in data if period.get("period") == "ALL"),
        None
    )

    if not all_period:
        return stats

    statistics_items = []

    # Połączenie wszystkich grup statystyk
    for group in all_period.get("groups", []):
        statistics_items.extend(group.get("statisticsItems", []))

    # Odczyt poszczególnych statystyk zwróconych przez API
    for item in statistics_items:
        key = item.get("key")

        home = safe_int(item.get("homeValue"))
        away = safe_int(item.get("awayValue"))

        # Rzuty rożne
        if key == "cornerKicks":
            stats["home_corners"] = home
            stats["away_corners"] = away

        # Żółte kartki
        elif key == "yellowCards":
            stats["home_yellow_cards"] = home
            stats["away_yellow_cards"] = away

        # Czerwone kartki
        elif key == "redCards":
            stats["home_red_cards"] = home
            stats["away_red_cards"] = away

        # Strzały celne
        elif key == "shotsOnGoal":
            stats["home_shots_on_goal"] = home
            stats["away_shots_on_goal"] = away

    return stats


# Zapisuje statystyki meczu do bazy danych.
# Jeśli rekord już istnieje, zostaje zaktualizowany.
def save_fixture_statistics(cursor, fixture_id, data):

    query = """
        INSERT INTO fixture_statistics (
            fixture_id,
            home_goals,
            away_goals,
            home_corners,
            away_corners,
            home_yellow_cards,
            away_yellow_cards,
            home_red_cards,
            away_red_cards,
            home_shots_on_goal,
            away_shots_on_goal
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

        ON CONFLICT(fixture_id) DO UPDATE SET
            home_goals = excluded.home_goals,
            away_goals = excluded.away_goals,
            home_corners = excluded.home_corners,
            away_corners = excluded.away_corners,
            home_yellow_cards = excluded.home_yellow_cards,
            away_yellow_cards = excluded.away_yellow_cards,
            home_red_cards = excluded.home_red_cards,
            away_red_cards = excluded.away_red_cards,
            home_shots_on_goal = excluded.home_shots_on_goal,
            away_shots_on_goal = excluded.away_shots_on_goal
    """

    values = (
        fixture_id,
        data["home_goals"],
        data["away_goals"],
        data["home_corners"],
        data["away_corners"],
        data["home_yellow_cards"],
        data["away_yellow_cards"],
        data["home_red_cards"],
        data["away_red_cards"],
        data["home_shots_on_goal"],
        data["away_shots_on_goal"]
    )

    cursor.execute(query, values)

# Pobiera mecze posiadające api_id,
# które nie mają jeszcze zapisanych statystyk
# oraz których data rozpoczęcia już minęła.
def fetch_today_fixtures(cursor):
    query = """
        SELECT
    f.id,
    f.api_id
FROM fixtures f
LEFT JOIN fixture_statistics fs
    ON fs.fixture_id = f.id
WHERE f.api_id IS NOT NULL
    AND fs.fixture_id IS NULL
    AND f.match_date <= datetime('now')
    """

    cursor.execute(query)
    return cursor.fetchall()


# Główna logika importu statystyk meczowych.
# Iteruje po meczach, pobiera dane z API i zapisuje je w bazie.
def main():
    print("[INFO] Starting fixture statistics import...")

    conn = get_db_connection()
    cursor = conn.cursor()

    fixtures = fetch_today_fixtures(cursor)

    print(f"[INFO] Found {len(fixtures)} fixtures")

    for fixture in fixtures:
        fixture_id = fixture["id"]
        api_id = fixture["api_id"]

        print(f"[INFO] Processing fixture {fixture_id} ({api_id})")

        # Pobranie danych meczu
        details = fetch_match_details(api_id)

        if not details:
            print("[WARNING] Match details not found")
            continue

        # Import tylko zakończonych meczów
        if details["status"] not in [
            "finished",
            "after_penalties",
            "after_extra_time"
        ]:
            print("[INFO] Match not finished yet")
            continue

        # Pobranie statystyk meczu
        statistics = fetch_match_statistics(api_id)

        final_data = {
            **details,
            **statistics
        }

        save_fixture_statistics(cursor, fixture_id, final_data)

        conn.commit()

        print(f"[SUCCESS] Statistics saved for fixture {fixture_id}")

        sleep(1)

    cursor.close()
    conn.close()

    print("[DONE] Fixture statistics import completed")


if __name__ == "__main__":
    main()