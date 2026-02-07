document.addEventListener("DOMContentLoaded", () => {
    const navToggle = document.querySelector("[data-nav-toggle]");
    const navMenu = document.querySelector("[data-nav-menu]");

    if (navToggle && navMenu) {
        navToggle.addEventListener("click", () => {
            navMenu.classList.toggle("hidden");
        });
    }

    const revealElements = document.querySelectorAll("[data-reveal]");
    if (!revealElements.length) return;

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
        revealElements.forEach((el) => el.classList.add("reveal-in"));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add("reveal-in");
                currentObserver.unobserve(entry.target);
            });
        },
        { threshold: 0.2 }
    );

    revealElements.forEach((el) => {
        el.classList.add("reveal-init");
        observer.observe(el);
    });
});
