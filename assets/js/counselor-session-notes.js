document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       ELEMENTS
    ========================================= */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    const notesSearch = document.getElementById("notesSearch");
    const notesFilter = document.getElementById("notesFilter");
    const notesDate = document.getElementById("notesDate");

    const noteCards = document.querySelectorAll(".note-card");

    const emptyNotes = document.getElementById("emptyNotes");
    const clearFilters = document.getElementById("clearFilters");

    const noteModal = document.getElementById("noteModal");
    const newNoteBtn = document.getElementById("newNoteBtn");
    const closeModal = document.getElementById("closeModal");
    const cancelNote = document.getElementById("cancelNote");

    const sessionNoteForm =
        document.getElementById("sessionNoteForm");

    const modalTitle =
        document.getElementById("modalTitle");

    const clientName =
        document.getElementById("clientName");

    const sessionDate =
        document.getElementById("sessionDate");


    /* =========================================
       MOBILE SIDEBAR
    ========================================= */

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =========================================
       OPEN MODAL
    ========================================= */

    function openModal(title = "New Session Note") {

        modalTitle.textContent = title;

        noteModal.classList.add("show");

        document.body.style.overflow = "hidden";

    }


    /* =========================================
       CLOSE MODAL
    ========================================= */

    function closeNoteModal() {

        noteModal.classList.remove("show");

        document.body.style.overflow = "";

    }


    /* =========================================
       NEW NOTE
    ========================================= */

    if (newNoteBtn) {

        newNoteBtn.addEventListener("click", function () {

            sessionNoteForm.reset();

            modalTitle.textContent = "New Session Note";

            openModal();

        });

    }


    /* =========================================
       CLOSE BUTTONS
    ========================================= */

    if (closeModal) {

        closeModal.addEventListener(
            "click",
            closeNoteModal
        );

    }


    if (cancelNote) {

        cancelNote.addEventListener(
            "click",
            closeNoteModal
        );

    }


    /* =========================================
       CLOSE WHEN CLICKING OUTSIDE
    ========================================= */

    if (noteModal) {

        noteModal.addEventListener("click", function (event) {

            if (event.target === noteModal) {

                closeNoteModal();

            }

        });

    }


    /* =========================================
       SEARCH + FILTER
    ========================================= */

    function filterNotes() {

        const searchValue =
            notesSearch.value.toLowerCase().trim();

        const filterValue =
            notesFilter.value;

        const dateValue =
            notesDate.value;

        let visibleCount = 0;


        noteCards.forEach(function (card) {

            const client =
                card.dataset.client.toLowerCase();

            const status =
                card.dataset.status;

            const date =
                card.dataset.date;


            const matchesSearch =
                client.includes(searchValue);


            const matchesFilter =
                filterValue === "all" ||
                status === filterValue;


            const matchesDate =
                dateValue === "" ||
                date === dateValue;


            if (
                matchesSearch &&
                matchesFilter &&
                matchesDate
            ) {

                card.style.display = "block";

                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        if (visibleCount === 0) {

            emptyNotes.classList.add("show");

        } else {

            emptyNotes.classList.remove("show");

        }

    }


    if (notesSearch) {

        notesSearch.addEventListener(
            "input",
            filterNotes
        );

    }


    if (notesFilter) {

        notesFilter.addEventListener(
            "change",
            filterNotes
        );

    }


    if (notesDate) {

        notesDate.addEventListener(
            "change",
            filterNotes
        );

    }


    /* =========================================
       CLEAR FILTERS
    ========================================= */

    if (clearFilters) {

        clearFilters.addEventListener("click", function () {

            notesSearch.value = "";
            notesFilter.value = "all";
            notesDate.value = "";

            filterNotes();

        });

    }


    /* =========================================
       EDIT NOTE
    ========================================= */

    document
        .querySelectorAll(".edit-note")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                sessionNoteForm.reset();

                clientName.value = client;

                modalTitle.textContent =
                    "Edit Session Note";

                openModal("Edit Session Note");

            });

        });


    /* =========================================
       ADD PENDING NOTE
    ========================================= */

    document
        .querySelectorAll(".complete-note")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                sessionNoteForm.reset();

                clientName.value = client;

                modalTitle.textContent =
                    "Complete Session Note";

                openModal("Complete Session Note");

            });

        });


    /* =========================================
       VIEW NOTE
    ========================================= */

    document
        .querySelectorAll(".view-note")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                alert(
                    "Viewing session note for " +
                    client +
                    ".\n\nThe full note details will be loaded from the database later."
                );

            });

        });


    /* =========================================
       DELETE NOTE
    ========================================= */

    document
        .querySelectorAll(".delete-note")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                const client =
                    button.dataset.client;

                const confirmed = confirm(
                    "Are you sure you want to delete the session note for " +
                    client +
                    "?"
                );


                if (confirmed) {

                    const card =
                        button.closest(".note-card");

                    card.remove();

                    alert(
                        "Session note deleted in this frontend demo."
                    );

                }

            });

        });


    /* =========================================
       SAVE NOTE
    ========================================= */

    if (sessionNoteForm) {

        sessionNoteForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                const client =
                    clientName.value;

                const discussion =
                    document
                        .getElementById("discussion")
                        .value
                        .trim();


                if (!client || !discussion) {

                    alert(
                        "Please select a client and enter the discussion."
                    );

                    return;

                }


                alert(
                    "Session note for " +
                    client +
                    " saved successfully in this frontend demo.\n\n" +
                    "The database connection will be added later."
                );


                closeNoteModal();

                sessionNoteForm.reset();

            }
        );

    }


    /* =========================================
       PAGINATION
    ========================================= */

    document
        .querySelectorAll(".page-btn")
        .forEach(function (button) {

            button.addEventListener("click", function () {

                if (
                    button.classList.contains("disabled") ||
                    button.classList.contains("active")
                ) {

                    return;

                }


                alert(
                    "Pagination is currently a frontend demo."
                );

            });

        });


    /* =========================================
       INITIAL FILTER
    ========================================= */

    filterNotes();

});