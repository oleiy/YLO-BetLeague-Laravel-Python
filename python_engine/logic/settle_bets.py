from core.db import get_db_connection

"""
Skrypt odpowiada za automatyczne rozliczanie zakładów użytkowników
na podstawie zakończonych meczów i zapisanych statystyk.
Aktualizuje statusy kuponów oraz statystyki użytkowników.
"""


def settle_market(odd, stats):
    """
    Zwraca:
    - won
    - lost
    - pending
    """
    stats = dict(stats)
    odd = dict(odd)

    market = str(odd["market_name"]).strip()
    outcome = str(odd["outcome_name"]).strip()
    specifier = float(odd["specifier"] or 0)

    # Statystyki meczu
    home_goals = stats.get("home_goals") or 0
    away_goals = stats.get("away_goals") or 0

    home_corners = stats.get("home_corners") or 0
    away_corners = stats.get("away_corners") or 0

    home_yellow = stats.get("home_yellow_cards") or 0
    away_yellow = stats.get("away_yellow_cards") or 0

    home_shots = stats.get("home_shots_on_goal") or 0
    away_shots = stats.get("away_shots_on_goal") or 0

    total_goals = home_goals + away_goals
    total_corners = home_corners + away_corners
    total_yellow = home_yellow + away_yellow
    total_shots = home_shots + away_shots

    # Wynik meczu
    if market == "Wynik":

        if outcome == "1":
            return "won" if home_goals > away_goals else "lost"
        elif outcome == "X":
            return "won" if home_goals == away_goals else "lost"
        elif outcome == "2":
            return "won" if away_goals > home_goals else "lost"

    # Podwójna szansa
    elif market == "Podwójna szansa":

        if outcome == "1X":
            return "won" if home_goals >= away_goals else "lost"
        elif outcome == "12":
            return "won" if home_goals != away_goals else "lost"
        elif outcome == "X2":
            return "won" if away_goals >= home_goals else "lost"

    # BTTS
    elif market == "Obie drużyny strzelą":

        both = home_goals > 0 and away_goals > 0

        if outcome == "Tak":
            return "won" if both else "lost"
        elif outcome == "Nie":
            return "won" if not both else "lost"

    # GOLE
    elif "Liczba goli" in market:

        is_over = "Powyżej" in outcome
        is_under = "Poniżej" in outcome

        if "drużyny" in market.lower():

            if odd["team_id"] is None:
                return "pending"

            fixture_home_team_id = odd["home_team_id"]
            team_id = odd["team_id"]

            team_goals = home_goals if team_id == fixture_home_team_id else away_goals

            if is_over:
                return "won" if team_goals > specifier else "lost"
            if is_under:
                return "won" if team_goals < specifier else "lost"

        else:
            if is_over:
                return "won" if total_goals > specifier else "lost"
            if is_under:
                return "won" if total_goals < specifier else "lost"

    # RZUTY ROŻNE
    elif "Rzuty rożne" in market:

        is_over = "Powyżej" in outcome
        is_under = "Poniżej" in outcome

        if "drużyny" in market.lower():

            if odd["team_id"] is None:
                return "pending"

            fixture_home_team_id = odd["home_team_id"]
            team_id = odd["team_id"]

            team_corners = home_corners if team_id == fixture_home_team_id else away_corners

            if is_over:
                return "won" if team_corners > specifier else "lost"
            if is_under:
                return "won" if team_corners < specifier else "lost"

        else:
            if is_over:
                return "won" if total_corners > specifier else "lost"
            if is_under:
                return "won" if total_corners < specifier else "lost"

    # KARTKI
    elif "Liczba kartek" in market:

        is_over = "Powyżej" in outcome
        is_under = "Poniżej" in outcome

        if "drużyny" in market.lower():

            if odd["team_id"] is None:
                return "pending"

            fixture_home_team_id = odd["home_team_id"]
            team_id = odd["team_id"]

            team_cards = home_yellow if team_id == fixture_home_team_id else away_yellow

            if is_over:
                return "won" if team_cards > specifier else "lost"
            if is_under:
                return "won" if team_cards < specifier else "lost"

        else:
            if is_over:
                return "won" if total_yellow > specifier else "lost"
            if is_under:
                return "won" if total_yellow < specifier else "lost"

    # CELOWE STRZAŁY
    elif "Celne strzały" in market:

        is_over = "Powyżej" in outcome
        is_under = "Poniżej" in outcome

        if "drużyny" in market.lower():

            if odd["team_id"] is None:
                return "pending"

            fixture_home_team_id = odd["home_team_id"]
            team_id = odd["team_id"]

            team_shots = home_shots if team_id == fixture_home_team_id else away_shots

            if is_over:
                return "won" if team_shots > specifier else "lost"
            if is_under:
                return "won" if team_shots < specifier else "lost"

        else:
            if is_over:
                return "won" if total_shots > specifier else "lost"
            if is_under:
                return "won" if total_shots < specifier else "lost"

    return "pending"


