import { mkdirSync, readFileSync, writeFileSync, copyFileSync } from 'node:fs';
const read = path => readFileSync(path, 'utf8');
mkdirSync('dist/server', { recursive: true });
mkdirSync('dist/.openai', { recursive: true });
copyFileSync('.openai/hosting.json', 'dist/.openai/hosting.json');
const assets = {
    css: read('src/styles.css'),
    about: read('src/about.html').match(/<p>[^<]+<\/p>/g).slice(0, 3).join(''),
    heroTrophy: readFileSync('src/hero-trophy.webp').toString('base64'),
    heroMotion: read('src/hero-motion.js'),
    countdown: read('src/countdown.js'),
    poster: read('src/poster.js'),
    passwordFields: read('src/password-fields.js'),
    navigation: read('src/navigation.js'),
    analyticsConsent: read('src/analytics-consent.js'),
    flags: JSON.parse(read('src/flags.json')),
    demoWinners: Array.from({length:12},(_,index)=>readFileSync('src/demo-winners/'+(index+1)+'.webp').toString('base64')),
    marble: readFileSync('src/marble.webp').toString('base64'),
    logo: readFileSync('src/trophy.png').toString('base64'),
    favicon: readFileSync('src/favicon.ico').toString('base64')
};
const parts = ['logic','views','payments','payment-test','program','awards','editorial','admin','search-reports','worker'].map(file =>
    read('src/' + file + '.js').replace(/^import .*;\n/gm, ''));
writeFileSync('dist/server/index.js', 'const assets = ' + JSON.stringify(assets) + ';\n' + parts.join('\n'));
