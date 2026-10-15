document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       MOBILE SIDEBAR
    ========================================= */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {
        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }


    /* =========================================
       ELEMENTS
    ========================================= */

    const resourceSearch = document.getElementById("resourceSearch");
    const headerSearch = document.getElementById("headerSearch");

    const categoryFilter = document.getElementById("categoryFilter");
    const typeFilter = document.getElementById("typeFilter");
    const statusFilter = document.getElementById("statusFilter");

    const clearFilters = document.getElementById("clearFilters");

    const resourceTabs =
        document.querySelectorAll(".resource-tab");

    const rows =
        document.querySelectorAll("#resourceTableBody tr");

    const emptyState =
        document.getElementById("emptyState");


    let activeTab = "all";


    /* =========================================
       SEARCH
    ========================================= */

    function filterResources() {

        const search =
            resourceSearch.value.toLowerCase().trim();

        const category =
            categoryFilter.value;

        const type =
            typeFilter.value;

        const status =
            statusFilter.value;

        let visibleCount = 0;


        rows.forEach(function (row) {

            const rowText =
                row.textContent.toLowerCase();

            const rowCategory =
                row.dataset.category;

            const rowType =
                row.dataset.type;

            const rowStatus =
                row.dataset.status;


            const matchesSearch =
                rowText.includes(search);

            const matchesCategory =
                category === "all" ||
                rowCategory === category;

            const matchesType =
                type === "all" ||
                rowType === type;

            const matchesStatus =
                status === "all" ||
                rowStatus === status;

            const matchesTab =
                activeTab === "all" ||
                rowStatus === activeTab;


            if (
                matchesSearch &&
                matchesCategory &&
                matchesType &&
                matchesStatus &&
                matchesTab
            ) {

                row.style.display = "";

                visibleCount++;

            } else {

                row.style.display = "none";

            }

        });


        if (visibleCount === 0) {
            emptyState.classList.add("show");
        } else {
            emptyState.classList.remove("show");
        }

    }


    /* =========================================
       SEARCH INPUT
    ========================================= */

    if (resourceSearch) {

        resourceSearch.addEventListener(
            "input",
            filterResources
        );

    }


    /* =========================================
       HEADER SEARCH
    ========================================= */

    if (headerSearch) {

        headerSearch.addEventListener("input", function () {

            resourceSearch.value = this.value;

            filterResources();

        });

    }


    /* =========================================
       FILTERS
    ========================================= */

    categoryFilter.addEventListener(
        "change",
        filterResources
    );

    typeFilter.addEventListener(
        "change",
        filterResources
    );

    statusFilter.addEventListener(
        "change",
        filterResources
    );


    /* =========================================
       CLEAR FILTERS
    ========================================= */

    clearFilters.addEventListener("click", function () {

        resourceSearch.value = "";
        headerSearch.value = "";

        categoryFilter.value = "all";
        typeFilter.value = "all";
        statusFilter.value = "all";

        activeTab = "all";

        resourceTabs.forEach(function (tab) {
            tab.classList.remove("active");
        });

        resourceTabs[0].classList.add("active");

        filterResources();

    });


    /* =========================================
       TABS
    ========================================= */

    resourceTabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            resourceTabs.forEach(function (item) {
                item.classList.remove("active");
            });

            this.classList.add("active");

            activeTab = this.dataset.tab;

            filterResources();

        });

    });


    /* =========================================
       MODAL
    ========================================= */

    const modal =
        document.getElementById("resourceModal");

    const addResourceBtn =
        document.getElementById("addResourceBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");


    function openModal() {

        modal.classList.add("show");

        document.body.style.overflow = "hidden";

    }


    function closeResourceModal() {

        modal.classList.remove("show");

        document.body.style.overflow = "";

    }


    addResourceBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeResourceModal
    );


    cancelModal.addEventListener(
        "click",
        closeResourceModal
    );


    modal.addEventListener("click", function (event) {

        if (event.target === modal) {
            closeResourceModal();
        }

    });


    /* =========================================
       FILE SELECTION
    ========================================= */

    const resourceFile =
        document.getElementById("resourceFile");

    const fileName =
        document.getElementById("fileName");


    resourceFile.addEventListener(
        "change",
        function () {

            if (this.files.length > 0) {

                fileName.textContent =
                    "Selected file: " +
                    this.files[0].name;

            } else {

                fileName.textContent =
                    "No file selected";

            }

        }
    );


    /* =========================================
       ADD RESOURCE FORM
    ========================================= */

    const resourceForm =
        document.getElementById("resourceForm");


    resourceForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const title =
                document.getElementById(
                    "resourceTitle"
                ).value.trim();

            const category =
                document.getElementById(
                    "resourceCategory"
                ).value;

            const type =
                document.getElementById(
                    "resourceType"
                ).value;

            const description =
                document.getElementById(
                    "resourceDescription"
                ).value.trim();


            if (
                !title ||
                !category ||
                !type ||
                !description
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            alert(
                "Resource added successfully in demo mode.\n\n" +
                "Database integration will be added later."
            );


            resourceForm.reset();

            fileName.textContent =
                "No file selected";

            closeResourceModal();

        }
    );


    /* =========================================
       RESOURCE ACTIONS
    ========================================= */

    document
        .querySelectorAll(".action-btn.view")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const row =
                    this.closest("tr");

                const title =
                    row.querySelector(
                        ".resource-info strong"
                    ).textContent.trim();

                alert(
                    "Viewing resource:\n\n" +
                    title
                );

            });

        });


    document
        .querySelectorAll(".action-btn.edit")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const row =
                    this.closest("tr");

                const title =
                    row.querySelector(
                        ".resource-info strong"
                    ).textContent.trim();

                alert(
                    "Edit resource:\n\n" +
                    title +
                    "\n\nEdit functionality will connect to the database later."
                );

            });

        });


    /* =========================================
       MORE ACTION
    ========================================= */

    document
        .querySelectorAll(".action-btn.more")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const row =
                    this.closest("tr");

                const title =
                    row.querySelector(
                        ".resource-info strong"
                    ).textContent.trim();

                const currentStatus =
                    row.dataset.status;


                let action =
                    prompt(
                        "Resource: " +
                        title +
                        "\n\n" +
                        "Current status: " +
                        currentStatus +
                        "\n\n" +
                        "Choose an action:\n" +
                        "1 - Publish\n" +
                        "2 - Archive\n" +
                        "3 - Delete\n" +
                        "4 - Cancel"
                    );


                if (action === "1") {

                    alert(
                        "Resource marked for publishing in demo mode."
                    );

                } else if (action === "2") {

                    alert(
                        "Resource archived in demo mode."
                    );

                } else if (action === "3") {

                    const confirmed =
                        confirm(
                            "Are you sure you want to delete this resource?"
                        );

                    if (confirmed) {

                        row.remove();

                        alert(
                            "Resource deleted in demo mode."
                        );

                        filterResources();

                    }

                }

            });

        });


    /* =========================================
       PAGINATION
    ========================================= */

    document
        .querySelectorAll(".pagination button")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                if (
                    !this.classList.contains("active")
                ) {

                    alert(
                        "Pagination is currently in demo mode."
                    );

                }

            });

        });


    /* =========================================
       INITIAL FILTER
    ========================================= */

    filterResources();

});