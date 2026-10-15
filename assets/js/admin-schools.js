document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       MOBILE SIDEBAR
    ========================================= */

    const mobileMenu =
        document.getElementById("mobileMenu");

    const sidebar =
        document.getElementById("sidebar");


    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener(
            "click",
            function () {

                sidebar.classList.toggle("show");

            }
        );

    }


    /* =========================================
       ELEMENTS
    ========================================= */

    const schoolSearch =
        document.getElementById("schoolSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const typeFilter =
        document.getElementById("schoolTypeFilter");

    const statusFilter =
        document.getElementById("schoolStatusFilter");

    const programFilter =
        document.getElementById("schoolProgramFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const tabs =
        document.querySelectorAll(".school-tab");

    const rows =
        document.querySelectorAll(
            "#schoolTableBody tr"
        );

    const emptyState =
        document.getElementById("emptyState");

    let activeTab = "all";


    /* =========================================
       FILTER SCHOOLS
    ========================================= */

    function filterSchools() {

        const search =
            schoolSearch.value
                .toLowerCase()
                .trim();

        const type =
            typeFilter.value;

        const status =
            statusFilter.value;

        const program =
            programFilter.value;


        let visibleCount = 0;


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
                rowText.includes(search);

            const matchesType =
                type === "all" ||
                rowType === type;

            const matchesStatus =
                status === "all" ||
                rowStatus === status;

            const matchesProgram =
                program === "all" ||
                rowProgram === program;

            const matchesTab =
                activeTab === "all" ||
                rowStatus === activeTab;


            if (
                matchesSearch &&
                matchesType &&
                matchesStatus &&
                matchesProgram &&
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
       SEARCH
    ========================================= */

    schoolSearch.addEventListener(
        "input",
        filterSchools
    );


    headerSearch.addEventListener(
        "input",
        function () {

            schoolSearch.value =
                this.value;

            filterSchools();

        }
    );


    /* =========================================
       FILTERS
    ========================================= */

    typeFilter.addEventListener(
        "change",
        filterSchools
    );

    statusFilter.addEventListener(
        "change",
        filterSchools
    );

    programFilter.addEventListener(
        "change",
        filterSchools
    );


    /* =========================================
       CLEAR FILTERS
    ========================================= */

    clearFilters.addEventListener(
        "click",
        function () {

            schoolSearch.value = "";
            headerSearch.value = "";

            typeFilter.value = "all";
            statusFilter.value = "all";
            programFilter.value = "all";

            activeTab = "all";


            tabs.forEach(function (tab) {

                tab.classList.remove("active");

            });


            tabs[0].classList.add("active");

            filterSchools();

        }
    );


    /* =========================================
       TABS
    ========================================= */

    tabs.forEach(function (tab) {

        tab.addEventListener(
            "click",
            function () {

                tabs.forEach(function (item) {

                    item.classList.remove("active");

                });


                this.classList.add("active");

                activeTab =
                    this.dataset.tab;

                filterSchools();

            }
        );

    });


    /* =========================================
       MODAL
    ========================================= */

    const modal =
        document.getElementById("schoolModal");

    const addSchoolBtn =
        document.getElementById("addSchoolBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");


    function openModal() {

        modal.classList.add("show");

        document.body.style.overflow =
            "hidden";

    }


    function closeSchoolModal() {

        modal.classList.remove("show");

        document.body.style.overflow =
            "";

    }


    addSchoolBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeSchoolModal
    );


    cancelModal.addEventListener(
        "click",
        closeSchoolModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {

                closeSchoolModal();

            }

        }
    );


    /* =========================================
       ADD SCHOOL FORM
    ========================================= */

    const schoolForm =
        document.getElementById("schoolForm");


    schoolForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const name =
                document.getElementById(
                    "schoolName"
                ).value.trim();

            const type =
                document.getElementById(
                    "schoolType"
                ).value;

            const location =
                document.getElementById(
                    "schoolLocation"
                ).value.trim();

            const phone =
                document.getElementById(
                    "schoolPhone"
                ).value.trim();

            const students =
                document.getElementById(
                    "schoolStudents"
                ).value;


            if (
                !name ||
                !type ||
                !location ||
                !phone ||
                !students
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            alert(
                "School added successfully in demo mode.\n\n" +
                "Database integration will be added later."
            );


            schoolForm.reset();

            closeSchoolModal();

        }
    );


    /* =========================================
       VIEW ACTION
    ========================================= */

    document
        .querySelectorAll(".action-btn.view")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        this.closest("tr");

                    const name =
                        row.querySelector(
                            ".school-info strong"
                        ).textContent.trim();

                    alert(
                        "Viewing school:\n\n" +
                        name
                    );

                }
            );

        });


    /* =========================================
       EDIT ACTION
    ========================================= */

    document
        .querySelectorAll(".action-btn.edit")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        this.closest("tr");

                    const name =
                        row.querySelector(
                            ".school-info strong"
                        ).textContent.trim();

                    alert(
                        "Edit school:\n\n" +
                        name +
                        "\n\n" +
                        "Editing will connect to the database later."
                    );

                }
            );

        });


    /* =========================================
       MORE ACTION
    ========================================= */

    document
        .querySelectorAll(".action-btn.more")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        this.closest("tr");

                    const name =
                        row.querySelector(
                            ".school-info strong"
                        ).textContent.trim();

                    const currentStatus =
                        row.dataset.status;


                    const action =
                        prompt(
                            "School:\n" +
                            name +
                            "\n\n" +
                            "Current status: " +
                            currentStatus +
                            "\n\n" +
                            "Choose an action:\n" +
                            "1 - View Details\n" +
                            "2 - Manage Programs\n" +
                            "3 - Activate\n" +
                            "4 - Deactivate\n" +
                            "5 - Delete\n" +
                            "6 - Cancel"
                        );


                    if (action === "1") {

                        alert(
                            "School details:\n\n" +
                            name
                        );

                    }


                    else if (action === "2") {

                        alert(
                            "Managing programs for:\n\n" +
                            name
                        );

                    }


                    else if (action === "3") {

                        alert(
                            "School activated in demo mode."
                        );

                    }


                    else if (action === "4") {

                        alert(
                            "School deactivated in demo mode."
                        );

                    }


                    else if (action === "5") {

                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this school?"
                            );


                        if (confirmed) {

                            row.remove();

                            alert(
                                "School deleted in demo mode."
                            );

                            filterSchools();

                        }

                    }

                }
            );

        });


    /* =========================================
       PAGINATION
    ========================================= */

    document
        .querySelectorAll(".pagination button")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    if (
                        !this.classList.contains("active")
                    ) {

                        alert(
                            "Pagination is currently in demo mode."
                        );

                    }

                }
            );

        });


    /* =========================================
       INITIAL FILTER
    ========================================= */

    filterSchools();

});