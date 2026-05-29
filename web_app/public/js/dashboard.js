/**
 * @desc Inicjalizuje slidery na stronie głównej (Dashboard).
 */
document.addEventListener('DOMContentLoaded', function () {
    initSwipers();
});

/**
 * @desc Konfiguruje Swiper dla Topowych Meczów oraz Popularnych Zakładów.
 */
function initSwipers() {
    // Slider dla "Topowe Nadchodzące Mecze"
    new Swiper('.topMatchesSwiper', {
        slidesPerView: 1,
        spaceBetween: 15,
        navigation: { nextEl: '.s-next-matches', prevEl: '.s-prev-matches' },
        breakpoints: {
            768: { slidesPerView: 2 },
            1200: { slidesPerView: 3 }
        }
    });

    // Slider dla "Najpopularniejsze Zakłady"
    new Swiper('.popularBetsSwiper', {
        slidesPerView: 1.2,
        spaceBetween: 12,
        navigation: { nextEl: '.s-next-popular', prevEl: '.s-prev-popular' },
        breakpoints: {
            768: { slidesPerView: 2.2 },
            1200: { slidesPerView: 3 }
        }
    });
}
