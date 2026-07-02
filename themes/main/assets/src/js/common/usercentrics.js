/**
 * Custom CSS injection for Usercentrics Shadow DOM
 */
function injectUsercentricsCSS() {
    const hostElement = document.getElementById('usercentrics-cmp-ui');
    
    if (hostElement && hostElement.shadowRoot) {
        if (hostElement.shadowRoot.getElementById('custom-uc-styles')) {
            return true;
        }

        const style = document.createElement('style');
        style.id = 'custom-uc-styles';
        style.innerHTML = `
            .privacyButton.left, .privacyButton.tablet.left {
                left: 5px !important;
                bottom: 5px !important;
            }
        `;
        
        hostElement.shadowRoot.appendChild(style);
        return true;
    }
    return false;
}

const observer = new MutationObserver((mutations, obs) => {
    if (document.getElementById('usercentrics-cmp-ui')) {
        if (injectUsercentricsCSS()) {
             obs.disconnect();
        }
    }
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Run once immediately in case it's already rendered
injectUsercentricsCSS();

// A few fallbacks just in case
setTimeout(injectUsercentricsCSS, 500);
setTimeout(injectUsercentricsCSS, 1000);
setTimeout(injectUsercentricsCSS, 2000);
