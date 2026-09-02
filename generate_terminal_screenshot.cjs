const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

async function main() {
  const logPath = 'C:\\Users\\LENOVO\\.gemini\\antigravity-ide\\brain\\a7475ce5-59af-4870-a3a3-1682349c1e2d\\.system_generated\\tasks\\task-524.log';
  const rawLog = fs.readFileSync(logPath, 'utf8');

  // Escape HTML
  const escapeHtml = (str) =>
    str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

  // Format terminal text with ANSI-like colors
  const lines = rawLog.split('\n');
  const formattedHtml = lines
    .map((line) => {
      let escaped = escapeHtml(line);
      if (escaped.includes('√')) {
        escaped = escaped.replace(/√/g, '<span style="color:#10b981;font-weight:bold;">√</span>');
        escaped = `<span style="color:#f1f5f9;">${escaped}</span>`;
      } else if (escaped.includes('Running:') || escaped.includes('Run Starting') || escaped.includes('Run Finished')) {
        escaped = `<span style="color:#38bdf8;font-weight:bold;">${escaped}</span>`;
      } else if (escaped.includes('passing')) {
        escaped = escaped.replace(/(\d+ passing)/g, '<span style="color:#10b981;font-weight:bold;">$1</span>');
      } else if (escaped.includes('All specs passed!')) {
        escaped = `<span style="color:#10b981;font-weight:bold;">${escaped}</span>`;
      } else if (escaped.includes('┌') || escaped.includes('└') || escaped.includes('│') || escaped.includes('├') || escaped.includes('─') || escaped.includes('═')) {
        escaped = `<span style="color:#64748b;">${escaped}</span>`;
      } else if (escaped.startsWith('  -  ') && escaped.includes('.png')) {
        escaped = `<span style="color:#94a3b8;">${escaped}</span>`;
      } else if (escaped.includes('Warning:')) {
        escaped = `<span style="color:#eab308;">${escaped}</span>`;
      }
      return `<div class="term-line">${escaped || '&nbsp;'}</div>`;
    })
    .join('');

  const htmlContent = `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Terminal - Cypress Test Execution</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: #0f172a;
      font-family: 'Cascadia Code', 'Consolas', 'Courier New', monospace;
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }
    .terminal-window {
      width: 1100px;
      background: #0b0f19;
      border-radius: 10px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
      border: 1px solid #1e293b;
      overflow: hidden;
    }
    .terminal-titlebar {
      background: #1e293b;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #334155;
    }
    .window-controls {
      display: flex;
      gap: 8px;
    }
    .control-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }
    .dot-close { background: #ef4444; }
    .dot-min { background: #f59e0b; }
    .dot-max { background: #10b981; }
    .window-title {
      color: #94a3b8;
      font-size: 13px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .terminal-body {
      padding: 24px;
      color: #cbd5e1;
      font-size: 13.5px;
      line-height: 1.5;
      overflow-x: auto;
      white-space: pre-wrap;
    }
    .command-prompt {
      color: #38bdf8;
      margin-bottom: 16px;
      font-size: 14px;
      font-weight: 600;
    }
    .command-prompt span.path {
      color: #a855f7;
    }
    .command-prompt span.cmd {
      color: #f8fafc;
    }
  </style>
</head>
<body>
  <div class="terminal-window">
    <div class="terminal-titlebar">
      <div class="window-controls">
        <div class="control-dot dot-close"></div>
        <div class="control-dot dot-min"></div>
        <div class="control-dot dot-max"></div>
      </div>
      <div class="window-title">PowerShell - Administrator: npx cypress run</div>
      <div style="width: 50px;"></div>
    </div>
    <div class="terminal-body">
      <div class="command-prompt">
        PS <span class="path">C:\\xampp\\htdocs\\Health-Delivery-System-Latest</span>&gt; <span class="cmd">npx cypress run</span>
      </div>
      ${formattedHtml}
    </div>
  </div>
</body>
</html>
  `;

  const htmlPath = path.join(__dirname, 'terminal_results.html');
  fs.writeFileSync(htmlPath, htmlContent, 'utf8');

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1200, height: 2600 } });
  await page.setContent(htmlContent);
  await page.waitForTimeout(500);

  // Full terminal screenshot
  const outPath = path.join(__dirname, 'cypress', 'screenshots', 'terminal_execution_results.png');
  const termElement = await page.$('.terminal-window');
  await termElement.screenshot({ path: outPath });

  // Summary zoomed screenshot focusing on run summary
  const summaryOutPath = path.join(__dirname, 'cypress', 'screenshots', 'terminal_summary_results.png');
  await page.setViewportSize({ width: 1200, height: 1000 });
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.screenshot({ path: summaryOutPath });

  await browser.close();
  console.log('Screenshots saved to:');
  console.log(outPath);
  console.log(summaryOutPath);
}

main().catch(console.error);
