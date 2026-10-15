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
   RESOURCE FILTER
========================================= */

const searchInput =
    document.getElementById("resourceSearch");

const categoryButtons =
    document.querySelectorAll(".category-btn");

const resourceCards =
    document.querySelectorAll(".resource-card");

const resourceCount =
    document.getElementById("resourceCount");

const noResources =
    document.getElementById("noResources");


let selectedCategory = "all";


function filterResources() {

    const searchValue =
        searchInput.value.toLowerCase().trim();

    let visibleCount = 0;


    resourceCards.forEach(card => {

        const title =
            card.dataset.title.toLowerCase();

        const category =
            card.dataset.category;


        const matchesSearch =
            title.includes(searchValue);


        const matchesCategory =
            selectedCategory === "all" ||
            category === selectedCategory;


        if (
            matchesSearch &&
            matchesCategory
        ) {

            card.style.display = "block";

            visibleCount++;

        } else {

            card.style.display = "none";

        }

    });


    resourceCount.textContent =
        visibleCount +
        " resource" +
        (visibleCount !== 1 ? "s" : "");


    if (visibleCount === 0) {

        noResources.style.display =
            "block";

    } else {

        noResources.style.display =
            "none";

    }

}


/* Search */

searchInput.addEventListener(
    "input",
    filterResources
);


/* Categories */

categoryButtons.forEach(button => {

    button.addEventListener(
        "click",
        function () {

            categoryButtons.forEach(item => {

                item.classList.remove("active");

            });


            this.classList.add("active");


            selectedCategory =
                this.dataset.category;


            filterResources();

        }
    );

});
