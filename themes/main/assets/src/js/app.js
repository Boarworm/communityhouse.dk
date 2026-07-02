import '../css/app.css';
import './dynamic-imports';
import 'lazysizes';
import './common/usercentrics';

import.meta.glob('../icons/**/*.svg', { eager: true });


import { scrollToElement } from './helpers';

window.scrollToElement = scrollToElement;
