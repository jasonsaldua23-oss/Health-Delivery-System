/**
 * Keeps a JavaScript-opened modal open across a page refresh (staff and admin portals).
 *
 * Modals driven by URL parameters (vitals, remarks, medical file, ...) already survive a refresh
 * and are not listed here. For the listed modals, the modal that is open when the page unloads is
 * remembered in sessionStorage and reopened after a reload (only after a reload, not after normal
 * navigation or a form submit), by, in order of preference:
 *   1. replaying the recorded opener call(s), with data refreshed from the page where configured
 *   2. clicking the same trigger element again (matched by id, onclick code or data-* attributes)
 *   3. restoring the modal's saved visibility (class, style, hidden)
 *
 * Usage: ModalPersist.init({ modals: ['modalId', ...], openers: { fnName: { modal: 'modalId', ... } } })
 *   opener options:
 *     reset: true          this call opens the modal (clears earlier recorded calls for it)
 *     freshFrom: 'fnName'  on restore, replace the first argument with the matching object parsed
 *                          from a current onclick="fnName({...})" on the page
 *     matchKeys: [...]     keys that identify that object (e.g. ['infant_key', 'patient_id'])
 */
(function () {
    'use strict';

    const STORAGE_PREFIX = 'modalPersist:';
    const storageKey = () => STORAGE_PREFIX + location.pathname + '?page=' + (new URLSearchParams(location.search).get('page') || '');

    let config = { modals: [], openers: {} };
    const callLog = {};      // modalId -> [{ fn, args }]
    let lastTrigger = null;  // descriptor of the last clicked element

    function storageGet(key) {
        try { return sessionStorage.getItem(key); } catch (e) { return null; }
    }
    function storageSet(key, value) {
        try { sessionStorage.setItem(key, value); } catch (e) { /* storage unavailable */ }
    }
    function storageRemove(key) {
        try { sessionStorage.removeItem(key); } catch (e) { /* storage unavailable */ }
    }

    function isVisible(el) {
        if (!el || !el.isConnected || el.hasAttribute('hidden') || el.classList.contains('hidden')) return false;
        return window.getComputedStyle(el).display !== 'none';
    }

    function isReload() {
        try {
            const nav = performance.getEntriesByType('navigation')[0];
            if (nav) return nav.type === 'reload';
        } catch (e) { /* fall through */ }
        return !!(performance.navigation && performance.navigation.type === 1);
    }

    // Elements can't be stored, so keep their attributes and hand back a stand-in on replay
    function serializeArg(arg) {
        if (arg instanceof Element) {
            const attrs = {};
            Array.from(arg.attributes).forEach(a => { attrs[a.name] = a.value; });
            return { __element: true, attrs };
        }
        if (arg instanceof Event) return null;
        return arg;
    }
    function deserializeArg(arg) {
        if (arg && arg.__element) {
            const attrs = arg.attrs || {};
            const dataset = {};
            Object.keys(attrs).forEach(name => {
                if (name.startsWith('data-')) {
                    dataset[name.slice(5).replace(/-([a-z])/g, (m, c) => c.toUpperCase())] = attrs[name];
                }
            });
            return { getAttribute: name => (name in attrs ? attrs[name] : null), hasAttribute: name => name in attrs, dataset };
        }
        return arg;
    }

    // Data embedded in the page is fresher than what was stored (e.g. after editing an infant)
    function freshArgFromPage(fnName, matchKeys, storedArg) {
        if (!storedArg || typeof storedArg !== 'object') return storedArg;
        const candidates = document.querySelectorAll('[onclick*="' + fnName + '("]');
        for (const el of candidates) {
            const code = el.getAttribute('onclick') || '';
            const start = code.indexOf(fnName + '(') + fnName.length + 1;
            const end = code.lastIndexOf(')');
            if (end <= start) continue;
            try {
                const parsed = JSON.parse(code.slice(start, end));
                if (matchKeys.every(k => String(parsed[k] ?? '') === String(storedArg[k] ?? ''))) {
                    return parsed;
                }
            } catch (e) { /* not a JSON argument */ }
        }
        return storedArg;
    }

    function describeTrigger(el) {
        const attrs = {};
        Array.from(el.attributes).forEach(a => {
            if (a.name === 'id' || a.name === 'onclick' || a.name.startsWith('data-')) attrs[a.name] = a.value;
        });
        return { tag: el.tagName, className: el.className || '', attrs };
    }

    function findTrigger(desc) {
        if (!desc) return null;
        if (desc.attrs.id) return document.getElementById(desc.attrs.id);
        if (desc.attrs.onclick) {
            const match = Array.from(document.querySelectorAll(desc.tag)).find(el => el.getAttribute('onclick') === desc.attrs.onclick);
            if (match) return match;
        }
        const dataNames = Object.keys(desc.attrs).filter(n => n.startsWith('data-'));
        if (dataNames.length === 0) return null;
        return Array.from(document.querySelectorAll(desc.tag)).find(el =>
            el.className === desc.className && dataNames.every(n => el.getAttribute(n) === desc.attrs[n])
        ) || null;
    }

    function wrapOpeners() {
        Object.keys(config.openers).forEach(fnName => {
            const original = window[fnName];
            if (typeof original !== 'function' || original.__modalPersistWrapped) return;
            const opts = config.openers[fnName];
            const wrapped = function () {
                const args = Array.from(arguments).map(serializeArg);
                if (opts.reset || !callLog[opts.modal]) callLog[opts.modal] = [];
                callLog[opts.modal].push({ fn: fnName, args });
                return original.apply(this, arguments);
            };
            wrapped.__modalPersistWrapped = true;
            window[fnName] = wrapped;
        });
    }

    function remember() {
        const openId = config.modals.find(id => isVisible(document.getElementById(id)));
        if (!openId) {
            storageRemove(storageKey());
            return;
        }
        const el = document.getElementById(openId);
        storageSet(storageKey(), JSON.stringify({
            modal: openId,
            calls: callLog[openId] || [],
            trigger: lastTrigger,
            state: { className: el.className, style: el.getAttribute('style'), hidden: el.hasAttribute('hidden') },
            scrollY: window.scrollY
        }));
    }

    function restore() {
        const raw = storageGet(storageKey());
        storageRemove(storageKey());
        if (!raw || !isReload()) return;

        let saved;
        try { saved = JSON.parse(raw); } catch (e) { return; }
        const el = document.getElementById(saved.modal);
        if (!el || isVisible(el)) return;

        // 1. Replay the recorded opener calls
        (saved.calls || []).forEach(call => {
            const fn = window[call.fn];
            if (typeof fn !== 'function') return;
            const opts = config.openers[call.fn] || {};
            const args = (call.args || []).map(deserializeArg);
            if (opts.freshFrom && opts.matchKeys) {
                args[0] = freshArgFromPage(opts.freshFrom, opts.matchKeys, args[0]);
            }
            try { fn.apply(window, args); } catch (e) { /* fall back below */ }
        });

        // 2. Click the same trigger again
        if (!isVisible(el) && saved.trigger) {
            const trigger = findTrigger(saved.trigger);
            if (trigger) {
                try { trigger.click(); } catch (e) { /* fall back below */ }
            }
        }

        // 3. Restore the saved visibility
        if (!isVisible(el) && saved.state) {
            el.className = saved.state.className;
            if (saved.state.style !== null) el.setAttribute('style', saved.state.style);
            if (saved.state.hidden) el.setAttribute('hidden', ''); else el.removeAttribute('hidden');
        }

        if (isVisible(el)) {
            document.body.style.overflow = 'hidden';
        }
    }

    window.ModalPersist = {
        init(options) {
            config = Object.assign({ modals: [], openers: {} }, options || {});

            // Remember what opened a modal: clicks inside the tracked modals themselves don't count
            document.addEventListener('click', e => {
                const el = e.target && e.target.closest ? e.target.closest('button, a, [onclick], [role="button"]') : null;
                if (!el) return;
                const insideModal = config.modals.some(id => {
                    const modal = document.getElementById(id);
                    return modal && modal.contains(el);
                });
                if (!insideModal) lastTrigger = describeTrigger(el);
            }, true);

            window.addEventListener('pagehide', remember);
            window.addEventListener('beforeunload', remember);

            const start = () => {
                wrapOpeners();
                // Let page scripts finish wiring their own handlers before reopening
                setTimeout(restore, 0);
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start);
            } else {
                start();
            }
        }
    };
})();
