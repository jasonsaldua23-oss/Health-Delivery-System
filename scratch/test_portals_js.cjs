const fs = require('fs');
const path = require('path');
const vm = require('vm');

console.log('======================================================');
console.log(' JAVASCRIPT & DOM PORTAL RESPONSE TEST');
console.log('======================================================\n');

const phpFileContent = fs.readFileSync(path.join(__dirname, '../Patients/index.php'), 'utf8');

// Extract script blocks
const scriptMatches = [...phpFileContent.matchAll(/<script(?![^>]*src=)(?:[^>]*)>([\s\S]*?)<\/script>/gi)];

if (scriptMatches.length === 0) {
    console.error('✗ No embedded script block found in Patients/index.php!');
    process.exit(1);
}

let mainScript = scriptMatches[0][1];
// Replace PHP template tags with valid JS literals for mock testing
mainScript = mainScript.replace(/<\?=[\s\S]*?\?>/g, '{}');

// Quick syntax check using vm.Script
try {
    new vm.Script(mainScript);
    console.log('✓ Embedded JavaScript syntax is 100% valid (no syntax/parsing errors).');
} catch (err) {
    console.error('✗ Syntax error in embedded script:', err);
    process.exit(1);
}

// Check for temporal dead zone or undeclared reference issues by mocking the DOM environment
class MockClassList {
    constructor() {
        this.classes = new Set();
    }
    add(...names) { names.forEach(n => this.classes.add(n)); }
    remove(...names) { names.forEach(n => this.classes.delete(n)); }
    toggle(name, force) {
        if (force === undefined) {
            if (this.classes.has(name)) { this.classes.delete(name); return false; }
            else { this.classes.add(name); return true; }
        }
        if (force) this.classes.add(name);
        else this.classes.delete(name);
        return force;
    }
    contains(name) { return this.classes.has(name); }
}

class MockElement {
    constructor(id = '', tag = 'div') {
        this.id = id;
        this.tagName = tag.toUpperCase();
        this.classList = new MockClassList();
        this.style = {};
        this.dataset = {};
        this.attributes = {};
        this.listeners = {};
        this.value = '';
        this.textContent = '';
        this.innerHTML = '';
        this.children = [];
    }
    addEventListener(event, callback) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(callback);
    }
    dispatchEvent(event) {
        const list = this.listeners[event.type] || [];
        list.forEach(cb => cb.call(this, event));
    }
    click() {
        this.dispatchEvent({ type: 'click', target: this, preventDefault: () => {} });
    }
    setAttribute(k, v) { this.attributes[k] = String(v); }
    getAttribute(k) { return this.attributes[k] || null; }
    removeAttribute(k) { delete this.attributes[k]; }
    hasAttribute(k) { return k in this.attributes; }
    focus() {}
    querySelector() { return null; }
    querySelectorAll() { return []; }
    appendChild(el) { this.children.push(el); }
    scrollIntoView() {}
}

const elementsById = {};
function getOrCreateEl(id) {
    if (!elementsById[id]) {
        elementsById[id] = new MockElement(id);
    }
    return elementsById[id];
}

// Create Portal elements
const portalCards = [
    (() => { const el = new MockElement('', 'button'); el.classList.add('portal-card'); el.dataset.portal = 'patient'; return el; })(),
    (() => { const el = new MockElement('', 'button'); el.classList.add('portal-card'); el.dataset.portal = 'volunteer'; return el; })(),
    (() => { const el = new MockElement('', 'button'); el.classList.add('portal-card'); el.dataset.portal = 'admin'; return el; })()
];

const patientModal = getOrCreateEl('patientModal'); patientModal.classList.add('hidden');
const volunteerModal = getOrCreateEl('volunteerModal'); volunteerModal.classList.add('hidden');
const adminModal = getOrCreateEl('adminModal'); adminModal.classList.add('hidden');
const privacyConsentModal = getOrCreateEl('privacyConsentModal'); privacyConsentModal.classList.add('hidden');

const documentListeners = {};
const mockDocument = {
    addEventListener: (evt, cb) => {
        if (!documentListeners[evt]) documentListeners[evt] = [];
        documentListeners[evt].push(cb);
    },
    querySelectorAll: (sel) => {
        if (sel === '.portal-card') return portalCards;
        if (sel === '.toggle-password' || sel === '.js-open-patient-portal' || sel === '[data-go-step]') return [];
        return [];
    },
    querySelector: () => null,
    getElementById: (id) => getOrCreateEl(id),
    createElement: (tag) => new MockElement('', tag),
    body: { style: {} }
};

