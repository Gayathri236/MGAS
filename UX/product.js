// Product Data
    const products = {
        micro: [
            {
                name: "Sunflower Microgreens",
                description: "Nutty flavor, rich in vitamins B, D, and E. Perfect for salads and sandwiches.",
                features: ["Rich in Protein", "Vitamin E", "Nutty Flavor"],
                price: "Rs. 450 / tray",
                image: "../img/kk.jpg",
                badge: "Best Seller"
            },
            {
                name: "Pea Shoots",
                description: "Sweet and crunchy, packed with vitamin C and folate. Great for stir-fries and garnishes.",
                features: ["Sweet Flavor", "Vitamin C", "Crunchy"],
                price: "Rs. 400 / tray",
                image: "../img/Microgreen-Pea-shoots-Tendril-MICRO-1.jpg",
                badge: "Popular"
            },
            {
                name: "Radish Microgreens",
                description: "Spicy and peppery, rich in antioxidants. Adds a kick to any dish.",
                features: ["Spicy Flavor", "Antioxidants", "Quick Growth"],
                price: "Rs. 420 / tray",
                image: "../img/RADISH.JPG",
                badge: "New"
            },
            {
                name: "Broccoli Microgreens",
                description: "Mild flavor, packed with sulforaphane. Excellent for immune health.",
                features: ["Immune Boosting", "Mild Taste", "Nutrient Dense"],
                price: "Rs. 480 / tray",
                image: "../img/hfeegfhj.jpg",
                badge: "Premium"
            },
            {
                name: "Amaranth Microgreens",
                description: "Earthy flavor, vibrant red color. Rich in iron and calcium.",
                features: ["Iron Rich", "Vibrant Color", "Earthy Taste"],
                price: "Rs. 440 / tray",
                image: "../img/AMATH.jpg",
                badge: ""
            },
            {
                name: "Cilantro Microgreens",
                description: "Intense cilantro flavor. Perfect for Mexican and Asian cuisine.",
                features: ["Strong Flavor", "Aromatic", "Versatile"],
                price: "Rs. 460 / tray",
                image: "../img/Cillantro-CorianderMicrogreens-9529.jpg",
                badge: ""
            }
        ],
        lettuce: [
            {
                name: "Green Oak Leaf Lettuce",
                description: "Tender leaves with a mild, sweet flavor. Perfect for salads and sandwiches.",
                features: ["Sweet Flavor", "Tender Leaves", "High Yield"],
                price: "Rs. 350 / head",
                image: "../img/CAA705_New.jpg",
                badge: "Best Seller"
            },
            {
                name: "Red Coral Lettuce",
                description: "Beautiful red-tipped leaves with a mild, nutty flavor. Adds color to any dish.",
                features: ["Beautiful Color", "Nutty Taste", "Crunchy"],
                price: "Rs. 380 / head",
                image: "../img/istockphoto-1306506935-612x612.jpg",
                badge: ""
            },
            {
                name: "Butterhead Lettuce",
                description: "Soft, buttery texture with a sweet flavor. Ideal for wraps and gourmet salads.",
                features: ["Buttery Texture", "Sweet Taste", "Soft Leaves"],
                price: "Rs. 400 / head",
                image: "../img/Oak-Leaf-LettuceBC3__14823.jpg",
                badge: "Premium"
            },
            {
                name: "Romaine Lettuce",
                description: "Crisp, sturdy leaves with a slightly bitter taste. Great for Caesar salads.",
                features: ["Crisp Texture", "Sturdy Leaves", "Classic Taste"],
                price: "Rs. 360 / head",
                image: "../img/RED.JPG",
                badge: ""
            },
            {
                name: "Lollo Rosso Lettuce",
                description: "Frilly red leaves with a slightly bitter, nutty flavor. Decorative and tasty.",
                features: ["Decorative", "Nutty Flavor", "Frilly Leaves"],
                price: "Rs. 390 / head",
                image: "../img/GrilledRomaineMediterraneanSalad14753-681x1024.jpg",
                badge: ""
            },
            {
                name: "Iceberg Lettuce",
                description: "Crisp and refreshing, with a mild flavor. Perfect for burgers and sandwiches.",
                features: ["Extra Crisp", "Mild Taste", "Juicy"],
                price: "Rs. 320 / head",
                image: "../img/19-11-ICEBURG_TN.jpg",
                badge: ""
            }
        ],
        pepper: [
            {
                name: "Bell Pepper - Green",
                description: "Sweet and crunchy, rich in vitamin C. Perfect for salads and stir-fries.",
                features: ["Sweet Flavor", "Vitamin C Rich", "Crunchy"],
                price: "Rs. 280 / kg",
                image: "../img/Green-Bell-Peppers.jpg",
                badge: "Popular"
            },
            {
                name: "Bell Pepper - Red",
                description: "Sweetest variety, packed with antioxidants. Great for roasting and fresh eating.",
                features: ["Sweet Taste", "Antioxidants", "Versatile"],
                price: "Rs. 350 / kg",
                image: "../img/02309_01_red_knight.jpg",
                badge: "Best Seller"
            },
            {
                name: "Bell Pepper - Yellow",
                description: "Mild and sweet, adds vibrant color to any dish.",
                features: ["Mild Sweetness", "Vibrant Color", "Fresh Taste"],
                price: "Rs. 330 / kg",
                image: "../img/4754_01_svpb8415.jpg",
                badge: ""
            },
            {
                name: "Jalapeño Pepper",
                description: "Medium heat, great for salsas, pickling, and spicy dishes.",
                features: ["Medium Heat", "Versatile", "Flavorful"],
                price: "Rs. 400 / kg",
                image: "../img/JEP.jpg",
                badge: "Spicy"
            },
            {
                name: "Sweet Banana Pepper",
                description: "Mild and sweet, perfect for sandwiches and salads.",
                features: ["Mild Flavor", "Sweet Taste", "Crunchy"],
                price: "Rs. 380 / kg",
                image: "../img/MALU.jpg",
                badge: ""
            },
            {
                name: "Mini Sweet Peppers",
                description: "Bite-sized sweet peppers, perfect for snacking and appetizers.",
                features: ["Bite-sized", "Sweet Flavor", "Colorful"],
                price: "Rs. 450 / kg",
                image: "../img/sweet-mini-peppers.jpg",
                badge: "New"
            }
        ]
    };

    // Function to render products
    function renderProducts(category) {
        const container = document.getElementById(`${category}Grid`);
        const productList = products[category];
        
        if (!container || !productList) return;
        
        container.innerHTML = productList.map(product => `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                    ${product.badge ? `<span class="product-badge">${product.badge}</span>` : ''}
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-features">
                        ${product.features.map(feature => `<span class="feature-tag">✓ ${feature}</span>`).join('')}
                    </div>
                    <div class="product-price">${product.price}</div>
                    <button class="btn-order" onclick="orderProduct('${product.name}')">Order Now →</button>
                </div>
            </div>
        `).join('');
    }

    // Order function
    function orderProduct(productName) {
        alert(`Thank you for your interest in ${productName}!\n\nPlease contact us at 076 980 6155 or email inokaj1977@gmail.com to place your order.`);
    }

    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    const sections = document.querySelectorAll('.section');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.getAttribute('data-tab');
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Update active section
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Render all products on page load
    renderProducts('micro');
    renderProducts('lettuce');
    renderProducts('pepper');

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


// Order function - replaced browser alert with custom modal
function orderProduct(productName) {
    // Create modal container
    const modal = document.createElement('div');
    modal.className = 'order-modal';
    modal.innerHTML = `
        <div class="order-modal-content">
            <div class="order-modal-icon">🌱</div>
            <h3>Order Request</h3>
            <p>Thank you for your interest in <strong>${productName}</strong>!</p>
            <div class="order-contact-details">
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <span>076 980 6155</span>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <span>inokaj1977@gmail.com</span>
                </div>
            </div>
            <p class="order-note">Please contact us to place your order.</p>
            <button class="order-modal-close" onclick="this.closest('.order-modal').remove()">Got it →</button>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(modal);
    
    // Close when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    // Close with Escape key
    const closeHandler = function(e) {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', closeHandler);
        }
    };
    document.addEventListener('keydown', closeHandler);
}

