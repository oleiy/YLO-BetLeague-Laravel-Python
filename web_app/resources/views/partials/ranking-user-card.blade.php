<div
    class="ranking-user-card compact {{ auth()->id() === $user->id ? 'current-user' : '' }}"
    id="rank-user-{{ $user->id }}"
>

    <div class="ranking-position">

        #{{ $index + 1 }}

    </div>

    <div class="ranking-user-info">

        <div class="ranking-avatar">

            {{ strtoupper(substr($user->username, 0, 2)) }}

        </div>

        <div>

            <div class="ranking-username">

                {{ $user->username }}

            </div>

            <div class="ranking-meta">

                <span class="ranking-accuracy">

                    {{ number_format($user->calculated_accuracy ?? 0, 1) }}%

                </span>

                <div class="recent-form">

                    @foreach(($user->recent_form ?? []) as $form)

                        <span class="form-dot {{ $form === 'won' ? 'win' : 'lose' }}"></span>

                    @endforeach

                </div>

            </div>

        </div>

    </div>

    <div class="ranking-points">

        {{ $points }}

    </div>

</div>
