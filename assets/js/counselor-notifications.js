document.addEventListener("DOMContentLoaded", function () {

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    const notificationList =
        document.getElementById("notificationList");

    const emptyState =
        document.getElementById("emptyState");

    const searchInput =
        document.getElementById("notificationSearch");

    const tabs =
        document.querySelectorAll(".notification-tab");

    const markAllRead =
        document.getElementById("markAllRead");

    const unreadCount =
        document.getElementById("unreadNotifications");

    const totalCount =
        document.getElementById("totalNotifications");

    const resultText =
        document.getElementById("notificationResult");


    let currentFilter = "all";


    /* =====================================
       MOBILE SIDEBAR
       ===================================== */

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =====================================
       UPDATE COUNTS
       ===================================== */

    function updateCounts() {

        const notifications =
            document.querySelectorAll(".notification-item");

        const unread =
            document.querySelectorAll(
                ".notification-item.unread"
            );

        if (totalCount) {
            totalCount.textContent = notifications.length;
        }

        if (unreadCount) {
            unreadCount.textContent = unread.length;
        }


        const sidebarBadge =
            document.querySelector(".notification-count");

        if (sidebarBadge) {

            sidebarBadge.textContent = unread.length;

            if (unread.length === 0) {
                sidebarBadge.style.display = "none";
            } else {
                sidebarBadge.style.display = "inline-flex";
            }

        }

    }


    /* =====================================
       FILTER NOTIFICATIONS
       ===================================== */

    function filterNotifications() {

        const notifications =
            document.querySelectorAll(".notification-item");

        const searchTerm =
            searchInput.value.toLowerCase().trim();

        let visibleCount = 0;


        notifications.forEach(function (notification) {

            const type =
                notification.dataset.type;

            const isUnread =
                notification.classList.contains("unread");

            const text =
                notification.textContent.toLowerCase();


            let matchesFilter = false;

            if (currentFilter === "all") {

                matchesFilter = true;

            } else if (currentFilter === "unread") {

                matchesFilter = isUnread;

            } else {

                matchesFilter =
                    type === currentFilter;

            }


            const matchesSearch =
                text.includes(searchTerm);


            if (matchesFilter && matchesSearch) {

                notification.style.display = "grid";

                visibleCount++;

            } else {

                notification.style.display = "none";

            }

        });


        if (resultText) {

            resultText.textContent =
                visibleCount +
                (visibleCount === 1
                    ? " notification"
                    : " notifications");

        }


        if (emptyState) {

            if (visibleCount === 0) {

                emptyState.style.display = "block";

            } else {

                emptyState.style.display = "none";

            }

        }

    }


    /* =====================================
       TABS
       ===================================== */

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {

                item.classList.remove("active");

            });


            this.classList.add("active");

            currentFilter =
                this.dataset.filter;


            filterNotifications();

        });

    });


    /* =====================================
       SEARCH
       ===================================== */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            filterNotifications
        );

    }


    /* =====================================
       MARK SINGLE NOTIFICATION AS READ
       ===================================== */

    function markAsRead(button) {

        const notification =
            button.closest(".notification-item");

        if (!notification) {
            return;
        }


        notification.classList.remove("unread");


        const dot =
            notification.querySelector(".unread-dot");

        if (dot) {
            dot.remove();
        }


        updateCounts();

        filterNotifications();

    }


    /* =====================================
       DELETE NOTIFICATION
       ===================================== */

    function deleteNotification(button) {

        const notification =
            button.closest(".notification-item");

        if (!notification) {
            return;
        }


        const confirmed = confirm(
            "Are you sure you want to delete this notification?"
        );


        if (!confirmed) {
            return;
        }


        notification.remove();

        updateCounts();

        filterNotifications();

    }


    /* =====================================
       NOTIFICATION BUTTONS
       ===================================== */

    notificationList.addEventListener(
        "click",
        function (event) {

            const readButton =
                event.target.closest(".read-btn");

            const deleteButton =
                event.target.closest(".delete-btn");


            if (readButton) {

                markAsRead(readButton);

            }


            if (deleteButton) {

                deleteNotification(deleteButton);

            }

        }
    );


    /* =====================================
       MARK ALL AS READ
       ===================================== */

    if (markAllRead) {

        markAllRead.addEventListener(
            "click",
            function () {

                const unreadNotifications =
                    document.querySelectorAll(
                        ".notification-item.unread"
                    );


                if (unreadNotifications.length === 0) {

                    alert(
                        "All notifications are already marked as read."
                    );

                    return;

                }


                unreadNotifications.forEach(
                    function (notification) {

                        notification.classList.remove(
                            "unread"
                        );


                        const dot =
                            notification.querySelector(
                                ".unread-dot"
                            );

                        if (dot) {
                            dot.remove();
                        }

                    }
                );


                updateCounts();

                filterNotifications();


                alert(
                    "All notifications have been marked as read."
                );

            }
        );

    }


    /* =====================================
       PAGINATION
       ===================================== */

    const paginationButtons =
        document.querySelectorAll(
            ".page-number, .pagination .page-btn"
        );


    paginationButtons.forEach(function (button) {

        if (
            !button.classList.contains("disabled")
        ) {

            button.addEventListener(
                "click",
                function () {

                    alert(
                        "Pagination is a frontend demo. More notifications will be loaded from the database later."
                    );

                }
            );

        }

    });


    /* =====================================
       INITIALIZE
       ===================================== */

    updateCounts();

    filterNotifications();

});