const shortClocks = [...document.querySelectorAll('time[data-countdown="short"]')];
const panels = [...document.querySelectorAll('[data-countdown-panel]')].map(panel => {
    const clock = panel.querySelector('[data-start]');
    return {
        clock,
        start: Date.parse(clock.dataset.start),
        digits: [...clock.querySelectorAll('[data-countdown-digit]')],
        ready: panel.querySelector('[data-countdown-ready]')
    };
});

function remaining(start, now) {
    const seconds = Math.max(0, Math.ceil((start - now) / 1000));
    return [Math.floor(seconds / 86400), Math.floor(seconds / 3600) % 24, Math.floor(seconds / 60) % 60, seconds % 60];
}

function tick() {
    const now = Date.now();
    for (const clock of shortClocks) {
        const [days,hours,minutes,seconds] = remaining(Date.parse(clock.dateTime), now);
        const open = !(days || hours || minutes || seconds);
        clock.previousElementSibling.textContent = open ? 'Vote' : 'Vote opens in';
        clock.textContent = open ? 'now' : days ? `${days}d ${hours}h` : hours || minutes ? `${hours}h ${minutes}m` : 'Under 1m';
    }
    for (const panel of panels) {
        const parts = remaining(panel.start, now);
        const open = parts.every(part => part === 0);
        panel.clock.hidden = open;
        panel.ready.hidden = !open;
        panel.digits.forEach((digit,i) => {
            const next = String(parts[i]).padStart(2,'0');
            if (digit.textContent !== next) digit.textContent = next;
        });
    }
}

if (shortClocks.length || panels.length) {
    tick();
    setInterval(tick, 1000);
}
