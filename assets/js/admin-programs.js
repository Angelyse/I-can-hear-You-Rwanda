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

    const programSearch =
        document.getElementById("programSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const typeFilter =
        document.getElementById("programTypeFilter");

    const statusFilter =
        document.getElementById("programStatusFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const tabs =
        document.querySelectorAll(".program-tab");

    const cards =
        document.querySelectorAll(".program-card");

    const emptyState =
        document.getElementById("emptyState");

    let activeTab = "all";


    /* =========================================
       FILTER PROGRAMS
    ========================================= */

    function filterPrograms() {

        const search =
            programSearch.value.toLowerCase().trim();

        const type =
            typeFilter.value;

        const status =
            statusFilter.value;

        let visibleCount = 0;


        cards.forEach(function (card) {

            const text =
                card.textContent.toLowerCase();

            const cardType =
                card.dataset.type;

            const cardStatus =
                card.dataset.status;


            const matchesSearch =
                text.includes(search);

            const matchesType =
                type === "all" ||
                cardType === type;

            const matchesStatus =
                status === "all" ||
                cardStatus === status;

            const matchesTab =
                activeTab === "all" ||
                cardStatus === activeTab;


            if (
                matchesSearch &&
                matchesType &&
                matchesStatus &&
                matchesTab
            ) {

                card.style.display = "";

                visibleCount++;

            } else {

                card.style.display = "none";

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

    programSearch.addEventListener(
        "input",
        filterPrograms
    );


    headerSearch.addEventListener(
        "input",
        function () {

            programSearch.value =
                this.value;

            filterPrograms();

        }
    );


    /* =========================================
       FILTERS
    ========================================= */

    typeFilter.addEventListener(
        "change",
        filterPrograms
    );

    statusFilter.addEventListener(
        "change",
        filterPrograms
    );


    /* =========================================
       CLEAR
    ========================================= */

    clearFilters.addEventListener(
        "click",
        function () {

            programSearch.value = "";
            headerSearch.value = "";

            typeFilter.value = "all";
            statusFilter.value = "all";

            activeTab = "all";


            tabs.forEach(function (tab) {

                tab.classList.remove("active");

            });


            tabs[0].classList.add("active");

            filterPrograms();

        }
    );


    /* =========================================
       TABS
    ========================================= */

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {

                item.classList.remove("active");

            });


            this.classList.add("active");

            activeTab =
                this.dataset.tab;

            filterPrograms();

        });

    });


    /* =========================================
       MODAL
    ========================================= */

    const modal =
        document.getElementById("programModal");

    const addProgramBtn =
        document.getElementById("addProgramBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");


    function openModal() {

        modal.classList.add("show");

        document.body.style.overflow =
            "hidden";

    }


    function closeProgramModal() {

        modal.classList.remove("show");

        document.body.style.overflow =
            "";

    }


    addProgramBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeProgramModal
    );


    cancelModal.addEventListener(
        "click",
        closeProgramModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {

                closeProgramModal();

            }

        }
    );


    /* =========================================
       DATE VALIDATION
    ========================================= */

    const startDate =
        document.getElementById("startDate");

    const endDate =
        document.getElementById("endDate");


    const today =
        new Date().toISOString().split("T")[0];

    startDate.min = today;
    endDate.min = today;


    startDate.addEventListener(
        "change",
        function () {

            endDate.min = this.value;

        }
    );


    /* =========================================
       ADD PROGRAM
    ========================================= */

    const programForm =
        document.getElementById("programForm");


    programForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const name =
                document.getElementById(
                    "programName"
                ).value.trim();

            const type =
                document.getElementById(
                    "programType"
                ).value;

            const start =
                startDate.value;

            const end =
                endDate.value;

            const description =
                document.getElementById(
                    "programDescription"
                ).value.trim();


            if (
                !name ||
                !type ||
                !start ||
                !end ||
                !description
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            if (end < start) {

                alert(
                    "End date cannot be earlier than the start date."
                );

                return;

            }


            alert(
                "Program created successfully in demo mode.\n\n" +
                "Database integration will be added later."
            );


            programForm.reset();

            endDate.min = today;

            closeProgramModal();

        }
    );


    /* =========================================
       PROGRAM MENU
    ========================================= */

    document
        .querySelectorAll(".program-menu")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const card =
                        this.closest(".program-card");

                    const programName =
                        card.querySelector("h3")
                            .textContent
                            .trim();


                    const action =
                        prompt(
                            "Program:\n" +
                            programName +
                            "\n\n" +
                            "Choose an action:\n" +
                            "1 - View Program\n" +
                            "2 - Edit Program\n" +
                            "3 - Manage Participants\n" +
                            "4 - Delete Program\n" +
                            "5 - Cancel"
                        );


                    if (action === "1") {

                        alert(
                            "Viewing program:\n\n" +
                            programName
                        );

                    }


                    else if (action === "2") {

                        alert(
                            "Edit program:\n\n" +
                            programName +
                            "\n\n" +
                            "Editing will connect to the database later."
                        );

                    }


                    else if (action === "3") {

                        alert(
                            "Participant management for:\n\n" +
                            programName +
                            "\n\n" +
                            "This feature will be connected later."
                        );

                    }


                    else if (action === "4") {

                        const confirmed =
                            confirm(
                                "Are you sure you want to delete this program?"
                            );


                        if (confirmed) {

                            card.remove();

                            alert(
                                "Program deleted in demo mode."
                            );

                            filterPrograms();

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

    filterPrograms();

});