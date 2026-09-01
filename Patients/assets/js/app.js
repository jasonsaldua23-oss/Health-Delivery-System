const searchInput = document.querySelector('#stationSearch');
const chips = Array.from(document.querySelectorAll('.chip'));
const cards = Array.from(document.querySelectorAll('.station-card'));
const schedulePicker = document.querySelector('[data-schedule-picker]');

let activeFilter = 'all';

function filterStations() {
    const query = (searchInput?.value || '').trim().toLowerCase();

    cards.forEach((card) => {
        const name = card.dataset.name || '';
        const barangay = card.dataset.barangay || '';
        const matchesQuery = name.includes(query);
        const matchesFilter = activeFilter === 'all' || barangay === activeFilter;
        card.classList.toggle('hidden', !(matchesQuery && matchesFilter));
    });
}

chips.forEach((chip) => {
    chip.addEventListener('click', () => {
        activeFilter = chip.dataset.filter || 'all';
        chips.forEach((item) => item.classList.remove('active'));
        chip.classList.add('active');
        filterStations();
    });
});

searchInput?.addEventListener('input', filterStations);

function renderSchedulePicker() {
    if (!schedulePicker) {
        return;
    }

    let availability;

    try {
        availability = JSON.parse(schedulePicker.dataset.availability || '{}');
    } catch (error) {
        availability = {};
    }

    const months = Array.isArray(availability.months) ? availability.months : [];
    const dates = availability.dates || {};
    const calendarGrid = schedulePicker.querySelector('#scheduleCalendarGrid');
    const monthLabel = schedulePicker.querySelector('#scheduleMonthLabel');
    const timesLabel = schedulePicker.querySelector('#scheduleTimesLabel');
    const selectionHint = schedulePicker.querySelector('#scheduleSelectionHint');
    const timeSlots = schedulePicker.querySelector('#scheduleTimeSlots');
    const selectedDateLabel = schedulePicker.querySelector('#selectedDateLabel');
    const selectedTimeLabel = schedulePicker.querySelector('#selectedTimeLabel');
    const dateInput = schedulePicker.querySelector('#preferred_date');
    const timeInput = schedulePicker.querySelector('#preferred_time');
    const prevButton = schedulePicker.querySelector('[data-month-nav="prev"]');
    const nextButton = schedulePicker.querySelector('[data-month-nav="next"]');

    if (!calendarGrid || !monthLabel || !timeSlots || !dateInput || !timeInput) {
        return;
    }

    const firstAvailableDate = Object.values(dates).find((entry) => entry.available)?.date || '';
    let selectedDate = dateInput.value && dates[dateInput.value] ? dateInput.value : firstAvailableDate;
    let selectedTime = timeInput.value;
    let activeMonthIndex = Math.max(0, months.findIndex((month) => month.key === dates[selectedDate]?.monthKey));

    function syncSelectionLabels() {
        const dateInfo = dates[selectedDate];
        selectedDateLabel.textContent = dateInfo ? dateInfo.longLabel : 'None';
        const selectedSlot = slotsForSelectedDate().find((slot) => slot.value === selectedTime);
        selectedTimeLabel.textContent = selectedSlot?.label || selectedTime || 'None';
    }

    function slotsForSelectedDate() {
        const dateInfo = dates[selectedDate];
        return Array.isArray(dateInfo?.slots) ? dateInfo.slots : [];
    }

    function renderTimes() {
        const dateInfo = dates[selectedDate];
        const slots = slotsForSelectedDate();
        const availableSlots = slots.filter((slot) => slot.available);
        const capacitySlot = slots[0];

        timesLabel.textContent = dateInfo ? `Available Slots for ${dateInfo.fullLabel}` : 'Available Slots';
        selectionHint.textContent = dateInfo
            ? (availableSlots.length ? (dateInfo.scheduleLabel || 'Please come on time for this service schedule.') : 'This day is already fully booked.')
            : 'Select an available day to view the remaining capacity.';

        if (!dateInfo) {
            timeSlots.innerHTML = '<p class="time-slots-empty">No schedule data available.</p>';
            return;
        }

        if (capacitySlot?.available) {
            selectedTime = capacitySlot.value || 'Daily Slot';
            timeInput.value = selectedTime;
            const remainingSlots = Number.isFinite(Number(capacitySlot.availableCount)) ? capacitySlot.availableCount : 0;
            timeSlots.innerHTML = `<div class="capacity-note"><span>Remaining slots available:</span><strong>${remainingSlots}</strong></div>`;
        } else {
            selectedTime = '';
            timeInput.value = '';
            timeSlots.innerHTML = '<p class="time-slots-empty">No slots available for this day.</p>';
        }
    }

    function renderCalendar() {
        const activeMonth = months[activeMonthIndex];
        if (!activeMonth) {
            calendarGrid.innerHTML = '';
            monthLabel.textContent = 'No available month';
            return;
        }

        monthLabel.textContent = activeMonth.label;
        prevButton.disabled = activeMonthIndex === 0;
        nextButton.disabled = activeMonthIndex === months.length - 1;

        const cells = [];
        for (let index = 0; index < activeMonth.firstWeekday; index += 1) {
            cells.push('<span class="calendar-spacer" aria-hidden="true"></span>');
        }

        for (let day = 1; day <= activeMonth.daysInMonth; day += 1) {
            const dateKey = `${activeMonth.key}-${String(day).padStart(2, '0')}`;
            const dateInfo = dates[dateKey];

            if (!dateInfo) {
                cells.push(`<span class="calendar-day is-disabled"><span>${day}</span></span>`);
                continue;
            }

            const classes = ['calendar-day'];
            if (!dateInfo.available) {
                classes.push('is-booked');
            }
            if (dateKey === selectedDate) {
                classes.push('is-selected');
            }

            cells.push(`
                <button type="button" class="${classes.join(' ')}" data-date="${dateKey}">
                    <span>${day}</span>
                    <i class="calendar-status-dot"></i>
                </button>
            `);
        }

        calendarGrid.innerHTML = cells.join('');
        calendarGrid.querySelectorAll('[data-date]').forEach((button) => {
            button.addEventListener('click', () => {
                const nextDate = button.dataset.date || '';
                if (!dates[nextDate]?.available) {
                    return;
                }

                selectedDate = nextDate;
                dateInput.value = selectedDate;

                const currentSlot = dates[selectedDate]?.slots?.find((slot) => slot.available);
                selectedTime = currentSlot?.value || 'Daily Slot';
                timeInput.value = selectedTime;

                renderCalendar();
                renderTimes();
                syncSelectionLabels();
                updateSubmitState();
            });
        });
    }

    prevButton?.addEventListener('click', () => {
        if (activeMonthIndex > 0) {
            activeMonthIndex -= 1;
            renderCalendar();
        }
    });

    nextButton?.addEventListener('click', () => {
        if (activeMonthIndex < months.length - 1) {
            activeMonthIndex += 1;
            renderCalendar();
        }
    });

    if (selectedDate) {
        dateInput.value = selectedDate;
    }
    if (!selectedTime) {
        selectedTime = dates[selectedDate]?.slots?.find((slot) => slot.available)?.value || 'Daily Slot';
        timeInput.value = selectedTime;
    }

    syncSelectionLabels();
    renderCalendar();
    renderTimes();
    updateSubmitState();
}

