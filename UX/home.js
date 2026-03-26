// Scroll Reveal
        const reveals = document.querySelectorAll('.reveal');
        
        function reveal() {
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }
        
        window.addEventListener('scroll', reveal);
        window.addEventListener('load', reveal);
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        

// Reviews Data - Load from localStorage or use default
let reviews = [];
let selectedRating = 0;

// Load saved reviews from localStorage
const savedReviews = localStorage.getItem('microgreens_reviews');
if (savedReviews) {
    reviews = JSON.parse(savedReviews);
} else {
    // Default reviews
    reviews = [
        {
            name: "Kasun Gunarathna",
            rating: 5,
            text: "Very fresh microgreens. High quality and fast delivery.",
            date: "2 days ago",
            img: 1
        },
        {
            name: "Chef Nimal",
            rating: 5,
            text: "Best supplier for restaurants. Highly recommended.",
            date: "1 week ago",
            img: 2
        },
        {
            name: "Hotel Manager",
            rating: 5,
            text: "Consistent quality and great service.",
            date: "3 weeks ago",
            img: 3
        }
    ];
}

// Function to update ratings and bars
function updateRatings() {
    const total = reviews.length;
    if (total === 0) {
        document.getElementById('avgRating').innerText = '0';
        document.getElementById('totalReviews').innerText = '0 reviews';
        return;
    }
    
    const sum = reviews.reduce((acc, review) => acc + review.rating, 0);
    const avg = (sum / total).toFixed(1);
    
    document.getElementById('avgRating').innerText = avg;
    document.getElementById('totalReviews').innerText = `${total} reviews`;
    
    // Update stars display
    const starsDiv = document.querySelector('.rating-box .stars');
    const fullStars = Math.floor(avg);
    const halfStar = avg % 1 >= 0.5;
    let starString = '';
    for (let i = 0; i < fullStars; i++) starString += '★';
    if (halfStar) starString += '½';
    for (let i = starString.length; i < 5; i++) starString += '☆';
    starsDiv.innerHTML = starString;
    
    // Calculate percentages for bars
    const ratingCounts = [0, 0, 0, 0, 0];
    reviews.forEach(review => {
        if (review.rating >= 1 && review.rating <= 5) {
            ratingCounts[review.rating - 1]++;
        }
    });
    
    for (let i = 0; i < 5; i++) {
        const percent = total > 0 ? (ratingCounts[i] / total) * 100 : 0;
        const barId = `bar${i + 1}`;
        const bar = document.getElementById(barId);
        if (bar) {
            bar.style.width = `${percent}%`;
        }
    }
}

// Function to display reviews in slider
function displayReviews() {
    const slider = document.getElementById('reviewSlider');
    if (!slider) return;
    
    slider.innerHTML = '';
    
    if (reviews.length === 0) {
        slider.innerHTML = '<div class="review-card"><p style="text-align:center; width:100%;">No reviews yet. Be the first to write a review!</p></div>';
        return;
    }
    
    reviews.forEach((review, index) => {
        const reviewCard = document.createElement('div');
        reviewCard.className = 'review-card';
        reviewCard.innerHTML = `
            <img src="https://i.pravatar.cc/50?img=${review.img || (index + 1)}" alt="${review.name}">
            <div style="flex:1">
                <h4>${escapeHtml(review.name)}</h4>
                <span>${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)} • ${escapeHtml(review.date)}</span>
                <p>${escapeHtml(review.text)}</p>
            </div>
        `;
        slider.appendChild(reviewCard);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Open popup
function openPopup() {
    const popup = document.getElementById('popup');
    if (popup) {
        popup.style.display = 'flex';
        selectedRating = 0;
        updateStarSelection();
        document.getElementById('name').value = '';
        document.getElementById('reviewText').value = '';
    }
}

// Close popup
function closePopup() {
    const popup = document.getElementById('popup');
    if (popup) {
        popup.style.display = 'none';
    }
}

// Set rating
function setRating(rating) {
    selectedRating = rating;
    updateStarSelection();
}

// Update star selection display
function updateStarSelection() {
    const stars = document.querySelectorAll('#starSelect span');
    stars.forEach((star, index) => {
        if (index < selectedRating) {
            star.classList.add('active');
            star.style.color = '#ffc107';
            star.innerHTML = '★';
        } else {
            star.classList.remove('active');
            star.style.color = '#ddd';
            star.innerHTML = '☆';
        }
    });
}

// Add new review
function addReview() {
    const name = document.getElementById('name').value.trim();
    const reviewText = document.getElementById('reviewText').value.trim();
    
    if (!name) {
        alert('Please enter your name');
        return;
    }
    
    if (!reviewText) {
        alert('Please write your review');
        return;
    }
    
    if (selectedRating === 0) {
        alert('Please select a rating');
        return;
    }
    
    // Get current date
    const now = new Date();
    const date = `${now.getDate()} ${now.toLocaleString('default', { month: 'short' })} ${now.getFullYear()}`;
    
    // Add new review
    const newReview = {
        name: name,
        rating: selectedRating,
        text: reviewText,
        date: date,
        img: reviews.length + 1
    };
    
    reviews.unshift(newReview);
    
    // Save to localStorage
    localStorage.setItem('microgreens_reviews', JSON.stringify(reviews));
    
    // Update display
    updateRatings();
    displayReviews();
    
    // Close popup
    closePopup();
    
    // Show success message
    alert('Thank you for your review!');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRatings();
    displayReviews();
    
    // Close popup on outside click
    const popup = document.getElementById('popup');
    if (popup) {
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                closePopup();
            }
        });
    }
    
    // ESC key to close popup
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });
});