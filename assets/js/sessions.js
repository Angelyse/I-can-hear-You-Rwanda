/* =========================================
   MOBILE SIDEBAR
========================================= */

const mobileMenu =
    document.getElementById("mobileMenu");

const sidebar =
    document.getElementById("sidebar");

if (mobileMenu) {

    mobileMenu.addEventListener(
        "click",
        function () {

            sidebar.classList.toggle("show");

        }
    );

}


/* =========================================
   SESSION FILTER TABS
========================================= */

const sessionTabs =
    document.querySelectorAll(".session-tab");

const sessionCards =
    document.querySelectorAll(".session-card");

const noSessions =
    document.getElementById("noSessions");


sessionTabs.forEach(tab => {

    tab.addEventListener("click", function () {

        /* Remove active from all tabs */

        sessionTabs.forEach(item => {

            item.classList.remove("active");

        });


        /* Activate selected tab */

        this.classList.add("active");


        const selectedStatus =
            this.dataset.status;


        let visibleSessions = 0;


        sessionCards.forEach(card => {

            const cardStatus =
                card.dataset.status;


            if (
                selectedStatus === "all" ||
                cardStatus === selectedStatus
            ) {

                card.style.display = "grid";

                visibleSessions++;

            } else {

                card.style.display = "none";

            }

        });


        if (visibleSessions === 0) {

            noSessions.style.display = "block";

        } else {

            noSessions.style.display = "none";

        }

    });

});
