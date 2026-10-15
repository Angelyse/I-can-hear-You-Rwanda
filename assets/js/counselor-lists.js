/* Generic search/filter for counsellor list pages (bookings, clients, sessions, notes, resources). */
(function () {
    "use strict";
    const ITEM = ".booking-card, .client-card, .session-card, .note-card, .resource-card";
    const items = Array.from(document.querySelectorAll(ITEM));
    if (!items.length) return;

    const $ = id => document.getElementById(id);
    const search = $("bookingSearch") || $("clientSearch") || $("sessionSearch") || $("notesSearch") || $("resourceSearch");
    const status = $("statusFilter") || $("notesFilter");
    const type = $("sessionTypeFilter") || $("typeFilter");
    const category = $("categoryFilter");
    const date = $("dateFilter") || $("notesDate");
    const tabs = Array.from(document.querySelectorAll(".booking-tab, .client-tab, .session-tab, .resource-tab"));
    let tab = "all";

    const nameOf = el => (el.dataset.name || el.dataset.client || el.dataset.title || "").toLowerCase();

    function apply() {
        const q = search ? search.value.trim().toLowerCase() : "";
        const s = status ? status.value : "all";
        const t = type ? type.value : "all";
        const c = category ? category.value : "all";
        const d = date ? date.value : "";
        let shown = 0;
        items.forEach(el => {
            const ok =
                (!q || nameOf(el).includes(q)) &&
                (s === "all" || !el.dataset.status || el.dataset.status === s) &&
                (tab === "all" || !el.dataset.status || el.dataset.status === tab || el.dataset.category === tab) &&
                (t === "all" || !el.dataset.type || el.dataset.type === t) &&
                (c === "all" || !el.dataset.category || el.dataset.category === c) &&
                (!d || !el.dataset.date || el.dataset.date === d);
            el.style.display = ok ? "" : "none";
            if (ok) shown++;
        });
        const empty = document.querySelector(".empty-sessions, .empty-notes, .empty-state");
        if (empty) empty.style.display = shown ? "none" : "block";
    }

    [search, status, type, category, date].forEach(el => el && el.addEventListener(el.tagName === "INPUT" && el.type === "text" ? "input" : "change", apply));
    tabs.forEach(b => b.addEventListener("click", function () {
        tabs.forEach(x => x.classList.remove("active"));
        this.classList.add("active");
        tab = this.dataset.filter || this.dataset.tab || this.dataset.category || "all";
        apply();
    }));
})();
