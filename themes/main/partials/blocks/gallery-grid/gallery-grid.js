import {Fancybox} from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export default function init(element) {
    Fancybox.bind(element, "[data-fancybox]", {
        Hash: false,
        thumbs: {
            autoStart: true
        }
    });
}
