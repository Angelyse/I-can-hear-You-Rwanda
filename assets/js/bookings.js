/* Filter tabs on the client bookings page. */
(function () {
    "use strict";
    const tabs = document.querySelectorAll(".booking-tab");
    const cards = document.querySelectorAll(".booking-card");
    tabs.forEach(tab => tab.addEventListener("click", function () {
        tabs.forEach(t => t.classList.remove("active"));
        this.classList.add("active");
        const s = this.dataset.status;
        cards.forEach(c => { c.style.display = (s === "all" || c.dataset.status === s) ? "" : "none"; });
    }));
})();
