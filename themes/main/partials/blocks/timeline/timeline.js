export default function init(element) {
    const track = element.querySelector('[data-timeline-track]');
    const fill = element.querySelector('[data-timeline-fill]');
    const dots = Array.from(element.querySelectorAll('[data-timeline-dot]'));

    if (!track || !fill || !dots.length) return;

    function update() {
        const rect = track.getBoundingClientRect();
        const viewportCenter = window.innerHeight / 2;
        const progress = Math.min(Math.max(viewportCenter - rect.top, 0), rect.height);

        fill.style.height = `${progress}px`;

        dots.forEach(dot => {
            const dotRect = dot.getBoundingClientRect();
            const isActive = dotRect.top <= viewportCenter;

            dot.classList.toggle('bg-white', isActive);
            dot.classList.toggle('bg-default-darker', !isActive);

            const row = dot.closest('[data-timeline-row]');
            row.querySelectorAll('[data-timeline-text]').forEach(text => {
                text.classList.toggle('text-white', isActive);
                text.classList.toggle('text-white/50', !isActive);
            });
        });
    }

    let ticking = false;
    function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            update();
            ticking = false;
        });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
}
