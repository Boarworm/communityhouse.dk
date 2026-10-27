import {Fancybox} from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export default function init(element) {
    Fancybox.bind('[data-fancybox]', {});
}

window.showPopup = function () {
    Fancybox.show(
        [
            {
                src: "#popup",
                type: "inline",

            }
        ],
        {
            on: {
                close: () => {
                    document.querySelector('.js-popup-content').innerHTML = '';
                },
            },
        }
    );
}

window.closePopup = function () {
    Fancybox.close();
}