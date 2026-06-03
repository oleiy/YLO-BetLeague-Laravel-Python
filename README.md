# Platforma typerska YLO BetLeague

## 1. Cel i istota platformy
**YLO BetLeague** to zaawansowana aplikacja internetowa, która przenosi emocje towarzyszące zakładom sportowym w świat bezpiecznej rywalizacji. Platforma przejmuje intuicyjną mechanikę znanych serwisów bukmacherskich, ale całkowicie zmienia reguły gry: zamiast realnych pieniędzy, stawką są tu punkty. To przestrzeń stworzona dla pasjonatów piłki nożnej i analityki, którzy chcą sprawdzić swoją wiedzę, śledzić statystyki i udoskonalać umiejętności prognozowania wyników bez żadnego ryzyka finansowego.

Projekt rozwiązuje problem braku bezpiecznej alternatywy dla osób chcących rywalizować w typowaniu sportowym. Użytkownik nie odczuwa stresu związanego z hazardem, ponieważ w YLO BetLeague ciężar rozgrywki przeniesiony jest na pozycję w rankingach oraz satysfakcję z trafnych analiz. Jest to miejsce dla osób szukających merytorycznego wyzwania, gdzie liczy się czysta pasja i umiejętność przewidywania, a nie chęć zysku.

### Wyróżniki platformy
Choć sposób obstawiania nawiązuje do tradycyjnych modeli bukmacherskich, YLO BetLeague definitywnie odróżnia się od nich modelem działania:

* **Bezpieczna alternatywa:** W przeciwieństwie do komercyjnych serwisów nastawionych na finansowy zysk, platforma stawia na rozwój kompetencji analitycznych. Środowisko jest w pełni wolne od ryzyka pieniężnego, co pozwala skupić się wyłącznie na zabawie.
* **Społeczność zamiast usług:** Tradycyjny bukmacher to jednokierunkowa relacja klient-firma. YLO BetLeague tworzy dynamiczną społeczność. Dzięki systemom rankingów, kodom polecającym oraz możliwości przeglądania typów innych graczy, użytkownicy wchodzą w interakcje, rywalizują w grupach i wspólnie tworzą angażującą atmosferę współzawodnictwa.
* **Typowanie oparte na danych:** System promuje świadome podejście do sportu. Wykorzystanie zautomatyzowanych narzędzi pozwala użytkownikom budować renomę w ramach społeczności w oparciu o twarde dane, a nie przypadek.

## 2. Wymagania środowiskowe

Poniższa tabela przedstawia szczegółowe wersje technologii wykorzystanych w projekcie. Zapewnienie zgodności z tymi wersjami jest niezbędne do poprawnego działania aplikacji.

