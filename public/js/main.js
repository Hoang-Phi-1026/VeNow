// Mobile Menu Toggle
document.addEventListener("DOMContentLoaded", () => {
    const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
    const navList = document.querySelector(".nav-list")
    const dropdowns = document.querySelectorAll(".dropdown")
  
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", () => {
        navList.classList.toggle("active")
        // Close all dropdowns when toggling the menu
        dropdowns.forEach((dropdown) => dropdown.classList.remove("active"))
      })
    }
  
    // Handle dropdowns on mobile
    dropdowns.forEach((dropdown) => {
      const link = dropdown.querySelector("a")
      if (link) {
        link.addEventListener("click", (e) => {
          if (window.innerWidth <= 768) {
            e.preventDefault()
            // Close other dropdowns
            dropdowns.forEach((d) => {
              if (d !== dropdown) d.classList.remove("active")
            })
            dropdown.classList.toggle("active")
          }
        })
      }
    })
  
    // Close mobile menu when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".main-nav") && !e.target.closest(".mobile-menu-toggle")) {
        navList.classList.remove("active")
        dropdowns.forEach((dropdown) => dropdown.classList.remove("active"))
      }
    })
  
    // Handle window resize
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        navList.classList.remove("active")
        dropdowns.forEach((dropdown) => dropdown.classList.remove("active"))
      }
    })
  
    // Hero slider functionality - IMPROVED
    const sliderItems = document.querySelectorAll(".slider-item")
    const dots = document.querySelectorAll(".dot")
    const prevBtn = document.querySelector(".slider-prev")
    const nextBtn = document.querySelector(".slider-next")
  
    if (sliderItems.length > 0) {
      let currentSlide = 0
      let isAnimating = false
      let autoSlideInterval
  
      // Function to show a specific slide
      function showSlide(index) {
        if (isAnimating) return
        isAnimating = true
  
        // Hide all slides
        sliderItems.forEach((item) => {
          item.classList.remove("active")
        })
  
        // Remove active class from all dots
        dots.forEach((dot) => {
          dot.classList.remove("active")
        })
  
        // Show the current slide and activate the corresponding dot
        sliderItems[index].classList.add("active")
        dots[index].classList.add("active")
  
        // Reset animation flag after transition completes
        setTimeout(() => {
          isAnimating = false
        }, 500)
      }
  
      // Next slide function
      function nextSlide() {
        currentSlide = (currentSlide + 1) % sliderItems.length
        showSlide(currentSlide)
      }
  
      // Previous slide function
      function prevSlide() {
        currentSlide = (currentSlide - 1 + sliderItems.length) % sliderItems.length
        showSlide(currentSlide)
      }
  
      // Event listeners for next and previous buttons
      if (nextBtn)
        nextBtn.addEventListener("click", () => {
          clearInterval(autoSlideInterval)
          nextSlide()
          startAutoSlide()
        })
  
      if (prevBtn)
        prevBtn.addEventListener("click", () => {
          clearInterval(autoSlideInterval)
          prevSlide()
          startAutoSlide()
        })
  
      // Event listeners for dots
      dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
          if (currentSlide === index) return
          clearInterval(autoSlideInterval)
          currentSlide = index
          showSlide(currentSlide)
          startAutoSlide()
        })
      })
  
      // Function to start auto slide
      function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000)
      }
  
      // Initialize auto slide
      startAutoSlide()
  
      // Pause auto slide when hovering over slider
      const heroSlider = document.querySelector(".hero-slider")
      if (heroSlider) {
        heroSlider.addEventListener("mouseenter", () => {
          clearInterval(autoSlideInterval)
        })
  
        heroSlider.addEventListener("mouseleave", () => {
          startAutoSlide()
        })
      }
    }
  
    // Add animation to elements when they come into view
    const animateOnScroll = () => {
      const elements = document.querySelectorAll(".event-card, .category-card, .section-title")
  
      elements.forEach((element) => {
        const elementPosition = element.getBoundingClientRect().top
        const screenPosition = window.innerHeight / 1.2
  
        if (elementPosition < screenPosition) {
          element.classList.add("slide-up")
        }
      })
    }
  
    // Run animation check on load and scroll
    window.addEventListener("load", animateOnScroll)
    window.addEventListener("scroll", animateOnScroll)
  })
  