const bookingForm = document.querySelector('.booking-form');
const submitButton = document.querySelector('#submitBookingButton');

function updateSubmitState() {
    if (!submitButton) {
        return;
    }

    submitButton.disabled = false;
    submitButton.classList.add('is-ready');
}

if (bookingForm) {
    bookingForm.addEventListener('submit', function () {
        const dateInput = document.getElementById('preferred_date');
        const timeInput = document.getElementById('preferred_time');
        if (dateInput && !dateInput.value.trim()) {
            const activeDateBtn = document.querySelector('.calendar-day.is-selected') || document.querySelector('.calendar-day[data-date]:not(.is-disabled):not(.is-booked)');
            if (activeDateBtn && activeDateBtn.dataset.date) {
                dateInput.value = activeDateBtn.dataset.date;
            }
        }
        if (timeInput && !timeInput.value.trim()) {
            timeInput.value = 'Daily Slot';
        }
    });
}

filterStations();
renderSchedulePicker();
updateSubmitState();
const downloadButton = document.querySelector('#downloadConfirmationButton');

function autoHidePageNotifications() {
    const selectors = ['.toast-success', '.photo-notice'];
    const notifications = Array.from(new Set(selectors.flatMap((selector) => Array.from(document.querySelectorAll(selector)))));
    if (notifications.length === 0) {
        return;
    }

    window.setTimeout(() => {
        notifications.forEach((message) => {
            message.style.transition = 'opacity 0.35s ease';
            message.style.opacity = '0';
            window.setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 350);
        });
    }, 5000);
}

