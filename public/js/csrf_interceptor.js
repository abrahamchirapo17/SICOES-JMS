document.addEventListener('DOMContentLoaded', () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    if (csrfToken) {
        const originalFetch = window.fetch;
        window.fetch = async function () {
            let [resource, config] = arguments;
            if (config === undefined) {
                config = {};
            }
            if (config.method && config.method.toUpperCase() === 'POST') {
                config.headers = {
                    ...config.headers,
                    'X-CSRF-Token': csrfToken
                };
            }
            return await originalFetch(resource, config);
        };
    }
});
