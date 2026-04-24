<script>
(() => {
    if (window.vybeTrack) {
        return;
    }

    const endpoint = @js(route('ux-events.store'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const parseJson = (value) => {
        if (!value) {
            return {};
        }

        try {
            const parsed = JSON.parse(value);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (_) {
            return {};
        }
    };

    const normalizeTargetId = (value) => {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const parsed = Number(value);

        return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
    };

    const isTrackedOnce = (element, key) => element.dataset[key] === '1';

    const markTracked = (element, key) => {
        element.dataset[key] = '1';
    };

    const formDataFromPayload = (payload) => {
        const formData = new FormData();

        formData.append('_token', csrfToken);
        formData.append('event_name', payload.event_name);

        ['page_type', 'target_type', 'target_id', 'context'].forEach((key) => {
            if (payload[key] !== undefined && payload[key] !== null && payload[key] !== '') {
                formData.append(key, String(payload[key]));
            }
        });

        if (payload.metadata && Object.keys(payload.metadata).length > 0) {
            formData.append('metadata', JSON.stringify(payload.metadata));
        }

        return formData;
    };

    const send = (payload, preferBeacon = true) => {
        if (!payload?.event_name) {
            return false;
        }

        const body = formDataFromPayload(payload);

        if (preferBeacon && navigator.sendBeacon) {
            try {
                return navigator.sendBeacon(endpoint, body);
            } catch (_) {
                // Fall back to fetch when beacon is unavailable.
            }
        }

        fetch(endpoint, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).catch(() => {});

        return true;
    };

    const buildPayload = (element, extra = {}) => {
        const metadata = {
            ...parseJson(element.dataset.trackMeta),
            ...(extra.metadata || {}),
        };

        if (element.dataset.trackLabel && !metadata.label) {
            metadata.label = element.dataset.trackLabel;
        }

        if (element.getAttribute('href') && !metadata.href) {
            metadata.href = element.getAttribute('href');
        }

        if (element.getAttribute('action') && !metadata.action) {
            metadata.action = element.getAttribute('action');
        }

        return {
            event_name: element.dataset.trackEvent || element.dataset.trackViewEvent || '',
            page_type: element.dataset.trackPageType || document.body.dataset.pageType || null,
            target_type: element.dataset.trackTargetType || null,
            target_id: normalizeTargetId(element.dataset.trackTargetId),
            context: element.dataset.trackContext || null,
            metadata: Object.keys(metadata).length > 0 ? metadata : null,
        };
    };

    window.vybeTrack = (eventName, payload = {}, options = {}) => send({
        event_name: eventName,
        page_type: payload.page_type || document.body.dataset.pageType || null,
        target_type: payload.target_type || null,
        target_id: normalizeTargetId(payload.target_id),
        context: payload.context || null,
        metadata: payload.metadata || null,
    }, options.preferBeacon !== false);

    document.addEventListener('click', (event) => {
        const element = event.target.closest('[data-track-event]:not([data-track-trigger]), [data-track-event][data-track-trigger="click"]');

        if (!element) {
            return;
        }

        if (element.dataset.trackOnce === '1' && isTrackedOnce(element, 'trackClickSent')) {
            return;
        }

        if (element.dataset.trackOnce === '1') {
            markTracked(element, 'trackClickSent');
        }

        send(buildPayload(element), true);
    });

    document.addEventListener('submit', (event) => {
        const element = event.target.closest('[data-track-event][data-track-trigger="submit"]');

        if (!element) {
            return;
        }

        send(buildPayload(element, {
            metadata: {
                form_id: element.getAttribute('id') || null,
                method: (element.getAttribute('method') || 'GET').toUpperCase(),
            },
        }), true);
    });

    document.addEventListener('change', (event) => {
        const element = event.target.closest('[data-track-event][data-track-trigger="change"]');

        if (!element) {
            return;
        }

        send(buildPayload(element, {
            metadata: {
                field: element.getAttribute('name') || null,
                value: element.value ?? null,
            },
        }), false);
    });

    const initViewTracking = () => {
        const elements = document.querySelectorAll('[data-track-view-event]');

        if (!elements.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            elements.forEach((element) => {
                if (!isTrackedOnce(element, 'trackViewSent')) {
                    markTracked(element, 'trackViewSent');
                    send(buildPayload(element), false);
                }
            });
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const element = entry.target;

                if (isTrackedOnce(element, 'trackViewSent')) {
                    observer.unobserve(element);
                    return;
                }

                markTracked(element, 'trackViewSent');
                send(buildPayload(element), false);
                observer.unobserve(element);
            });
        }, {
            threshold: 0.35,
        });

        elements.forEach((element) => observer.observe(element));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initViewTracking, { once: true });
    } else {
        initViewTracking();
    }
})();
</script>
