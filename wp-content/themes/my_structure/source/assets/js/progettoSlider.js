document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.swiper-progetto').forEach(swiperEl => {
        const slides = swiperEl.querySelectorAll('.swiper-slide');
        const shouldLoop = slides.length > 2;

        new Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: shouldLoop,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            speed: 600,
            pagination: {
                el: swiperEl.querySelector('.swiper-pagination'),
                clickable: true,
            },
            a11y: {
                prevSlideMessage: 'Previous image',
                nextSlideMessage: 'Next image',
                paginationBulletMessage: 'Go to image {{index}}',
            },
            breakpoints: {
                640: { slidesPerView: 1 },
                768: { slidesPerView: 1 },
                1024: { slidesPerView: 1 },
            }
        });
    });
});