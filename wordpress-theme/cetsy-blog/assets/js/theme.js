(function () {
    const header = document.querySelector('.site-header');

    if (!header) {
        return;
    }

    const toggleHeaderShadow = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    toggleHeaderShadow();
    window.addEventListener('scroll', toggleHeaderShadow, { passive: true });
})();
