document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("counsellorModal");
    const form = document.getElementById("counsellorForm");

    const openButton = document.getElementById("openAddCounsellor");
    const closeButton = document.getElementById("closeCounsellorModal");
    const cancelButton = document.getElementById("cancelCounsellorModal");

    const actionInput = document.getElementById("formAction");
    const idInput = document.getElementById("counsellorId");

    const nameInput = document.getElementById("cName");
    const emailInput = document.getElementById("cEmail");
    const phoneInput = document.getElementById("cPhone");
    const qualificationInput = document.getElementById("cQualification");
    const specializationInput = document.getElementById("cSpecialization");
    const experienceInput = document.getElementById("cExperience");
    const languagesInput = document.getElementById("cLanguages");
    const bioInput = document.getElementById("cBio");
    const statusInput = document.getElementById("cStatus");
    const passwordInput = document.getElementById("cPassword");

    const title = document.getElementById("modalTitle");
    const passwordHint = document.getElementById("passwordHint");

    function openModal() {
        modal.hidden = false;
        document.body.classList.add("modal-open");
        nameInput.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove("modal-open");
        form.reset();

        actionInput.value = "create";
        idInput.value = "";
        passwordInput.required = true;
        passwordHint.textContent = "(required for new accounts)";
        title.textContent = "Add Counsellor";
    }

    openButton.addEventListener("click", function () {
        closeModal();

        modal.hidden = false;
        document.body.classList.add("modal-open");

        actionInput.value = "create";
        passwordInput.required = true;
        title.textContent = "Add Counsellor";

        nameInput.focus();
    });

    closeButton.addEventListener("click", closeModal);
    cancelButton.addEventListener("click", closeModal);

    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) {
            closeModal();
        }
    });

    document.querySelectorAll(".edit-counsellor").forEach(function (button) {
        button.addEventListener("click", function () {
            form.reset();

            actionInput.value = "update";
            idInput.value = button.dataset.id || "";

            nameInput.value = button.dataset.name || "";
            emailInput.value = button.dataset.email || "";
            phoneInput.value = button.dataset.phone || "";
            qualificationInput.value = button.dataset.qualification || "";
            specializationInput.value = button.dataset.specialization || "";
            experienceInput.value = button.dataset.experience || "0";
            languagesInput.value = button.dataset.languages || "English";
            bioInput.value = button.dataset.bio || "";
            statusInput.value = button.dataset.status || "pending";

            passwordInput.value = "";
            passwordInput.required = false;

            passwordHint.textContent =
                "(optional; leave blank to keep the current password)";

            title.textContent = "Edit Counsellor";

            openModal();
        });
    });

    form.addEventListener("submit", function (event) {
        const password = passwordInput.value;
        const experience = Number(experienceInput.value);

        if (actionInput.value === "create" && password.length < 8) {
            event.preventDefault();
            alert("A new counsellor must have a password of at least 8 characters.");
            passwordInput.focus();
            return;
        }

        if (password !== "" && password.length < 8) {
            event.preventDefault();
            alert("A new password must contain at least 8 characters.");
            passwordInput.focus();
            return;
        }

        if (!Number.isInteger(experience) || experience < 0 || experience > 80) {
            event.preventDefault();
            alert("Enter a valid number of years of experience.");
            experienceInput.focus();
        }
    });
});