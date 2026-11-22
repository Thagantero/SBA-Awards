const trophy = document.querySelector('.hero-trophy');

if (trophy) {
    const hero = trophy.closest('.hero');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    let frame;

    function drawTrophy() {
        frame = undefined;
        if (motion.matches) {
            trophy.style.removeProperty('--trophy-turn');
            return;
        }
        const bounds = hero.getBoundingClientRect();
        const progress = Math.max(0, Math.min(1, -bounds.top / (bounds.height * .82)));
        trophy.style.setProperty('--trophy-turn', `${progress * 360}deg`);
    }

    function requestDraw() {
        if (frame === undefined) frame = requestAnimationFrame(drawTrophy);
    }

    addEventListener('scroll', requestDraw, { passive: true });
    addEventListener('resize', requestDraw, { passive: true });
    motion.addEventListener('change', requestDraw);
    requestDraw();
}
