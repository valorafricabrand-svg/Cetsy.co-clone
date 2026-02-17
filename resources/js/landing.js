document.addEventListener("DOMContentLoaded", () => {
    const revealElements = document.querySelectorAll("[data-reveal]");

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (revealElements.length) {
        if (prefersReducedMotion || !("IntersectionObserver" in window)) {
            revealElements.forEach((el) => el.classList.add("reveal-in"));
        } else {
            const observer = new IntersectionObserver(
                (entries, currentObserver) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add("reveal-in");
                        currentObserver.unobserve(entry.target);
                    });
                },
                { threshold: 0.15 }
            );

            revealElements.forEach((el) => {
                el.classList.add("reveal-init");
                observer.observe(el);
            });
        }
    }

    const dockLinks = document.querySelectorAll(".mobile-dock .dock-link");
    const current = window.location.pathname.replace(/\/$/, "") || "/";
    dockLinks.forEach((link) => {
        const href = new URL(link.href, window.location.origin).pathname.replace(/\/$/, "") || "/";
        if (href === current) {
            link.classList.add("is-active");
        } else {
            link.classList.remove("is-active");
        }
    });
});
