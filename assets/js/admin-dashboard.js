document.addEventListener("DOMContentLoaded", function () {

    /* ================= MOBILE SIDEBAR ================= */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });

    }


    /* ================= NOTIFICATIONS ================= */

    const notificationBtn = document.getElementById("notificationBtn");

    if (notificationBtn) {

        notificationBtn.addEventListener("click", function () {

            alert(
                "You have 5 unread notifications.\n\n" +
                "• New booking request\n" +
                "• New user registration\n" +
                "• New message\n" +
                "• Resource published\n" +
                "• System notification"
            );

        });

    }


    /* ================= QUICK ACTIONS ================= */

    const quickActions = document.querySelectorAll(".quick-action");

    quickActions.forEach(function (button) {

        button.addEventListener("click", function () {

            const action = this.dataset.action;

            if (action === "user") {
                alert("Add User page will be connected in the next admin stage.");
            }

            else if (action === "counsellor") {
                alert("Add Counsellor page will be connected in the next admin stage.");
            }

            else if (action === "resource") {
                alert("Add Resource page will be connected in the next admin stage.");
            }

            else if (action === "program") {
                alert("Create Program page will be connected in the next admin stage.");
            }

        });

    });


    /* ================= ACTIVITY PERIOD ================= */

    const activityPeriod = document.getElementById("activityPeriod");

    if (activityPeriod) {

        activityPeriod.addEventListener("change", function () {

            alert(
                "Selected period: " +
                this.value +
                "\n\nThe chart will become dynamic after connecting the database."
            );

        });

    }


    /* ================= GLOBAL SEARCH ================= */

    const globalSearch = document.getElementById("globalSearch");

    if (globalSearch) {

        globalSearch.addEventListener("keydown", function (event) {

            if (event.key === "Enter") {

                const query = this.value.trim();

                if (query === "") {
                    alert("Please enter something to search.");
                    return;
                }

                alert(
                    'Searching the platform for: "' +
                    query +
                    '"\n\nGlobal search will be connected to the database later.'
                );

            }

        });

    }


    /* ================= LOGOUT ================= */

    const logoutLink = document.getElementById("logoutLink");

    if (logoutLink) {

        logoutLink.addEventListener("click", function (event) {

            const confirmLogout = confirm(
                "Are you sure you want to logout?"
            );

            if (!confirmLogout) {
                event.preventDefault();
            }

        });

    }


    /* ================= RESPONSIVE SIDEBAR ================= */

    document.addEventListener("click", function (event) {

        if (
            window.innerWidth <= 900 &&
            sidebar &&
            sidebar.classList.contains("show") &&
            !sidebar.contains(event.target) &&
            event.target !== mobileMenu
        ) {

            sidebar.classList.remove("show");

        }

    });

});