/* Settings tab switching for the counsellor area. */
document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".settings-tab");
    const sections = document.querySelectorAll(".settings-section");
    tabs.forEach(tab => tab.addEventListener("click", function () {
        tabs.forEach(t => t.classList.remove("active"));
        sections.forEach(s => s.classList.remove("active"));
        this.classList.add("active");
        const target = document.getElementById(this.dataset.section);
        if (target) target.classList.add("active");
    }));
});