const mockWindow = {
    addEventListener: () => {},
    setTimeout: (cb) => setTimeout(cb, 0),
    setInterval: (cb) => setInterval(cb, 1000),
    clearInterval: (id) => clearInterval(id),
    showSystemToast: (msg) => console.log('   [Toast]:', msg)
};

const context = vm.createContext({
    document: mockDocument,
    window: mockWindow,
    Date: Date,
    String: String,
    Math: Math,
    console: console,
    FormData: class {},
    fetch: async () => ({ json: async () => ({}) })
});

// Run script in mock environment
try {
    vm.runInContext(mainScript, context);
    console.log('✓ Script initialized in DOM environment without runtime errors.');
} catch (err) {
    console.error('✗ Runtime error during script initialization:', err);
    process.exit(1);
}

// Trigger DOMContentLoaded
const domLoadedHandlers = documentListeners['DOMContentLoaded'] || [];
domLoadedHandlers.forEach(fn => fn());
console.log('✓ DOMContentLoaded event executed successfully.');

// Test 1: Click Volunteer portal card
console.log('\n[Test 1] Simulating click on Volunteer portal card:');
console.log('  Before click: volunteerModal.hidden =', volunteerModal.classList.contains('hidden'));
portalCards[1].click();
const volunteerOpened = !volunteerModal.classList.contains('hidden');
console.log('  After click:  volunteerModal.hidden =', volunteerModal.classList.contains('hidden'));
console.log(volunteerOpened ? '  ✓ SUCCESS: Volunteer modal opened responding to click!' : '  ✗ FAILED: Volunteer modal remained hidden!');

// Test 1b: Press Escape key to close Volunteer modal
console.log('\n[Test 1b] Simulating Escape key press:');
const keydownListeners = documentListeners['keydown'] || [];
keydownListeners.forEach(cb => cb({ key: 'Escape' }));
const volunteerClosed = volunteerModal.classList.contains('hidden');
console.log('  After Escape: volunteerModal.hidden =', volunteerModal.classList.contains('hidden'));
console.log(volunteerClosed ? '  ✓ SUCCESS: Volunteer modal closed on Escape!' : '  ✗ FAILED: Volunteer modal remained open!');

// Test 2: Click Admin portal card
console.log('\n[Test 2] Simulating click on Admin portal card:');
console.log('  Before click: adminModal.hidden =', adminModal.classList.contains('hidden'));
portalCards[2].click();
const adminOpened = !adminModal.classList.contains('hidden');
console.log('  After click:  adminModal.hidden =', adminModal.classList.contains('hidden'));
console.log(adminOpened ? '  ✓ SUCCESS: Admin modal opened responding to click!' : '  ✗ FAILED: Admin modal remained hidden!');

// Test 2b: Close Admin modal
keydownListeners.forEach(cb => cb({ key: 'Escape' }));
const adminClosed = adminModal.classList.contains('hidden');
console.log('  After Escape: adminModal.hidden =', adminModal.classList.contains('hidden'));
console.log(adminClosed ? '  ✓ SUCCESS: Admin modal closed on Escape!' : '  ✗ FAILED: Admin modal remained open!');

// Test 3: Click Patient portal card
console.log('\n[Test 3] Simulating click on Patient portal card:');
console.log('  Before click: patientModal.hidden =', patientModal.classList.contains('hidden'));
portalCards[0].click();
const patientOpened = !patientModal.classList.contains('hidden');
console.log('  After click:  patientModal.hidden =', patientModal.classList.contains('hidden'));
console.log(patientOpened ? '  ✓ SUCCESS: Patient modal opened responding to click!' : '  ✗ FAILED: Patient modal remained hidden!');

if (volunteerOpened && volunteerClosed && adminOpened && adminClosed && patientOpened) {
    console.log('\n======================================================');
    console.log(' ALL PORTAL RESPONSIVENESS & LIFECYCLE TESTS PASSED!');
    console.log('======================================================');
    process.exit(0);
} else {
    process.exit(1);
}
