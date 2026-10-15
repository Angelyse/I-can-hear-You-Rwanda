document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("bookingSearch");
    const statusFilter = document.getElementById("statusFilter");
    const sessionTypeFilter = document.getElementById("sessionTypeFilter");
    const dateFilter = document.getElementById("dateFilter");

    const tabs = document.querySelectorAll(".booking-tab");
    const bookings = document.querySelectorAll(".booking-card");

    const emptyState = document.getElementById("emptyState");

    let currentTab = "all";


    // =========================================
    // FILTER BOOKINGS
    // =========================================

    function filterBookings() {

        const searchValue =
            searchInput.value.toLowerCase().trim();

        const statusValue =
            statusFilter.value;

        const typeValue =
            sessionTypeFilter.value;

        const dateValue =
            dateFilter.value;

        let visibleCount = 0;


        bookings.forEach(function (booking) {

            const name =
                booking.dataset.name.toLowerCase();

            const status =
                booking.dataset.status;

            const type =
                booking.dataset.type;

            const date =
                booking.dataset.date;


            const matchesSearch =
                name.includes(searchValue);


            const matchesStatus =
                statusValue === "all" ||
                status === statusValue;


            const matchesType =
                typeValue === "all" ||
                type === typeValue;


            const matchesDate =
                dateValue === "" ||
                date === dateValue;


            const matchesTab =
                currentTab === "all" ||
                status === currentTab;


            if (
                matchesSearch &&
                matchesStatus &&
                matchesType &&
                matchesDate &&
                matchesTab
            ) {

                booking.classList.remove("hidden");

                visibleCount++;

            } else {

                booking.classList.add("hidden");

            }

        });


        // Show empty state

        if (visibleCount === 0) {

            emptyState.classList.add("show");

        } else {

            emptyState.classList.remove("show");

        }

    }


    // =========================================
    // SEARCH
    // =========================================

    searchInput.addEventListener("input", function () {

        filterBookings();

    });


    // =========================================
    // STATUS FILTER
    // =========================================

    statusFilter.addEventListener("change", function () {

        currentTab = "all";

        tabs.forEach(function (tab) {

            tab.classList.remove("active");

        });

        tabs[0].classList.add("active");

        filterBookings();

    });


    // =========================================
    // SESSION TYPE FILTER
    // =========================================

    sessionTypeFilter.addEventListener("change", function () {

        filterBookings();

    });


    // =========================================
    // DATE FILTER
    // =========================================

    dateFilter.addEventListener("change", function () {

        filterBookings();

    });


    // =========================================
    // TABS
    // =========================================

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {

                item.classList.remove("active");

            });

            tab.classList.add("active");

            currentTab =
                tab.dataset.filter;

            statusFilter.value = "all";

            filterBookings();

        });

    });


    // =========================================
    // VIEW DETAILS
    // =========================================

    const viewButtons =
        document.querySelectorAll(".view-btn");


    viewButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const client =
                button.dataset.client;

            alert(
                "Booking details for " +
                client +
                " will be loaded from the database later."
            );

        });

    });


    // =========================================
    // APPROVE BOOKING
    // =========================================

    const approveButtons =
        document.querySelectorAll(".approve-btn");


    approveButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const booking =
                button.closest(".booking-card");

            const client =
                booking.dataset.name;


            const confirmed =
                confirm(
                    "Approve the booking for " +
                    client +
                    "?"
                );


            if (confirmed) {

                alert(
                    "Booking approved successfully."
                );

                // Demo only
                booking.dataset.status = "confirmed";

                const status =
                    booking.querySelector(".booking-status");

                status.textContent = "Confirmed";

                status.className =
                    "booking-status confirmed";


                button.remove();


                const declineButton =
                    booking.querySelector(".decline-btn");

                if (declineButton) {
                    declineButton.remove();
                }


                filterBookings();

            }

        });

    });


    // =========================================
    // DECLINE BOOKING
    // =========================================

    const declineButtons =
        document.querySelectorAll(".decline-btn");


    declineButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const booking =
                button.closest(".booking-card");

            const client =
                booking.dataset.name;


            const confirmed =
                confirm(
                    "Decline the booking request from " +
                    client +
                    "?"
                );


            if (confirmed) {

                alert(
                    "Booking declined."
                );

                booking.dataset.status =
                    "cancelled";


                const status =
                    booking.querySelector(".booking-status");

                status.textContent =
                    "Cancelled";

                status.className =
                    "booking-status cancelled";


                button.remove();


                const approveButton =
                    booking.querySelector(".approve-btn");

                if (approveButton) {
                    approveButton.remove();
                }


                filterBookings();

            }

        });

    });


    // =========================================
    // JOIN SESSION
    // =========================================

    const joinButtons =
        document.querySelectorAll(".join-btn");


    joinButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            alert(
                "The video/chat session will open here when the meeting system is connected."
            );

        });

    });


    // =========================================
    // ADD NOTES
    // =========================================

    const notesButtons =
        document.querySelectorAll(".notes-btn");


    notesButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            alert(
                "Session Notes page will open here."
            );

        });

    });


    // =========================================
    // CALENDAR BUTTON
    // =========================================

    const calendarBtn =
        document.getElementById("calendarBtn");


    if (calendarBtn) {

        calendarBtn.addEventListener("click", function () {

            alert(
                "Calendar view will be connected later."
            );

        });

    }


    // =========================================
    // PAGINATION
    // =========================================

    const pageButtons =
        document.querySelectorAll(".page-btn");


    pageButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            pageButtons.forEach(function (btn) {

                btn.classList.remove("active");

            });

            button.classList.add("active");

            alert(
                "Pagination will load bookings from the database later."
            );

        });

    });


    const nextButton =
        document.querySelector(".page-next");


    if (nextButton) {

        nextButton.addEventListener("click", function () {

            alert(
                "Next booking page will be loaded from the database later."
            );

        });

    }


    // Initial filtering

    filterBookings();

});