| Komponent | Technologia | Wersja | Oficjalna strona |
| :--- | :--- | :--- | :--- |
| **Backend** | PHP | 8.5.3 | [php.net](https://www.php.net/) |
| **Framework** | Laravel | 13.3.0 | [laravel.com](https://laravel.com/) |
| **Baza danych** | SQLite | 3.x (wbudowana w PHP / system) | [sqlite.org](https://www.sqlite.org/) |
| **Silnik danych** | Python | 3.12.10 | [python.org](https://python.org/) |
| **Zarządzanie PHP** | Composer | 2.9.5 | [getcomposer.org](https://getcomposer.org/) |
| **Framework CSS** | Bootstrap | 5.3.0 | [getbootstrap.com](https://getbootstrap.com/) |
| **Ikony** | Bootstrap Icons | 1.11.0 | [icons.getbootstrap.com](https://icons.getbootstrap.com/) |
| **Slider** | Swiper.js | 11.x | [swiperjs.com](https://swiperjs.com/) |

## 3. Wymagania programowe

Do uruchomienia i rozwijania projektu w trybie deweloperskim wymagane jest zainstalowanie poniższych narzędzi:

### System operacyjny
* Projekt był rozwijany i testowany w systemie **Windows 11**.

### Środowisko programistyczne
* **Edytor kodu (PHP/Frontend):** Visual Studio Code w wersji 1.121.0.
* **Edytor kodu (PythonEngine):** PyCharm w wersji 2025.2.3.

### Skład środowiska bazodanowego
* **Baza danych:** SQLite (plik lokalny `.sqlite`, bez serwera bazodanowego)
* **Sterowanie bazą:** realizowane przez Laravel (migracje + seedery)

## Proces instalacji
Poniższy przewodnik opisuje proces instalacji, konfiguracji oraz pierwszego uruchomienia kompletnego środowiska aplikacji opartego o PHP oraz SQLite.

### Krok 1: Klonowanie repozytorium
1. Otwórz terminal.
2. Przejdź do wybranego folderu, w którym ma się znaleźć projekt
3. Sklonuj repozytorium: 
```bash
git clone https://github.com/UR-INF/projekt-oh_project.git
```
4. Przejdź do folderu aplikacji webowej:

```bash
cd web_app
```

### Krok 2: Przygotowanie środowiska systemowego
Upewnij się, że posiadasz zainstalowane: PHP 8.5.3, Composer 2.9.5 oraz Python 3.12.10.

### Krok 3: Instalacja zależności PHP (Laravel)
```bash
composer install
```

## Krok 4: Konfiguracja środowiska przed pierwszym uruchomieniem

Przed pierwszym uruchomieniem aplikacji należy skonfigurować środowisko projektu, bazę danych oraz wymagane zmienne środowiskowe.

---

### 1. Konfiguracja zmiennych środowiskowych (`.env`)

Projekt wykorzystuje dwa oddzielne pliki środowiskowe:

- `web_app/.env` — konfiguracja aplikacji Laravel
- `python_engine/.env` — konfiguracja silnika analitycznego Python

#### Laravel (`web_app/.env`)

Przejdź do folderu `web_app` i utwórz plik `.env` na podstawie przykładowego pliku konfiguracyjnego:

```cmd
copy .env.example .env
```

Następnie wygeneruj klucz aplikacji Laravel:
```cmd
php artisan key:generate
```

Domyślna konfiguracja SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Panel administratora wykorzystuje interpreter Python do uruchamiania skryptów analitycznych:
```env
PYTHON_PATH=python
```

Jeżeli Python nie został dodany do zmiennej systemowej PATH, należy podać pełną ścieżkę do interpretera:
```env
PYTHON_PATH=C:\Users\User\AppData\Local\Programs\Python\Python312\python.exe
```

#### Python Engine (`python_engine/.env`)

Przejdź do katalogu `python_engine` i utwórz plik `.env` na podstawie pliku przykładowego:

```cmd
copy .env.example .env
```

Następnie uzupełnij konfigurację:

```env
DB_PATH=../web_app/database/database.sqlite

RAPIDAPI_KEY=your_api_key
RAPIDAPI_HOST=sofascore6.p.rapidapi.com
RAPIDAPI_BASE_URL=https://sofascore6.p.rapidapi.com/api/sofascore/v1

FIXTURES_IMPORT_MODE=production
FIXTURES_DEMO_DATE_SHIFT_DAYS=14
```

Parametr `DB_PATH` określa lokalizację współdzielonej bazy SQLite wykorzystywanej zarówno przez Laravel, jak i Python Engine.

#### Tryb importu terminarza spotkań

Importer meczów (`import_fixtures.py`) obsługuje dwa tryby działania określane przez zmienną:

```env
FIXTURES_IMPORT_MODE=production
```

Dostępne wartości:

- `production` — tryb produkcyjny. System pobiera mecze z API SofaScore od aktualnej daty na kolejne 14 dni i zapisuje do bazy ich rzeczywiste daty.
- `test` — tryb demonstracyjny. System pobiera mecze z poprzednich 14 dni, a następnie przesuwa ich daty o liczbę dni określoną w `FIXTURES_DEMO_DATE_SHIFT_DAYS`. Pozwala to zaprezentować działanie platformy poza aktywnym terminarzem rozgrywek, zachowując prawdziwe identyfikatory meczów, drużyny oraz dane pochodzące z API.

Liczba dni przesunięcia w trybie demonstracyjnym określana jest przez parametr:

```env
FIXTURES_DEMO_DATE_SHIFT_DAYS=14
```

Przykładowa konfiguracja produkcyjna:

```env
FIXTURES_IMPORT_MODE=production
FIXTURES_DEMO_DATE_SHIFT_DAYS=14
```

Przykładowa konfiguracja demonstracyjna:

```env
FIXTURES_IMPORT_MODE=test
FIXTURES_DEMO_DATE_SHIFT_DAYS=14
```

#### Uzyskanie klucza RapidAPI

Projekt wykorzystuje API SofaScore dostępne za pośrednictwem platformy RapidAPI.

1. Załóż konto na stronie:
https://rapidapi.com
2. Link do usługi:
https://rapidapi.com/rapidapi-org1-rapidapi-org-default/api/SofaScore
3. Wygeneruj własny klucz API.
4. Wklej wygenerowaną wartość do pliku:
```env
RAPIDAPI_KEY=YOUR_RAPIDAPI_KEY
```

Brak poprawnego klucza API uniemożliwi działanie modułów:
- Pobierz Ligi
- Pobierz Drużyny
- Pobierz Mecze na 14 Dni
- Pobierz Statystyki Meczu
---

### 2. Konfiguracja bazy danych SQLite

Projekt wykorzystuje lokalną bazę danych SQLite w postaci pliku `.sqlite`.

W folderze `web_app` utwórz plik bazy danych:

```bash
mkdir database
type nul > database/database.sqlite
```

Połączenie z bazą danych realizowane jest przez zmienne środowiskowe:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Silnik Python korzysta z tej samej bazy danych za pomocą ścieżki:

```env
DB_PATH=../web_app/database/database.sqlite
```

---

### 3. Migracje bazy danych

W celu utworzenia struktury tabel uruchom migracje Laravel:

```bash
php artisan migrate
```

---

### 4. Dane początkowe (Seed)

#### Konto administratora

Aby utworzyć domyślne konto administratora, uruchom:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Domyślne dane logowania administratora:

```text
Login: admin
Hasło: admin
```

#### Dane demonstracyjne (opcjonalnie)

Projekt zawiera dodatkowe seedery umożliwiające wygenerowanie przykładowych użytkowników oraz zakładów wykorzystywanych podczas testów i demonstracji działania systemu.

Generowanie użytkowników:

```bash
php artisan db:seed --class=DemoUsersSeeder
```
Seeder tworzy 100 przykładowych użytkowników wraz z odpowiadającymi im rekordami w tabeli user_stats

Generowanie zakładów:
```bash
php artisan db:seed --class=DemoBetsSeeder
```
Seeder tworzy 1000 przykładowych kuponów przypisanych do wygenerowanych użytkowników.

Wygenerowane kupony:

- wykorzystują rzeczywiste kursy znajdujące się w tabeli odds,
- dotyczą wyłącznie spotkań zapisanych w tabeli fixtures,
- obsługują zarówno pojedyncze typy, jak i zakłady typu Bet Builder,
- zawierają przykładowe analizy użytkowników,
- generują realistyczne statusy zakładów (won, lost, active).

Uwaga: Przed uruchomieniem DemoBetsSeeder w bazie danych muszą znajdować się wcześniej zaimportowane mecze oraz wygenerowane kursy.


### Krok 5: Uruchomienie backendu Laravel
```bash
php artisan serve
```
### Krok 6: Konfiguracja silnika Python

1. W nowym oknie terminala przejdź do folderu python_engine:

```bash
cd ../python_engine
```

2. Zainstaluj wymagane biblioteki:

```bash
pip install -r requirements.txt
```
Plik `requirements.txt` zawiera pełną listę bibliotek Python wymaganych do uruchomienia silnika analitycznego wraz z wersjami wykorzystanymi podczas tworzenia projektu.

### Krok 7: Uruchomienie systemu
1. Wejdź pod adres: http://127.0.0.1:8000.
2. Utwórz konta użytkownika za pomocą przycisku "Rejestracja".
3. Logowanie: admin / admin (administrator) dostępny po uruchomieniu seedera.

### Automatyzacja zadań (Scheduler)

Projekt wykorzystuje mechanizm Laravel Scheduler do automatycznej aktualizacji statusów spotkań oraz zakładów.

W celu uruchomienia harmonogramu zadań należy wykonać polecenie:

```bash
php artisan schedule:work
```

Polecenie działa w trybie ciągłym i co minutę sprawdza zadania zdefiniowane w harmonogramie aplikacji.

Po uruchomieniu scheduler automatycznie wykonuje m.in.:

- aktualizację statusów spotkań (`NS → LIVE → FT`),
- aktualizację statusów zakładów (`pending → active → settling`).
### Krok 8: Inicjalizacja danych w Panelu Administratora

Przy pierwszym uruchomieniu systemu należy wykonać poniższe operacje w podanej kolejności:
1. Importuj Ligi (uruchamia import_leagues.py - pobiera ligi z API)
2. Importuj Drużyny (uruchamia import_teams.py - pobiera drużyny dla zapisanych wcześniej lig z API)
3. Pobierz Mecze (uruchamia import_fixtures.py — pobiera terminarz spotkań zgodnie z konfiguracją FIXTURES_IMPORT_MODE)
   > Importer działa zgodnie z konfiguracją `FIXTURES_IMPORT_MODE` w pliku `python_engine/.env`:
   - w trybie `production` pobiera mecze od aktualnej daty na kolejne 14 dni,
   - w trybie `test` pobiera mecze testowe i przesuwa ich daty o wartość `FIXTURES_DEMO_DATE_SHIFT_DAYS`.
   
4. Synchronizuj statystyki (pobiera aktualne pliki csv ze statystykami oraz automatycznie generuje plik processed_stats.json wykorzystywany przez model Poissona)
5. Generuj Kursy (Poisson) (uruchamia odds_engine.py — oblicza rozkłady prawdopodobieństwa i zapisuje wygenerowane kursy do bazy danych)

Po zakończeniu spotkań:
6. Synchronizuj wyniki (uruchamia import_fixture_statistics.py — uzupełnia bazę danych o szczegółowe statystyki z rozegranych meczów)
7. Rozlicz typy (uruchamia settle_bets.py — sprawdza wyniki zakończonych spotkań i aktualizuje statusy kuponów oraz statystyki punktowe graczy)

W prawym dolnym rogu panelu administracyjnego znajduje się wbudowana konsola logów wykonywanych operacji. Podczas klikania kolejnych przycisków wyświetla ona komunikaty w czasie rzeczywistym o przebiegu poszczególnych operacji.

## Uruchomienie projektu (user)
Jeśli środowisko jest już skonfigurowane, uruchomienie projektu sprowadza się do dwóch prostych kroków (baza danych SQLite działa lokalnie w pliku, więc nie musisz uruchamiać modułu MySQL w XAMPP):

1. **Uruchomienie serwera aplikacji (Laravel):**
   * Otwórz terminal.
   * Przejdź do folderu aplikacji webowej:
     ```cmd
     cd web_app
     ```
   * Uruchom serwer deweloperski poleceniem:
     ```cmd
     php artisan serve
     ```
   * Aplikacja będzie dostępna w przeglądarce pod adresem: [http://127.0.0.1:8000](http://127.0.0.1:8000).

### Zalecana specyfikacja sprzętowa (Środowisko deweloperskie)
Projekt był rozwijany na następującej konfiguracji, która zapewnia płynne działanie serwera lokalnego oraz środowiska IDE:
* **Procesor:** 13th Gen Intel(R) Core(TM) i5-13420H (2.10 GHz)
* **Pamięć RAM:** 16,0 GB
* **Dysk:** SSD 512 GB
* **System operacyjny:** Windows 11

# Podręcznik użytkownika

## Role w systemie

System YLO BetLeague obsługuje trzy poziomy dostępu:

- Gość (niezalogowany użytkownik)
- Użytkownik (zalogowany użytkownik)
- Administrator

---

## Ścieżka użytkownika – Gość (Guest)

Gość może korzystać z części funkcjonalności platformy bez konieczności zakładania konta.

### Dashboard

Po wejściu na stronę główną użytkownik widzi listę nadchodzących spotkań piłkarskich wraz z podstawowymi informacjami o meczach.

### Mecze

Gość może przejść do zakładki **Mecze**, gdzie dostępny jest pełny harmonogram spotkań dla wybranego dnia.

Dla każdego spotkania prezentowane są kursy wygenerowane automatycznie przez silnik analityczny Python (`python_engine`) wykorzystujący model matematyczny oparty na rozkładzie Poissona.

### Społeczność

Gość ma również dostęp do zakładki **Społeczność**, w której może:

- przeglądać publiczne typy innych użytkowników,
- analizować opublikowane analizy meczowe,
- przeglądać ranking najlepszych typerów platformy.

### Ograniczenia

Pozostałe funkcjonalności systemu wymagają zalogowania się na konto użytkownika.

---

## Ścieżka użytkownika – Użytkownik

### Rejestracja

Nowy użytkownik może utworzyć konto za pomocą przycisku **Rejestracja** dostępnego w nagłówku strony.

Podczas rejestracji podaje:

- nazwę użytkownika,
- adres e-mail,
- hasło,
- opcjonalny kod polecający.

Jeżeli użytkownik poda poprawny kod polecający, otrzymuje dodatkowe punkty startowe wykorzystywane do typowania.

### Logowanie

Logowanie odbywa się za pomocą przycisku **Zaloguj**.

Użytkownik może zalogować się wykorzystując:

- nazwę użytkownika lub adres e-mail,
- hasło.

Po poprawnym zalogowaniu uzyskuje dostęp do wszystkich funkcjonalności platformy.

---

## Stawianie typów

Najważniejszą funkcją platformy jest możliwość typowania zdarzeń sportowych.

Po prawej stronie aplikacji dostępny jest panel typów roboczych, który pozwala użytkownikowi dodawać kursy do kuponu z dowolnego miejsca systemu.

Typy można dodawać poprzez:

- wybór kursów w zakładce Dashboard,
- wybór kursów w zakładce Mecze,
- kopiowanie typów innych użytkowników w zakładce Społeczność.

Podczas tworzenia typu użytkownik może dodać własną analizę tekstową opisującą przesłanki podjętej decyzji.

### Rodzaje typów

#### SOLO

Typ zawierający pojedyncze zdarzenie dla jednego meczu.

#### BET BUILDER

Typ zawierający kilka zdarzeń dotyczących tego samego spotkania.

### Stawka

Podczas składania typu użytkownik określa stawkę wyrażoną w punktach (PKT).

W przypadku wygranego typu system automatycznie oblicza wygraną według wzoru:

```text
Wygrana = Stawka × Łączny Kurs
```

Następnie odpowiednia liczba punktów zostaje dopisana do salda użytkownika.

---

## Moje Typy

Zakładka **Moje Typy** zawiera historię wszystkich zakładów użytkownika.

Widoczne są zarówno:

- typy aktywne,
- typy wygrane,
- typy przegrane.

Dostępne są również filtry umożliwiające wyszukiwanie kuponów według:

- statusu,
- daty,
- kursu,

### Zarządzanie analizą

Użytkownik może:

- edytować własną analizę,
- usuwać własną analizę,

w przypadku błędnego lub nieaktualnego wpisu.

---

## Zdobywaj Balans

W zakładce **Zdobywaj Balans** użytkownik uzyskuje dostęp do dodatkowych metod zdobywania punktów.

### Program poleceń

Każdy użytkownik posiada indywidualny kod referencyjny, który może udostępniać nowym graczom.

Za skuteczne polecenie system przyznaje bonusowe punkty.

### Bonus codziennego logowania

Platforma udostępnia również system nagród za codzienne logowanie.

Po spełnieniu warunków użytkownik może odebrać dodatkowe punkty zwiększające jego saldo.

---

## Społeczność

Zakładka **Społeczność** umożliwia analizowanie aktywności innych użytkowników.

Dostępne są filtry pozwalające wyświetlać:

- typy z analizą,
- typy bez analizy,
- typy z wybranego dnia,
- typy posortowane według różnych kryteriów.

### Rankingi

System automatycznie generuje ranking użytkowników.

Aktualnie zalogowany użytkownik jest dodatkowo wyróżniony na liście rankingowej, co ułatwia odnalezienie własnej pozycji nawet przy dużej liczbie uczestników.

---

# Ścieżka użytkownika – Administrator

Administrator posiada specjalną rolę zapisaną w bazie danych.

Po zalogowaniu zostaje automatycznie przekierowany do dedykowanego panelu administracyjnego.

Administrator ma dostęp do następujących sekcji:

- Dashboard
- Mecze
- Typy Użytkowników
- Użytkownicy

---

## Dashboard Administratora

Najważniejszym elementem panelu administracyjnego jest sekcja sterowania silnikiem systemowym.

Administrator może uruchamiać wszystkie skrypty Python Engine:

- Pobierz Ligi (`import_leagues.py`)
- Pobierz Drużyny (`import_teams.py`)
- Pobierz Mecze na 14 Dni (`import_fixtures.py`)
- Pobierz Statystyki Meczu (`import_fixture_statistics.py`)
- Przetwórz Statystyki CSV (`stats_aggregator.py`)
- Generuj Kursy (Poisson) (`odds_engine.py`)
- Rozlicz Zakłady (`settle_bets.py`)

Wbudowana konsola administracyjna wyświetla logi wykonywanych operacji w czasie rzeczywistym.

---

## Mecze

Administrator może przeglądać spotkania dla wybranego dnia wraz z ich aktualnym statusem.

Widoczne są między innymi statusy:

- NS (Not Started)
- LIVE
- FT (Full Time)

---

## Typy Użytkowników

Zakładka umożliwia monitorowanie aktywności społeczności.

Administrator może:

- przeglądać wszystkie typy użytkowników,
- filtrować typy według daty,
- filtrować typy według użytkownika,
- usuwać analizy zawierające niepożądane treści.

---

## Użytkownicy

Administrator posiada pełne uprawnienia zarządzania kontami.

Może:

- przeglądać listę użytkowników,
- wyszukiwać użytkowników,
- edytować dane użytkowników,
- usuwać konta,
- blokować użytkowników.

Dostępne filtry umożliwiają szybkie wyszukiwanie użytkowników nawet przy dużej liczbie kont.

---

## Najważniejsze procesy systemowe

Administrator odpowiada za inicjalizację oraz utrzymanie danych wykorzystywanych przez platformę.

Najważniejsze zadania administracyjne obejmują:

- import lig i drużyn przed rozpoczęciem sezonu,
- aktualizację terminarza spotkań,
- pobieranie statystyk zakończonych meczów,
- generowanie kursów przy pomocy modelu Poissona,
- rozliczanie typów użytkowników,
- aktualizację rankingów oraz statystyk społeczności.

## Plany rozbudowy
Projekt mógłby zostać wzbogacony o nowe moduły, które zwiększą zaangażowanie społeczności oraz głębię analityczną platformy:

### Rozwój społecznościowy
* **Moduł czatu:** Implementacja komunikatora w czasie rzeczywistym, umożliwiającego wymianę analiz i emocji między użytkownikami podczas meczów.
* **System rang i odznak:** Wprowadzenie progresywnego systemu poziomów oraz odznak za osiągnięcia.
* **Rankingi ligowe:** Tworzenie sub-grup (prywatnych lig) dla znajomych, pozwalających na rywalizację wewnątrz zamkniętych kręgów.
* **Lista znajomych:** Możliwość dodawania użytkowników do znajomych i rywalizacji w rankingach ze znajomymi.
* **Obserwowanie:** System śledzenia aktywności najlepszych analityków ("Follow"), aby otrzymywać powiadomienia o ich nowych typach.
* **Personalizacja:** Możliwość wyboru "Ulubionych lig" i "Ulubionych zespołów", co pozwoli na filtrowanie widoku meczów i otrzymywanie dedykowanych powiadomień.

### Ekspansja danych i dyscyplin
* **Wielokierunkowość sportowa:** Rozszerzenie silnika analitycznego o obsługę innych dyscyplin (np. koszykówka NBA, tenis, e-sport), co wymaga dostosowania `python_engine` do pracy z nowymi modelami statystycznymi.
* **Globalny zasięg lig:** Zwiększenie bazy dostępnych rozgrywek poza obecnie obsługiwane TOP-5 lig europejskich oraz UEFA Champions League.
* **Rozszerzone statystyki (Deep Stats):** Implementacja bardziej zaawansowanych parametrów (np. posiadanie piłki, xG - expected goals, mapy strzałów), które będą brane pod uwagę przez silnik `odds_engine` przy generowaniu kursów.
