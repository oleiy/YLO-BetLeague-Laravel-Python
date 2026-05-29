import time

from core.db import get_db_connection
from core.api import make_request

"""
Skrypt odpowiedzialny za import lig z API SofaScore
oraz synchronizację danych w tabeli leagues.
Obsługuje dodawanie nowych lig i aktualizację istniejących rekordów.
"""

# Kategorie krajów i regionów używane podczas pobierania lig
CATEGORIES = {
    "1": "England",
    "32": "Spain",
    "31": "Italy",
    "30": "Germany",
    "7": "France",
    "1465": "Europe",
}

# Lista lig, które mają zostać zapisane w bazie danych
TARGET_LEAGUES = [
    17,  # Premier League
    8,   # LaLiga
    23,  # Serie A
    35,  # Bundesliga
    34,  # Ligue 1
    7    # Champions League
]

# Pobiera ligi z API i synchronizuje dane z tabelą leagues
def fetch_and_save():

    conn = get_db_connection()
    cursor = conn.cursor()

    # Iteracja po wybranych kategoriach lig
    for cat_id, cat_name in CATEGORIES.items():

        print(f"[INFO] Fetching leagues for {cat_name}...")

        # Pobranie listy lig dla danej kategorii
        data = make_request(
            "category/unique-tournaments",
            {
                "category_id": cat_id
            }
        )

        if not data:
            print(f"[ERROR] API error for {cat_name}")
            continue

        # Przetwarzanie każdej ligi zwróconej przez API
        for tournament in data:

            tournament_id = tournament.get("id")

            # Pomijanie lig spoza listy docelowej
            if tournament_id not in TARGET_LEAGUES:
                continue

            name = tournament.get("name")

            country = (
                tournament.get("category", {})
                .get("name")
            )

            # Sprawdzenie czy liga istnieje już w bazie danych
            cursor.execute(
                """
                SELECT id
                FROM leagues
                WHERE api_id = ?
                """,
                (tournament_id,),
            )

            existing_league = cursor.fetchone()

            # Aktualizacja danych istniejącej ligi
            if existing_league:

                cursor.execute(
                    """
                    UPDATE leagues
                    SET
                        name = ?,
                        country = ?,
                        updated_at = datetime('now')
                    WHERE api_id = ?
                    """,
                    (
                        name,
                        country,
                        tournament_id
                    ),
                )

                print(f"[UPDATE] {name}")

            # Dodanie nowej ligi do tabeli leagues
            else:

                cursor.execute(
                    """
                    INSERT INTO leagues (
                        api_id,
                        name,
                        country,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        ?,
                        ?,
                        ?,
                        datetime('now'),
                        datetime('now')
                    )
                    """,
                    (
                        tournament_id,
                        name,
                        country
                    ),
                )

                print(f"[INSERT] {name}")

        # Zapis zmian do bazy danych
        conn.commit()

        # Opóźnienie zabezpieczające przed przekroczeniem limitu API
        time.sleep(1)

    cursor.close()
    conn.close()

    print("\n[DONE] League import completed")

# Punkt startowy skryptu
if __name__ == "__main__":
    fetch_and_save()