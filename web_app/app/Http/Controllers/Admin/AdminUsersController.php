<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\AdminIndexRequest;

/**
 * * Kontroler zarządzający użytkownikami w panelu administratora.
 * Odpowiada za:
 * 1. Wyświetlanie listy użytkowników z obsługą wyszukiwaniai i sortowania,
 * 2. Edycję danych profilowych oraz ról użytkowników.
 * 3. Zarządzanie banami poprzez interakcję z modelem statystyk użytkownika.
 * 4. Usuwanie kont użytkowników.
 */

class AdminUsersController extends Controller
{

    public function index(
        AdminIndexRequest $request
    ) {

        $validated = $request->validated();

        $search = $validated['search'] ?? null;

        $sort = $validated['sort']
            ?? 'username_asc';

        $users = User::with([
            'stats',
            'bets'
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($search) {

            $users->where(
                'username',
                'LIKE',
                '%' . $search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */

        switch ($sort) {

            case 'username_desc':

                $users->orderBy(
                    'username',
                    'desc'
                );

                break;

            case 'newest':

                $users->latest();

                break;

            case 'oldest':

                $users->oldest();

                break;

            case 'username_asc':

            default:

                $users->orderBy(
                    'username',
                    'asc'
                );

                break;
        }

        return view(
            'admin.users',
            [
                'users' => $users->paginate(25),

                'search' => $search,

                'sort' => $sort,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    public function update(
        AdminIndexRequest $request,
        User $user
    ) {

        $validated = $request->validated();

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return back()->with(
            'success',
            'Użytkownik został zaktualizowany.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with(
            'success',
            'Użytkownik został usunięty.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BAN USER
    |--------------------------------------------------------------------------
    */

public function ban(
    AdminIndexRequest $request,
    User $user
) {

    if (!$user->stats) {

        return back()->with(
            'error',
            'Brak statystyk użytkownika.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BAN
    |--------------------------------------------------------------------------
    */

    if ($request->input('is_banned') == 1) {

        $user->stats->update([
            'is_banned' => true,
            'ban_until' => $request->filled('ban_until')
    ? \Carbon\Carbon::parse($request->input('ban_until'))
    : null,
        ]);

        return back()->with(
            'success',
            'Użytkownik został zbanowany.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UNBAN
    |--------------------------------------------------------------------------
    */

    $user->stats->update([
        'is_banned' => false,
        'ban_until' => null,
    ]);

    return back()->with(
        'success',
        'Ban został zdjęty.'
    );
}
}
