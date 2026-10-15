document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       MOBILE SIDEBAR
       ===================================================== */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {
        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }


    /* =====================================================
       ELEMENTS
       ===================================================== */

    const organizationSearch =
        document.getElementById("organizationSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const typeFilter =
        document.getElementById("typeFilter");

    const statusFilter =
        document.getElementById("statusFilter");

    const programFilter =
        document.getElementById("programFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const tableBody =
        document.getElementById("organizationsTableBody");

    const emptyState =
        document.getElementById("emptyState");

    const tabButtons =
        document.querySelectorAll(".tab-btn");

    const rows =
        document.querySelectorAll("#organizationsTableBody tr");


    let currentTab = "all";


    /* =====================================================
       SEARCH
       ===================================================== */

    function applyFilters() {

        const searchValue =
            organizationSearch.value.toLowerCase().trim();

        const typeValue =
            typeFilter.value;

        const statusValue =
            statusFilter.value;

        const programValue =
            programFilter.value;

        let visibleRows = 0;


        rows.forEach(function (row) {

            const rowText =
                row.textContent.toLowerCase();

            const rowType =
                row.dataset.type;

            const rowStatus =
                row.dataset.status;

            const rowProgram =
                row.dataset.program;


            const matchesSearch =
                searchValue === "" ||
                rowText.includes(searchValue);


            const matchesType =
                typeValue === "all" ||
                rowType === typeValue;


            const matchesStatus =
                statusValue === "all" ||
                rowStatus === statusValue;


            const matchesProgram =
                programValue === "all" ||
                rowProgram === programValue;


            const matchesTab =
                currentTab === "all" ||
                rowStatus === currentTab;


            if (
                matchesSearch &&
                matchesType &&
                matchesStatus &&
                matchesProgram &&
                matchesTab
            ) {

                row.style.display = "";

                visibleRows++;

            } else {

                row.style.display = "none";

            }

        });


        if (visibleRows === 0) {

            emptyState.style.display = "block";

            tableBody.style.display = "none";

        } else {

            emptyState.style.display = "none";

            tableBody.style.display = "table-row-group";

        }

    }


    /* =====================================================
       SEARCH EVENTS
       ===================================================== */

    organizationSearch.addEventListener(
        "input",
        applyFilters
    );


    headerSearch.addEventListener(
        "input",
        function () {

            organizationSearch.value =
                headerSearch.value;

            applyFilters();

        }
    );


    /* =====================================================
       FILTER EVENTS
       ===================================================== */

    typeFilter.addEventListener(
        "change",
        applyFilters
    );

    statusFilter.addEventListener(
        "change",
        applyFilters
    );

    programFilter.addEventListener(
        "change",
        applyFilters
    );


    /* =====================================================
       CLEAR FILTERS
       ===================================================== */

    clearFilters.addEventListener(
        "click",
        function () {

            organizationSearch.value = "";

            headerSearch.value = "";

            typeFilter.value = "all";

            statusFilter.value = "all";

            programFilter.value = "all";

            currentTab = "all";


            tabButtons.forEach(function (button) {
                button.classList.remove("active");
            });

            document
                .querySelector('[data-tab="all"]')
                .classList.add("active");


            applyFilters();

        }
    );


    /* =====================================================
       TABS
       ===================================================== */

    tabButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                tabButtons.forEach(function (btn) {
                    btn.classList.remove("active");
                });

                button.classList.add("active");

                currentTab =
                    button.dataset.tab;

                applyFilters();

            }
        );

    });


    /* =====================================================
       MODAL
       ===================================================== */

    const modal =
        document.getElementById("organizationModal");

    const addOrganizationBtn =
        document.getElementById("addOrganizationBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");

    const organizationForm =
        document.getElementById("organizationForm");


    function openModal() {
        modal.classList.add("show");
    }


    function closeOrganizationModal() {
        modal.classList.remove("show");
    }


    addOrganizationBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeOrganizationModal
    );


    cancelModal.addEventListener(
        "click",
        closeOrganizationModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {
                closeOrganizationModal();
            }

        }
    );


    /* =====================================================
       ADD ORGANIZATION
       ===================================================== */

    organizationForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const name =
                document.getElementById("organizationName").value.trim();

            const type =
                document.getElementById("organizationType").value;

            const location =
                document.getElementById("location").value.trim();

            const contactPerson =
                document.getElementById("contactPerson").value.trim();

            const phone =
                document.getElementById("phone").value.trim();

            const email =
                document.getElementById("email").value.trim();


            if (
                name === "" ||
                type === "" ||
                location === "" ||
                contactPerson === "" ||
                phone === "" ||
                email === ""
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            alert(
                "Organization added successfully in demo mode.\n\n" +
                "Organization: " + name
            );


            organizationForm.reset();

            closeOrganizationModal();

        }
    );


    /* =====================================================
       VIEW ORGANIZATION
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".view-btn");

            if (!button) {
                return;
            }


            const name =
                button.dataset.name;


            alert(
                "Organization Profile\n\n" +
                name +
                "\n\n" +
                "This will open the organization profile page " +
                "after the backend is connected."
            );

        }
    );


    /* =====================================================
       EDIT ORGANIZATION
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".edit-btn");

            if (!button) {
                return;
            }


            const name =
                button.dataset.name;


            alert(
                "Edit Organization\n\n" +
                "You selected: " +
                name +
                "\n\n" +
                "The edit form will be connected to the database later."
            );

        }
    );


    /* =====================================================
       MORE ACTIONS
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".more-btn");

            if (!button) {
                return;
            }


            const name =
                button.dataset.name;


            const choice =
                prompt(
                    "Organization: " +
                    name +
                    "\n\n" +
                    "Choose an action:\n" +
                    "1 - View Details\n" +
                    "2 - Manage Programs\n" +
                    "3 - Activate\n" +
                    "4 - Deactivate\n" +
                    "5 - Delete"
                );


            if (choice === "1") {

                alert(
                    "Opening details for " +
                    name
                );

            } else if (choice === "2") {

                alert(
                    "Manage Programs for " +
                    name
                );

            } else if (choice === "3") {

                if (
                    confirm(
                        "Activate " +
                        name +
                        "?"
                    )
                ) {

                    alert(
                        name +
                        " has been activated in demo mode."
                    );

                }

            } else if (choice === "4") {

                if (
                    confirm(
                        "Deactivate " +
                        name +
                        "?"
                    )
                ) {

                    alert(
                        name +
                        " has been deactivated in demo mode."
                    );

                }

            } else if (choice === "5") {

                if (
                    confirm(
                        "Are you sure you want to delete " +
                        name +
                        "?"
                    )
                ) {

                    alert(
                        name +
                        " has been deleted in demo mode."
                    );

                }

            }

        }
    );


    /* =====================================================
       EXPORT
       ===================================================== */

    const exportBtn =
        document.getElementById("exportBtn");


    exportBtn.addEventListener(
        "click",
        function () {

            alert(
                "Export feature is ready for backend integration.\n\n" +
                "Later this button can generate CSV or Excel reports."
            );

        }
    );


    /* =====================================================
       PAGINATION DEMO
       ===================================================== */

    const paginationButtons =
        document.querySelectorAll(
            ".pagination-buttons button"
        );


    paginationButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                if (
                    !button.disabled &&
                    !button.classList.contains("active")
                ) {

                    alert(
                        "Pagination will load organization records " +
                        "from the database after backend integration."
                    );

                }

            }
        );

    });


    /* =====================================================
       INITIAL FILTER
       ===================================================== */

    applyFilters();

});