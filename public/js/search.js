document.addEventListener("DOMContentLoaded", () => {
    // Theme Toggle
    const themeToggle = document.querySelector(".theme-toggle")
  
    // Check for saved theme preference
    const savedTheme = localStorage.getItem("theme")
    if (savedTheme) {
      document.documentElement.setAttribute("data-theme", savedTheme)
      updateThemeIcon(savedTheme)
    }
  
    // Theme toggle click handler
    themeToggle.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme")
      const newTheme = currentTheme === "dark" ? "light" : "dark"
  
      document.documentElement.setAttribute("data-theme", newTheme)
      localStorage.setItem("theme", newTheme)
      updateThemeIcon(newTheme)
    })
  
    function updateThemeIcon(theme) {
      const icon = themeToggle.querySelector("i")
      icon.className = theme === "dark" ? "fas fa-sun" : "fas fa-moon"
    }
  
    // Add hover effect to event cards
    const eventCards = document.querySelectorAll(".event-card")
    eventCards.forEach((card) => {
      card.addEventListener("mouseenter", () => {
        card.style.transform = "translateY(-5px)"
      })
      card.addEventListener("mouseleave", () => {
        card.style.transform = "translateY(0)"
      })
    })
  
    // Lazy loading for images
    if ("IntersectionObserver" in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target
            const src = img.getAttribute("data-src")
  
            if (src) {
              img.src = src
              img.removeAttribute("data-src")
            }
  
            observer.unobserve(img)
          }
        })
      })
  
      document.querySelectorAll("img[data-src]").forEach((img) => {
        imageObserver.observe(img)
      })
    }
  
    // Get search form elements
    const searchForm = document.querySelector(".search-form")
    const searchInput = document.querySelector('input[name="q"]')
  
    if (searchForm && searchInput) {
      // Add event listener for form submission
      searchForm.addEventListener("submit", (e) => {
        // Trim the search input
        searchInput.value = searchInput.value.trim()
  
        // If search input is empty and no other filters are set, prevent form submission
        if (searchInput.value === "") {
          const hasOtherFilters = Array.from(searchForm.elements).some((element) => {
            return element.name !== "q" && element.name !== "" && element.value !== ""
          })
  
          if (!hasOtherFilters) {
            e.preventDefault()
            alert("Vui lòng nhập từ khóa tìm kiếm hoặc chọn bộ lọc.")
          }
        }
      })
    }
  
    // Reset filters button
    const resetButton = document.querySelector(".reset-filters")
    if (resetButton) {
      resetButton.addEventListener("click", (e) => {
        // Prevent default anchor behavior
        e.preventDefault()
  
        // Clear all form inputs
        const form = document.querySelector(".search-form")
        if (form) {
          // Reset all select elements
          form.querySelectorAll("select").forEach((select) => {
            select.selectedIndex = 0
          })
  
          // Reset all input elements
          form.querySelectorAll("input").forEach((input) => {
            if (input.type === "text" || input.type === "date") {
              input.value = ""
            }
          })
  
          // Redirect to base search URL
          window.location.href = form.action
        }
      })
    }
  })
  