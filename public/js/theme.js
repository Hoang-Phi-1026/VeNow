// Theme handling
document.addEventListener("DOMContentLoaded", () => {
    // Get theme toggle button
    const themeToggle = document.querySelector(".theme-toggle")
  
    // Function to set theme
    function setTheme(theme) {
      // Add transition class to body before changing theme
      document.body.classList.add("theme-transition")
  
      // Set the theme after a small delay to ensure transition class is applied
      setTimeout(() => {
        document.documentElement.setAttribute("data-theme", theme)
        localStorage.setItem("theme", theme)
  
        // Update icon
        const icon = themeToggle.querySelector("i")
        if (theme === "dark") {
          icon.className = "fas fa-sun"
          icon.title = "Chuyển sang chế độ sáng"
        } else {
          icon.className = "fas fa-moon"
          icon.title = "Chuyển sang chế độ tối"
        }
  
        // Remove transition class after theme change is complete
        setTimeout(() => {
          document.body.classList.remove("theme-transition")
        }, 300)
      }, 10)
    }
  
    // Check for saved theme preference
    const savedTheme = localStorage.getItem("theme")
    if (savedTheme) {
      setTheme(savedTheme)
    } else {
      // Check system preference
      if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        setTheme("dark")
      } else {
        setTheme("light")
      }
    }
  
    // Theme toggle click handler
    themeToggle.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme")
      const newTheme = currentTheme === "dark" ? "light" : "dark"
      setTheme(newTheme)
    })
  
    // Listen for system theme changes
    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
      if (!localStorage.getItem("theme")) {
        setTheme(e.matches ? "dark" : "light")
      }
    })
  })
  