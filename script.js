document.addEventListener("DOMContentLoaded", function () {
    let servicesSection = document.getElementById("services");
    let brandSelect = document.getElementById("brand");
    let modelSelect = document.getElementById("model");
    let servicesContainer = document.getElementById("services-container");
    let showServicesBtn = document.getElementById("showServicesBtn");
    let productsSection = document.getElementById("products");
    let productsContainer = document.getElementById("products-container");
    let sessionsSection = document.getElementById("sessions");
    let sessionsContainer = document.getElementById("sessions-container");
    let cartContainer = document.getElementById("cart-container");
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Функція для безпечного отримання елементів
    function getElementSafe(id) {
        return document.getElementById(id) || null;
    }

    // Функція для перемикання видимості секцій
    function toggleSection(section) {
        if (!section) return;
        section.style.display = section.style.display === "none" ? "block" : "none";
    }

    window.toggleServices = function () {
        toggleSection(servicesSection);
        if (productsSection) productsSection.style.display = 'none';
        if (sessionsSection) sessionsSection.style.display = 'none';
    };

    window.toggleProducts = function () {
        toggleSection(productsSection);
        if (servicesSection) servicesSection.style.display = 'none';
        if (sessionsSection) sessionsSection.style.display = 'none';
        if (productsSection && productsSection.style.display === "block") loadProducts();
    };

    window.toggleSessions = function () {
        toggleSection(sessionsSection);
        if (servicesSection) servicesSection.style.display = 'none';
        if (productsSection) productsSection.style.display = 'none';
    };

    // Завантаження послуг для обраної марки та моделі
    function loadServices() {
        const brand = document.getElementById('brand').value;
        const model = document.getElementById('model').value;
        if (!brand || !model) return;

        fetch(`index.php?action=get_services&brand=${brand}&model=${model}`)
            .then(response => response.json())
            .then(services => {
                servicesContainer.innerHTML = ''; // Очищаємо контейнер
                services.forEach(service => {
                    const div = document.createElement('div');
                    div.textContent = `${service.name} - ${service.price} грн`;
                    servicesContainer.appendChild(div);
                });
            })
            .catch(error => console.error("Помилка завантаження послуг:", error));
    }

    function loadProducts() {
        fetch("index.php?action=get_products")
            .then(response => response.json())
            .then(products => {
                if (!productsContainer) return;
                productsContainer.innerHTML = "";
                if (products.length === 0) {
                    productsContainer.innerHTML = "<p>Немає доступних товарів.</p>";
                    return;
                }
                products.forEach(product => {
                    let productCard = document.createElement("div");
                    productCard.classList.add("product-card");
                    productCard.innerHTML = `
                        <img src="img/${product.image_url}" alt="${product.name}" class="product-image">
                        <h3>${product.name}</h3>
                        <p>${product.description}</p>
                        <p><strong>Ціна:</strong> ${product.price} грн</p>
                        <p><strong>В наявності:</strong> ${product.stock_quantity}</p>
                        <button class="add-to-cart" onclick="addToCart(${product.id}, '${product.name}', ${product.price}, 'img/${product.image_url}', ${product.stock_quantity})">Додати в кошик</button>
                    `;
                    productsContainer.appendChild(productCard);
                });
            })
            .catch(error => console.error("Помилка завантаження товарів:", error));
    }

    if (brandSelect) {
        brandSelect.addEventListener("change", function () {
            let brand = brandSelect.value;
            if (!modelSelect || !showServicesBtn) return;
            modelSelect.innerHTML = "<option value=''>Виберіть модель</option>";
            modelSelect.disabled = true;
            showServicesBtn.disabled = true;
            if (brand) {
                fetch(`index.php?action=get_models&brand=${brand}`)
                    .then(response => response.json())
                    .then(models => {
                        models.forEach(model => {
                            let option = document.createElement("option");
                            option.value = model;
                            option.textContent = model;
                            modelSelect.appendChild(option);
                        });
                        modelSelect.disabled = false;
                        showServicesBtn.disabled = false;
                    })
                    .catch(error => console.error("Помилка завантаження моделей:", error));
            }
        });
    }

    if (showServicesBtn) {
        showServicesBtn.addEventListener("click", function () {
            let brand = brandSelect.value;
            let model = modelSelect.value;
            if (!brand || !model) {
                alert("Будь ласка, оберіть марку та модель!");
                return;
            }
            loadServices(); // Завантажуємо послуги після натискання кнопки
        });
    }

    window.addToCart = function (productId, productName, productPrice, productImage, stockQuantity) {
        let quantity = prompt(`Виберіть кількість (максимум ${stockQuantity}):`);
        quantity = parseInt(quantity);

        if (isNaN(quantity) || quantity <= 0 || quantity > stockQuantity) {
            alert("Введена кількість неправильна або перевищує наявність на складі.");
            return;
        }

        let cartItem = {
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            quantity: quantity
        };

        let existingProduct = cart.find(item => item.id === productId);
        if (existingProduct) {
            existingProduct.quantity += quantity;
        } else {
            cart.push(cartItem);
        }

        localStorage.setItem("cart", JSON.stringify(cart));
        showCart();
    };

    window.showCart = function () {
        if (!cartContainer) return;
        cartContainer.innerHTML = "";
        if (cart.length === 0) {
            cartContainer.innerHTML = "<p>Ваш кошик порожній</p>";
        } else {
            cart.forEach(item => {
                let productDiv = document.createElement("div");
                productDiv.classList.add("cart-item");
                productDiv.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" width="50">
                    <p>${item.name} - ${item.quantity} x ${item.price} грн</p>
                    <button onclick="removeFromCart(${item.id})">Видалити</button>
                `;
                cartContainer.appendChild(productDiv);
            });
        }
    };

    window.removeFromCart = function (productId) {
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem("cart", JSON.stringify(cart));
        showCart();
    };

    showCart(); // Виводимо кошик при завантаженні сторінки
});
