document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("clientSearch");
    const statusFilter = document.getElementById("statusFilter");
    const concernFilter = document.getElementById("concernFilter");

    const tabs = document.querySelectorAll(".client-tab");
    const clients = document.querySelectorAll(".client-card");

    let currentTab = "all";


    // =========================================
    // FILTER CLIENTS
    // =========================================

    function filterClients() {

        const searchValue = searchInput.value.toLowerCase().trim();
        const statusValue = statusFilter.value;
        const concernValue = concernFilter.value;

        clients.forEach(function (client) {

            const name = client.dataset.name.toLowerCase();
            const status = client.dataset.status;
            const concern = client.dataset.concern;

            const matchesSearch =
                name.includes(searchValue);

            const matchesStatus =
                statusValue === "all" ||
                status === statusValue;

            const matchesConcern =
                concernValue === "all" ||
                concern === concernValue;

            const matchesTab =
                currentTab === "all" ||
                status === currentTab;

            if (
                matchesSearch &&
                matchesStatus &&
                matchesConcern &&
                matchesTab
            ) {
                client.classList.remove("hidden");
            } else {
                client.classList.add("hidden");
            }

        });

    }


    // =========================================
    // SEARCH
    // =========================================

    if (searchInput) {

        searchInput.addEventListener("input", function () {
            filterClients();
        });

    }


    // =========================================
    // STATUS FILTER
    // =========================================

    if (statusFilter) {

        statusFilter.addEventListener("change", function () {

            currentTab = "all";

            tabs.forEach(function (tab) {
                tab.classList.remove("active");
            });

            tabs[0].classList.add("active");

            filterClients();

        });

    }


    // =========================================
    // CONCERN FILTER
    // =========================================

    if (concernFilter) {

        concernFilter.addEventListener("change", function () {
            filterClients();
        });

    }


    // =========================================
    // TABS
    // =========================================

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {
                item.classList.remove("active");
            });

            tab.classList.add("active");

            currentTab = tab.dataset.filter;

            statusFilter.value = "all";

            filterClients();

        });

    });


    // =========================================
    // ADD CLIENT BUTTON
    // =========================================

    const addClientBtn = document.getElementById("addClientBtn");

    if (addClientBtn) {

        addClientBtn.addEventListener("click", function () {

            alert(
                "Client registration will be connected to the database later."
            );

        });

    }


    // =========================================
    // PAGINATION DEMO
    // =========================================

    const pageButtons = document.querySelectorAll(".page-btn");

    pageButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            pageButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            button.classList.add("active");

            alert(
                "Pagination will load clients from the database later."
            );

        });

    });


    const nextButton = document.querySelector(".page-next");

    if (nextButton) {

        nextButton.addEventListener("click", function () {

            alert(
                "Next page will be connected to the database later."
            );

        });

    }


    // Initial filtering
    filterClients();

});