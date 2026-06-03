import time
import os

from datetime import datetime, timedelta

from core.db import get_db_connection
from core.api import make_request


"""
Skrypt odpowiedzialny za pobieranie terminarza meczów z API SofaScore
i synchronizację danych w tabeli fixtures.

Na potrzeby demonstracji projektu daty spotkań mogą zostać
przesunięte o liczbę dni określoną w zmiennej
FIXTURES_DEMO_DATE_SHIFT_DAYS.
"""

TARGET_LEAGUES = [17, 8, 23, 35, 34, 7]

FIXTURES_IMPORT_MODE = os.getenv("FIXTURES_IMPORT_MODE", "production").lower()
DEMO_DATE_SHIFT_DAYS = int(os.getenv("FIXTURES_DEMO_DATE_SHIFT_DAYS", "14"))


def fetch_fixtures_for_next_days(days=14):

    print(f"[INFO] Fixtures import mode: {FIXTURES_IMPORT_MODE}")

    if FIXTURES_IMPORT_MODE == "test":
        print(f"[INFO] Demo date shift: +{DEMO_DATE_SHIFT_DAYS} days")

    conn = get_db_connection()
    cursor = conn.cursor()

    if FIXTURES_IMPORT_MODE == "test":
        start_date = datetime.now() - timedelta(days=14)
    else:
        start_date = datetime.now()

    for i in range(days):
        current_date = (
            start_date + timedelta(days=i)
        ).strftime("%Y-%m-%d")

        print(f"\n[INFO] Checking fixtures for {current_date}...")

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

        for event in events:
            unique_tournament = event.get("uniqueTournament", {})
            api_league_id = unique_tournament.get("id")

            if api_league_id not in TARGET_LEAGUES:
                continue

            api_id = event.get("id")

            home_team = event.get("homeTeam", {})
            away_team = event.get("awayTeam", {})

            home_api_id = home_team.get("id")
            away_api_id = away_team.get("id")

            timestamp = event.get("timestamp")

            if not timestamp:
                print(f"[SKIP] Missing timestamp for event {api_id}")
                continue

            match_datetime = datetime.fromtimestamp(timestamp)

            if FIXTURES_IMPORT_MODE == "test":
                match_datetime = match_datetime + timedelta(days=DEMO_DATE_SHIFT_DAYS)

            match_date = match_datetime.strftime("%Y-%m-%d %H:%M:%S")

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

            if not league_row:
                print(f"[SKIP] League not found: {api_league_id}")
                continue

            if not home_row:
                print(f"[SKIP] Home team not found: {home_api_id}")
                continue

            if not away_row:
                print(f"[SKIP] Away team not found: {away_api_id}")
                continue

            league_id = league_row["id"]
            home_team_id = home_row["id"]
            away_team_id = away_row["id"]

            cursor.execute(
                """
                SELECT id
                FROM fixtures
                WHERE api_id = ?
                """,
                (api_id,)
            )

            existing_fixture = cursor.fetchone()

            if existing_fixture:
                cursor.execute(
                    """
                    UPDATE fixtures
                    SET
                        league_id = ?,
                        home_team_id = ?,
                        away_team_id = ?,
                        match_date = ?,
                        updated_at = datetime('now')
                    WHERE api_id = ?
                    """,
                    (
                        league_id,
                        home_team_id,
                        away_team_id,
                        match_date,
                        api_id
                    )
                )

                print(
                    f"[UPDATE] {home_team.get('name')} "
                    f"vs {away_team.get('name')} "
                    f"({match_date})"
                )

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
                    f"[NEW] {home_team.get('name')} "
                    f"vs {away_team.get('name')} "
                    f"({match_date})"
                )

        conn.commit()
        time.sleep(1)

    cursor.close()
    conn.close()

    print("\n[DONE] Fixtures updated")


if __name__ == "__main__":
    fetch_fixtures_for_next_days(14)