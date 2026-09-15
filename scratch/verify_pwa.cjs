const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const manifestPath = path.join(root, 'manifest.json');
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

console.log('Manifest Name:', manifest.name);
console.log('Manifest Display:', manifest.display);
console.log('Manifest Start URL:', manifest.start_url);

let allValid = true;

for (const icon of manifest.icons) {
    const iconPath = path.join(root, icon.src);
    if (!fs.existsSync(iconPath)) {
        console.error(`Missing icon file: ${icon.src}`);
        allValid = false;
    } else {
        const stats = fs.statSync(iconPath);
        console.log(`Verified icon: ${icon.src} (${stats.size} bytes)`);
    }
}

for (const shortcut of manifest.shortcuts) {
    for (const icon of shortcut.icons) {
        const iconPath = path.join(root, icon.src);
        if (!fs.existsSync(iconPath)) {
            console.error(`Missing shortcut icon: ${icon.src}`);
            allValid = false;
        }
    }
}

const offlinePath = path.join(root, 'offline.html');
if (fs.existsSync(offlinePath)) {
    console.log(`Verified offline.html (${fs.statSync(offlinePath).size} bytes)`);
} else {
    console.error('Missing offline.html');
    allValid = false;
}

const swPath = path.join(root, 'sw.js');
if (fs.existsSync(swPath)) {
    console.log(`Verified sw.js (${fs.statSync(swPath).size} bytes)`);
} else {
    console.error('Missing sw.js');
    allValid = false;
}

if (allValid) {
    console.log('\n>>> ALL PWA AND MOBILE APP ASSETS VERIFIED 100% VALID! <<<');
} else {
    process.exit(1);
}
