import Swiper from 'swiper';
import { Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

export default function init(element) {
    new Swiper(element, {
        modules: [Pagination, Navigation],
        loop: true,
        slidesPerView: 1,
        spaceBetween: 20,
        speed: 500,
        pagination: {
            el: element.querySelector('.swiper-pagination'),
            bulletClass: "swiper-pagination-bullet !bg-transparent !border-2 !border-neutral !w-4 !h-4 !rounded-full !opacity-100",
            dynamicBullets: true,
            dynamicMainBullets: 1,
            clickable: true
        },
        navigation: {
            nextEl: element.querySelector('.swiper-button-next'),
            prevEl: element.querySelector('.swiper-button-prev'),
        },
        breakpoints: {
            575: {
                slidesPerView: 2,
                slidesPerGroup: 2,
            },
            1200: {
                slidesPerView: 3,
                slidesPerGroup: 3,
            }
        }
    });
}
