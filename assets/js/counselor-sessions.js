document.addEventListener("DOMContentLoaded", function () {

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    const sessionSearch = document.getElementById("sessionSearch");
    const statusFilter = document.getElementById("statusFilter");
    const typeFilter = document.getElementById("typeFilter");
    const dateFilter = document.getElementById("dateFilter");

    const sessionTabs = document.querySelectorAll(".session-tab");
    const sessionCards = document.querySelectorAll(".session-card");

    const emptySessions = document.getElementById("emptySessions");
    const clearFilters = document.getElementById("clearFilters");

    const calendarBtn = document.getElementById("calendarBtn");


    /* =========================================
       MOBILE SIDEBAR
    ========================================= */

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =========================================
       FILTER SESSIONS
    ========================================= */

    let activeTab = "all";


    function filterSessions() {

        const searchValue =
            sessionSearch.value.toLowerCase().trim();

        const statusValue =
            statusFilter.value;

        const typeValue =
            typeFilter.value;

        const dateValue =
            dateFilter.value;

        let visibleCount = 0;


        sessionCards.forEach(function (card) {

            const client =
                card.dataset.client.toLowerCase();

            const status =
                card.dataset.status;

            const type =
                card.dataset.type;

            const date =
                card.dataset.date;


            /* Search */

            const matchesSearch =
                client.includes(searchValue);


            /* Status filter */

            const matchesStatus =
                statusValue === "all" ||
                status === statusValue;


            /* Type filter */

            const matchesType =
                typeValue === "all" ||
                type === typeValue;


            /* Date filter */

            const matchesDate =
                dateValue === "" ||
                date === dateValue;


            /* Tab */

            const matchesTab =
                activeTab === "all" ||
                status === activeTab;


            if (
                matchesSearch &&
                matchesStatus &&
                matchesType &&
                matchesDate &&
                matchesTab
            ) {

                card.style.display = "grid";
                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        /* Empty state */

        if (visibleCount === 0) {

            emptySessions.classList.add("show");

        } else {

            emptySessions.classList.remove("show");

        }

    }


    /* =========================================
       SEARCH
    ========================================= */

    if (sessionSearch) {

        sessionSearch.addEventListener(
            "input",
            filterSessions
        );

    }


    /* =========================================
       STATUS FILTER
    ========================================= */

    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            function () {

                activeTab = "all";

                sessionTabs.forEach(function (tab) {
                    tab.classList.remove("active");
                });

                document
                    .querySelector('[data-tab="all"]')
                    .classList.add("active");

                filterSessions();

            }
        );

    }


    /* =========================================
       TYPE FILTER
    ========================================= */

    if (typeFilter) {

        typeFilter.addEventListener(
            "change",
            filterSessions
        );

    }


    /* =========================================
       DATE FILTER
    ========================================= */

    if (dateFilter) {

        dateFilter.addEventListener(
            "change",
            filterSessions
        );

    }


    /* =========================================
       SESSION TABS
    ========================================= */

    sessionTabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            sessionTabs.forEach(function (item) {
                item.classList.remove("active");
            });

            tab.classList.add("active");

            activeTab =
                tab.dataset.tab;

            filterSessions();

        });

    });


    /* =========================================
       CLEAR FILTERS
    ========================================= */

    if (clearFilters) {

        clearFilters.addEventListener("click", function () {

            sessionSearch.value = "";
            statusFilter.value = "all";
            typeFilter.value = "all";
            dateFilter.value = "";

            activeTab = "all";

            sessionTabs.forEach(function (tab) {

                tab.classList.remove("active");

            });

            document
                .querySelector('[data-tab="all"]')
                .classList.add("active");

            filterSessions();

        });

    }


    /* =========================================
       START / JOIN SESSION
    ========================================= */

    document
        .querySelectorAll(".start-session")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                alert(
                    "Starting session with " +
                    client +
                    ".\n\nVideo/chat functionality will be connected to the backend later."
                );

            });

        });


    /* =========================================
       MESSAGE CLIENT
    ========================================= */

    document
        .querySelectorAll(".message-client")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                alert(
                    "Opening conversation with " +
                    client +
                    "."
                );

                // Later:
                // window.location.href =
                // "messages.php?client=" +
                // encodeURIComponent(client);

            });

        });


    /* =========================================
       SESSION NOTES
    ========================================= */

    document
        .querySelectorAll(".notes-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                alert(
                    "Opening session notes for " +
                    client +
                    "."
                );

                // Later:
                // window.location.href =
                // "session-notes.php?client=" +
                // encodeURIComponent(client);

            });

        });


    /* =========================================
       VIEW DETAILS
    ========================================= */

    document
        .querySelectorAll(".view-details")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                alert(
                    "Session details page will be connected later."
                );

            });

        });


    /* =========================================
       CALENDAR VIEW
    ========================================= */

    if (calendarBtn) {

        calendarBtn.addEventListener("click", function () {

            alert(
                "Calendar View will be implemented in a future stage."
            );

        });

    }


    /* =========================================
       PAGINATION DEMO
    ========================================= */

    document
        .querySelectorAll(".page-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                if (
                    button.classList.contains("disabled") ||
                    button.classList.contains("active")
                ) {
                    return;
                }

                alert(
                    "Pagination is currently a frontend demo."
                );

            });

        });


    /* =========================================
       INITIAL FILTER
    ========================================= */

    filterSessions();

});