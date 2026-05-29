import requests
import os

"""
Skrypt odpowiedzialny za pobieranie oraz aktualizację historycznych danych
meczowych w formacie CSV z serwisu football-data.co.uk.

Pliki CSV są później wykorzystywane do generowania statystyk drużyn
oraz wyliczania kursów bukmacherskich w systemie.
"""

# Główny katalog projektu
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
# Folder przechowujący dane CSV
DATA_DIR = os.path.join(BASE_DIR, "data")

# Źródła plików CSV dla poszczególnych lig
CSV_SOURCES = {
    "E0.csv": "https://www.football-data.co.uk/mmz4281/2526/E0.csv",
    "SP1.csv": "https://www.football-data.co.uk/mmz4281/2526/SP1.csv",
    "D1.csv": "https://www.football-data.co.uk/mmz4281/2526/D1.csv",
    "F1.csv": "https://www.football-data.co.uk/mmz4281/2526/F1.csv",
    "I1.csv": "https://www.football-data.co.uk/mmz4281/2526/I1.csv",
}


def update_csv_files():
    print(f"Rozpoczynam aktualizację plików CSV w: {DATA_DIR}")

    # Sprawdzenie czy folder danych istnieje
    if not os.path.exists(DATA_DIR):
        print(f"BŁĄD: Folder {DATA_DIR} nie istnieje! Sprawdź ścieżkę.")
        return

    # Iteracja po wszystkich plikach CSV
    for filename, url in CSV_SOURCES.items():
        # Łączymy ścieżkę folderu z nazwą pliku
        full_path = os.path.join(DATA_DIR, filename)

        try:
            print(f"Pobieranie najnowszych danych dla {filename}...")

            # Pobranie danych CSV
            response = requests.get(url, timeout=10)

            # Poprawne pobranie pliku
            if response.status_code == 200:
                with open(full_path, "wb") as f:
                    f.write(response.content)
                print(f"Plik {filename} został zaktualizowany.")
            else:
                print(
                    f"Nie można pobrać {filename} (Kod błędu: {response.status_code})."
                )

        except Exception as e:
            print(f"Wystąpił błąd przy {filename}: {e}")

    print("\n Proces aktualizacji zakończony.")

# Uruchomienie aktualizacji po bezpośrednim odpaleniu pliku
if __name__ == "__main__":
    update_csv_files()
