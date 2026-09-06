export default function init(element) {
    const tabLinks = element.querySelectorAll('[data-js="tab-link"]');
    const tabContents = element.querySelector('[data-js="tab-contents"]');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.dataset.tab;

            tabContents.querySelectorAll('[data-js="tab-content"]').forEach(content => {
                content.classList.toggle('hidden', content.id !== tabId);
            });

            tabLinks.forEach(otherLink => {
                otherLink.classList.toggle('active', otherLink === link);
            });
        });
    });
}
