import NiceSelect from 'nice-select2';
import 'nice-select2/dist/css/nice-select2.css';
import './field-select-nice.css';

export default function init($el) {
    new NiceSelect($el, {
        searchable: false
    });
}