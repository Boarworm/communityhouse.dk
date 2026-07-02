import {Fancybox} from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export default function init(element) {
    Fancybox.bind("[data-fancybox]", {
        thumbs: {
            autoStart: true
        }
    });
}
