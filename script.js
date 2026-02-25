(function () {

  function initSlider(root) {
    const track = root.querySelector(".slider-track");
    const prevBtn = root.querySelector(".prev");
    const nextBtn = root.querySelector(".next");

    if (!track || !prevBtn || !nextBtn) return;

    function step() {
      return Math.max(1, Math.floor(track.clientWidth * 0.9));
    }

  
    prevBtn.addEventListener("click", () => {
      track.scrollBy({ left: -step(), behavior: "smooth" });
    });

    nextBtn.addEventListener("click", () => {
      track.scrollBy({ left: step(), behavior: "smooth" });
    });


    track.addEventListener("keydown", (e) => {
      if (e.key === "ArrowRight") {
        e.preventDefault();
        track.scrollBy({ left: step(), behavior: "smooth" });
      } else if (e.key === "ArrowLeft") {
        e.preventDefault();
        track.scrollBy({ left: -step(), behavior: "smooth" });
      }
    });

    track.addEventListener("wheel", (e) => {
      if (e.shiftKey) {
        e.preventDefault();
        track.scrollBy({ left: e.deltaY, behavior: "smooth" });
      }
    }, { passive: false });


    let isDown = false;
    let startX = 0;
    let scrollStartLeft = 0;

    track.addEventListener("pointerdown", (e) => {
      isDown = true;
      track.setPointerCapture(e.pointerId);
      startX = e.clientX;
      scrollStartLeft = track.scrollLeft;
    });

    track.addEventListener("pointermove", (e) => {
      if (!isDown) return;
      const delta = e.clientX - startX;
      track.scrollLeft = scrollStartLeft - delta;
    });

    track.addEventListener("pointerup", (e) => {
      isDown = false;
      try { track.releasePointerCapture(e.pointerId); } catch (_) {}
    });

    track.addEventListener("pointercancel", () => { isDown = false; });
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-slider]").forEach(initSlider);
  });
})();