def main():
    print("[INFO] Starting bet settlement...")

    conn = get_db_connection()
    cursor = conn.cursor()

    # aktywne kupony
    cursor.execute("""
        SELECT *
        FROM user_bets
        WHERE status IN ('pending', 'active', 'settling')
    """)

    bets = cursor.fetchall()

    print(f"[INFO] Found {len(bets)} bets to process")

    for bet in bets:

        bet_id = bet["id"]

        print("\n==========================================")
        print(f"[INFO] Processing bet #{bet_id}")

        # pozycje kuponu
        cursor.execute("""
            SELECT
                ubi.id AS item_id,
                ubi.status AS item_status,

                o.id AS odd_id,
                o.fixture_id,
                o.team_id,
                o.market_name,
                o.outcome_name,
                o.specifier,

                f.status AS fixture_status,
                f.home_team_id,
                f.away_team_id

            FROM user_bet_items ubi
            JOIN odds o ON ubi.odd_id = o.id
            JOIN fixtures f ON o.fixture_id = f.id
            WHERE ubi.bet_id = ?
        """, (bet_id,))

        items = cursor.fetchall()

        if not items:
            print("[WARNING] No items found for bet")
            continue

        item_results = []

        for item in items:

            item_id = item["item_id"]
            fixture_id = item["fixture_id"]

            print(f"\n[INFO] Settling item #{item_id}")

            if item["fixture_status"] not in ["FT", "finished", "AET", "PEN"]:
                print("[INFO] Fixture not finished")

                item_results.append("pending")

                cursor.execute("""
                    UPDATE user_bet_items
                    SET status = 'pending'
                    WHERE id = ?
                """, (item_id,))

                continue

            cursor.execute("""
                SELECT *
                FROM fixture_statistics
                WHERE fixture_id = ?
            """, (fixture_id,))

            stats = cursor.fetchone()

            if not stats:
                print("[WARNING] Missing statistics")

                item_results.append("pending")

                cursor.execute("""
                    UPDATE user_bet_items
                    SET status = 'pending'
                    WHERE id = ?
                """, (item_id,))

                continue

            result = settle_market(item, stats)

            print(f"[INFO] Result = {result}")

            item_results.append(result)

            cursor.execute("""
                UPDATE user_bet_items
                SET status = ?
                WHERE id = ?
            """, (result, item_id))

        if "lost" in item_results:
            final_status = "lost"
        elif "pending" in item_results:
            final_status = "pending"
        else:
            final_status = "won"

        print(f"\n[INFO] Final bet status = {final_status}")

        cursor.execute("""
            UPDATE user_bets
            SET
                status = ?,
                settled_at = CASE
                    WHEN ? IN ('won', 'lost') THEN datetime('now')
                    ELSE settled_at
                END
            WHERE id = ?
        """, (final_status, final_status, bet_id))

        if final_status in ["won", "lost"]:

            user_id = bet["user_id"]

            cursor.execute("""
                SELECT *
                FROM user_stats
                WHERE user_id = ?
            """, (user_id,))

            user_stats = cursor.fetchone()

            if user_stats:

                total_bets = user_stats["total_bets"] + 1
                won_bets = user_stats["won_bets"]
                lost_bets = user_stats["lost_bets"]

                current_streak = user_stats["current_streak"]
                best_streak = user_stats["best_streak"]
                balance_points = user_stats["balance_points"]

                if final_status == "won":
                    won_bets += 1
                    current_streak += 1

                    if current_streak > best_streak:
                        best_streak = current_streak

                    balance_points += int(bet["potential_win"])

                else:
                    lost_bets += 1
                    current_streak = 0

                accuracy = round((won_bets / total_bets) * 100, 2) if total_bets > 0 else 0

                cursor.execute("""
                    UPDATE user_stats
                    SET
                        total_bets = ?,
                        won_bets = ?,
                        lost_bets = ?,
                        accuracy_rate = ?,
                        current_streak = ?,
                        best_streak = ?,
                        balance_points = ?
                    WHERE user_id = ?
                """, (
                    total_bets,
                    won_bets,
                    lost_bets,
                    accuracy,
                    current_streak,
                    best_streak,
                    balance_points,
                    user_id
                ))

    conn.commit()
    cursor.close()
    conn.close()

    print("\n[DONE] Bet settlement completed")


if __name__ == "__main__":
    main()