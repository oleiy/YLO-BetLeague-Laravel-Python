<?php

return [

    'python_path' => env('PYTHON_PATH', 'python'),

    'python' => [

        'import_fixture_statistics' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'importers' . DIRECTORY_SEPARATOR .
            'import_fixture_statistics.py',

        'import_fixtures' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'importers' . DIRECTORY_SEPARATOR .
            'import_fixtures.py',

        'update_csv_data' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'importers' . DIRECTORY_SEPARATOR .
            'update_csv_data.py',

        'stats_aggregator' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'logic' . DIRECTORY_SEPARATOR .
            'stats_aggregator.py',

        'import_leagues' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'importers' . DIRECTORY_SEPARATOR .
            'import_leagues.py',

        'import_teams' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'importers' . DIRECTORY_SEPARATOR .
            'import_teams.py',

        'odds_engine' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'logic' . DIRECTORY_SEPARATOR .
            'odds_engine.py',

        'settle_bets' =>
        dirname(base_path()) . DIRECTORY_SEPARATOR .
            'python_engine' . DIRECTORY_SEPARATOR .
            'logic' . DIRECTORY_SEPARATOR .
            'settle_bets.py',

    ],

];