autoHidePageNotifications();

function roundedRect(ctx, x, y, width, height, radius, fillStyle) {
    ctx.fillStyle = fillStyle;
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
    ctx.fill();
}

function writeWrappedText(ctx, text, x, y, maxWidth, lineHeight, color = '#21314d', font = '28px Outfit') {
    ctx.fillStyle = color;
    ctx.font = font;

    const words = text.split(' ');
    let line = '';
    let currentY = y;

    words.forEach((word, index) => {
        const testLine = line ? `${line} ${word}` : word;
        const width = ctx.measureText(testLine).width;
        if (width > maxWidth && line) {
            ctx.fillText(line, x, currentY);
            line = word;
            currentY += lineHeight;
        } else {
            line = testLine;
        }

        if (index === words.length - 1 && line) {
            ctx.fillText(line, x, currentY);
        }
    });

    return currentY;
}

function downloadConfirmationImage() {
    if (!downloadButton) {
        return;
    }

    const details = {
        reference: downloadButton.dataset.reference || '',
        patientId: downloadButton.dataset.patientId || '',
        station: downloadButton.dataset.station || '',
        service: downloadButton.dataset.service || '',
        date: downloadButton.dataset.date || '',
        time: downloadButton.dataset.time || '',
        name: downloadButton.dataset.name || '',
        contact: downloadButton.dataset.contact || '',
        email: downloadButton.dataset.email || '',
        address: downloadButton.dataset.address || '',
    };

    const canvas = document.createElement('canvas');
    canvas.width = 1600;
    canvas.height = 1200;
    const ctx = canvas.getContext('2d');

    ctx.fillStyle = '#f5fbff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const gradient = ctx.createLinearGradient(180, 120, 1420, 1200);
    gradient.addColorStop(0, '#10b981');
    gradient.addColorStop(1, '#059669');
    roundedRect(ctx, 120, 90, 1360, 330, 36, gradient);

    ctx.fillStyle = '#ffffff';
    ctx.font = '700 72px Outfit';
    ctx.fillText('Booking Confirmed!', 420, 220);
    ctx.font = '400 34px Outfit';
    ctx.fillText('Your appointment request has been submitted successfully.', 300, 285);

    roundedRect(ctx, 520, 315, 560, 120, 26, 'rgba(255,255,255,0.18)');
    ctx.font = '500 28px Outfit';
    ctx.fillText('Appointment ID', 660, 362);
    ctx.font = '800 58px Outfit';
    ctx.fillText(details.reference, 555, 415);

    roundedRect(ctx, 1120, 315, 220, 120, 26, 'rgba(255,255,255,0.18)');
    ctx.font = '500 24px Outfit';
    ctx.fillText('Patient ID', 1168, 360);
    ctx.font = '700 30px Outfit';
    ctx.fillText(details.patientId || 'Pending', 1150, 405);

    roundedRect(ctx, 120, 470, 1360, 410, 28, '#ffffff');
    ctx.strokeStyle = '#dbe7f3';
    ctx.lineWidth = 2;
    ctx.strokeRect(120, 470, 1360, 410);

    ctx.fillStyle = '#0f2240';
    ctx.font = '700 52px Outfit';
    ctx.fillText('Appointment Details', 170, 560);

    const rows = [
        ['Health Station', details.station],
        ['Service', details.service],
        ['Appointment Date', details.date],
        ['Service Slot', details.time],
        ['Name', details.name],
        ['Contact Number', details.contact],
        ['Email', details.email],
        ['Address', details.address],
    ];

    let x = 170;
    let y = 640;
    rows.forEach((row, index) => {
        ctx.fillStyle = '#6b7a90';
        ctx.font = '500 24px Outfit';
        ctx.fillText(row[0], x, y);
        const endY = writeWrappedText(ctx, row[1], x, y + 42, 540, 36, '#11284a', '600 30px Outfit');

        if (index % 2 === 1) {
            x = 170;
            y += 120;
        } else {
            x = 830;
        }

        if (endY > y + 78 && index % 2 === 1) {
            y = endY + 44;
        }
    });

    roundedRect(ctx, 120, 925, 1360, 185, 28, '#eaf3ff');
    ctx.fillStyle = '#1f4cbf';
    ctx.font = '700 46px Outfit';
    ctx.fillText('What Happens Next?', 170, 1000);

    const nextSteps = [
        'The health station will review your booking request and confirm availability.',
        'You will receive a confirmation via SMS or call within 24 hours.',
        'Please arrive 10-15 minutes before your appointment date queue begins.',
        'Bring a valid ID and any relevant medical records or documents.',
    ];

    let stepY = 1050;
    nextSteps.forEach((step, index) => {
        ctx.fillStyle = '#2563eb';
        ctx.beginPath();
        ctx.arc(190, stepY - 10, 18, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 22px Outfit';
        ctx.fillText(String(index + 1), 184, stepY - 3);
        writeWrappedText(ctx, step, 225, stepY, 1180, 30, '#22459b', '500 26px Outfit');
        stepY += 42;
    });

    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = `${details.reference || 'booking-confirmation'}.png`;
    link.click();
}

downloadButton?.addEventListener('click', downloadConfirmationImage);

/* ── Auto-capitalize: first letter of each word as you type ── */
document.querySelectorAll('.capitalize-input').forEach((input) => {
    input.addEventListener('input', function () {
        const pos = this.selectionStart;
        const words = this.value.split(' ');
        const capitalized = words.map(w => w.length > 0 ? w.charAt(0).toUpperCase() + w.slice(1) : w).join(' ');
        if (capitalized !== this.value) {
            this.value = capitalized;
            this.setSelectionRange(pos, pos);
        }
    });
});

/* ── Combine purok + address_remainder → hidden complete_address ── */
const purokSelect = document.getElementById('purok_select');
const addressBarangaySelect = document.getElementById('address_barangay');
const addressRemainder = document.getElementById('address_remainder');
const completeAddressHidden = document.getElementById('complete_address');
const addressFields = document.querySelector('.address-fields');
let purokOptionsByBarangay = {};

try {
    purokOptionsByBarangay = JSON.parse(addressFields?.dataset.puroksByBarangay || '{}');
} catch (error) {
    purokOptionsByBarangay = {};
}

function populatePuroksForBarangay(barangay, selectedPurok = '') {
    if (!purokSelect) return;

    const options = Array.isArray(purokOptionsByBarangay[barangay]) ? purokOptionsByBarangay[barangay] : [];
    const placeholder = barangay ? (options.length ? 'Select Purok' : 'No Puroks Listed') : 'Select Barangay First';
    purokSelect.innerHTML = `<option value="">${placeholder}</option>`;

    options.forEach((purok) => {
        const option = document.createElement('option');
        option.value = purok;
        option.textContent = purok;
        option.selected = purok === selectedPurok;
        purokSelect.appendChild(option);
    });
}

function syncCompleteAddress() {
    if (!purokSelect || !addressBarangaySelect || !addressRemainder || !completeAddressHidden) return;
    const purok = purokSelect.value.trim();
    const barangay = addressBarangaySelect.value.trim();
    const rest = addressRemainder.value.trim();
    completeAddressHidden.value = [barangay, purok, rest, 'Bacolod City'].filter(Boolean).join(', ');
    // Trigger required field check
    completeAddressHidden.dispatchEvent(new Event('change', { bubbles: true }));
    updateSubmitState();
}

purokSelect?.addEventListener('change', syncCompleteAddress);
addressBarangaySelect?.addEventListener('change', () => {
    populatePuroksForBarangay(addressBarangaySelect.value.trim());
    syncCompleteAddress();
});
addressRemainder?.addEventListener('input', syncCompleteAddress);

/* ── Gender radio: sync state & hook into required field validation ── */
document.querySelectorAll('.gender-options .radio-option input[type="radio"]').forEach((radio) => {
    radio.addEventListener('change', function () {
        const container = this.closest('.gender-options');
        if (container) {
            container.querySelectorAll('.radio-option').forEach((opt) => {
                const isChecked = opt.querySelector('input[type="radio"]')?.checked;
                opt.classList.toggle('is-selected', !!isChecked);
            });
        }
        if (typeof updateSubmitState === 'function') {
            updateSubmitState();
        }
    });
});

/* ══════════════════════════════════════════════════════════════════════
   GLOBAL MODERN SYSTEM TOAST HELPER
   ══════════════════════════════════════════════════════════════════════ */
window.showSystemToast = function (message, options = {}) {
    if (!message) return null;
    const config = typeof options === 'string' ? { title: options } : options;
    const {
        title = '',
        type = 'success',
        theme = 'patient',
        duration = 5000,
        badge = ''
    } = config;

    let container = document.getElementById('systemToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'systemToastContainer';
        container.className = 'system-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `system-toast theme-${theme} type-${type}`;

    let iconSvg = '';
    if (type === 'error') {
        iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
    } else if (type === 'warning') {
        iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
    } else if (type === 'info') {
        iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`;
    } else {
        iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
    }

    const defaultTitle = title || (type === 'error' ? 'Notice' : (type === 'warning' ? 'Warning' : 'Success'));
    const badgeHtml = badge ? `<span class="toast-code-pill">${badge}</span>` : '';

    toast.innerHTML = `
        <div class="toast-icon-badge">${iconSvg}</div>
        <div class="toast-body">
            <h4 class="toast-title">${defaultTitle} ${badgeHtml}</h4>
            <p class="toast-message">${message}</p>
        </div>
        <button type="button" class="toast-close-btn" aria-label="Close notification">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="toast-progress-bar">
            <div class="toast-progress-fill" style="animation-duration: ${duration}ms;"></div>
        </div>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('toast-visible');
    });

    let dismissTimeout;
    const removeToast = () => {
        if (dismissTimeout) clearTimeout(dismissTimeout);
        toast.classList.remove('toast-visible');
        toast.classList.add('toast-leaving');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 400);
    };

    toast.querySelector('.toast-close-btn')?.addEventListener('click', removeToast);

    if (duration > 0) {
        dismissTimeout = setTimeout(removeToast, duration);
        toast.addEventListener('mouseenter', () => {
            clearTimeout(dismissTimeout);
            const fill = toast.querySelector('.toast-progress-fill');
            if (fill) fill.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            const fill = toast.querySelector('.toast-progress-fill');
            if (fill) fill.style.animationPlayState = 'running';
            dismissTimeout = setTimeout(removeToast, 2500);
        });
    }

    return toast;
};
