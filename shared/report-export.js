/**
 * Report export chooser (admin and staff Reports pages).
 *
 * ReportExport.choose({ csvUrl, pdfDataUrl, accent }) asks whether to export as CSV or PDF.
 *   CSV: downloads csvUrl (the server writes the file with the active filters).
 *   PDF: fetches pdfDataUrl (JSON: title, filters, summary, columns, rows for the same filters)
 *        and builds the PDF in the browser with jsPDF + AutoTable, loaded from cdnjs on first use.
 */
(function () {
    'use strict';

    const JSPDF_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    const AUTOTABLE_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js';

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) return resolve();
            const el = document.createElement('script');
            el.src = src;
            el.onload = resolve;
            el.onerror = () => reject(new Error('Could not load ' + src));
            document.head.appendChild(el);
        });
    }

    async function loadPdfLibrary() {
        if (!(window.jspdf && window.jspdf.jsPDF)) await loadScript(JSPDF_URL);
        await loadScript(AUTOTABLE_URL);
        return window.jspdf.jsPDF;
    }

    function hexToRgb(hex) {
        const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '');
        return m ? [parseInt(m[1], 16), parseInt(m[2], 16), parseInt(m[3], 16)] : [37, 99, 235];
    }

    async function exportPdf(dataUrl, accent) {
        const response = await fetch(dataUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('The server could not prepare the report (HTTP ' + response.status + ').');
        const data = await response.json();
        const JsPDF = await loadPdfLibrary();
        const color = hexToRgb(accent);

        const doc = new JsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
        const pageWidth = doc.internal.pageSize.getWidth();
        const margin = 36;
        let y = margin;

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(15);
        doc.setTextColor(15, 23, 42);
        doc.text(String(data.title || 'Report'), margin, y + 6);
        y += 22;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(100, 116, 139);
        doc.text('Generated ' + (data.generated_at || ''), margin, y);
        y += 14;

        // Active filters and summary, two small tables side by side
        const filterRows = Object.entries(data.filters || {}).map(([k, v]) => [k, String(v)]);
        const summaryRows = Object.entries(data.summary || {}).map(([k, v]) => [k, String(v)]);
        const halfWidth = (pageWidth - margin * 2 - 16) / 2;
        const smallTable = (title, body, x) => doc.autoTable({
            startY: y,
            head: [[title, '']],
            body,
            theme: 'grid',
            margin: { left: x },
            tableWidth: halfWidth,
            styles: { fontSize: 8.5, cellPadding: 4 },
            headStyles: { fillColor: color, textColor: 255 },
            columnStyles: { 0: { fontStyle: 'bold', cellWidth: 110 } },
        });
        smallTable('Active Filters', filterRows, margin);
        const afterFilters = doc.lastAutoTable.finalY;
        smallTable('Summary', summaryRows, margin + halfWidth + 16);
        y = Math.max(afterFilters, doc.lastAutoTable.finalY) + 18;

        // Records
        doc.autoTable({
            startY: y,
            head: [data.columns || []],
            body: (data.rows || []).length ? data.rows : [[{ content: 'No records match the active filters.', colSpan: (data.columns || []).length || 1, styles: { halign: 'center' } }]],
            theme: 'striped',
            margin: { left: margin, right: margin },
            styles: { fontSize: 8, cellPadding: 4, overflow: 'linebreak' },
            headStyles: { fillColor: color, textColor: 255, halign: 'center' },
            bodyStyles: { halign: 'center' },
            didDrawPage: () => {
                doc.setFontSize(8);
                doc.setTextColor(148, 163, 184);
                doc.text('Page ' + doc.internal.getCurrentPageInfo().pageNumber, pageWidth - margin, doc.internal.pageSize.getHeight() - 16, { align: 'right' });
            },
        });

        doc.save(data.file_name || 'report.pdf');
    }

    function buildChooser(options) {
        const accent = options.accent || '#2563eb';
        const overlay = document.createElement('div');
        overlay.id = 'reportExportChooser';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,0.55);display:flex;align-items:center;justify-content:center;padding:16px;';
        overlay.innerHTML = `
            <div role="dialog" aria-modal="true" aria-labelledby="reportExportTitle" style="background:#fff;border-radius:18px;max-width:420px;width:100%;box-shadow:0 25px 60px -12px rgba(15,23,42,0.35);overflow:hidden;font-family:inherit;">
                <div style="padding:20px 22px 6px;">
                    <h3 id="reportExportTitle" style="margin:0;font-size:1.1rem;color:#0f172a;">Export Report</h3>
                    <p style="margin:6px 0 0;font-size:0.86rem;color:#64748b;">Choose a format. The export includes the records matching the filters currently applied.</p>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:16px 22px;">
                    <button type="button" data-format="csv" style="border:1.5px solid #e2e8f0;background:#f8fafc;border-radius:14px;padding:16px 10px;cursor:pointer;text-align:center;">
                        <strong style="display:block;font-size:1rem;color:#0f172a;">CSV</strong>
                        <span style="font-size:0.78rem;color:#64748b;">Spreadsheet (Excel)</span>
                    </button>
                    <button type="button" data-format="pdf" style="border:1.5px solid #e2e8f0;background:#f8fafc;border-radius:14px;padding:16px 10px;cursor:pointer;text-align:center;">
                        <strong style="display:block;font-size:1rem;color:#0f172a;">PDF</strong>
                        <span style="font-size:0.78rem;color:#64748b;">Printable document</span>
                    </button>
                </div>
                <p data-role="status" style="display:none;margin:0 22px 10px;font-size:0.84rem;font-weight:600;"></p>
                <div style="display:flex;justify-content:flex-end;padding:0 22px 18px;">
                    <button type="button" data-role="cancel" style="border:1px solid #cbd5e1;background:#fff;color:#475569;border-radius:10px;padding:8px 16px;font-weight:600;cursor:pointer;">Cancel</button>
                </div>
            </div>`;

        const close = () => {
            overlay.remove();
            document.removeEventListener('keydown', onKey);
        };
        const onKey = e => { if (e.key === 'Escape') close(); };
        const status = overlay.querySelector('[data-role="status"]');
        const setStatus = (text, isError) => {
            status.style.display = text ? 'block' : 'none';
            status.style.color = isError ? '#dc2626' : accent;
            status.textContent = text || '';
        };

        overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
        overlay.querySelector('[data-role="cancel"]').addEventListener('click', close);
        overlay.querySelectorAll('[data-format]').forEach(btn => {
            btn.addEventListener('mouseenter', () => { btn.style.borderColor = accent; });
            btn.addEventListener('mouseleave', () => { btn.style.borderColor = '#e2e8f0'; });
            btn.addEventListener('click', async () => {
                if (btn.dataset.format === 'csv') {
                    window.location.href = options.csvUrl;
                    close();
                    return;
                }
                overlay.querySelectorAll('button').forEach(b => { b.disabled = true; });
                setStatus('Preparing PDF...', false);
                try {
                    await exportPdf(options.pdfDataUrl, accent);
                    close();
                } catch (err) {
                    overlay.querySelectorAll('button').forEach(b => { b.disabled = false; });
                    setStatus('Could not create the PDF: ' + (err && err.message ? err.message : 'unknown error'), true);
                }
            });
        });
        document.addEventListener('keydown', onKey);
        return overlay;
    }

    window.ReportExport = {
        choose(options) {
            if (!options || !options.csvUrl || !options.pdfDataUrl) return;
            const existing = document.getElementById('reportExportChooser');
            if (existing) existing.remove();
            document.body.appendChild(buildChooser(options));
        },
        exportPdf,
    };
})();
