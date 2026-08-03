import Swiper from 'swiper';
import {Autoplay, Navigation, EffectFade} from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-fade';

export default function init(element) {
    new Swiper(element, {
        modules: [Autoplay, Navigation, EffectFade],
        loop: true,
        slidesPerView: 1,
        speed: 1500,
        autoplay: true,
        effect: 'fade',
        fadeEffect: {
            crossFade: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
}
