const searchInput = document.getElementById("counselorSearch");
const specializationFilter = document.getElementById("specializationFilter");
const languageFilter = document.getElementById("languageFilter");
const availabilityFilter = document.getElementById("availabilityFilter");

const counselorCards = document.querySelectorAll(".counselor-card");
const counselorCount = document.getElementById("counselorCount");
const noResults = document.getElementById("noResults");


function filterCounselors() {

    const searchValue = searchInput.value.toLowerCase();

    const specializationValue =
        specializationFilter.value;

    const languageValue =
        languageFilter.value;

    const availabilityValue =
        availabilityFilter.value;


    let visibleCount = 0;


    counselorCards.forEach(card => {

        const name =
            card.dataset.name.toLowerCase();

        const specialization =
            card.dataset.specialization;

        const language =
            card.dataset.language;

        const availability =
            card.dataset.availability;


        const matchesSearch =
            name.includes(searchValue) ||
            specialization.toLowerCase().includes(searchValue);


        const matchesSpecialization =
            specializationValue === "" ||
            specialization === specializationValue;


        const matchesLanguage =
            languageValue === "" ||
            language.split(" ").includes(languageValue);


        const matchesAvailability =
            availabilityValue === "" ||
            availability === availabilityValue;


        if (
            matchesSearch &&
            matchesSpecialization &&
            matchesLanguage &&
            matchesAvailability
        ) {

            card.style.display = "block";

            visibleCount++;

        } else {

            card.style.display = "none";

        }

    });


    counselorCount.textContent =
        visibleCount + " counsellor" +
        (visibleCount !== 1 ? "s" : "");


    if (visibleCount === 0) {

        noResults.style.display = "block";

    } else {

        noResults.style.display = "none";

    }

}


searchInput.addEventListener(
    "input",
    filterCounselors
);

specializationFilter.addEventListener(
    "change",
    filterCounselors
);

languageFilter.addEventListener(
    "change",
    filterCounselors
);

availabilityFilter.addEventListener(
    "change",
    filterCounselors
);