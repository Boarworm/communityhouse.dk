export default function init(element) {
    let scrollPos = 0;
    const menuMobile = document.querySelector('[data-block-layout="menu-mobile"]')
    const menuMobileBurgerCheckbox = document.querySelector('[data-block="burger"] input[type="checkbox"]')

    window.addEventListener('scroll', fixedHeader);

    function fixedHeader() {
        if ((document.body.getBoundingClientRect()).top > scrollPos) {
            element.classList.remove('!-top-32');
        } else {
            element.classList.add('!-top-32');
        }

        menuMobile.classList.remove('!left-0')
        menuMobileBurgerCheckbox.checked = false
        scrollPos = (document.body.getBoundingClientRect()).top;
    }
}

