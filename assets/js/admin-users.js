document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("userModal");
    const openAddButton = document.getElementById("openAddUser");
    const closeButton = document.getElementById("closeUserModal");
    const cancelButton = document.getElementById("cancelUserModal");

    const form = document.getElementById("userForm");
    const formAction = document.getElementById("formAction");
    const userId = document.getElementById("userId");

    const nameInput = document.getElementById("userName");
    const emailInput = document.getElementById("userEmail");
    const phoneInput = document.getElementById("userPhone");
    const roleInput = document.getElementById("userRole");
    const statusInput = document.getElementById("userStatus");
    const passwordInput = document.getElementById("userPassword");

    const modalTitle = document.getElementById("modalTitle");
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
        userId.value = "";
        formAction.value = "create";
        passwordInput.required = true;
        passwordHint.textContent = "(required for new users)";
        modalTitle.textContent = "Add User";
    }

    openAddButton.addEventListener("click", function () {
        closeModal();
        modal.hidden = false;
        document.body.classList.add("modal-open");
        modalTitle.textContent = "Add User";
        passwordInput.required = true;
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

    document.querySelectorAll(".edit-user").forEach(function (button) {
        button.addEventListener("click", function () {
            form.reset();

            formAction.value = "update";
            userId.value = button.dataset.id;

            nameInput.value = button.dataset.name || "";
            emailInput.value = button.dataset.email || "";
            phoneInput.value = button.dataset.phone || "";
            roleInput.value = button.dataset.role || "client";
            statusInput.value = button.dataset.status || "active";

            passwordInput.value = "";
            passwordInput.required = false;
            passwordHint.textContent = "(optional; leave blank to keep current password)";

            modalTitle.textContent = "Edit User";
            openModal();
        });
    });

    form.addEventListener("submit", function (event) {
        if (passwordInput.value !== "" && passwordInput.value.length < 8) {
            event.preventDefault();
            alert("The password must contain at least 8 characters.");
            passwordInput.focus();
            return;
        }

        if (formAction.value === "create" && passwordInput.value.length < 8) {
            event.preventDefault();
            alert("Enter a password of at least 8 characters.");
            passwordInput.focus();
        }
    });
});