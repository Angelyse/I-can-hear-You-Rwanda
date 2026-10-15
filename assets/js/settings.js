/* Settings page navigation. Forms post to ../api/account.php. */
document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".settings-menu-item");
    const sections = document.querySelectorAll(".settings-section");
    menuItems.forEach(function (item) {
        item.addEventListener("click", function () {
            menuItems.forEach(m => m.classList.remove("active"));
            sections.forEach(s => s.classList.remove("active"));
            this.classList.add("active");
            const target = document.getElementById(this.dataset.section);
            if (target) target.classList.add("active");
        });
    });
});
