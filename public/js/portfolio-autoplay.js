(() => {
    function setupVideoAutoplay() {
        const videos = document.querySelectorAll('.portfolio-feed-collage video');
        if (!videos.length || !('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                    video.muted = true;
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            });
        }, { threshold: [0, 0.6, 1] });

        videos.forEach(video => observer.observe(video));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupVideoAutoplay);
    } else {
        setupVideoAutoplay();
    }
})();
