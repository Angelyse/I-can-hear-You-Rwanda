const mobileMenu = document.getElementById("mobileMenu");
const sidebar = document.getElementById("sidebar");

if (mobileMenu) {

    mobileMenu.addEventListener("click", function () {

        sidebar.classList.toggle("show");

    });

}