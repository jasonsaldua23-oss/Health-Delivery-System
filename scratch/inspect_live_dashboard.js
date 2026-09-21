const { execSync } = require('child_process');
const fs = require('fs');

const out = execSync('curl.exe -b scratch/live_admin_cookie.txt -s https://bsns.online/Admin/index.php?page=dashboard').toString();
fs.writeFileSync('scratch/live_dash_output.html', out);

console.log('Saved live_dash_output.html, length:', out.length);

// Check circles
const circleMatches = [...out.matchAll(/<circle[^>]*data-val="([^"]+)"[^>]*>/g)].map(m => m[1]);
console.log('Circles data-val:', circleMatches);

// Check Demand
const demandMatches = [...out.matchAll(/<div class="dash-prog-name">(.*?)<\/div>[\s\S]*?<strong[^>]*>(.*?)<\/strong>/g)].map(m => m[1] + ': ' + m[2]);
console.log('Demand breakdown:', demandMatches);

// Check Recent Activity
const actIdx = out.indexOf('dash-activity-list');
if (actIdx !== -1) {
    console.log('Activity block:', out.slice(actIdx, actIdx + 500));
}
