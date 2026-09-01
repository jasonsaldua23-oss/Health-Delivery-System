(function () {
    function fillCoordinates(form) {
        return new Promise((resolve, reject) => {
            const latInput = form.querySelector('input[name="geo_lat"]');
            const lngInput = form.querySelector('input[name="geo_lng"]');

            if (!latInput || !lngInput) {
                reject(new Error('Missing geofence fields.'));
                return;
            }

            if (!navigator.geolocation) {
                reject(new Error('Geolocation is not supported on this device.'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latInput.value = String(position.coords.latitude);
                    lngInput.value = String(position.coords.longitude);
                    resolve();
                },
                () => reject(new Error('Unable to read your location. Please allow location access and try again.')),
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
    }

    function attachGeofenceForms() {
        document.querySelectorAll('[data-geofence-form]').forEach((form) => {
            if (form.dataset.geofenceBound === 'true') {
                return;
            }

            form.dataset.geofenceBound = 'true';
            form.addEventListener('submit', async (event) => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    await fillCoordinates(form);
                } catch (error) {
                    event.preventDefault();
                    window.alert(error.message || 'Location verification failed.');
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachGeofenceForms);
    } else {
        attachGeofenceForms();
    }
})();
