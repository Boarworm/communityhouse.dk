export default function init(element) {
    const videoContainer = element.querySelector('.js-video-background-youtube-container');
    const videoId = element.getAttribute('data-video-id');
    const muteIcon = element.querySelector('.js-video-background-youtube-mute-icon');
    const mutedIcon = element.querySelector('.js-video-background-youtube-icon-muted');
    const unmutedIcon = element.querySelector('.js-video-background-youtube-icon-unmuted');

    const tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    tag.async = true;
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    let player;

    window.onYouTubeIframeAPIReady = function () {
        player = new YT.Player(videoContainer, {
            videoId: videoId,
            playerVars: {
                'playlist': videoId,
                'rel': 0,
                'loop': 1,
                'controls': 0,
                'autoplay': 1,
                'autohide': 1,
                'fs': 0,
                'modestbranding': 1,
                'cc_load_policy': 0,
            },
            events: {
                'onReady': onPlayerReady,
            }
        });
    }

    function onPlayerReady(event) {
        event.target.playVideo();
        event.target.mute();
    }

    muteIcon.addEventListener('click', function () {
        if (player && typeof player.isMuted === 'function') {
            if (player.isMuted()) {
                player.unMute();
                mutedIcon.classList.add('hidden');
                unmutedIcon.classList.remove('hidden');
            } else {
                player.mute();
                unmutedIcon.classList.add('hidden');
                mutedIcon.classList.remove('hidden');
            }
        }
    });
}
