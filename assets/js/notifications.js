document.addEventListener("DOMContentLoaded", function () {

    const tabs = document.querySelectorAll(".notification-tab");
    const notifications = document.querySelectorAll(".notification-item");

    const unreadCountElement =
        document.getElementById("unreadNotifications");

    const totalElement =
        document.getElementById("totalNotifications");

    const emptyState =
        document.getElementById("emptyNotifications");

    const markAllReadButton =
        document.getElementById("markAllRead");

    const searchInput =
        document.getElementById("notificationSearch");


    /* =========================================
       UPDATE COUNTS
    ========================================= */

    function updateCounts() {

        const currentNotifications =
            document.querySelectorAll(".notification-item");

        const unreadNotifications =
            document.querySelectorAll(".notification-item.unread");

        if (totalElement) {
            totalElement.textContent =
                currentNotifications.length;
        }

        if (unreadCountElement) {
            unreadCountElement.textContent =
                unreadNotifications.length;
        }

        const navBadge =
            document.querySelector(".notification-nav-badge");

        if (navBadge) {

            if (unreadNotifications.length > 0) {
                navBadge.textContent =
                    unreadNotifications.length;
                navBadge.style.display = "inline-flex";
            } else {
                navBadge.style.display = "none";
            }
        }

        const tabCount =
            document.querySelector(".tab-count");

        if (tabCount) {
            tabCount.textContent =
                unreadNotifications.length;
        }
    }


    /* =========================================
       FILTER NOTIFICATIONS
    ========================================= */

    function filterNotifications(filter) {

        let visibleCount = 0;

        const searchTerm =
            searchInput
                ? searchInput.value.toLowerCase().trim()
                : "";

        notifications.forEach(function (notification) {

            const category =
                notification.dataset.category;

            const isUnread =
                notification.classList.contains("unread");

            const text =
                notification.textContent.toLowerCase();

            let matchesFilter = false;
            let matchesSearch = false;


            /* Filter */

            if (filter === "all") {
                matchesFilter = true;
            }

            else if (filter === "unread") {
                matchesFilter = isUnread;
            }

            else {
                matchesFilter =
                    category === filter;
            }


            /* Search */

            if (searchTerm === "") {
                matchesSearch = true;
            } else {
                matchesSearch =
                    text.includes(searchTerm);
            }


            /* Show / Hide */

            if (matchesFilter && matchesSearch) {

                notification.style.display = "flex";
                visibleCount++;

            } else {

                notification.style.display = "none";

            }

        });


        /* Empty state */

        if (visibleCount === 0) {
            emptyState.style.display = "block";
        } else {
            emptyState.style.display = "none";
        }
    }


    /* =========================================
       TAB CLICK
    ========================================= */

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            tabs.forEach(function (item) {
                item.classList.remove("active");
            });

            this.classList.add("active");

            const filter =
                this.dataset.filter;

            filterNotifications(filter);
        });

    });


    /* =========================================
       SEARCH
    ========================================= */

    if (searchInput) {

        searchInput.addEventListener("input", function () {

            const activeTab =
                document.querySelector(
                    ".notification-tab.active"
                );

            const filter =
                activeTab
                    ? activeTab.dataset.filter
                    : "all";

            filterNotifications(filter);

        });

    }


    /* =========================================
       MARK ONE AS READ
    ========================================= */

    document.querySelectorAll(".read-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const notification =
                    this.closest(".notification-item");

                if (!notification) {
                    return;
                }

                notification.classList.remove("unread");

                const unreadDot =
                    notification.querySelector(".unread-dot");

                if (unreadDot) {
                    unreadDot.remove();
                }

                updateCounts();

                const activeTab =
                    document.querySelector(
                        ".notification-tab.active"
                    );

                if (activeTab) {
                    filterNotifications(
                        activeTab.dataset.filter
                    );
                }

            });

        });


    /* =========================================
       MARK ALL AS READ
    ========================================= */

    if (markAllReadButton) {

        markAllReadButton.addEventListener(
            "click",
            function () {

                notifications.forEach(
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

                const activeTab =
                    document.querySelector(
                        ".notification-tab.active"
                    );

                if (activeTab) {

                    filterNotifications(
                        activeTab.dataset.filter
                    );

                }

            }
        );

    }


    /* =========================================
       DELETE NOTIFICATION
    ========================================= */

    document.querySelectorAll(".delete-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const notification =
                    this.closest(".notification-item");

                if (!notification) {
                    return;
                }

                notification.remove();

                updateCounts();

                const activeTab =
                    document.querySelector(
                        ".notification-tab.active"
                    );

                if (activeTab) {

                    filterNotifications(
                        activeTab.dataset.filter
                    );

                }

            });

        });


    /* =========================================
       INITIALIZE
    ========================================= */

    updateCounts();

});