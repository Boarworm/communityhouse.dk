import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

export default function init(element) {
    new Swiper(element.querySelector('.swiper'), {
        modules: [Navigation, Pagination],
        slidesPerView: 1.2,
        spaceBetween: 32,
        speed: 500,
        navigation: {
            nextEl: element.querySelector('.swiper-button-next'),
            prevEl: element.querySelector('.swiper-button-prev'),
        },
        pagination: {
            el: element.querySelector('.swiper-pagination'),
            bulletActiveClass: 'swiper-pagination-bullet-active !bg-primary',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            }
        }
    });
}
