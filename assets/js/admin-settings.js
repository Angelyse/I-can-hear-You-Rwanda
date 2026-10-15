document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       MOBILE SIDEBAR
    ========================================= */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {
        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });
    }


    /* =========================================
       SETTINGS TABS
    ========================================= */

    const tabs = document.querySelectorAll(".settings-tab");
    const panels = document.querySelectorAll(".settings-panel");

    tabs.forEach(function (tab) {

        tab.addEventListener("click", function () {

            const target = this.dataset.tab;

            tabs.forEach(function (item) {
                item.classList.remove("active");
            });

            panels.forEach(function (panel) {
                panel.classList.remove("active");
            });

            this.classList.add("active");

            const selectedPanel = document.getElementById(target);

            if (selectedPanel) {
                selectedPanel.classList.add("active");
            }

        });

    });


    /* =========================================
       SAVE SETTINGS
    ========================================= */

    const saveButton = document.getElementById("saveSettings");

    if (saveButton) {

        saveButton.addEventListener("click", function () {

            const platformName =
                document.getElementById("platformName");

            if (platformName && platformName.value.trim() === "") {
                alert("Please enter the platform name.");
                platformName.focus();
                return;
            }

            alert(
                "Settings saved successfully.\n\n" +
                "This is currently a frontend demo. " +
                "The settings will be stored in MySQL when the backend is connected."
            );

        });

    }


    /* =========================================
       RESET SETTINGS
    ========================================= */

    const resetButton = document.getElementById("resetSettings");

    if (resetButton) {

        resetButton.addEventListener("click", function () {

            const confirmReset = confirm(
                "Are you sure you want to reset the settings?\n\n" +
                "Unsaved changes will be lost."
            );

            if (confirmReset) {
                window.location.reload();
            }

        });

    }


    /* =========================================
       CHANGE ADMIN PASSWORD
    ========================================= */

    const changePassword =
        document.getElementById("changeAdminPassword");

    if (changePassword) {

        changePassword.addEventListener("click", function () {

            const currentPassword = prompt(
                "Enter your current admin password:"
            );

            if (!currentPassword) {
                return;
            }

            const newPassword = prompt(
                "Enter your new password:"
            );

            if (!newPassword) {
                return;
            }

            if (newPassword.length < 8) {

                alert(
                    "Password must contain at least 8 characters."
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
                "Admin password changed successfully.\n\n" +
                "Backend authentication will be connected later."
            );

        });

    }


    /* =========================================
       MAINTENANCE MODE
    ========================================= */

    const maintenanceMode =
        document.getElementById("maintenanceMode");

    if (maintenanceMode) {

        maintenanceMode.addEventListener("change", function () {

            if (this.checked) {

                const confirmation = confirm(
                    "Enable maintenance mode?\n\n" +
                    "Users may not be able to access the platform."
                );

                if (!confirmation) {
                    this.checked = false;
                }

            }

        });

    }


    /* =========================================
       LOGO UPLOAD
    ========================================= */

    const uploadLogo =
        document.getElementById("uploadLogo");

    const logoInput =
        document.getElementById("logoInput");

    const logoPreview =
        document.querySelector(".logo-preview img");

    if (uploadLogo && logoInput) {

        uploadLogo.addEventListener("click", function () {
            logoInput.click();
        });

    }

    if (logoInput) {

        logoInput.addEventListener("change", function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith("image/")) {

                alert("Please select an image file.");

                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                if (logoPreview) {
                    logoPreview.src = event.target.result;
                }

            };

            reader.readAsDataURL(file);

        });

    }


    /* =========================================
       INTEGRATION BUTTONS
    ========================================= */

    const integrationButtons =
        document.querySelectorAll(".integration-btn");

    integrationButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const card = this.closest(".integration-card");

            if (!card) {
                return;
            }

            const service =
                card.querySelector(".integration-info h3");

            const serviceName =
                service ? service.textContent : "Service";

            alert(
                "Integration settings for " +
                serviceName +
                " will be configured here.\n\n" +
                "Backend integration will be added later."
            );

        });

    });


    /* =========================================
       BACKUP DATABASE
    ========================================= */

    const backupDatabase =
        document.getElementById("backupDatabase");

    if (backupDatabase) {

        backupDatabase.addEventListener("click", function () {

            const confirmation = confirm(
                "Start database backup now?"
            );

            if (!confirmation) {
                return;
            }

            alert(
                "Database backup started.\n\n" +
                "Real database backup functionality will be connected later."
            );

        });

    }


    /* =========================================
       CLEAR CACHE
    ========================================= */

    const clearCache =
        document.getElementById("clearCache");

    if (clearCache) {

        clearCache.addEventListener("click", function () {

            const confirmation = confirm(
                "Are you sure you want to clear the system cache?"
            );

            if (!confirmation) {
                return;
            }

            alert(
                "System cache cleared successfully.\n\n" +
                "This is currently a frontend demonstration."
            );

        });

    }


    /* =========================================
       SYSTEM HEALTH
    ========================================= */

    const systemHealth =
        document.getElementById("systemHealth");

    if (systemHealth) {

        systemHealth.addEventListener("click", function () {

            alert(
                "System Health Check\n\n" +
                "✓ Database: Healthy\n" +
                "✓ Storage: Healthy\n" +
                "✓ API: Healthy\n" +
                "✓ Notification Service: Healthy"
            );

        });

    }


    /* =========================================
       DANGER ZONE
    ========================================= */

    const disableRegistration =
        document.getElementById("disableRegistration");

    if (disableRegistration) {

        disableRegistration.addEventListener("click", function () {

            const confirmation = confirm(
                "Disable new user registrations?\n\n" +
                "Existing users will not be affected."
            );

            if (confirmation) {

                alert(
                    "New user registration has been disabled.\n\n" +
                    "Backend functionality will be connected later."
                );

            }

        });

    }


    const resetDemoData =
        document.getElementById("resetDemoData");

    if (resetDemoData) {

        resetDemoData.addEventListener("click", function () {

            const firstConfirmation = confirm(
                "WARNING!\n\n" +
                "This action will remove demo data.\n\n" +
                "Do you want to continue?"
            );

            if (!firstConfirmation) {
                return;
            }

            const secondConfirmation = confirm(
                "Are you absolutely sure?\n\n" +
                "This action may not be reversible."
            );

            if (secondConfirmation) {

                alert(
                    "Demo data reset requested.\n\n" +
                    "The real database operation will be implemented later."
                );

            }

        });

    }


    const deactivateSystem =
        document.getElementById("deactivateSystem");

    if (deactivateSystem) {

        deactivateSystem.addEventListener("click", function () {

            const confirmation = confirm(
                "WARNING!\n\n" +
                "Deactivating the system can prevent all users " +
                "from accessing the platform.\n\n" +
                "Are you sure?"
            );

            if (confirmation) {

                alert(
                    "System deactivation request received.\n\n" +
                    "This functionality will be protected and implemented " +
                    "during the backend stage."
                );

            }

        });

    }


    /* =========================================
       HEADER SEARCH
    ========================================= */

    const headerSearch =
        document.getElementById("headerSearch");

    if (headerSearch) {

        headerSearch.addEventListener("input", function () {

            const value = this.value.toLowerCase().trim();

            if (!value) {
                return;
            }

            const allText =
                document.querySelector(".settings-content");

            if (!allText) {
                return;
            }

            const text =
                allText.innerText.toLowerCase();

            if (!text.includes(value)) {

                console.log(
                    "No matching setting found for:",
                    value
                );

            }

        });

    }

});