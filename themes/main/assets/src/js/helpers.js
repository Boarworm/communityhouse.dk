export function initInView(el) {
    return new Promise((resolve, reject) => {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    resolve(true);
                    observer.unobserve(el);
                }
            });
        });
        observer.observe(el);
    });
}

export function scrollToElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        // First scroll triggers lazy load
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Second scroll after delay to correct for dynamic content
        setTimeout(() => {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 800);
    } else {
        console.error(`Element with selector '${selector}' not found.`);
    }
}

/**
 * Executes a callback once user interacts with the page
 */
export function onUserInteraction(callback) {
    const handleInteraction = () => {
        if (window.userInteracted) return;
        window.userInteracted = true;

        callback();

        // Cleanup
        ['mousemove', 'scroll', 'touchstart', 'click'].forEach(event => {
            window.removeEventListener(event, handleInteraction);
        });
    };

    ['mousemove', 'scroll', 'touchstart', 'click'].forEach(event => {
        window.addEventListener(event, handleInteraction, { passive: true, once: true });
    });
}

/**
 * Injects a script into the DOM
 */
export function loadScript(src) {
    if (document.querySelector(`script[src="${src}"]`)) return;

    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    document.head.appendChild(script);
}

/**
 * Returns a promise that resolves when the October CMS framework is ready
 */
export function waitForFramework() {
    return new Promise((resolve) => {
        if (window.oc) {
            resolve(window.oc);
            return;
        }

        const interval = setInterval(() => {
            if (window.oc) {
                clearInterval(interval);
                resolve(window.oc);
            }
        }, 50);

        // Safety timeout
        setTimeout(() => {
            clearInterval(interval);
            resolve(null);
        }, 5000);
    });
}