import Swiper from 'swiper';
import {Autoplay, Navigation} from 'swiper/modules';
import 'swiper/css';

export default function init(element) {
    new Swiper(element, {
        modules: [Autoplay, Navigation],
        loop: true,
        slidesPerView: 1,
        speed: 1500,
        autoplay: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
}
