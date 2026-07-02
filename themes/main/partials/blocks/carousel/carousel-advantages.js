import Swiper from 'swiper';
import { Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

export default function init(element) {
    new Swiper(element, {
        modules: [Pagination, Navigation],
        loop: true,
        slidesPerView: 1.2,
        spaceBetween: 20,
        speed: 500,
        pagination: {
            el: '.swiper-pagination',
            bulletClass: "swiper-pagination-bullet !bg-transparent !border-2 !border-neutral !w-4 !h-4 !rounded-full !opacity-100",
            dynamicBullets: true,
            dynamicMainBullets: 1,
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                enabled: false,
            }
        }
    });
}
