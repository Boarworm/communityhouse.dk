import NiceSelect from 'nice-select2';
import 'nice-select2/dist/css/nice-select2.css';
import './language-picker.css';

export default function init($el) {
    const instance = new NiceSelect($el, {
        searchable: false,
    });

    // Add flags to the dropdown options
    const wrapper = $el.closest('[data-block="language-picker"]');
    const niceSelect = wrapper.querySelector('.nice-select');
    
    if (!niceSelect) return;

    // Add flag to current selected
    const currentSpan = niceSelect.querySelector('.current');
    const selectedOption = $el.querySelector('option:checked');
    if (selectedOption && currentSpan) {
        const flagSrc = selectedOption.getAttribute('data-flag');
        if (flagSrc) {
            currentSpan.innerHTML = `<img width="20" height="15" src="${flagSrc}" alt="${currentSpan.textContent}" class="w-5 h-auto inline-block mr-2"> ${currentSpan.textContent}`;
        }
    }

    // Add flags to dropdown options
    const listItems = niceSelect.querySelectorAll('.list .option');
    const options = $el.querySelectorAll('option');
    
    listItems.forEach((li, index) => {
        const option = options[index];
        if (option) {
            const flagSrc = option.getAttribute('data-flag');
            if (flagSrc) {
                li.innerHTML = `<img width="20" height="15" src="${flagSrc}" alt="${li.textContent}"> ${li.textContent}`;
            }
        }
    });
}
