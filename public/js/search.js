document.addEventListener("DOMContentLoaded", () => {
    // Theme Toggle
    const themeToggle = document.querySelector('.theme-toggle');
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
    }

    // Theme toggle click handler
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Add hover effect to event cards
    const eventCards = document.querySelectorAll('.event-card')
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)'
        })
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)'
        })
    })

    // Lazy loading for images
    if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const img = entry.target
                    const src = img.getAttribute('data-src')

                    if (src) {
                        img.src = src
                        img.removeAttribute('data-src')
                    }

                    observer.unobserve(img)
                }
            })
        })

        document.querySelectorAll('img[data-src]').forEach((img) => {
            imageObserver.observe(img)
        })
    }
}) 