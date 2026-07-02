import Swiper from 'swiper';
import {Navigation} from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

import {Fancybox} from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export default function init(element) {
    new Swiper(element, {
        modules: [Navigation],
        loop: false,
        slidesPerView: 1,
        speed: 1000,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 5,
            }
        }
    });
    Fancybox.bind("[data-fancybox]", {
        thumbs: {
            autoStart: true
        }
    });
}

