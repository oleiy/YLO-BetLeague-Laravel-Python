console.log('My Bets loaded');

/**
 * =========================
 * INLINE EDIT TOGGLE
 * =========================
 */
document.addEventListener("click", function (e) {

    // EDIT OPEN
    if (e.target.closest(".btn-edit-analysis")) {

        const btn = e.target.closest(".btn-edit-analysis");
        const id = btn.dataset.id;

        document.getElementById("analysis-text-" + id).classList.add("d-none");
        document.getElementById("analysis-form-" + id).classList.remove("d-none");
    }

    // CANCEL EDIT
    if (e.target.closest(".btn-cancel-edit")) {

        const btn = e.target.closest(".btn-cancel-edit");
        const id = btn.dataset.id;

        document.getElementById("analysis-text-" + id).classList.remove("d-none");
        document.getElementById("analysis-form-" + id).classList.add("d-none");
    }
});


/**
 * =========================
 * AJAX UPDATE ANALYSIS
 * =========================
 */
document.querySelectorAll(".analysis-edit-form").forEach(form => {

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const id = this.id.replace("analysis-form-", "");
        const textarea = this.querySelector("textarea");

        const updatedText = textarea.value;
        const response = await fetch(this.action, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            },
            body: JSON.stringify({
                _method: "PUT",
                analysis: updatedText
            })
        });

        if (!response.ok) {
            alert("Błąd podczas zapisu analizy");
            return;
        }

        // update UI without reload
        const textEl = document.getElementById("analysis-text-" + id);
        textEl.textContent = updatedText;

        // hide form, show text
        this.classList.add("d-none");
        textEl.classList.remove("d-none");
    });
});
