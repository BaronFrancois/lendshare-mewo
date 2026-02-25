document.addEventListener('DOMContentLoaded', function() {
    chargerDerniersConsultes();
});

function chargerDerniersConsultes() {
    // Récupérer les IDs des derniers produits consultés depuis le localStorage
    const derniersConsultes = JSON.parse(localStorage.getItem('derniersProduitsConsultes') || '[]');
    
    if (derniersConsultes.length === 0) {
        // Si aucun produit consulté, afficher les produits vedettes par défaut
        fetch('backend/api/api_produits.php?vedettes=1')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.produits && data.produits.length > 0) {
                    afficherProduitsVedettes(data.produits.slice(0, 5));
                } else {
                    document.getElementById('grille-vedettes').innerHTML = 
                        '<p style="grid-column: 1 / -1; text-align: center; color: #999;">Consultez des produits pour les voir apparaître ici</p>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('grille-vedettes').innerHTML = 
                    '<p style="grid-column: 1 / -1; text-align: center; color: #999;">Erreur de chargement</p>';
            });
    } else {
        // Charger les détails des produits consultés
        const ids = derniersConsultes.slice(0, 5).join(',');
        fetch(`backend/api/api_produits.php?ids=${ids}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.produits && data.produits.length > 0) {
                    afficherProduitsVedettes(data.produits);
                } else {
                    document.getElementById('grille-vedettes').innerHTML = 
                        '<p style="grid-column: 1 / -1; text-align: center; color: #999;">Aucun produit récent</p>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }
}

function afficherProduitsVedettes(produits) {
    const grille = document.getElementById('grille-vedettes');
    grille.innerHTML = '';
    
    produits.forEach(produit => {
        const card = document.createElement('div');
        card.className = 'carte-produit';
        
        // Vérifier la disponibilité
        const estDisponible = produit.quantite_disponible > 0;
        const quantiteDisponible = produit.quantite_disponible || 0;
        
        // Ajouter la classe de disponibilité
        if (estDisponible) {
            card.classList.add('produit-disponible');
        } else {
            card.classList.add('produit-indisponible');
        }
        card.style.cursor = 'pointer';
        
        // Badge de disponibilité
        const badgeDisponibilite = estDisponible 
            ? `<span class="badge-disponibilite disponible">✓ Disponible (${quantiteDisponible})</span>`
            : '<span class="badge-disponibilite indisponible">✗ Indisponible</span>';
        
        // Utiliser un conteneur d'image pour uniformiser les formats
        const imageHTML = produit.image_url_produit 
            ? `<div class="carte-produit-image-container"><img src="${produit.image_url_produit}" alt="${produit.nom_produit}"></div>`
            : `<div class="carte-produit-image-container" style="background: #f5f5f5;"></div>`;
        
        card.innerHTML = `
            ${imageHTML}
            ${badgeDisponibilite}
            <div class="info-produit">
                <h3>${produit.nom_produit}</h3>
                <button class="bouton-voir-produit">Voir produit</button>
            </div>
        `;
        
        // Permettre le clic pour tous les produits - Ouvrir la modale
        card.addEventListener('click', async () => {
            try {
                const reponse = await fetch(`backend/api/api_produits.php?id=${produit.id_produit}`);
                const donnees = await reponse.json();
                
                if (donnees.success && donnees.produit) {
                    produit_actuel = donnees.produit;
                    
                    // Enregistrer dans les derniers consultés
                    enregistrerProduitConsulte(produit.id_produit);
                    
                    afficherModalProduit(produit_actuel);
                } else {
                    alert('Impossible de charger les détails du produit');
                }
            } catch (erreur) {
                console.error('Erreur:', erreur);
                alert('Erreur de connexion au serveur');
            }
        });
        
        grille.appendChild(card);
    });
}
