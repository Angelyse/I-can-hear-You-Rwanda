document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       MOBILE SIDEBAR
       ===================================================== */

    const mobileMenu = document.getElementById("mobileMenu");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenu && sidebar) {

        mobileMenu.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });

    }


    /* =====================================================
       DATE RANGE
       ===================================================== */

    const dateRangeBtn =
        document.getElementById("dateRangeBtn");


    dateRangeBtn.addEventListener("click", function () {

        const choice = prompt(
            "Select report period:\n\n" +
            "1 - Last 7 Days\n" +
            "2 - Last 30 Days\n" +
            "3 - Last 3 Months\n" +
            "4 - Last 6 Months\n" +
            "5 - This Year"
        );


        const labels = {
            "1": "Last 7 Days",
            "2": "Last 30 Days",
            "3": "Last 3 Months",
            "4": "Last 6 Months",
            "5": "This Year"
        };


        if (labels[choice]) {

            dateRangeBtn.innerHTML =
                '<i class="fa-regular fa-calendar"></i> ' +
                labels[choice] +
                ' <i class="fa-solid fa-chevron-down"></i>';

            alert(
                "Reports updated for: " +
                labels[choice] +
                "\n\n" +
                "The real data will be loaded from the database later."
            );

        }

    });


    /* =====================================================
       GENERATE REPORT
       ===================================================== */

    const generateReportBtn =
        document.getElementById("generateReportBtn");


    generateReportBtn.addEventListener("click", function () {

        const choice = prompt(
            "Generate Report\n\n" +
            "1 - Complete Platform Report\n" +
            "2 - Client Report\n" +
            "3 - Session Report\n" +
            "4 - Counsellor Report\n" +
            "5 - Program Impact Report"
        );


        const reports = {
            "1": "Complete Platform Report",
            "2": "Client Report",
            "3": "Session Report",
            "4": "Counsellor Report",
            "5": "Program Impact Report"
        };


        if (reports[choice]) {

            alert(
                reports[choice] +
                "\n\n" +
                "Report generation is currently running in demo mode.\n\n" +
                "Later this feature will generate a downloadable PDF or Excel report."
            );

        }

    });


    /* =====================================================
       SESSION CHART FILTER
       ===================================================== */

    const sessionChartFilter =
        document.getElementById("sessionChartFilter");


    sessionChartFilter.addEventListener("change", function () {

        if (this.value === "12") {

            alert(
                "The 12-month session dataset will be loaded from the database later."
            );

        } else {

            alert(
                "Showing the last 6 months of demo session data."
            );

        }

    });


    /* =====================================================
       VIEW PROGRAMS
       ===================================================== */

    const viewProgramsBtn =
        document.getElementById("viewProgramsBtn");


    viewProgramsBtn.addEventListener("click", function () {

        window.location.href = "programs.php";

    });


    /* =====================================================
       QUICK REPORTS
       ===================================================== */

    const quickReports =
        document.querySelectorAll(".quick-report");


    quickReports.forEach(function (button) {

        button.addEventListener("click", function () {

            const reportName =
                button.dataset.report;


            const confirmed =
                confirm(
                    "Generate " +
                    reportName +
                    "?"
                );


            if (confirmed) {

                alert(
                    reportName +
                    "\n\n" +
                    "The report is ready for backend integration.\n\n" +
                    "Later it can be exported as PDF, Excel or CSV."
                );

            }

        });

    });


    /* =====================================================
       HEADER SEARCH
       ===================================================== */

    const headerSearch =
        document.getElementById("headerSearch");


    headerSearch.addEventListener("input", function () {

        const value =
            this.value.toLowerCase().trim();


        if (value === "") {
            return;
        }


        if (value.includes("client")) {

            alert(
                "Client reports are available in the analytics dashboard."
            );

        } else if (value.includes("session")) {

            alert(
                "Session activity reports are available in the analytics dashboard."
            );

        } else if (value.includes("program")) {

            alert(
                "Program performance reports are available below."
            );

        }

    });


    /* =====================================================
       BAR CHART ANIMATION
       ===================================================== */

    const bars =
        document.querySelectorAll(".bar");


    bars.forEach(function (bar) {

        const finalHeight =
            bar.style.height;

        bar.style.height = "0";


        setTimeout(function () {
            bar.style.height = finalHeight;
        }, 300);

    });


});