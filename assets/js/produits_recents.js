/**
 * Gestion des produits récemment consultés
 */

/**
 * Ajoute un produit aux produits récents
 * @param {Object} produit - Le produit à ajouter
 */
function ajouterProduitRecent(produit) {
    let produits_recents = obtenirProduitsRecents();
    
    // Retirer le produit s'il existe déjà
    produits_recents = produits_recents.filter(p => p.id_produit !== produit.id_produit);
    
    // Ajouter en première position
    produits_recents.unshift(produit);
    
    // Limiter à 6 produits
    if (produits_recents.length > 6) {
        produits_recents = produits_recents.slice(0, 6);
    }
    
    // Sauvegarder dans localStorage
    localStorage.setItem('produits_recents', JSON.stringify(produits_recents));
}

/**
 * Récupère les produits récents depuis localStorage
 * @returns {Array} Liste des produits récents
 */
function obtenirProduitsRecents() {
    const produits_json = localStorage.getItem('produits_recents');
    return produits_json ? JSON.parse(produits_json) : [];
}

/**
 * Affiche les produits récents sur la page d'accueil
 */
function afficherProduitsRecents() {
    const conteneur = document.getElementById('grille-recents');
    if (!conteneur) return;
    
    const produits_recents = obtenirProduitsRecents();
    
    if (produits_recents.length === 0) {
        conteneur.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; color: #999;">Consultez des produits pour les voir apparaître ici</p>';
        return;
    }
    
    let html = '';
    
    produits_recents.forEach(produit => {
        const image_url = produit.image_url_produit || '../assets/images/default-product.png';
        
        html += `
            <a href="pages/categories.php?produit=${produit.id_produit}" class="tile-categorie tile-produit-recent">
                <img src="${image_url}" alt="${escapeHtml(produit.nom_produit)}" onerror="this.src='../assets/images/default-product.png'">
                <h4>${escapeHtml(produit.nom_produit)}</h4>
            </a>
        `;
    });
    
    conteneur.innerHTML = html;
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(texte) {
    const div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

// Charger les produits récents au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', afficherProduitsRecents);
} else {
    afficherProduitsRecents();
}
