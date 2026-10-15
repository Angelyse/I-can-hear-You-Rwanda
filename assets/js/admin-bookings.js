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

    const searchInput =
        document.getElementById("bookingSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const statusFilter =
        document.getElementById("statusFilter");

    const sessionTypeFilter =
        document.getElementById("sessionTypeFilter");

    const counsellorFilter =
        document.getElementById("counsellorFilter");

    const dateFilter =
        document.getElementById("dateFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const tableBody =
        document.getElementById("bookingsTableBody");

    const rows =
        Array.from(tableBody.querySelectorAll("tr"));

    const emptyState =
        document.getElementById("emptyState");

    const tabs =
        document.querySelectorAll(".tab-btn");


    /* =====================================
       FILTER BOOKINGS
    ===================================== */

    function filterBookings() {

        const search =
            searchInput.value.toLowerCase().trim();

        const status =
            statusFilter.value;

        const type =
            sessionTypeFilter.value;

        const counsellor =
            counsellorFilter.value;

        const date =
            dateFilter.value;


        let visible = 0;


        rows.forEach(function (row) {

            const id =
                row.dataset.id.toLowerCase();

            const client =
                row.dataset.client.toLowerCase();

            const rowCounsellor =
                row.dataset.counsellor;

            const rowStatus =
                row.dataset.status;

            const rowType =
                row.dataset.type;

            const rowDate =
                row.dataset.date;


            const matchesSearch =
                id.includes(search) ||
                client.includes(search) ||
                rowCounsellor.toLowerCase().includes(search);


            const matchesStatus =
                status === "all" ||
                rowStatus === status;


            const matchesType =
                type === "all" ||
                rowType === type;


            const matchesCounsellor =
                counsellor === "all" ||
                rowCounsellor === counsellor;


            const matchesDate =
                !date ||
                rowDate === date;


            if (
                matchesSearch &&
                matchesStatus &&
                matchesType &&
                matchesCounsellor &&
                matchesDate
            ) {

                row.style.display = "";

                visible++;

            } else {

                row.style.display = "none";

            }

        });


        emptyState.style.display =
            visible === 0 ? "block" : "none";

    }


    /* =====================================
       SEARCH
    ===================================== */

    searchInput.addEventListener(
        "input",
        filterBookings
    );


    /* =====================================
       HEADER SEARCH
    ===================================== */

    headerSearch.addEventListener(
        "input",
        function () {

            searchInput.value =
                headerSearch.value;

            filterBookings();

        }
    );


    /* =====================================
       FILTER EVENTS
    ===================================== */

    statusFilter.addEventListener(
        "change",
        filterBookings
    );

    sessionTypeFilter.addEventListener(
        "change",
        filterBookings
    );

    counsellorFilter.addEventListener(
        "change",
        filterBookings
    );

    dateFilter.addEventListener(
        "change",
        filterBookings
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

            counsellorFilter.value = "all";

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

                    } else {

                        row.style.display =
                            row.dataset.category === selected
                                ? ""
                                : "none";

                    }

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
       VIEW BOOKING
    ===================================== */

    document.querySelectorAll(".view-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    alert(
                        "Booking Details\n\n" +
                        "Booking ID: " +
                        row.dataset.id +
                        "\n\nClient: " +
                        row.dataset.client +
                        "\nCounsellor: " +
                        row.dataset.counsellor +
                        "\nDate: " +
                        row.dataset.date +
                        "\nSession: " +
                        row.dataset.type +
                        "\nStatus: " +
                        row.dataset.status
                    );

                }
            );

        });


    /* =====================================
       APPROVE BOOKING
    ===================================== */

    document.querySelectorAll(".approve-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    const confirmed =
                        confirm(
                            "Approve booking " +
                            row.dataset.id +
                            " for " +
                            row.dataset.client +
                            "?"
                        );


                    if (confirmed) {

                        row.dataset.status =
                            "Confirmed";

                        row.dataset.category =
                            "confirmed";


                        const statusElement =
                            row.querySelector(".status");

                        if (statusElement) {

                            statusElement.textContent =
                                "Confirmed";

                            statusElement.className =
                                "status confirmed";

                        }


                        alert(
                            "Booking approved successfully."
                        );

                    }

                }
            );

        });


    /* =====================================
       DECLINE BOOKING
    ===================================== */

    document.querySelectorAll(".decline-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");


                    const confirmed =
                        confirm(
                            "Decline booking " +
                            row.dataset.id +
                            "?"
                        );


                    if (confirmed) {

                        row.dataset.status =
                            "Declined";

                        row.dataset.category =
                            "declined";


                        const statusElement =
                            row.querySelector(".status");

                        if (statusElement) {

                            statusElement.textContent =
                                "Declined";

                            statusElement.className =
                                "status declined";

                        }


                        alert(
                            "Booking declined."
                        );

                    }

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

                    alert(
                        "Opening video session for " +
                        row.dataset.client +
                        ".\n\n" +
                        "The real meeting room will be connected later."
                    );

                }
            );

        });


    /* =====================================
       CHAT
    ===================================== */

    document.querySelectorAll(".chat-btn")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    const row =
                        button.closest("tr");

                    alert(
                        "Opening chat with " +
                        row.dataset.client +
                        "."
                    );

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
                        "Client: " +
                        row.dataset.client +
                        "\nBooking: " +
                        row.dataset.id +
                        "\n\n" +
                        "Session notes will be connected later."
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
                            "Booking " +
                            row.dataset.id +
                            "\n\n" +
                            "Enter action:\n" +
                            "1 = View Details\n" +
                            "2 = Cancel Booking\n" +
                            "3 = Reschedule\n" +
                            "4 = Delete Booking"
                        );


                    if (action === "1") {

                        alert(
                            "Opening booking details..."
                        );

                    } else if (action === "2") {

                        const confirmCancel =
                            confirm(
                                "Cancel this booking?"
                            );

                        if (confirmCancel) {

                            row.dataset.status =
                                "Cancelled";

                            row.dataset.category =
                                "cancelled";

                            const statusElement =
                                row.querySelector(".status");

                            statusElement.textContent =
                                "Cancelled";

                            statusElement.className =
                                "status cancelled";

                            alert(
                                "Booking cancelled."
                            );

                        }

                    } else if (action === "3") {

                        alert(
                            "Reschedule feature will be connected later."
                        );

                    } else if (action === "4") {

                        const deleteBooking =
                            confirm(
                                "Delete booking " +
                                row.dataset.id +
                                " permanently?"
                            );

                        if (deleteBooking) {

                            row.remove();

                            alert(
                                "Booking deleted from the demo."
                            );

                        }

                    }

                }
            );

        });


    /* =====================================
       ADD BOOKING MODAL
    ===================================== */

    const addBookingBtn =
        document.getElementById("addBookingBtn");

    const bookingModal =
        document.getElementById("bookingModal");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");

    const bookingForm =
        document.getElementById("bookingForm");


    function openModal() {

        bookingModal.classList.add("show");

    }


    function closeBookingModal() {

        bookingModal.classList.remove("show");

        bookingForm.reset();

    }


    addBookingBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeBookingModal
    );


    cancelModal.addEventListener(
        "click",
        closeBookingModal
    );


    /* =====================================
       CLOSE MODAL OUTSIDE
    ===================================== */

    bookingModal.addEventListener(
        "click",
        function (event) {

            if (event.target === bookingModal) {

                closeBookingModal();

            }

        }
    );


    /* =====================================
       MINIMUM DATE
    ===================================== */

    const newDate =
        document.getElementById("newDate");

    const today =
        new Date().toISOString().split("T")[0];

    newDate.min = today;


    /* =====================================
       CREATE BOOKING
    ===================================== */

    bookingForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const client =
                document.getElementById("newClient").value;

            const counsellor =
                document.getElementById("newCounsellor").value;

            const date =
                document.getElementById("newDate").value;


            if (!client || !counsellor || !date) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            alert(
                "Booking created successfully!\n\n" +
                "Client: " +
                client +
                "\nCounsellor: " +
                counsellor +
                "\nDate: " +
                date +
                "\n\n" +
                "This is currently a frontend demo. " +
                "The booking will be saved to MySQL when the backend is connected."
            );


            closeBookingModal();

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
                "The backend will later generate CSV or Excel files containing booking records."
            );

        }
    );


    /* =====================================
       PAGINATION DEMO
    ===================================== */

    document.querySelectorAll(".pagination-buttons button")
        .forEach(function (button) {

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