const feedback = document.querySelector('#control-feedback');
const emergencyBanner = document.querySelector('#emergency-banner');
const resetForm = document.querySelector('[data-emergency-reset]')?.form;
const emergencyButton = document.querySelector('[data-emergency-stop]');

document.querySelectorAll('form[data-async-control]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        if (feedback) {
            feedback.textContent = 'Opdracht wordt naar de PLC verzonden…';
            feedback.classList.remove('error');
            feedback.classList.add('visible');
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message ?? `PLC-request mislukt (HTTP ${response.status}).`);
            }

            if (feedback) feedback.textContent = result.message ?? 'PLC heeft de opdracht geaccepteerd.';

            if (typeof result.emergencyStopActive === 'boolean') {
                document.body.classList.toggle('emergency-active', result.emergencyStopActive);
                if (emergencyBanner) emergencyBanner.hidden = !result.emergencyStopActive;
                if (resetForm) resetForm.hidden = !result.emergencyStopActive;
                if (emergencyButton) emergencyButton.disabled = result.emergencyStopActive;
            }
        } catch (error) {
            if (feedback) {
                feedback.textContent = error instanceof Error ? error.message : 'PLC-request mislukt.';
                feedback.classList.add('error');
            }
        } finally {
            if (submitButton && !document.body.classList.contains('emergency-active')) {
                submitButton.disabled = false;
            }
        }
    });
});