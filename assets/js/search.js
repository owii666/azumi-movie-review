/**
 * Live Search Script (Compact & Non-intrusive)
 * Azumi Movie Reviews
 */

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("search-input");
    const searchResultsDiv = document.getElementById("search-quick-results") || document.getElementById("search-results");

    if (!searchInput || !searchResultsDiv) return;

    let debounceTimer;

    searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();

        if (query.length < 2) {
            searchResultsDiv.innerHTML = "";
            searchResultsDiv.classList.remove("has-results");
            return;
        }

        debounceTimer = setTimeout(() => {
            searchResultsDiv.innerHTML = `<div style="padding: 12px 16px; color: var(--lighter-grey); font-size: 0.85rem;">Mencari ulasan & film...</div>`;
            searchResultsDiv.classList.add("has-results");

            // Determine relative API path
            const isInsideAdmin = window.location.pathname.includes('/admin/');
            const apiUrl = (isInsideAdmin ? '../' : '') + 'api-search.php?q=' + encodeURIComponent(query);

            fetch(apiUrl)
                .then(res => res.json())
                .then(data => {
                    searchResultsDiv.innerHTML = "";
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const resultLink = document.createElement("a");
                            resultLink.className = "search-quick-item";
                            const targetUrl = (isInsideAdmin ? '../' : '') + item.url;
                            resultLink.href = targetUrl;
                            
                            const imgHtml = item.image ? `<img src="${(isInsideAdmin ? '../' : '') + escapeHtml(item.image)}" alt="${escapeHtml(item.title)}" class="search-item-thumb" loading="lazy">` : '';

                            resultLink.innerHTML = `
                                <div class="search-item-inner">
                                    ${imgHtml}
                                    <div class="search-item-content">
                                        <span class="search-item-cat">${escapeHtml(item.category)}</span>
                                        <div class="search-item-title">${escapeHtml(item.title)}</div>
                                        <span class="search-item-meta">${escapeHtml(item.meta || '')}</span>
                                    </div>
                                </div>
                            `;
                            searchResultsDiv.appendChild(resultLink);
                        });

                        // Add "Lihat semua hasil" at the bottom
                        const viewAllLink = document.createElement("a");
                        viewAllLink.className = "search-quick-item";
                        viewAllLink.style.backgroundColor = "#FAF9F5";
                        viewAllLink.style.textAlign = "center";
                        viewAllLink.style.fontSize = "0.82rem";
                        viewAllLink.style.fontWeight = "700";
                        viewAllLink.style.color = "var(--accent)";
                        viewAllLink.href = (isInsideAdmin ? '../' : '') + 'search.php?q=' + encodeURIComponent(query);
                        viewAllLink.textContent = `Lihat semua hasil pencarian untuk "${query}" →`;
                        searchResultsDiv.appendChild(viewAllLink);

                        searchResultsDiv.classList.add("has-results");
                    } else {
                        searchResultsDiv.innerHTML = `
                            <div style="padding: 12px 16px; color: var(--lighter-grey); font-size: 0.85rem;">
                                Tidak ada ulasan atau film untuk "${escapeHtml(query)}"
                            </div>
                        `;
                        searchResultsDiv.classList.add("has-results");
                    }
                })
                .catch(err => {
                    console.error("Search error:", err);
                    searchResultsDiv.innerHTML = `<div style="padding: 12px 16px; color: var(--accent); font-size: 0.85rem;">Gagal memuat hasil pencarian.</div>`;
                    searchResultsDiv.classList.add("has-results");
                });
        }, 280);
    });

    // Close results dropdown on Escape key
    searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            searchResultsDiv.innerHTML = "";
            searchResultsDiv.classList.remove("has-results");
            const dropdown = document.getElementById("search-dropdown-bar");
            if (dropdown) dropdown.classList.remove("active");
        }
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
