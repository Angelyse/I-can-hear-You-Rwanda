document.addEventListener("DOMContentLoaded", function () {


    /* =========================================
       ELEMENTS
    ========================================== */

    const mobileMenu =
        document.getElementById("mobileMenu");

    const sidebar =
        document.getElementById("sidebar");

    const resourceSearch =
        document.getElementById("resourceSearch");

    const categoryFilter =
        document.getElementById("categoryFilter");

    const typeFilter =
        document.getElementById("typeFilter");

    const resourceCards =
        document.querySelectorAll(".resource-card");

    const resourceTabs =
        document.querySelectorAll(".resource-tab");

    const emptyResources =
        document.getElementById("emptyResources");

    const clearFilters =
        document.getElementById("clearFilters");

    const resourceModal =
        document.getElementById("resourceModal");

    const addResourceBtn =
        document.getElementById("addResourceBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelResource =
        document.getElementById("cancelResource");

    const resourceForm =
        document.getElementById("resourceForm");


    let activeCategory = "all";


    /* =========================================
       MOBILE SIDEBAR
    ========================================== */

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =========================================
       FILTER RESOURCES
    ========================================== */

    function filterResources() {

        const searchValue =
            resourceSearch.value.toLowerCase().trim();

        const categoryValue =
            categoryFilter.value;

        const typeValue =
            typeFilter.value;

        let visibleCount = 0;


        resourceCards.forEach(function (card) {

            const title =
                card.dataset.title.toLowerCase();

            const category =
                card.dataset.category;

            const type =
                card.dataset.type;


            const matchesSearch =
                title.includes(searchValue);


            const matchesCategory =
                categoryValue === "all" ||
                category === categoryValue;


            const matchesType =
                typeValue === "all" ||
                type === typeValue;


            const matchesTab =
                activeCategory === "all" ||
                category === activeCategory;


            if (
                matchesSearch &&
                matchesCategory &&
                matchesType &&
                matchesTab
            ) {

                card.style.display = "block";

                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        if (visibleCount === 0) {

            emptyResources.classList.add("show");

        } else {

            emptyResources.classList.remove("show");

        }

    }


    /* =========================================
       SEARCH
    ========================================== */

    if (resourceSearch) {

        resourceSearch.addEventListener(
            "input",
            filterResources
        );

    }


    /* =========================================
       CATEGORY FILTER
    ========================================== */

    if (categoryFilter) {

        categoryFilter.addEventListener(
            "change",
            function () {

                activeCategory = "all";

                resourceTabs.forEach(function (tab) {

                    tab.classList.remove("active");

                });

                document
                    .querySelector('[data-category="all"]')
                    .classList.add("active");

                filterResources();

            }
        );

    }


    /* =========================================
       TYPE FILTER
    ========================================== */

    if (typeFilter) {

        typeFilter.addEventListener(
            "change",
            filterResources
        );

    }


    /* =========================================
       CATEGORY TABS
    ========================================== */

    resourceTabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            resourceTabs.forEach(function (item) {

                item.classList.remove("active");

            });

            tab.classList.add("active");

            activeCategory =
                tab.dataset.category;


            categoryFilter.value =
                activeCategory === "all"
                    ? "all"
                    : activeCategory;


            filterResources();

        });

    });


    /* =========================================
       CLEAR FILTERS
    ========================================== */

    if (clearFilters) {

        clearFilters.addEventListener(
            "click",
            function () {

                resourceSearch.value = "";

                categoryFilter.value = "all";

                typeFilter.value = "all";

                activeCategory = "all";


                resourceTabs.forEach(function (tab) {

                    tab.classList.remove("active");

                });


                document
                    .querySelector('[data-category="all"]')
                    .classList.add("active");


                filterResources();

            }
        );

    }


    /* =========================================
       OPEN MODAL
    ========================================== */

    function openResourceModal() {

        resourceModal.classList.add("show");

        document.body.style.overflow = "hidden";

    }


    /* =========================================
       CLOSE MODAL
    ========================================== */

    function closeResourceModal() {

        resourceModal.classList.remove("show");

        document.body.style.overflow = "";

    }


    /* =========================================
       ADD RESOURCE
    ========================================== */

    if (addResourceBtn) {

        addResourceBtn.addEventListener(
            "click",
            function () {

                resourceForm.reset();

                openResourceModal();

            }
        );

    }


    /* =========================================
       CLOSE MODAL
    ========================================== */

    if (closeModal) {

        closeModal.addEventListener(
            "click",
            closeResourceModal
        );

    }


    if (cancelResource) {

        cancelResource.addEventListener(
            "click",
            closeResourceModal
        );

    }


    /* =========================================
       CLOSE OUTSIDE MODAL
    ========================================== */

    if (resourceModal) {

        resourceModal.addEventListener(
            "click",
            function (event) {

                if (event.target === resourceModal) {

                    closeResourceModal();

                }

            }
        );

    }


    /* =========================================
       VIEW RESOURCE
    ========================================== */

    document
        .querySelectorAll(".view-resource")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const title =
                        button.dataset.title;

                    alert(
                        "Opening resource:\n\n" +
                        title +
                        "\n\nResource viewing functionality will be connected later."
                    );

                }
            );

        });


    /* =========================================
       RESOURCE MENU
    ========================================== */

    document
        .querySelectorAll(".resource-menu")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    alert(
                        "Resource options will be available here:\n\n" +
                        "• Edit\n" +
                        "• Delete\n" +
                        "• Share\n" +
                        "• Manage Access"
                    );

                }
            );

        });


    /* =========================================
       FILE SELECTION
    ========================================== */

    const resourceFile =
        document.getElementById("resourceFile");


    if (resourceFile) {

        resourceFile.addEventListener(
            "change",
            function () {

                if (resourceFile.files.length > 0) {

                    const fileName =
                        resourceFile.files[0].name;

                    const fileUpload =
                        document.querySelector(".file-upload strong");

                    fileUpload.textContent =
                        fileName;

                }

            }
        );

    }


    /* =========================================
       SAVE RESOURCE
    ========================================== */

    if (resourceForm) {

        resourceForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const title =
                    document
                        .getElementById("resourceTitle")
                        .value
                        .trim();


                const category =
                    document
                        .getElementById("resourceCategory")
                        .value;


                const type =
                    document
                        .getElementById("resourceType")
                        .value;


                const description =
                    document
                        .getElementById("resourceDescription")
                        .value
                        .trim();


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
                    "Resource saved successfully in this frontend demo.\n\n" +
                    "Database storage will be connected later."
                );


                closeResourceModal();

                resourceForm.reset();

            }
        );

    }


    /* =========================================
       PAGINATION
    ========================================== */

    document
        .querySelectorAll(".page-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    if (
                        button.classList.contains("disabled") ||
                        button.classList.contains("active")
                    ) {

                        return;

                    }


                    alert(
                        "Pagination is currently a frontend demo."
                    );

                }
            );

        });


    /* =========================================
       INITIAL FILTER
    ========================================== */

    filterResources();

});