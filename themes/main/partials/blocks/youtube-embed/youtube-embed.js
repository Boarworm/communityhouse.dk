export default function init($el) {
    const $container = $el.querySelector('.js-youtube-embed-container');
    const videoId = $el.getAttribute('data-video-id');

    if (!videoId) return;

    const loadPlayer = () => {
        if (!window.YT) {
            const tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            tag.async = true;
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        const createPlayer = () => {
            new YT.Player($container, {
                videoId: videoId,
                playerVars: {
                    'rel': 0,
                    'modestbranding': 1,
                    'autoplay': 0,
                },
                events: {
                    'onReady': (event) => {
                        $el.querySelector('.js-youtube-embed-container').classList.remove('pointer-events-none');
                    },
                }
            });
        };

        if (window.YT && window.YT.Player) {
            createPlayer();
        } else {
            const previousOnReady = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = () => {
                if (previousOnReady) previousOnReady();
                createPlayer();
            };
        }
    };

    loadPlayer();
}

