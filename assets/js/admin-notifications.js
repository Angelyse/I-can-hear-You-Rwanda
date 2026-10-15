document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       MOBILE SIDEBAR
       ===================================================== */

    const mobileMenu =
        document.getElementById("mobileMenu");

    const sidebar =
        document.getElementById("sidebar");


    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =====================================================
       ELEMENTS
       ===================================================== */

    const notificationSearch =
        document.getElementById("notificationSearch");

    const headerSearch =
        document.getElementById("headerSearch");

    const typeFilter =
        document.getElementById("typeFilter");

    const audienceFilter =
        document.getElementById("audienceFilter");

    const statusFilter =
        document.getElementById("statusFilter");

    const clearFilters =
        document.getElementById("clearFilters");

    const notificationList =
        document.getElementById("notificationList");

    const emptyState =
        document.getElementById("emptyState");

    const tabButtons =
        document.querySelectorAll(".tab-btn");

    const notificationItems =
        document.querySelectorAll(".notification-item");


    let currentTab = "all";


    /* =====================================================
       FILTER NOTIFICATIONS
       ===================================================== */

    function applyFilters() {

        const search =
            notificationSearch.value
                .toLowerCase()
                .trim();

        const type =
            typeFilter.value;

        const audience =
            audienceFilter.value;

        const status =
            statusFilter.value;

        let visibleCount = 0;


        notificationItems.forEach(function (item) {

            const text =
                item.textContent.toLowerCase();

            const itemType =
                item.dataset.type;

            const itemAudience =
                item.dataset.audience;

            const itemStatus =
                item.dataset.status;


            const matchesSearch =
                search === "" ||
                text.includes(search);


            const matchesType =
                type === "all" ||
                itemType === type;


            const matchesAudience =
                audience === "all" ||
                itemAudience === audience ||
                (
                    audience === "everyone" &&
                    itemAudience === "everyone"
                );


            const matchesStatus =
                status === "all" ||
                itemStatus === status;


            let matchesTab = true;


            if (currentTab === "unread") {

                matchesTab =
                    item.classList.contains("unread");

            } else if (currentTab !== "all") {

                matchesTab =
                    itemType === currentTab;

            }


            if (
                matchesSearch &&
                matchesType &&
                matchesAudience &&
                matchesStatus &&
                matchesTab
            ) {

                item.style.display = "flex";

                visibleCount++;

            } else {

                item.style.display = "none";

            }

        });


        if (visibleCount === 0) {

            notificationList.style.display = "none";

            emptyState.style.display = "block";

        } else {

            notificationList.style.display = "flex";

            emptyState.style.display = "none";

        }

    }


    /* =====================================================
       SEARCH
       ===================================================== */

    notificationSearch.addEventListener(
        "input",
        applyFilters
    );


    headerSearch.addEventListener(
        "input",
        function () {

            notificationSearch.value =
                headerSearch.value;

            applyFilters();

        }
    );


    /* =====================================================
       FILTERS
       ===================================================== */

    typeFilter.addEventListener(
        "change",
        applyFilters
    );

    audienceFilter.addEventListener(
        "change",
        applyFilters
    );

    statusFilter.addEventListener(
        "change",
        applyFilters
    );


    /* =====================================================
       CLEAR FILTERS
       ===================================================== */

    clearFilters.addEventListener(
        "click",
        function () {

            notificationSearch.value = "";

            headerSearch.value = "";

            typeFilter.value = "all";

            audienceFilter.value = "all";

            statusFilter.value = "all";

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
       MARK ONE NOTIFICATION AS READ / UNREAD
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".read-btn");


            if (!button) {
                return;
            }


            const item =
                button.closest(".notification-item");


            if (!item) {
                return;
            }


            const icon =
                button.querySelector("i");


            if (item.classList.contains("unread")) {

                item.classList.remove("unread");

                item.dataset.read = "true";

                if (icon) {
                    icon.className =
                        "fa-solid fa-envelope";
                }

                button.title =
                    "Mark as unread";

            } else {

                item.classList.add("unread");

                item.dataset.read = "false";

                if (icon) {
                    icon.className =
                        "fa-solid fa-check";
                }

                button.title =
                    "Mark as read";

            }


            applyFilters();

        }
    );


    /* =====================================================
       MARK ALL AS READ
       ===================================================== */

    const markAllReadBtn =
        document.getElementById("markAllReadBtn");


    markAllReadBtn.addEventListener(
        "click",
        function () {

            notificationItems.forEach(
                function (item) {

                    item.classList.remove("unread");

                    item.dataset.read = "true";


                    const icon =
                        item.querySelector(".read-btn i");


                    const readButton =
                        item.querySelector(".read-btn");


                    if (icon) {

                        icon.className =
                            "fa-solid fa-envelope";

                    }


                    if (readButton) {

                        readButton.title =
                            "Mark as unread";

                    }

                }
            );


            alert(
                "All notifications have been marked as read."
            );


            applyFilters();

        }
    );


    /* =====================================================
       DELETE ONE NOTIFICATION
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".delete-btn");


            if (!button) {
                return;
            }


            const item =
                button.closest(".notification-item");


            if (!item) {
                return;
            }


            const title =
                item.querySelector(
                    ".notification-title-row strong"
                );


            const notificationTitle =
                title
                    ? title.textContent.trim()
                    : "this notification";


            if (
                confirm(
                    "Delete " +
                    notificationTitle +
                    "?"
                )
            ) {

                item.remove();

                alert(
                    "Notification deleted in demo mode."
                );


                applyFilters();

            }

        }
    );


    /* =====================================================
       DELETE READ NOTIFICATIONS
       ===================================================== */

    const deleteReadBtn =
        document.getElementById("deleteReadBtn");


    deleteReadBtn.addEventListener(
        "click",
        function () {

            const readItems =
                document.querySelectorAll(
                    ".notification-item:not(.unread)"
                );


            if (readItems.length === 0) {

                alert(
                    "There are no read notifications to delete."
                );

                return;

            }


            if (
                confirm(
                    "Delete all read notifications?"
                )
            ) {

                readItems.forEach(function (item) {

                    item.remove();

                });


                alert(
                    "Read notifications deleted in demo mode."
                );


                applyFilters();

            }

        }
    );


    /* =====================================================
       MORE ACTION
       ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(".more-btn");


            if (!button) {
                return;
            }


            const item =
                button.closest(".notification-item");


            const title =
                item.querySelector(
                    ".notification-title-row strong"
                );


            const notificationTitle =
                title
                    ? title.textContent.trim()
                    : "Notification";


            const choice =
                prompt(
                    notificationTitle +
                    "\n\n" +
                    "Choose an action:\n\n" +
                    "1 - View Details\n" +
                    "2 - Mark as Read\n" +
                    "3 - Delete"
                );


            if (choice === "1") {

                alert(
                    "Notification Details\n\n" +
                    notificationTitle +
                    "\n\n" +
                    "Detailed notification information will be loaded from the database later."
                );

            } else if (choice === "2") {

                item.classList.remove("unread");

                alert(
                    "Notification marked as read."
                );


                applyFilters();

            } else if (choice === "3") {

                if (
                    confirm(
                        "Delete this notification?"
                    )
                ) {

                    item.remove();

                    applyFilters();

                }

            }

        }
    );


    /* =====================================================
       CREATE NOTIFICATION MODAL
       ===================================================== */

    const modal =
        document.getElementById("notificationModal");

    const createNotificationBtn =
        document.getElementById("createNotificationBtn");

    const closeModal =
        document.getElementById("closeModal");

    const cancelModal =
        document.getElementById("cancelModal");

    const notificationForm =
        document.getElementById("notificationForm");


    function openModal() {

        modal.classList.add("show");

    }


    function closeNotificationModal() {

        modal.classList.remove("show");

    }


    createNotificationBtn.addEventListener(
        "click",
        openModal
    );


    closeModal.addEventListener(
        "click",
        closeNotificationModal
    );


    cancelModal.addEventListener(
        "click",
        closeNotificationModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {

                closeNotificationModal();

            }

        }
    );


    /* =====================================================
       CREATE NOTIFICATION
       ===================================================== */

    notificationForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const title =
                document.getElementById(
                    "notificationTitle"
                ).value.trim();


            const type =
                document.getElementById(
                    "notificationType"
                ).value;


            const audience =
                document.getElementById(
                    "notificationAudience"
                ).value;


            const status =
                document.getElementById(
                    "notificationStatus"
                ).value;


            const message =
                document.getElementById(
                    "notificationMessage"
                ).value.trim();


            if (
                title === "" ||
                type === "" ||
                audience === "" ||
                status === "" ||
                message === ""
            ) {

                alert(
                    "Please complete all required fields."
                );

                return;

            }


            if (status === "scheduled") {

                const date =
                    document.getElementById(
                        "notificationDate"
                    ).value;


                const time =
                    document.getElementById(
                        "notificationTime"
                    ).value;


                if (date === "" || time === "") {

                    alert(
                        "Please select a schedule date and time."
                    );

                    return;

                }

            }


            let statusMessage =
                "Notification created successfully in demo mode.";


            if (status === "scheduled") {

                statusMessage =
                    "Notification scheduled successfully in demo mode.";

            } else if (status === "draft") {

                statusMessage =
                    "Notification saved as draft in demo mode.";

            }


            alert(
                statusMessage +
                "\n\n" +
                "Title: " +
                title
            );


            notificationForm.reset();

            closeNotificationModal();

        }
    );


    /* =====================================================
       PAGINATION
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
                    !button.classList.contains("active") &&
                    button.textContent.trim() !== "..."
                ) {

                    alert(
                        "Pagination will load notifications from the database after backend integration."
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