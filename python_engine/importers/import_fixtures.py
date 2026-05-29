import time

from datetime import datetime, timedelta

from core.db import get_db_connection
from core.api import make_request


"""
Skrypt odpowiedzialny za pobieranie terminarza meczów z API SofaScore
i synchronizację danych w tabeli fixtures.
Dodaje nowe mecze oraz aktualizuje daty istniejących spotkań.
"""

# Lista wybranych lig pobieranych z API
TARGET_LEAGUES = [17, 8, 23, 35, 34, 7]


# Pobiera mecze z kolejnych dni i zapisuje je do bazy danych.
# Obsługuje zarówno dodawanie nowych spotkań,
# jak i aktualizację istniejących rekordów.
def fetch_fixtures_for_next_days(days=14):
    conn = get_db_connection()
    cursor = conn.cursor()

    start_date = datetime.now()

    # Iteracja po kolejnych dniach do przodu
    for i in range(days):
        current_date = (
            start_date + timedelta(days=i)
        ).strftime("%Y-%m-%d")

        print(f"\n[INFO] Checking fixtures for {current_date}...")

        # Pobranie listy meczów dla konkretnej daty
        data = make_request(
            "match/list",
            {
                "date": current_date,
                "sport_slug": "football"
            }
        )

        if not data:
            print(f"[ERROR] API error for {current_date}")
            continue

        events = data

        # Przetwarzanie każdego meczu zwróconego przez API
        for event in events:
            unique_tournament = event.get(
                "uniqueTournament",
                {}
            )

            # Pobranie identyfikatora ligi z API
            api_league_id = unique_tournament.get("id")

            # Pomijanie lig spoza listy docelowej
            if api_league_id not in TARGET_LEAGUES:
                continue

            api_id = event.get("id")

            home_team = event.get("homeTeam", {})
            away_team = event.get("awayTeam", {})

            home_api_id = home_team.get("id")
            away_api_id = away_team.get("id")

            # Konwersja timestampu API na format daty
            timestamp = event.get("timestamp")

            match_date = datetime.fromtimestamp(
                timestamp
            ).strftime("%Y-%m-%d %H:%M:%S")

            # Wyszukiwanie wewnętrznych ID ligi i drużyn w bazie danych
            cursor.execute(
                """
                SELECT id
                FROM leagues
                WHERE api_id = ?
                """,
                (api_league_id,)
            )

            league_row = cursor.fetchone()

            cursor.execute(
                """
                SELECT id
                FROM teams
                WHERE api_id = ?
                """,
                (home_api_id,)
            )

            home_row = cursor.fetchone()

            cursor.execute(
                """
                SELECT id
                FROM teams
                WHERE api_id = ?
                """,
                (away_api_id,)
            )

            away_row = cursor.fetchone()

            # Pomijanie rekordów bez powiązania w bazie
            if not league_row:
                print(f"[SKIP] League not found: {api_league_id}")
                continue

            # Sprawdzenie drużyny gospodarzy
            if not home_row:
                print(f"[SKIP] Home team not found: {home_api_id}")
                continue

            # Sprawdzenie drużyny gości
            if not away_row:
                print(f"[SKIP] Away team not found: {away_api_id}")
                continue

            league_id = league_row["id"]
            home_team_id = home_row["id"]
            away_team_id = away_row["id"]

            # Sprawdzenie czy mecz istnieje już w bazie danych
            cursor.execute(
                """
                SELECT id
                FROM fixtures
                WHERE api_id = ?
                """,
                (api_id,)
            )

            existing_fixture = cursor.fetchone()

            # Aktualizacja istniejącego meczu
            if existing_fixture:
                cursor.execute(
                    """
                    UPDATE fixtures
                    SET
                        match_date = ?,
                        updated_at = datetime('now')
                    WHERE api_id = ?
                    """,
                    (
                        match_date,
                        api_id
                    )
                )

                print(f"[UPDATE] Fixture {api_id}")

            # Dodanie nowego meczu
            else:
                cursor.execute(
                    """
                    INSERT INTO fixtures (
                        api_id,
                        league_id,
                        home_team_id,
                        away_team_id,
                        match_date,
                        status,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'NS',
                        datetime('now'),
                        datetime('now')
                    )
                    """,
                    (
                        api_id,
                        league_id,
                        home_team_id,
                        away_team_id,
                        match_date
                    )
                )

                print(
                    f"[NEW] "
                    f"{home_team.get('name')} "
                    f"vs "
                    f"{away_team.get('name')}"
                )

        # Zapis zmian po zakończeniu przetwarzania dnia
        conn.commit()
        # Krótkie opóźnienie zabezpieczające przed spamowaniem API
        time.sleep(1)

    cursor.close()
    conn.close()

    print("\n[DONE] Fixtures updated")


# Start skryptu
if __name__ == "__main__":
    fetch_fixtures_for_next_days(14)