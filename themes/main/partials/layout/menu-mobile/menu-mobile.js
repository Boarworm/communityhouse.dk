export default function init(element) {
    const menuMobile = document.querySelector('.js-menu-mobile');
    const burger = document.querySelector('.js-burger input');
    const triggers = document.querySelectorAll('.js-menu-mobile-trigger');

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            menuMobile.classList.toggle('!left-0');
        });
    });
}