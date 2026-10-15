/* Chat for clients and counsellors. Talks to ../api/messages.php. */
(function () {
    "use strict";
    const page = document.querySelector(".messages-page");
    if (!page) return;

    const csrf = page.dataset.csrf;
    const me = parseInt(page.dataset.me, 10);
    const role = page.dataset.role;
    let convId = parseInt(page.dataset.conversation, 10) || 0;

    const list = document.getElementById("chatMessages");
    const input = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendMessage") || document.getElementById("sendBtn");
    const search = document.getElementById("conversationSearch");
    const newBtn = document.getElementById("newMessageBtn");
    const newBox = document.getElementById("newConversationBox");
    const newSelect = document.getElementById("newConversationSelect");

    function lastId() {
        const rows = list ? list.querySelectorAll("[data-id]") : [];
        return rows.length ? parseInt(rows[rows.length - 1].dataset.id, 10) : 0;
    }

    function addRow(m) {
        if (list.querySelector('[data-id="' + m.id + '"]')) return;
        const row = document.createElement("div");
        row.className = (page.dataset.layout === "counselor" ? "message " : "message-row ") + (m.mine ? "sent" : "received");
        row.dataset.id = m.id;
        const content = document.createElement("div");
        content.className = "message-content";
        const bubble = document.createElement("div");
        bubble.className = "message-bubble";
        bubble.textContent = m.message;          // textContent: never inject HTML
        const time = document.createElement("span");
        time.className = "message-time";
        time.textContent = m.time;
        content.appendChild(bubble);
        content.appendChild(time);
        row.appendChild(content);
        list.appendChild(row);
    }

    function scrollDown() { if (list) list.scrollTop = list.scrollHeight; }

    async function poll() {
        if (!convId || !list) return;
        try {
            const res = await fetch("../api/messages.php?action=thread&conversation_id=" + convId + "&after=" + lastId(), { credentials: "same-origin" });
            const json = await res.json();
            if (json.ok && json.messages.length) {
                const atBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 80;
                json.messages.forEach(addRow);
                if (atBottom) scrollDown();
            }
        } catch (e) { /* offline: try again next tick */ }
    }

    async function send() {
        const text = input.value.trim();
        if (!text || !convId) return;
        sendBtn.disabled = true;
        const body = new FormData();
        body.append("csrf_token", csrf);
        body.append("action", "send");
        body.append("conversation_id", convId);
        body.append("message", text);
        try {
            const res = await fetch("../api/messages.php", { method: "POST", body, credentials: "same-origin" });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error || "Could not send.");
            input.value = "";
            await poll();
            scrollDown();
        } catch (e) { alert(e.message); }
        sendBtn.disabled = false;
        input.focus();
    }

    if (sendBtn) sendBtn.addEventListener("click", send);
    if (input) input.addEventListener("keydown", e => {
        if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); send(); }
    });

    if (search) search.addEventListener("input", function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll(".conversation-item").forEach(item => {
            item.style.display = (item.dataset.name || "").toLowerCase().includes(q) ? "" : "none";
        });
    });

    if (newBtn && newBox) newBtn.addEventListener("click", () => {
        newBox.style.display = newBox.style.display === "none" ? "block" : "none";
    });
    if (newSelect) newSelect.addEventListener("change", async function () {
        if (!this.value) return;
        const body = new FormData();
        body.append("csrf_token", csrf);
        body.append("action", "start");
        body.append(role === "client" ? "counselor_id" : "client_id", this.value);
        try {
            const res = await fetch("../api/messages.php", { method: "POST", body, credentials: "same-origin" });
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);
            window.location.href = "messages.php?c=" + json.conversation_id;
        } catch (e) { alert(e.message || "Could not start the conversation."); }
    });

    scrollDown();
    if (convId) setInterval(poll, 5000);
})();
