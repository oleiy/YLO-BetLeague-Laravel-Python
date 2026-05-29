import json
import math
from scipy.stats import poisson

from core.db import get_db_connection
from core.mappings import get_csv_team_name
from core.paths import PROCESSED_STATS_FILE


class OddsGenerator:
    def __init__(self, margin=0.08):
        self.margin = margin

        with open(PROCESSED_STATS_FILE, "r", encoding="utf-8") as f:
            self.data = json.load(f)

        self.teams_stats = self.data["teams"]
        self.default_team_stats = self.calculate_default_stats()

    # =========================
    # DEFAULT STATS
    # =========================
    def calculate_default_stats(self):

        default_home = {}
        default_away = {}

        keys = [
            "goals_scored",
            "goals_conceded",
            "sot_won",
            "sot_lost",
            "corners_won",
            "corners_lost",
            "cards_received",
            "cards_opponent",
        ]

        for key in keys:
            home_values = [t["home"][key] for t in self.teams_stats.values()]
            away_values = [t["away"][key] for t in self.teams_stats.values()]

            default_home[key] = sum(home_values) / len(home_values) if home_values else 1.0
            default_away[key] = sum(away_values) / len(away_values) if away_values else 1.0

        return {"home": default_home, "away": default_away}

    # =========================
    # 1X2 + BTTS
    # =========================
    def calculate_1x2_and_more(self, lambda_home, lambda_away):

        prob_home = prob_draw = prob_away = prob_btts_yes = 0
        max_goals = 10

        for h in range(max_goals):
            p_h = poisson.pmf(h, lambda_home)

            for a in range(max_goals):
                p_a = poisson.pmf(a, lambda_away)

                p = p_h * p_a

                if h > a:
                    prob_home += p
                elif h == a:
                    prob_draw += p
                else:
                    prob_away += p

                if h > 0 and a > 0:
                    prob_btts_yes += p

        odds = {
            "1": round((1 / prob_home) * (1 - self.margin), 2),
            "X": round((1 / prob_draw) * (1 - self.margin), 2),
            "2": round((1 / prob_away) * (1 - self.margin), 2),
            "1X": round((1 / (prob_home + prob_draw)) * (1 - self.margin), 2),
            "12": round((1 / (prob_home + prob_away)) * (1 - self.margin), 2),
            "X2": round((1 / (prob_draw + prob_away)) * (1 - self.margin), 2),
            "BTTS_TAK": round((1 / prob_btts_yes) * (1 - self.margin), 2),
            "BTTS_NIE": round((1 / (1 - prob_btts_yes)) * (1 - self.margin), 2),
        }

        for k in odds:
            odds[k] = max(1.01, min(odds[k], 100.0))

        return odds

    # =========================
    # OVER / UNDER
    # =========================
    def calculate_line(self, expected_value, threshold):

        if expected_value <= 0:
            return 100.0, 1.01

        prob_under = poisson.cdf(math.floor(threshold), expected_value)
        prob_over = 1 - prob_under

        odd_under = (
            round((1 / prob_under) * (1 - self.margin), 2)
            if prob_under > 0.01 else 100.0
        )

        odd_over = (
            round((1 / prob_over) * (1 - self.margin), 2)
            if prob_over > 0.01 else 100.0
        )

        return (
            max(1.01, min(odd_under, 100.0)),
            max(1.01, min(odd_over, 100.0))
        )

    # =========================
    # DB INSERT
    # =========================
    def save_to_db(self, cursor, fixture_id, market_name, outcome_name, specifier, value, team_id=None):

        query = """
            INSERT INTO odds (
                fixture_id,
                team_id,
                market_name,
                outcome_name,
                specifier,
                value,
                created_at,
                updated_at
            )
            VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
        """

        cursor.execute(
            query,
            (fixture_id, team_id, market_name, outcome_name, str(specifier), value),
        )

    # =========================
    # ENGINE
    # =========================
    def run_engine(self):

        db = get_db_connection()
        cursor = db.cursor()

        cursor.execute("""
            SELECT
                f.id,
                f.home_team_id,
                f.away_team_id,
                t1.name AS home_name,
                t2.name AS away_name
            FROM fixtures f
            JOIN teams t1 ON f.home_team_id = t1.id
            JOIN teams t2 ON f.away_team_id = t2.id
            WHERE f.match_date BETWEEN datetime('now')
            AND datetime('now', '+14 days')
        """)

        matches = cursor.fetchall()

        print(f"[INFO] Generating odds for {len(matches)} matches...")

        for match in matches:

            fixture_id = match["id"]

            home_csv = get_csv_team_name(match["home_name"])
            away_csv = get_csv_team_name(match["away_name"])

            home_stats = self.teams_stats.get(home_csv, self.default_team_stats)["home"]
            away_stats = self.teams_stats.get(away_csv, self.default_team_stats)["away"]

            # =========================
            # EXPECTED GOALS
            # =========================
            lambda_home = (home_stats["goals_scored"] + away_stats["goals_conceded"]) / 2
            lambda_away = (away_stats["goals_scored"] + home_stats["goals_conceded"]) / 2

            # TOTALS
            lambdas = {
                "goals": {
                    "total": lambda_home + lambda_away,
                    "home": lambda_home,
                    "away": lambda_away,
                },
                "corners": {
                    "total": (
                        home_stats["corners_won"]
                        + away_stats["corners_lost"]
                        + away_stats["corners_won"]
                        + home_stats["corners_lost"]
                    ) / 2,
                    "home": (home_stats["corners_won"] + away_stats["corners_lost"]) / 2,
                    "away": (away_stats["corners_won"] + home_stats["corners_lost"]) / 2,
                },
                "cards": {
                    "total": (
                        home_stats["cards_received"]
                        + away_stats["cards_opponent"]
                        + away_stats["cards_received"]
                        + home_stats["cards_opponent"]
                    ) / 2,
                    "home": (home_stats["cards_received"] + away_stats["cards_opponent"]) / 2,
                    "away": (away_stats["cards_received"] + home_stats["cards_opponent"]) / 2,
                },
                "shots": {
                    "total": (
                        home_stats["sot_won"]
                        + away_stats["sot_lost"]
                        + away_stats["sot_won"]
                        + home_stats["sot_lost"]
                    ) / 2,
                    "home": (home_stats["sot_won"] + away_stats["sot_lost"]) / 2,
                    "away": (away_stats["sot_won"] + home_stats["sot_lost"]) / 2,
                },
            }

            # =========================
            # MAIN MARKETS
            # =========================
            main_odds = self.calculate_1x2_and_more(lambda_home, lambda_away)

            self.save_to_db(cursor, fixture_id, "Wynik", "1", 0, main_odds["1"])
            self.save_to_db(cursor, fixture_id, "Wynik", "X", 0, main_odds["X"])
            self.save_to_db(cursor, fixture_id, "Wynik", "2", 0, main_odds["2"])

            self.save_to_db(cursor, fixture_id, "Podwójna szansa", "1X", 0, main_odds["1X"])
            self.save_to_db(cursor, fixture_id, "Podwójna szansa", "12", 0, main_odds["12"])
            self.save_to_db(cursor, fixture_id, "Podwójna szansa", "X2", 0, main_odds["X2"])

            self.save_to_db(cursor, fixture_id, "Obie drużyny strzelą", "Tak", 0, main_odds["BTTS_TAK"])
            self.save_to_db(cursor, fixture_id, "Obie drużyny strzelą", "Nie", 0, main_odds["BTTS_NIE"])

            # =========================
            # OVER / UNDER MARKETS
            # =========================
            market_configs = [
                ("Liczba goli", lambdas["goals"]["total"], [0.5, 1.5, 2.5, 3.5, 4.5, 5.5], None),
                ("Rzuty rożne", lambdas["corners"]["total"], [5.5, 6.5, 7.5, 8.5, 9.5, 10.5, 11.5], None),
                ("Liczba kartek", lambdas["cards"]["total"], [2.5, 3.5, 4.5, 5.5], None),
                ("Celne strzały", lambdas["shots"]["total"], [5.5, 6.5, 8.5, 10.5, 12.5], None),
            ]

            for market_name, expected_value, lines, team_id in market_configs:
                for line in lines:
                    under, over = self.calculate_line(expected_value, line)

                    self.save_to_db(cursor, fixture_id, market_name, "Poniżej", line, under, team_id)
                    self.save_to_db(cursor, fixture_id, market_name, "Powyżej", line, over, team_id)

            # =========================
            # TEAM MARKETS
            # =========================
            team_market_configs = [
                ("Liczba goli drużyny", [
                    (home_team_id := match["home_team_id"], lambdas["goals"]["home"]),
                    (away_team_id := match["away_team_id"], lambdas["goals"]["away"]),
                ], [0.5, 1.5, 2.5, 3.5]),

                ("Rzuty rożne drużyny", [
                    (home_team_id, lambdas["corners"]["home"]),
                    (away_team_id, lambdas["corners"]["away"]),
                ], [2.5, 3.5, 4.5, 5.5, 6.5]),

                ("Liczba kartek drużyny", [
                    (home_team_id, lambdas["cards"]["home"]),
                    (away_team_id, lambdas["cards"]["away"]),
                ], [0.5, 1.5, 2.5, 3.5]),

                ("Celne strzały drużyny", [
                    (home_team_id, lambdas["shots"]["home"]),
                    (away_team_id, lambdas["shots"]["away"]),
                ], [1.5, 2.5, 3.5, 4.5, 5.5]),
            ]

            for market_name, teams_data, lines in team_market_configs:
                for team_id, expected_value in teams_data:
                    for line in lines:
                        under, over = self.calculate_line(expected_value, line)

                        self.save_to_db(cursor, fixture_id, market_name, "Poniżej", line, under, team_id)
                        self.save_to_db(cursor, fixture_id, market_name, "Powyżej", line, over, team_id)

            db.commit()

            print(f"[SUCCESS] {match['home_name']} vs {match['away_name']}")

        cursor.close()
        db.close()


if __name__ == "__main__":
    OddsGenerator(margin=0.07).run_engine()