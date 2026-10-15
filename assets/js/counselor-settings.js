document.addEventListener("DOMContentLoaded", function () {

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    const tabs = document.querySelectorAll(".settings-tab");
    const sections = document.querySelectorAll(".settings-section");

    const saveSettings = document.getElementById("saveSettings");
    const cancelSettings = document.getElementById("cancelSettings");

    const changePassword =
        document.getElementById("changePassword");

    const enable2FA =
        document.getElementById("enable2FA");

    const manageSessions =
        document.getElementById("manageSessions");

    const deactivateAccount =
        document.getElementById("deactivateAccount");

    const deleteAccount =
        document.getElementById("deleteAccount");


    /* =====================================
       MOBILE SIDEBAR
       ===================================== */

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {

            sidebar.classList.toggle("show");

        });

    }


    /* =====================================
       SETTINGS TABS
       ===================================== */

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            const sectionId =
                this.dataset.section;


            tabs.forEach(function (item) {

                item.classList.remove("active");

            });


            sections.forEach(function (section) {

                section.classList.remove("active");

            });


            this.classList.add("active");


            const selectedSection =
                document.getElementById(sectionId);


            if (selectedSection) {

                selectedSection.classList.add("active");

            }

        });

    });


    /* =====================================
       SAVE SETTINGS
       ===================================== */

    if (saveSettings) {

        saveSettings.addEventListener("click", function () {

            alert(
                "Settings saved successfully!\n\n" +
                "Frontend demo only — the settings have not yet been saved to MySQL."
            );

        });

    }


    /* =====================================
       CANCEL SETTINGS
       ===================================== */

    if (cancelSettings) {

        cancelSettings.addEventListener("click", function () {

            const confirmed = confirm(
                "Are you sure you want to discard your changes?"
            );


            if (confirmed) {

                window.location.reload();

            }

        });

    }


    /* =====================================
       CHANGE PASSWORD
       ===================================== */

    if (changePassword) {

        changePassword.addEventListener("click", function () {

            const currentPassword = prompt(
                "Enter your current password:"
            );


            if (currentPassword === null) {
                return;
            }


            if (currentPassword.trim() === "") {

                alert(
                    "Please enter your current password."
                );

                return;

            }


            const newPassword = prompt(
                "Enter your new password:"
            );


            if (newPassword === null) {
                return;

            }


            if (newPassword.length < 8) {

                alert(
                    "Your new password must contain at least 8 characters."
                );

                return;

            }


            const confirmPassword = prompt(
                "Confirm your new password:"
            );


            if (newPassword !== confirmPassword) {

                alert(
                    "The passwords do not match."
                );

                return;

            }


            alert(
                "Password changed successfully!\n\n" +
                "Frontend demo only — the password has not yet been saved."
            );

        });

    }


    /* =====================================
       TWO FACTOR AUTHENTICATION
       ===================================== */

    if (enable2FA) {

        enable2FA.addEventListener("click", function () {

            alert(
                "Two-Factor Authentication setup will be implemented when the backend authentication system is connected."
            );

        });

    }


    /* =====================================
       MANAGE LOGIN SESSIONS
       ===================================== */

    if (manageSessions) {

        manageSessions.addEventListener("click", function () {

            alert(
                "Login session management will be connected to the authentication system later."
            );

        });

    }


    /* =====================================
       DEACTIVATE ACCOUNT
       ===================================== */

    if (deactivateAccount) {

        deactivateAccount.addEventListener(
            "click",
            function () {

                const confirmed = confirm(
                    "Are you sure you want to deactivate your counsellor account?"
                );


                if (!confirmed) {
                    return;
                }


                alert(
                    "Account deactivation is a backend feature and will be implemented later."
                );

            }
        );

    }


    /* =====================================
       DELETE ACCOUNT
       ===================================== */

    if (deleteAccount) {

        deleteAccount.addEventListener(
            "click",
            function () {

                const firstConfirm = confirm(
                    "WARNING: Deleting your account may permanently remove your account information.\n\nDo you want to continue?"
                );


                if (!firstConfirm) {
                    return;
                }


                const secondConfirm = confirm(
                    "Are you absolutely sure you want to delete this account?"
                );


                if (!secondConfirm) {
                    return;
                }


                alert(
                    "Account deletion will be implemented securely through the backend."
                );

            }
        );

    }

});