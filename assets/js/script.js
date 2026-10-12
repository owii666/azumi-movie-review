document.addEventListener("DOMContentLoaded", () => {
    // Dynamic Date Formatting
    const dateElements = document.querySelectorAll(".logo .date");
    if (dateElements.length > 0) {
        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = today.toLocaleDateString('en-US', options);
        dateElements.forEach(el => {
            el.textContent = formattedDate;
        });
    }

    const hamburgerMenu = document.querySelector(".hamburger-menu");
    const navLinks = document.querySelector(".nav-links");
    const searchDropdownBar = document.getElementById("search-dropdown-bar") || document.getElementById("search-overlay");
    const searchIcon = document.getElementById("search-icon");
    const closeSearch = document.getElementById("close-search");
    const searchForm = document.getElementById("search-inline-form");
    const searchInput = document.getElementById("search-input");

    if (hamburgerMenu && navLinks) {
        // Mobile Navbar Toggle
        hamburgerMenu.addEventListener("click", () => {
            navLinks.classList.toggle("active");
            if (searchDropdownBar && searchDropdownBar.classList.contains("active")) {
                searchDropdownBar.classList.remove("active"); // Close search if open
            }
        });
    }

    if (closeSearch) {
        closeSearch.addEventListener("click", () => {
            if (searchDropdownBar) {
                searchDropdownBar.classList.remove("active");
            }
            if (searchInput) searchInput.value = "";
            const quickResults = document.getElementById("search-quick-results");
            if (quickResults) {
                quickResults.innerHTML = "";
                quickResults.classList.remove("has-results");
            }
        });
    }

    // Search Toggle
    if (searchIcon && searchDropdownBar) {
        searchIcon.addEventListener("click", (e) => {
            e.preventDefault();
            const isOpen = searchDropdownBar.classList.contains("active");

            // If already open and user typed a keyword, clicking search icon triggers search submit
            if (isOpen && searchInput && searchInput.value.trim().length > 0) {
                if (searchForm) {
                    searchForm.submit();
                    return;
                }
            }

            searchDropdownBar.classList.toggle("active");
            if (navLinks && navLinks.classList.contains("active")) {
                navLinks.classList.remove("active"); // Close nav if open
            }
            if (searchDropdownBar.classList.contains("active")) {
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });

        // Close search when clicking outside
        document.addEventListener("click", (e) => {
            if (!searchDropdownBar.contains(e.target) && !searchIcon.contains(e.target)) {
                searchDropdownBar.classList.remove("active");
            }
        });
    }

    // Back to top (example scroll animation, if needed for long pages)
    // const backToTopButton = document.querySelector(".back-to-top");
    // if (backToTopButton) {
    //     window.addEventListener("scroll", () => {
    //         if (window.scrollY > 300) {
    //             backToTopButton.style.display = "block";
    //         } else {
    //             backToTopButton.style.display = "none";
    //         }
    //     });

    //     backToTopButton.addEventListener("click", () => {
    //         window.scrollTo({
    //             top: 0,
    //             behavior: "smooth"
    //         });
    //     });
    // }

    // Smooth scroll for anchor links (if any)
    document.querySelectorAll("a[href^=\"#\"]").forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            const targetId = this.getAttribute("href");
            if (targetId && targetId !== "#") {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: "smooth"
                    });
                }
            }
        });
    });

    // Card click functionality - make the whole card clickable
    document.querySelectorAll(".review-card, .feature-card, .review-card-large, .feature-card-large").forEach(card => {
        card.addEventListener("click", function(e) {
            // Prevent navigating if a specific interactive element inside the card was clicked (e.g., a button)
            if (e.target.tagName !== "A" && e.target.tagName !== "BUTTON") {
                const link = this.querySelector("a");
                if (link && link.href) {
                    window.location.href = link.href;
                }
            }
        });
    });

    // Ensure all navigation links work correctly (already handled by href attributes, but good for completeness)
    document.querySelectorAll(".nav-links a").forEach(link => {
        link.addEventListener("click", () => {
            if (navLinks.classList.contains("active")) {
                navLinks.classList.remove("active"); // Close nav after clicking a link
            }
        });
    });
});
