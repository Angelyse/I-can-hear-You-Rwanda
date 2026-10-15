document.addEventListener("DOMContentLoaded", function () {

    /*
     * Counsellor dashboard JavaScript
     *
     * This stage is frontend only.
     * Database functionality will be added later.
     */


    /* =========================================
       AVAILABILITY STATUS
    ========================================== */

    const availability = null; // availability is controlled from the profile page

    if (availability) {

        availability.addEventListener("click", function () {

            const statusDot =
                this.querySelector(".status-dot");

            if (
                this.classList.contains("not-available")
            ) {

                this.classList.remove(
                    "not-available"
                );

                this.innerHTML =
                    '<span class="status-dot"></span>' +
                    ' Available for Sessions';

            } else {

                this.classList.add(
                    "not-available"
                );

                this.innerHTML =
                    '<span class="status-dot"></span>' +
                    ' Currently Unavailable';

            }

        });

    }


    /* =========================================
       QUICK ACTION FEEDBACK
    ========================================== */

    const quickActions =
        document.querySelectorAll(".quick-action");

    quickActions.forEach(function (action) {

        action.addEventListener("click", function () {

            console.log(
                "Opening:",
                this.innerText.trim()
            );

        });

    });

});