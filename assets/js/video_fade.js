document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('hero');
    
    if (!video) return;
    
    const fadeDuration = 1.5;
    const fadeOverlay = document.createElement('div');
    fadeOverlay.id = 'video-fade-overlay';
    fadeOverlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: black;
        opacity: 0;
        pointer-events: none;
        transition: opacity ${fadeDuration}s ease-in-out;
        z-index: 1;
    `;
    
    const aside = video.parentElement;
    aside.style.position = 'relative';
    aside.appendChild(fadeOverlay);
    
    video.addEventListener('timeupdate', function() {
        const currentTime = video.currentTime;
        const duration = video.duration;
        const fadeStartTime = duration - fadeDuration - 0.5;
        
        if (currentTime >= fadeStartTime && currentTime < duration) {
            const fadeProgress = (currentTime - fadeStartTime) / fadeDuration;
            fadeOverlay.style.opacity = Math.min(fadeProgress, 1);
        } else if (currentTime < fadeStartTime) {
            fadeOverlay.style.opacity = 0;
        }
    });
    
    video.addEventListener('ended', function() {
        fadeOverlay.style.opacity = 0;
    });
    
    video.addEventListener('seeked', function() {
        const currentTime = video.currentTime;
        const duration = video.duration;
        const fadeStartTime = duration - fadeDuration - 0.5;
        
        if (currentTime < fadeStartTime) {
            fadeOverlay.style.opacity = 0;
        }
    });
});
