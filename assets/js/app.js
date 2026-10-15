/* Shared front-end behaviour wired to the PHP API. Loaded on every dashboard page. */
(function () {
    "use strict";

    const root = document.querySelector("[data-csrf]");
    const csrf = root ? root.dataset.csrf : (document.querySelector('meta[name="csrf"]') || {}).content || "";

    // Resolve /api relative to the dashboard folder (client/, counselor/, Admin/).
    const API = "../api/";

    async function post(endpoint, data) {
        const body = new FormData();
        body.append("csrf_token", csrf);
        Object.keys(data).forEach(k => body.append(k, data[k]));
        const res = await fetch(API + endpoint, { method: "POST", body, credentials: "same-origin" });
        let json = {};
        try { json = await res.json(); } catch (e) { /* non-JSON response */ }
        if (!res.ok || json.ok === false) throw new Error(json.error || "Request failed.");
        return json;
    }
    window.icPost = post;

    /* ---------- mobile sidebar ---------- */
    const menu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");
    if (menu && sidebar && !menu.dataset.bound) {
        menu.dataset.bound = "1";
        menu.addEventListener("click", () => sidebar.classList.toggle("show"));
    }

    /* ---------- bookings: cancel / confirm / decline ---------- */
    document.addEventListener("click", async function (e) {
        const cancel = e.target.closest("[data-cancel]");
        const confirmBtn = e.target.closest("[data-confirm-booking]");
        const decline = e.target.closest("[data-decline-booking]");
        const btn = cancel || confirmBtn || decline;
        if (!btn) return;
        e.preventDefault();
        const id = btn.dataset.cancel || btn.dataset.confirmBooking || btn.dataset.declineBooking;
        const action = cancel ? "cancel" : confirmBtn ? "confirm" : "decline";
        if (action !== "confirm" && !window.confirm(action === "cancel" ? "Cancel this booking?" : "Decline this booking request?")) return;
        btn.disabled = true;
        try {
            await post("bookings.php", { action, booking_id: id });
            window.location.reload();
        } catch (err) {
            alert(err.message);
            btn.disabled = false;
        }
    });

    /* ---------- sessions: start / complete / feedback ---------- */
    document.addEventListener("click", async function (e) {
        const start = e.target.closest("[data-start-session]");
        const complete = e.target.closest("[data-complete-session]");
        const fb = e.target.closest(".feedback-btn[data-session]");
        if (start || complete) {
            e.preventDefault();
            const btn = start || complete;
            const id = btn.dataset.startSession || btn.dataset.completeSession;
            if (complete && !window.confirm("Mark this session as completed?")) return;
            btn.disabled = true;
            try {
                const r = await post("sessions.php", { action: start ? "start" : "complete", session_id: id });
                if (start && r.meeting_link) window.open(r.meeting_link, "_blank", "noopener");
                window.location.reload();
            } catch (err) { alert(err.message); btn.disabled = false; }
        }
        if (fb) {
            e.preventDefault();
            const rating = parseInt(window.prompt("Rate your session from 1 (poor) to 5 (excellent):", "5"), 10);
            if (!(rating >= 1 && rating <= 5)) return;
            const comment = window.prompt("Any comments? (optional)", "") || "";
            try {
                await post("sessions.php", { action: "feedback", session_id: fb.dataset.session, rating, comment });
                window.location.reload();
            } catch (err) { alert(err.message); }
        }
    });

    /* ---------- resources: save / unsave ---------- */
    document.addEventListener("click", async function (e) {
        const btn = e.target.closest(".save-resource");
        if (!btn) return;
        try {
            const r = await post("resources.php", { action: "toggle_save", resource_id: btn.dataset.id });
            btn.textContent = r.saved ? "★ Saved" : "☆ Save";
        } catch (err) { alert(err.message); }
    });

    /* ---------- notifications: read / delete / read all (DOM handled by page script) ---------- */
    document.addEventListener("click", function (e) {
        const read = e.target.closest(".read-btn");
        const del = e.target.closest(".delete-btn");
        const all = e.target.closest("#markAllRead");
        const item = e.target.closest(".notification-item");
        if (all) post("notifications.php", { action: "read_all" }).catch(() => {});
        else if (item && item.dataset.id && (read || del)) {
            post("notifications.php", { action: read ? "read" : "delete", id: item.dataset.id }).catch(() => {});
        }
    }, true);

    /* ---------- live unread badge ---------- */
    const badge = document.querySelector(".notification-count, .nav-badge");
    if (badge && document.querySelector(".sidebar")) {
        setInterval(function () {
            fetch(API + "notifications.php?action=count", { credentials: "same-origin" })
                .then(r => r.json()).then(j => {
                    if (j && j.ok) { badge.textContent = j.messages; badge.style.display = j.messages > 0 ? "" : "none"; }
                }).catch(() => {});
        }, 30000);
    }
})();
