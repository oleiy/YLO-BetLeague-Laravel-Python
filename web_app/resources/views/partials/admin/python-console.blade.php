{{-- =========================================
    FLOATING CONSOLE
========================================= --}}

<button
    class="admin-console-toggle"
    onclick="toggleAdminConsole()"
>
    <i class="bi bi-terminal-fill"></i>
    Console
</button>

<div
    id="adminConsoleDrawer"
    class="admin-console-drawer"
>

    <div class="admin-console-topbar">

        <div class="admin-console-title">
            <i class="bi bi-terminal-fill"></i>
            <span>YLO System Console</span>
        </div>

        <button
            class="admin-console-close"
            onclick="toggleAdminConsole()"
        >
            <i class="bi bi-x-lg"></i>
        </button>

    </div>

    <div id="adminConsole" class="admin-console">

        <div class="console-line success">
            [SYSTEM] Console initialized successfully.
        </div>

    </div>

</div>

@push('scripts')

<script>

    function toggleAdminConsole()
    {
        const drawer =
            document.getElementById(
                'adminConsoleDrawer'
            );

        drawer.classList.toggle('open');
    }

    function addConsoleLine(message, type = 'default')
    {
        const consoleBox =
            document.getElementById(
                'adminConsole'
            );

        const line =
            document.createElement('div');

        line.classList.add('console-line');

        if (type) {
            line.classList.add(type);
        }

        const now = new Date();

        const time =
            now.getHours().toString().padStart(2, '0')
            + ':'
            + now.getMinutes().toString().padStart(2, '0')
            + ':'
            + now.getSeconds().toString().padStart(2, '0');

        line.innerHTML = `
            <span class="console-time">[${time}]</span>
            ${message}
        `;

        consoleBox.appendChild(line);

        consoleBox.scrollTop =
            consoleBox.scrollHeight;
    }

    function runConsole(event, message, form)
    {
        event.preventDefault();

        addConsoleLine(
            '[INFO] ' + message,
            'info'
        );

        addConsoleLine(
            '[SYSTEM] Uruchamianie procesu...',
            'warning'
        );

        fetch(form.action, {

            method: 'POST',

            headers: {
                'X-CSRF-TOKEN':
                    document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,

                'Accept': 'application/json',
            },

        })
        .then(async response => {

            const text = await response.text();

            console.log(text);

            return JSON.parse(text);
        })

        .then(data => {

            if (data.success) {

                addConsoleLine(
                    '[SUCCESS] ' + data.message,
                    'success'
                );

                if (data.output) {

                    addConsoleLine(
                        data.output,
                        'default'
                    );
                }

            } else {

                addConsoleLine(
                    '[ERROR] ' + (
                        data.output ||
                        data.error ||
                        data.message ||
                        'Operacja nie powiodła się.'
                    ),
                    'error'
                );
            }
        })

        .catch(error => {

            console.error(error);

            addConsoleLine(
                '[ERROR] ' + error,
                'error'
            );
        });
    }

    window.addEventListener(
        'DOMContentLoaded',
        () => {

            addConsoleLine(
                '[SYSTEM] Admin module loaded.',
                'success'
            );

            addConsoleLine(
                '[SYSTEM] Python engine connected.',
                'success'
            );

            addConsoleLine(
                '[SYSTEM] Database connection established.',
                'success'
            );
        }
    );

</script>

@endpush
