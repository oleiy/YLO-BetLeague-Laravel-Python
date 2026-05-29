import os
import requests

# ============================================
# API CLIENT
# ============================================
# Moduł odpowiedzialny za komunikację z API.
# Obsługuje wysyłanie requestów HTTP,
# autoryzację RapidAPI oraz zwracanie danych JSON.

from dotenv import load_dotenv

# Załadowanie zmiennych środowiskowych z pliku .env
load_dotenv()

# Konfiguracja API pobierana z .env
API_KEY = os.getenv("RAPIDAPI_KEY")
API_HOST = os.getenv("RAPIDAPI_HOST")
BASE_URL = os.getenv("RAPIDAPI_BASE_URL")

# Wysyła zapytanie GET do API i zwraca odpowiedź JSON
def make_request(endpoint, params=None):

    # Budowanie pełnego adresu endpointu
    url = f"{BASE_URL}/{endpoint}"

    # Nagłówki wymagane przez RapidAPI
    headers = {
        "x-rapidapi-key": API_KEY,
        "x-rapidapi-host": API_HOST,
        "Content-Type": "application/json",
    }

    try:
        # Wysłanie requestu HTTP GET do API
        response = requests.get(
            url,
            headers=headers,
            params=params,
            timeout=20
        )

        # Obsługa błędnych odpowiedzi API
        if response.status_code != 200:
            print(f"[ERROR] API {response.status_code}: {url}")
            return None

        # Zwrócenie danych w formacie JSON
        return response.json()

    # Obsługa błędów połączenia lub requestu
    except Exception as e:
        print(f"[ERROR] Request failed: {e}")
        return None