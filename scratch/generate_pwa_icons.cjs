const fs = require('fs');
const path = require('path');
const { chromium } = require('@playwright/test');

(async () => {
    const iconsDir = path.join(__dirname, '..', 'assets', 'icons');
    if (!fs.existsSync(iconsDir)) {
        fs.mkdirSync(iconsDir, { recursive: true });
    }

    const svgPath = path.join(iconsDir, 'icon.svg');
    const svgContent = fs.readFileSync(svgPath, 'utf8');

    // Create favicon.svg
    fs.writeFileSync(path.join(iconsDir, 'favicon.svg'), svgContent);

    const browser = await chromium.launch();
    const page = await browser.newPage();

    const sizes = [
        { name: 'icon-192.png', size: 192 },
        { name: 'icon-512.png', size: 512 },
        { name: 'icon-maskable-192.png', size: 192, maskable: true },
        { name: 'icon-maskable-512.png', size: 512, maskable: true },
        { name: 'apple-touch-icon.png', size: 180 }
    ];

    for (const item of sizes) {
        await page.setViewportSize({ width: item.size, height: item.size });
        
        let html;
        if (item.maskable) {
            // Maskable icons have safe zone (padded background)
            html = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body, html { margin: 0; padding: 0; width: ${item.size}px; height: ${item.size}px; overflow: hidden; background: #0d9488; display: flex; align-items: center; justify-content: center; }
                    svg { width: ${Math.round(item.size * 0.8)}px; height: ${Math.round(item.size * 0.8)}px; }
                </style>
            </head>
            <body>
                ${svgContent}
            </body>
            </html>`;
        } else {
            html = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body, html { margin: 0; padding: 0; width: ${item.size}px; height: ${item.size}px; overflow: hidden; background: transparent; display: flex; align-items: center; justify-content: center; }
                    svg { width: ${item.size}px; height: ${item.size}px; }
                </style>
            </head>
            <body>
                ${svgContent}
            </body>
            </html>`;
        }

        await page.setContent(html);
        const outPath = path.join(iconsDir, item.name);
        await page.screenshot({ path: outPath, omitBackground: !item.maskable });
        console.log(`Generated ${item.name} (${item.size}x${item.size})`);
    }

    await browser.close();
    console.log('All PWA icons generated successfully!');
})();
