/*Attache les événements pour la gestion du panier
function attachCartEvents() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
            const productId = parseInt(this.getAttribute('data-id'));
            addToCart(productId);
        });
    });
}

// Charger et synchroniser le panier depuis la session serveur
function loadCartFromServer() {
    fetch('/panier', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.json())
        .then(data => {
            updateCartCount(data.cartItemCount);  // Mettre à jour le badge avec le nombre correct d'articles
            updateAddToCartButtons(data.cartItems);  // Désactiver les boutons pour les produits déjà dans le panier
        })
        .catch(error => console.error('Erreur de chargement du panier:', error));
}

// Mettre à jour l'état des boutons "Ajouter"
function updateAddToCartButtons(cartItems) {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        const productId = parseInt(button.getAttribute('data-id'));
        const productInCart = cartItems.some(item => item.id === productId);

        if (productInCart) {
            button.textContent = 'Ajouté';
            button.classList.add('btn-secondary');
            button.classList.remove('btn-success');
            button.disabled = true;
        } else {
            button.textContent = 'Ajouter';
            button.classList.add('btn-success');
            button.classList.remove('btn-secondary');
            button.disabled = false;
        }
    });
}

// Ajouter un produit au panier
function addToCart(productId) {
    fetch(`/panier/ajouter/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Erreur:', data.error);
            } else {
                updateCartCount(data.cartItemCount);  // Mettre à jour le badge avec le nombre correct d'articles
                loadCartFromServer();  // Recharger l'état du panier et mettre à jour les boutons
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Mettre à jour l'affichage du nombre d'articles dans le panier
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

// Mettre à jour les quantités et les prix des produits dans le panier
function attachQuantityChangeEvents() {
    document.querySelectorAll('.quantity-input').forEach(function(select) {
        select.addEventListener('change', function() {
            const itemId = this.getAttribute('data-id'); // ID de l'article
            const newQuantity = parseInt(this.value); // Nouvelle quantité sélectionnée
            const priceElement = document.getElementById('price-' + itemId); // Élément contenant le prix
            const basePrice = parseFloat(priceElement.dataset.basePrice); // Le prix de base stocké dans 'data-base-price'

            // Calculer le nouveau prix total pour cet article
            const newTotalPrice = basePrice * newQuantity;

            // Mettre à jour l'affichage du prix
            priceElement.textContent = newTotalPrice.toFixed(2) + ' €';

            // Mettre à jour les données du résumé total
            updateTotalPrice();
        });
    });
}

// Fonction pour mettre à jour le prix total dans le résumé
function updateTotalPrice() {
    let totalProducts = 0;

    // Calculer le prix total des produits
    document.querySelectorAll('.quantity-input').forEach(function(select) {
        const itemId = select.getAttribute('data-id');
        const quantity = parseInt(select.value);
        const priceElement = document.getElementById('price-' + itemId);
        const basePrice = parseFloat(priceElement.dataset.basePrice);
        const itemTotal = basePrice * quantity;
        totalProducts += itemTotal;
    });

    // Afficher le prix total des produits
    document.getElementById('total-products').textContent = totalProducts.toFixed(2) + ' €';

    // Calculer le coût total avec la livraison
    const shippingCost = parseFloat(document.getElementById('shipping-cost').textContent);
    const totalPrice = totalProducts + shippingCost;

    // Mettre à jour l'affichage du prix total
    document.getElementById('total-price').textContent = totalPrice.toFixed(2) + ' €';
}

// Attacher les événements pour la gestion des produits par catégorie
function attachCategoryEvents() {
    const categoryLinks = document.querySelectorAll('#category-sidebar a');

    categoryLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Get the URL for the category products
            const filterUrl = this.href;

            // AJAX request to fetch the filtered products
            fetch(filterUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    // Update the product list section with the new content
                    document.getElementById('product-list').innerHTML = html;

                    // Réattacher les événements aux nouveaux produits
                    attachCartEvents();
                })
                .catch(error => console.error('Erreur:', error));
        });
    });
}

// Attacher les événements pour le profil
function attachProfileEvents() {
    const sidebarLinks = document.querySelectorAll('#profile-sidebar a');
    const profileContent = document.getElementById('profile-content');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // Empêche le comportement par défaut de changement de page
            const url = this.getAttribute('href');

            // Ajoute la classe active pour le lien sélectionné
            sidebarLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Effectue la requête AJAX pour charger le contenu
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Indique que c'est une requête AJAX
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors du chargement du contenu');
                    }
                    return response.text();
                })
                .then(html => {
                    profileContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        });
    });

    // Charger la première section automatiquement au démarrage (ex: Infos générales)
    document.querySelector('#profile-sidebar a.active').click();
}

// Gestion des événements avec Turbo pour remplacer DOMContentLoaded
document.addEventListener('turbo:load', function () {
    // Charger le panier et attacher les événements
    attachCartEvents();
    loadCartFromServer();

    // Attacher les événements pour les quantités
    attachQuantityChangeEvents();

    // Attacher les événements pour les catégories
    attachCategoryEvents();

    // Attacher les événements pour le profil
    attachProfileEvents();
});*/
