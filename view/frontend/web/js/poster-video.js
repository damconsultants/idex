document.addEventListener("DOMContentLoaded", function () {
    let videoTag = document.querySelector(".pagebuilder-video-container video");

    if (videoTag) {
        let videoSrc = videoTag.getAttribute("poster");

        // Check if the poster image exists
        if (videoSrc && videoSrc.trim() !== "") {
            videoTag.removeAttribute("controls"); // Hide controls initially

            // Create a play button overlay
            let playButton = document.createElement("button");
            playButton.innerHTML = "▶";
            playButton.classList.add("play-button");
            Object.assign(playButton.style, {
                position: "absolute",
                top: "50%",
                left: "50%",
                transform: "translate(-50%, -50%)",
                background: "rgba(0, 0, 0, 0.6)",
                color: "white",
                border: "none",
                borderRadius: "50%",
                width: "60px",
                height: "60px",
                fontSize: "24px",
                cursor: "pointer",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                zIndex: "10",
                transition: "background 0.3s ease",
            });

            playButton.addEventListener("mouseover", function () {
                playButton.style.background = "rgba(0, 0, 0, 0.8)";
            });
            playButton.addEventListener("mouseout", function () {
                playButton.style.background = "rgba(0, 0, 0, 0.6)";
            });

            // Make sure the video container is properly positioned
            let videoContainer = document.querySelector(".pagebuilder-video-container");
            videoContainer.style.position = "relative";
            videoContainer.appendChild(playButton);

            // Ensure poster is shown initially
            videoTag.style.display = "block";
            videoTag.style.pointerEvents = "none"; // Prevent interaction before play

            // Play video when clicking the play button
            playButton.addEventListener("click", function () {
                playButton.style.display = "none"; // Hide play button
                videoTag.style.background = "none"; // Remove poster image
                videoTag.setAttribute("controls", "true"); // Enable controls
                videoTag.style.pointerEvents = "auto"; // Allow clicking
                videoTag.play(); // Play the video
            });

            // Show poster and play button again when video ends
            videoTag.addEventListener("ended", function () {
                playButton.style.display = "flex";
                videoTag.style.background = `url('${videoSrc}') center/cover no-repeat`;
                videoTag.removeAttribute("controls");
            });

            // Scroll Event: Pause video and show poster/play button
            window.addEventListener("scroll", function () {
                let rect = videoContainer.getBoundingClientRect();
                let windowHeight = window.innerHeight;

                if (rect.top < -50 || rect.bottom > windowHeight + 50) {
                    // If video is out of view, reset
                    if (!videoTag.paused) {
                        videoTag.pause();
                    }
                    playButton.style.display = "flex";
                    videoTag.style.background = `url('${videoSrc}') center/cover no-repeat`;
                    videoTag.removeAttribute("controls");
                }
            });
        }
    }
});
