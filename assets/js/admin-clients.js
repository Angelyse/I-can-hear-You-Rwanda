/* =========================================
   ADMIN CLIENTS JAVASCRIPT
========================================= */


document.addEventListener("DOMContentLoaded", function () {

    /* =====================================
       MOBILE SIDEBAR
    ===================================== */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });

    }


    /* =====================================
       ELEMENTS
    ===================================== */

    const clientSearch = document.getElementById("clientSearch");
    const clientTypeFilter = document.getElementById("clientTypeFilter");
    const statusFilter = document.getElementById("statusFilter");
    const consentFilter = document.getElementById("consentFilter");
    const languageFilter = document.getElementById("languageFilter");
    const clearFilters = document.getElementById("clearFilters");

    const tableBody = document.getElementById("clientsTableBody");
    const rows = Array.from(tableBody.querySelectorAll("tr"));

    const emptyState = document.getElementById("emptyState");

    const tabs = document.querySelectorAll(".tab-btn");



    /* =====================================
       FILTER CLIENTS
    ===================================== */

    function filterClients() {

        const searchValue =
            clientSearch.value.toLowerCase().trim();

        const typeValue =
            clientTypeFilter.value;

        const statusValue =
            statusFilter.value;

        const consentValue =
            consentFilter.value;

        const languageValue =
            languageFilter.value;


        let visibleCount = 0;


        rows.forEach(function (row) {

            const name =
                row.dataset.name.toLowerCase();

            const email =
                row.dataset.email.toLowerCase();

            const type =
                row.dataset.type;

            const status =
                row.dataset.status;

            const consent =
                row.dataset.consent;

            const language =
                row.dataset.language;


            const matchesSearch =
                name.includes(searchValue) ||
                email.includes(searchValue);

            const matchesType =
                typeValue === "all" ||
                type === typeValue;

            const matchesStatus =
                statusValue === "all" ||
                status === statusValue;

            const matchesConsent =
                consentValue === "all" ||
                consent === consentValue;

            const matchesLanguage =
                languageValue === "all" ||
                language === languageValue;


            if (
                matchesSearch &&
                matchesType &&
                matchesStatus &&
                matchesConsent &&
                matchesLanguage
            ) {

                row.style.display = "";

                visibleCount++;

            } else {

                row.style.display = "none";

            }

        });


        if (visibleCount === 0) {

            emptyState.style.display = "block";

        } else {

            emptyState.style.display = "none";

        }

    }


    /* =====================================
       SEARCH
    ===================================== */

    clientSearch.addEventListener(
        "input",
        filterClients
    );


    /* =====================================
       FILTERS
    ===================================== */

    clientTypeFilter.addEventListener(
        "change",
        filterClients
    );

    statusFilter.addEventListener(
        "change",
        filterClients
    );

    consentFilter.addEventListener(
        "change",
        filterClients
    );

    languageFilter.addEventListener(
        "change",
        filterClients
    );


    /* =====================================
       CLEAR FILTERS
    ===================================== */

    clearFilters.addEventListener("click", function () {

        clientSearch.value = "";

        clientTypeFilter.value = "all";

        statusFilter.value = "all";

        consentFilter.value = "all";

        languageFilter.value = "all";


        rows.forEach(function (row) {
            row.style.display = "";
        });


        emptyState.style.display = "none";

    });


    /* =====================================
       TABS
    ===================================== */

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {
                item.classList.remove("active");
            });

            tab.classList.add("active");


            const selectedTab =
                tab.dataset.tab;


            rows.forEach(function (row) {

                const category =
                    row.dataset.category;


                if (selectedTab === "all") {

                    row.style.display = "";

                } else if (selectedTab === category) {

                    row.style.display = "";

                } else {

                    row.style.display = "none";

                }

            });


            let visibleRows =
                rows.filter(function (row) {
                    return row.style.display !== "none";
                });


            if (visibleRows.length === 0) {
                emptyState.style.display = "block";
            } else {
                emptyState.style.display = "none";
            }

        });

    });


    /* =====================================
       ADD CLIENT MODAL
    ===================================== */

    const addClientBtn =
        document.getElementById("addClientBtn");

    const clientModal =
        document.getElementById("clientModal");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");

    const clientForm =
        document.getElementById("clientForm");


    function openModal() {

        clientModal.classList.add("show");

    }


    function closeClientModal() {

        clientModal.classList.remove("show");

        clientForm.reset();

    }


    addClientBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeClientModal
    );


    cancelModal.addEventListener(
        "click",
        closeClientModal
    );


    /* =====================================
       CLOSE MODAL WHEN CLICKING OUTSIDE
    ===================================== */

    clientModal.addEventListener(
        "click",
        function (event) {

            if (event.target === clientModal) {

                closeClientModal();

            }

        }
    );


    /* =====================================
       CREATE CLIENT
    ===================================== */

    clientForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const name =
                document.getElementById("fullName").value.trim();

            const email =
                document.getElementById("email").value.trim();


            if (!name || !email) {

                alert(
                    "Please enter the client's name and email."
                );

                return;

            }


            alert(
                "Client created successfully!\n\n" +
                "This is a frontend demo. " +
                "The client will be saved to MySQL when the backend is connected."
            );


            closeClientModal();

        }
    );


    /* =====================================
       VIEW PROFILE
    ===================================== */

    document.querySelectorAll(".view-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    const name =
                        row.dataset.name;

                    alert(
                        "Client Profile\n\n" +
                        name +
                        "\n\nProfile page will be connected in a future stage."
                    );

                }
            );

        });


    /* =====================================
       EDIT CLIENT
    ===================================== */

    document.querySelectorAll(".edit-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    const name =
                        row.dataset.name;

                    alert(
                        "Edit Client\n\n" +
                        name +
                        "\n\nEditing will be connected to the database later."
                    );

                }
            );

        });


    /* =====================================
       MORE ACTIONS
    ===================================== */

    document.querySelectorAll(".more-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    const name =
                        row.dataset.name;

                    const status =
                        row.dataset.status;


                    const action =
                        confirm(
                            "Client: " +
                            name +
                            "\n\n" +
                            "Current status: " +
                            status +
                            "\n\n" +
                            "Press OK to open account actions."
                        );


                    if (action) {

                        alert(
                            "Account actions:\n\n" +
                            "• View Profile\n" +
                            "• Edit Client\n" +
                            "• View Consent\n" +
                            "• View Wellness Information\n" +
                            "• Activate / Deactivate Account\n" +
                            "• View Activity"
                        );

                    }

                }
            );

        });


    /* =====================================
       EXPORT BUTTON
    ===================================== */

    const exportBtn =
        document.getElementById("exportBtn");


    exportBtn.addEventListener(
        "click",
        function () {

            alert(
                "Export feature\n\n" +
                "Client data can later be exported as CSV or Excel from the backend."
            );

        }
    );


    /* =====================================
       HEADER SEARCH
    ===================================== */

    const headerSearch =
        document.getElementById("headerSearch");


    headerSearch.addEventListener(
        "input",
        function () {

            clientSearch.value =
                headerSearch.value;

            filterClients();

        }
    );


});