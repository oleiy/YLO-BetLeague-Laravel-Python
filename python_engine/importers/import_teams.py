import time
import urllib3
import os
import re
import unicodedata

from core.db import get_db_connection
from core.api import make_request
from core.paths import TEAM_LOGOS_DIR

"""
Skrypt odpowiedzialny za import drużyn piłkarskich z API SofaScore.
Pobiera dane zespołów, zapisuje logotypy oraz synchronizuje rekordy
w tabelach teams i league_team.
"""

# Wyłączenie ostrzeżeń SSL podczas pobierania obrazów
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Lista lig, dla których pobierane są drużyny
LEAGUES = [
    {"id": 17, "name": "Premier League"},
    {"id": 8, "name": "LaLiga"},
    {"id": 23, "name": "Serie A"},
    {"id": 35, "name": "Bundesliga"},
    {"id": 34, "name": "Ligue 1"},
    {"id": 7, "name": "Champions League"},
]

# Zamienia nazwę drużyny na bezpieczny format używany w nazwach plików
def slugify(text):

    text = unicodedata.normalize("NFKD", text)
    text = text.encode("ascii", "ignore").decode("ascii")

    text = text.lower()

    # everything non-alphanumeric -> "-"
    text = re.sub(r"[^a-z0-9]+", "-", text)

    # remove duplicate "-"
    text = re.sub(r"-+", "-", text)

    # remove "-" from start/end
    text = text.strip("-")

    return text


# Biblioteka używana do pobierania logotypów z obsługą impersonacji przeglądarki
from curl_cffi import requests as curl_requests

# Pobiera i zapisuje logotyp drużyny w folderze public/uploads/teams
def download_logo(team_id, team_name):
    # Generowanie adresu URL logotypu drużyny
    logo_url = f"https://img.sofascore.com/api/v1/team/{team_id}/image"

    safe_name = slugify(team_name)

    relative_path = f"uploads/teams/{safe_name}.png"

    full_path = os.path.join(
        TEAM_LOGOS_DIR,
        f"{safe_name}.png"
    )

    os.makedirs(os.path.dirname(full_path), exist_ok=True)

    print(f"[LOGO] Downloading: {team_name}")
    print(f"[LOGO] URL: {logo_url}")

    try:

        response = curl_requests.get(
            logo_url,
            impersonate="chrome136",
            timeout=20
        )

        print(f"[HTTP] Status: {response.status_code}")

        if response.status_code != 200:

            print(f"[ERROR] HTTP {response.status_code}")

            return None

        content_type = response.headers.get("content-type", "")

        if "image" not in content_type:

            print(f"[ERROR] Invalid content type: {content_type}")

            return None

        with open(full_path, "wb") as f:
            f.write(response.content)

        if os.path.getsize(full_path) == 0:

            print(f"[ERROR] Empty file")

            return None

        print(f"[SUCCESS] Saved: {full_path}")

        return relative_path

    except Exception as e:

        print(f"[ERROR] {e}")

        return None


# Pobiera identyfikator najnowszego sezonu dla wybranej ligi
def get_latest_season(tournament_id):

    data = make_request(
        "unique-tournament/seasons",
        {
            "unique_tournament_id": str(tournament_id)
        }
    )

    if not data:
        return None

    if len(data) == 0:
        return None

    latest_season = data[0]

    return latest_season.get("id")


# Pobiera drużyny z API i synchronizuje dane z bazą danych
def fetch_and_save_teams():

    conn = get_db_connection()
    cursor = conn.cursor()

    for league in LEAGUES:

        print(f"\n[INFO] Processing {league['name']}...")

        # Pobranie identyfikatora ligi z lokalnej bazy danych
        cursor.execute(
            """
            SELECT id
            FROM leagues
            WHERE api_id = ?
            """,
            (league["id"],),
        )

        league_row = cursor.fetchone()

        if not league_row:

            print(f"[ERROR] League not found: {league['name']}")

            continue

        league_db_id = league_row["id"]

        # Pobranie aktualnego sezonu ligi
        season_id = get_latest_season(league["id"])

        if not season_id:

            print(f"[ERROR] No season found for {league['name']}")

            continue

        print(f"[INFO] Season ID: {season_id}")

        # Pobranie tabeli ligowej zawierającej listę drużyn
        data = make_request(
            "unique-tournament/season/standings",
            {
                "unique_tournament_id": str(league["id"]),
                "season_id": str(season_id),
                "type": "total"
            }
        )

        if not data:

            print(f"[ERROR] Standings API failed for {league['name']}")

            continue

        if len(data) == 0:

            print(f"[ERROR] Empty standings for {league['name']}")

            continue

        standings = data[0]

        rows = standings.get("rows", [])

        # Przetwarzanie każdej drużyny z tabeli ligowej
        for row in rows:

            team_data = row.get("team", {})

            api_id = team_data.get("id")
            name = team_data.get("name")
            short_name = team_data.get("nameCode")

            print(f"\n[TEAM] {name}")

            logo_path = download_logo(api_id, name)

            # Sprawdzenie czy drużyna istnieje już w bazie danych
            cursor.execute(
                """
                SELECT id
                FROM teams
                WHERE api_id = ?
                """,
                (api_id,),
            )

            existing_team = cursor.fetchone()

            # Aktualizacja istniejącej drużyny
            if existing_team:

                team_db_id = existing_team["id"]

                cursor.execute(
                    """
                    UPDATE teams
                    SET
                        name = ?,
                        short_name = ?,
                        logo_path = COALESCE(?, logo_path),
                        updated_at = datetime('now')
                    WHERE id = ?
                    """,
                    (
                        name,
                        short_name,
                        logo_path,
                        team_db_id
                    )
                )

                print(f"[UPDATE] {name}")

            # Dodanie nowej drużyny do tabeli teams
            else:

                cursor.execute(
                    """
                    INSERT INTO teams (
                        api_id,
                        name,
                        short_name,
                        logo_path,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        datetime('now'),
                        datetime('now')
                    )
                    """,
                    (
                        api_id,
                        name,
                        short_name,
                        logo_path
                    )
                )

                team_db_id = cursor.lastrowid

                print(f"[INSERT] {name}")

            # Sprawdzenie relacji drużyny z ligą
            cursor.execute(
                """
                SELECT id
                FROM league_team
                WHERE league_id = ?
                AND team_id = ?
                """,
                (
                    league_db_id,
                    team_db_id
                ),
            )

            relation_exists = cursor.fetchone()

            if not relation_exists:

                cursor.execute(
                    """
                    INSERT INTO league_team (
                        league_id,
                        team_id
                    )
                    VALUES (
                        ?,
                        ?
                    )
                    """,
                    (
                        league_db_id,
                        team_db_id
                    ),
                )

                print(f"[RELATION] Added {name} to {league['name']}")

        conn.commit()

        print(f"[DONE] {league['name']} updated")

        # Opóźnienie zabezpieczające przed limitem API
        time.sleep(1)

    cursor.close()
    conn.close()

    print("\n[DONE] Team import completed")


# Punkt startowy skryptu
if __name__ == "__main__":
    fetch_and_save_teams()