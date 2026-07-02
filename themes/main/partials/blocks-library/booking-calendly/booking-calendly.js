export default function init($el) {
    if (!window.Calendly) {
        const script = document.createElement('script');
        script.setAttribute('src', 'https://assets.calendly.com/assets/external/widget.js');
        script.setAttribute('async', '');
        document.body.appendChild(script);
    } else {
        window.Calendly.initInlineWidget({
            url: $el.querySelector('.calendly-inline-widget').dataset.url,
            parentElement: $el.querySelector('.calendly-inline-widget')
        });
    }
}
