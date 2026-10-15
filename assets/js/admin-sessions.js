document.addEventListener("DOMContentLoaded", function () {

    /* =====================================
       MOBILE SIDEBAR
    ===================================== */

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


    /* =====================================
       ELEMENTS
    ===================================== */

    const searchInput =
        document.getElementById("sessionSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const statusFilter =
        document.getElementById("statusFilter");

    const sessionTypeFilter =
        document.getElementById("sessionTypeFilter");

    const meetingTypeFilter =
        document.getElementById("meetingTypeFilter");

    const dateFilter =
        document.getElementById("dateFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const tableBody =
        document.getElementById("sessionsTableBody");

    const rows =
        Array.from(tableBody.querySelectorAll("tr"));

    const emptyState =
        document.getElementById("emptyState");

    const tabs =
        document.querySelectorAll(".tab-btn");


    /* =====================================
       FILTER SESSIONS
    ===================================== */

    function filterSessions() {

        const search =
            searchInput.value.toLowerCase().trim();

        const status =
            statusFilter.value;

        const type =
            sessionTypeFilter.value;

        const meeting =
            meetingTypeFilter.value;

        const date =
            dateFilter.value;


        let visible = 0;


        rows.forEach(function (row) {

            const id =
                row.dataset.id.toLowerCase();

            const client =
                row.dataset.client.toLowerCase();

            const counsellor =
                row.dataset.counsellor.toLowerCase();

            const rowStatus =
                row.dataset.status;

            const rowType =
                row.dataset.type;

            const rowMeeting =
                row.dataset.meeting;

            const rowDate =
                row.dataset.date;


            const matchesSearch =
                id.includes(search) ||
                client.includes(search) ||
                counsellor.includes(search);


            const matchesStatus =
                status === "all" ||
                rowStatus === status;


            const matchesType =
                type === "all" ||
                rowType === type;


            const matchesMeeting =
                meeting === "all" ||
                rowMeeting === meeting;


            const matchesDate =
                !date ||
                rowDate === date;


            if (
                matchesSearch &&
                matchesStatus &&
                matchesType &&
                matchesMeeting &&
                matchesDate
            ) {

                row.style.display = "";

                visible++;

            } else {

                row.style.display = "none";

            }

        });


        emptyState.style.display =
            visible === 0
                ? "block"
                : "none";

    }


    /* =====================================
       SEARCH
    ===================================== */

    searchInput.addEventListener(
        "input",
        filterSessions
    );


    /* =====================================
       HEADER SEARCH
    ===================================== */

    headerSearch.addEventListener(
        "input",
        function () {

            searchInput.value =
                headerSearch.value;

            filterSessions();

        }
    );


    /* =====================================
       FILTER EVENTS
    ===================================== */

    statusFilter.addEventListener(
        "change",
        filterSessions
    );

    sessionTypeFilter.addEventListener(
        "change",
        filterSessions
    );

    meetingTypeFilter.addEventListener(
        "change",
        filterSessions
    );

    dateFilter.addEventListener(
        "change",
        filterSessions
    );


    /* =====================================
       CLEAR FILTERS
    ===================================== */

    clearFilters.addEventListener(
        "click",
        function () {

            searchInput.value = "";

            headerSearch.value = "";

            statusFilter.value = "all";

            sessionTypeFilter.value = "all";

            meetingTypeFilter.value = "all";

            dateFilter.value = "";


            rows.forEach(function (row) {

                row.style.display = "";

            });


            emptyState.style.display = "none";

        }
    );


    /* =====================================
       TABS
    ===================================== */

    tabs.forEach(function (tab) {

        tab.addEventListener(
            "click",
            function () {

                tabs.forEach(function (item) {

                    item.classList.remove("active");

                });


                tab.classList.add("active");


                const selected =
                    tab.dataset.tab;


                rows.forEach(function (row) {

                    if (selected === "all") {

                        row.style.display = "";

                        return;

                    }


                    row.style.display =
                        row.dataset.category === selected
                            ? ""
                            : "none";

                });


                const visibleRows =
                    rows.filter(function (row) {

                        return row.style.display !== "none";

                    });


                emptyState.style.display =
                    visibleRows.length === 0
                        ? "block"
                        : "none";

            }
        );

    });


    /* =====================================
       VIEW SESSION
    ===================================== */

    document.querySelectorAll(".view-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");


                    alert(
                        "Session Details\n\n" +

                        "Session ID: " +
                        row.dataset.id +

                        "\n\nClient: " +
                        row.dataset.client +

                        "\nCounsellor: " +
                        row.dataset.counsellor +

                        "\nDate: " +
                        row.dataset.date +

                        "\nSession Type: " +
                        row.dataset.type +

                        "\nMeeting: " +
                        row.dataset.meeting +

                        "\nStatus: " +
                        row.dataset.status
                    );

                }
            );

        });


    /* =====================================
       JOIN SESSION
    ===================================== */

    document.querySelectorAll(".join-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");


                    if (
                        row.dataset.type === "Video"
                    ) {

                        alert(
                            "Opening video meeting for " +
                            row.dataset.client +
                            ".\n\n" +
                            "The real meeting link will be connected later."
                        );

                    } else {

                        alert(
                            "Opening chat room for " +
                            row.dataset.client +
                            "."
                        );

                    }

                }
            );

        });


    /* =====================================
       SESSION NOTES
    ===================================== */

    document.querySelectorAll(".notes-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");


                    alert(
                        "Session Notes\n\n" +

                        "Session: " +
                        row.dataset.id +

                        "\nClient: " +
                        row.dataset.client +

                        "\nCounsellor: " +
                        row.dataset.counsellor +

                        "\n\n" +

                        "Session notes will be connected to the session_notes table later."
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


                    const action =
                        prompt(
                            "Session " +
                            row.dataset.id +

                            "\n\n" +

                            "Choose an action:\n" +

                            "1 = View Details\n" +

                            "2 = Mark Completed\n" +

                            "3 = Cancel Session\n" +

                            "4 = Reschedule\n" +

                            "5 = Delete Session"
                        );


                    if (action === "1") {

                        alert(
                            "Opening session details..."
                        );

                    }


                    else if (action === "2") {

                        const confirmComplete =
                            confirm(
                                "Mark this session as completed?"
                            );


                        if (confirmComplete) {

                            row.dataset.status =
                                "Completed";

                            row.dataset.category =
                                "completed";


                            const status =
                                row.querySelector(".status");


                            status.textContent =
                                "Completed";

                            status.className =
                                "status completed";


                            alert(
                                "Session marked as completed."
                            );

                        }

                    }


                    else if (action === "3") {

                        const confirmCancel =
                            confirm(
                                "Cancel this session?"
                            );


                        if (confirmCancel) {

                            row.dataset.status =
                                "Cancelled";

                            row.dataset.category =
                                "cancelled";


                            const status =
                                row.querySelector(".status");


                            status.textContent =
                                "Cancelled";

                            status.className =
                                "status cancelled";


                            alert(
                                "Session cancelled."
                            );

                        }

                    }


                    else if (action === "4") {

                        alert(
                            "Reschedule feature will be connected to the booking system later."
                        );

                    }


                    else if (action === "5") {

                        const confirmDelete =
                            confirm(
                                "Delete session " +
                                row.dataset.id +
                                " permanently?"
                            );


                        if (confirmDelete) {

                            row.remove();

                            alert(
                                "Session deleted from the frontend demo."
                            );

                        }

                    }

                }
            );

        });


    /* =====================================
       ADD SESSION MODAL
    ===================================== */

    const addSessionBtn =
        document.getElementById("addSessionBtn");

    const sessionModal =
        document.getElementById("sessionModal");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");

    const sessionForm =
        document.getElementById("sessionForm");


    function openModal() {

        sessionModal.classList.add("show");

    }


    function closeSessionModal() {

        sessionModal.classList.remove("show");

        sessionForm.reset();

    }


    addSessionBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeSessionModal
    );


    cancelModal.addEventListener(
        "click",
        closeSessionModal
    );


    /* =====================================
       CLOSE MODAL OUTSIDE
    ===================================== */

    sessionModal.addEventListener(
        "click",
        function (event) {

            if (event.target === sessionModal) {

                closeSessionModal();

            }

        }
    );


    /* =====================================
       MINIMUM DATE
    ===================================== */

    const newDate =
        document.getElementById("newDate");


    newDate.min =
        new Date()
            .toISOString()
            .split("T")[0];


    /* =====================================
       CREATE SESSION
    ===================================== */

    sessionForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const client =
                document.getElementById("newClient").value;

            const counsellor =
                document.getElementById("newCounsellor").value;

            const date =
                document.getElementById("newDate").value;

            const startTime =
                document.getElementById("newStartTime").value;

            const endTime =
                document.getElementById("newEndTime").value;


            if (
                !client ||
                !counsellor ||
                !date ||
                !startTime ||
                !endTime
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            if (endTime <= startTime) {

                alert(
                    "End time must be later than start time."
                );

                return;

            }


            alert(
                "Session created successfully!\n\n" +

                "Client: " +
                client +

                "\nCounsellor: " +
                counsellor +

                "\nDate: " +
                date +

                "\nTime: " +
                startTime +
                " - " +
                endTime +

                "\n\n" +

                "This is currently a frontend demo. " +
                "The session will be saved to MySQL when the backend is connected."
            );


            closeSessionModal();

        }
    );


    /* =====================================
       EXPORT
    ===================================== */

    const exportBtn =
        document.getElementById("exportBtn");


    exportBtn.addEventListener(
        "click",
        function () {

            alert(
                "Export feature\n\n" +
                "The backend will later generate CSV or Excel reports for sessions."
            );

        }
    );


    /* =====================================
       PAGINATION
    ===================================== */

    document.querySelectorAll(
        ".pagination-buttons button"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                if (
                    !button.disabled &&
                    !button.classList.contains("current")
                ) {

                    alert(
                        "Pagination is currently a frontend demo."
                    );

                }

            }
        );

    });

});