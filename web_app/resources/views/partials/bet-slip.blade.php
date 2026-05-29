<div class="bet-slip-container d-flex flex-column h-100" id="betSlipContainer">
    <div class="slip-header d-flex justify-content-between align-items-center mb-3">
        <span class="slip-title">TYPY ROBOCZE</span>
        <div class="slip-badge-count" id="slipCountBadge">TYPY: 0</div>
    </div>

    <a href="/moje-typy" class="btn-nav-history">
        <i class="bi bi-collection-play"></i>
        ZOBACZ POSTAWIONE TYPY
    </a>

    <div class="bet-slip-scroll flex-grow-1" style="overflow-x: hidden !important;">
        <div id="bet-slip-items" style="width: 100%;">
            <style>
        /* Styl lokalny tylko dla tego widoku */
        .bet-slip-scroll::-webkit-scrollbar { display: none !important; width: 0 !important; }
    </style>
            <div class="empty-slip-msg text-center text-secondary py-5">
                <p>Wybierz kursy, aby stworzyć kupon.</p>
            </div>
        </div>
    </div>

    <div class="slip-footer mt-auto pt-3 border-top border-secondary d-none" id="slipFooter">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-secondary small">ŁĄCZNA WYGRANA:</span>
            <span class="fw-bold text-white" id="totalOdd">0.00 PKT</span>
        </div>

        <button class="btn-cyber-primary" id="submitSlipBtn" onclick="window.placeBet()">POSTAW TYPY</button>
    </div>
</div>
