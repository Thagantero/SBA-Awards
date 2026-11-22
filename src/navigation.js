const smallScreen = matchMedia('(max-width: 900px)');

for (const toggle of document.querySelectorAll('[data-menu-toggle]')) {
    const navigation = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!navigation) continue;

    function setOpen(open) {
        toggle.setAttribute('aria-expanded', String(open));
        navigation.hidden = !open && smallScreen.matches;
    }
    function syncViewport() {
        toggle.hidden = !smallScreen.matches;
        setOpen(!smallScreen.matches);
    }
    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    navigation.addEventListener('click', event => {
        if (smallScreen.matches && event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && smallScreen.matches && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
    smallScreen.addEventListener('change', syncViewport);
    syncViewport();
}
