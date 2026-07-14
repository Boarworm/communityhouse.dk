import { initInView } from "./helpers";

document.querySelectorAll('[data-block-layout="header"]').forEach($el => {
    import('../../../partials/layout/header/header' /* webpackChunkName: "/dist/js/header" */).then(({ default: init }) => init($el));
});

document.querySelectorAll('[data-block-layout="menu-mobile"]').forEach($el => {
    import('../../../partials/layout/menu-mobile/menu-mobile' /* webpackChunkName: "/dist/js/menu-mobile" */).then(({ default: init }) => init($el));
});

document.querySelectorAll('[data-js="post-carousel-latest"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/post-carousel-latest/post-carousel-latest' /* webpackChunkName: "/dist/js/post-carousel-latest" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-js="carousel-advantages"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/carousel/carousel-advantages' /* webpackChunkName: "/dist/js/carousel-advantages" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-js="testimonial-carousel"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/testimonial-carousel/testimonial-carousel' /* webpackChunkName: "/dist/js/testimonial-carousel" */).then(({ default: init }) => init($el));
    });
});


document.querySelectorAll('[data-block="gallery-grid"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/gallery-grid/gallery-grid' /* webpackChunkName: "/dist/js/gallery-grid" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-fancybox]').forEach($el => {
    initInView($el).then(() => {
        import('./common/fancybox-image' /* webpackChunkName: "/dist/js/fancybox-image" */).then(({ default: init }) => init($el));
    });
});



document.querySelectorAll('[data-block="youtube-embed"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/youtube-embed/youtube-embed' /* webpackChunkName: "/dist/js/youtube-embed" */).then(({ default: init }) => init($el));
    });
});


document.querySelectorAll('[data-block="language-picker"] select').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/language-picker/language-picker' /* webpackChunkName: "/dist/js/language-picker" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-block="openstreet-map"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/openstreet-map/openstreet-map' /* webpackChunkName: "/dist/js/openstreet-map" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-block="video-background-youtube"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/video-background-youtube/video-background-youtube' /* webpackChunkName: "/dist/js/video-background-youtube" */).then(({ default: init }) => init($el));
    });
});

document.querySelectorAll('[data-block="slider"]').forEach($el => {
    initInView($el).then(() => {
        import('../../../partials/blocks/slider/slider' /* webpackChunkName: "/dist/js/slider" */).then(({ default: init }) => init($el));
    });
});

