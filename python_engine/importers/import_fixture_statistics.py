from time import sleep

from core.db import get_db_connection
from core.api import make_request


"""
Skrypt odpowiedzialny za pobieranie statystyk zakończonych meczów z API SofaScore
oraz zapisywanie ich do tabeli fixture_statistics w bazie danych.

Dodatkowe zabezpieczenie:
przed zapisem wyników skrypt sprawdza, czy api_id meczu faktycznie odpowiada
tym samym drużynom, które są zapisane lokalnie w bazie danych.
"""


def fetch_match_details(api_id):
    data = make_request(
        "match/details",
        {"match_id": str(api_id)}
    )

    if not data:
        return None

    status = data.get("status", {})
    status_type = status.get("type") if isinstance(status, dict) else None

    home_score = data.get("homeScore", {})
    away_score = data.get("awayScore", {})

    home_goals = home_score.get("current", 0) if isinstance(home_score, dict) else 0
    away_goals = away_score.get("current", 0) if isinstance(away_score, dict) else 0

    home_team = data.get("homeTeam", {})
    away_team = data.get("awayTeam", {})

    return {
        "status": status_type,
        "home_goals": home_goals,
        "away_goals": away_goals,
        "home_api_id": home_team.get("id"),
        "away_api_id": away_team.get("id"),
        "home_name": home_team.get("name"),
        "away_name": away_team.get("name"),
    }


def safe_int(value):
    try:
        return int(float(value))
    except Exception:
        return 0


def fetch_match_statistics(api_id):
    stats = {
        "home_corners": 0,
        "away_corners": 0,
        "home_yellow_cards": 0,
        "away_yellow_cards": 0,
        "home_red_cards": 0,
        "away_red_cards": 0,
        "home_shots_on_goal": 0,
        "away_shots_on_goal": 0,
    }

    data = make_request(
        "match/statistics",
        {"match_id": str(api_id)}
    )

    if not data or not isinstance(data, list):
        return stats

    all_period = next(
        (period for period in data if period.get("period") == "ALL"),
        None
    )

    if not all_period:
        return stats

    statistics_items = []

    for group in all_period.get("groups", []):
        statistics_items.extend(group.get("statisticsItems", []))

    for item in statistics_items:
        key = item.get("key")

        home = safe_int(item.get("homeValue"))
        away = safe_int(item.get("awayValue"))

        if key == "cornerKicks":
            stats["home_corners"] = home
            stats["away_corners"] = away

        elif key == "yellowCards":
            stats["home_yellow_cards"] = home
            stats["away_yellow_cards"] = away

        elif key == "redCards":
            stats["home_red_cards"] = home
            stats["away_red_cards"] = away

        elif key == "shotsOnGoal":
            stats["home_shots_on_goal"] = home
            stats["away_shots_on_goal"] = away

    return stats


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
        data["away_shots_on_goal"],
    )

    cursor.execute(query, values)


def fetch_today_fixtures(cursor):
    query = """
        SELECT
            f.id,
            f.api_id,

            ht.api_id AS home_api_id,
            ht.name AS home_name,

            at.api_id AS away_api_id,
            at.name AS away_name

        FROM fixtures f
        JOIN teams ht
            ON ht.id = f.home_team_id
        JOIN teams at
            ON at.id = f.away_team_id
        LEFT JOIN fixture_statistics fs
            ON fs.fixture_id = f.id
        WHERE f.api_id IS NOT NULL
            AND fs.fixture_id IS NULL
            AND f.status = 'FT'
    """

    cursor.execute(query)
    return cursor.fetchall()


def teams_match(fixture, details):
    return (
        fixture["home_api_id"] == details["home_api_id"]
        and fixture["away_api_id"] == details["away_api_id"]
    )


def main():
    print("[INFO] Starting fixture statistics import...")

    conn = get_db_connection()
    cursor = conn.cursor()

    fixtures = fetch_today_fixtures(cursor)

    print(f"[INFO] Found {len(fixtures)} fixtures")

    for fixture in fixtures:
        fixture_id = fixture["id"]
        api_id = fixture["api_id"]

        print("\n==========================================")
        print(f"[INFO] Processing fixture {fixture_id} ({api_id})")
        print(f"[DB] {fixture['home_name']} vs {fixture['away_name']}")

        details = fetch_match_details(api_id)

        if not details:
            print("[WARNING] Match details not found")
            continue

        print(f"[API] {details['home_name']} vs {details['away_name']}")

        if not teams_match(fixture, details):
            print("[WARNING] API fixture does not match local fixture")
            print(
                f"[DB IDS] home={fixture['home_api_id']} "
                f"away={fixture['away_api_id']}"
            )
            print(
                f"[API IDS] home={details['home_api_id']} "
                f"away={details['away_api_id']}"
            )
            print("[SKIP] Statistics not saved")
            continue

        if details["status"] not in [
            "finished",
            "after_penalties",
            "after_extra_time",
        ]:
            print("[INFO] Match not finished yet")
            continue

        statistics = fetch_match_statistics(api_id)

        final_data = {
            **details,
            **statistics,
        }

        save_fixture_statistics(cursor, fixture_id, final_data)

        conn.commit()

        print(
            f"[SUCCESS] Statistics saved for fixture {fixture_id} "
            f"({details['home_goals']}:{details['away_goals']})"
        )

        sleep(1)

    cursor.close()
    conn.close()

    print("\n[DONE] Fixture statistics import completed")


if __name__ == "__main__":
    main()