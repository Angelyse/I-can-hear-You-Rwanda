/* Booking form: loads free slots from the API and submits the booking. */
(function () {
    "use strict";
    const page = document.querySelector(".booking-page");
    const form = document.getElementById("bookingForm");
    if (!page || !form) return;

    const counselorId = page.dataset.counselorId;
    const csrf = page.dataset.csrf;
    const dateInput = document.getElementById("sessionDate");
    const timeSelect = document.getElementById("sessionTime");
    const summaryType = document.getElementById("summaryType");
    const summaryDate = document.getElementById("summaryDate");
    const summaryTime = document.getElementById("summaryTime");
    const errorBox = document.getElementById("bookingError");

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.style.display = msg ? "block" : "none";
        if (msg) errorBox.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    function label(hhmm) {
        const [h, m] = hhmm.split(":").map(Number);
        const d = new Date(); d.setHours(h, m, 0, 0);
        return d.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" });
    }

    const t = new Date();
    dateInput.min = t.getFullYear() + "-" + String(t.getMonth() + 1).padStart(2, "0") + "-" + String(t.getDate()).padStart(2, "0");

    document.querySelectorAll('input[name="session_type"]').forEach(r => r.addEventListener("change", function () {
        summaryType.textContent = this.value === "video" ? "Video Session" : "Chat Session";
    }));

    dateInput.addEventListener("change", async function () {
        summaryTime.textContent = "Not selected";
        timeSelect.innerHTML = '<option value="">Loading…</option>';
        if (!this.value) { summaryDate.textContent = "Not selected"; timeSelect.innerHTML = '<option value="">Choose a date first</option>'; return; }
        summaryDate.textContent = new Date(this.value + "T00:00:00").toLocaleDateString("en-US",
            { weekday: "short", month: "short", day: "numeric", year: "numeric" });
        try {
            const res = await fetch("../api/bookings.php?action=slots&counselor_id=" + encodeURIComponent(counselorId) + "&date=" + this.value, { credentials: "same-origin" });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);
            if (!json.slots.length) { timeSelect.innerHTML = '<option value="">No free times — pick another date</option>'; return; }
            timeSelect.innerHTML = '<option value="">Choose available time</option>' +
                json.slots.map(s => '<option value="' + s + '">' + label(s) + "</option>").join("");
            showError("");
        } catch (e) { timeSelect.innerHTML = '<option value="">Could not load times</option>'; showError(e.message); }
    });

    timeSelect.addEventListener("change", function () {
        summaryTime.textContent = this.value ? label(this.value) : "Not selected";
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        showError("");
        const type = document.querySelector('input[name="session_type"]:checked');
        const reason = document.getElementById("reason").value.trim();
        if (!type || !dateInput.value || !timeSelect.value || !reason) { showError("Please complete every field."); return; }

        const btn = form.querySelector(".confirm-booking");
        btn.disabled = true;
        const body = new FormData();
        body.append("csrf_token", csrf);
        body.append("action", "create");
        body.append("counselor_id", counselorId);
        body.append("session_type", type.value);
        body.append("date", dateInput.value);
        body.append("time", timeSelect.value);
        body.append("reason", reason);
        try {
            const res = await fetch("../api/bookings.php", { method: "POST", body, credentials: "same-origin" });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || "Booking failed.");
            window.location.href = json.redirect || "bookings.php";
        } catch (err) {
            showError(err.message);
            btn.disabled = false;
        }
    });
})();
