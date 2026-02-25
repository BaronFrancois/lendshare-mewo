/**
 * Script de gestion des contrôles d'administration
 * Gère l'affichage des outils admin et les opérations CRUD sur les produits
 */

let estAdminConnecte = false;
let allCategories = [];

// Au chargement de la page
document.addEventListener('DOMContentLoaded', async function() {
    await verifierStatusAdmin();
    await chargerCategoriesProduitsAjout();
});

/**
 * Vérifie le statut d'administration
 */
async function verifierStatusAdmin() {
    try {
        const reponse = await fetch('../backend/api/api_verifier_session.php');
        const donnees = await reponse.json();

        if (donnees.connecte && donnees.utilisateur.role_utilisateur === 'administrateur') {
            estAdminConnecte = true;
            afficherBarreAdmin();
            afficherBoutonsAdmin();
        } else {
            estAdminConnecte = false;
            cacherBarreAdmin();
            cacherBoutonsAdmin();
        }
    } catch (erreur) {
        console.error('Erreur vérification admin:', erreur);
        estAdminConnecte = false;
    }
}

/**
 * Affiche la barre d'administration
 */
function afficherBarreAdmin() {
    const barreAdmin = document.getElementById('barre-admin');
    if (barreAdmin) {
        barreAdmin.style.display = 'flex';
    }
}

/**
 * Cache la barre d'administration
 */
function cacherBarreAdmin() {
    const barreAdmin = document.getElementById('barre-admin');
    if (barreAdmin) {
        barreAdmin.style.display = 'none';
    }
}

/**
 * Affiche les boutons admin sur tous les produits
 */
function afficherBoutonsAdmin() {
    const tousLesBoutons = document.querySelectorAll('.boutons-admin-produit');
    tousLesBoutons.forEach(bouton => {
        bouton.style.display = 'flex';
    });
}

/**
 * Cache les boutons admin sur tous les produits
 */
function cacherBoutonsAdmin() {
    const tousLesBoutons = document.querySelectorAll('.boutons-admin-produit');
    tousLesBoutons.forEach(bouton => {
        bouton.style.display = 'none';
    });
}

/**
 * Charge les catégories pour le select du formulaire d'ajout
 */
async function chargerCategoriesProduitsAjout() {
    try {
        const reponse = await fetch('../backend/api/api_categories.php');
        const donnees = await reponse.json();

        if (donnees.succes && donnees.donnees) {
            allCategories = donnees.donnees;
            const selectCategories = document.getElementById('input-categorie-produit');

            allCategories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id_categorie;
                option.textContent = cat.nom_categorie;
                selectCategories.appendChild(option);
            });
        }
    } catch (erreur) {
        console.error('Erreur chargement catégories pour ajout:', erreur);
    }
}

/**
 * Ouvre la modal d'ajout de produit
 */
function ouvrirModalAjouter() {
    if (!estAdminConnecte) {
        alert('Vous devez être connecté comme administrateur');
        return;
    }

    // Réinitialiser le formulaire
    document.getElementById('form-ajouter-produit').reset();
    document.getElementById('modal-ajouter-produit').style.display = 'flex';
}

/**
 * Ferme la modal d'ajout de produit
 */
function fermerModalAjouter() {
    document.getElementById('modal-ajouter-produit').style.display = 'none';
}

/**
 * Ajoute un nouveau produit
 */
async function ajouterProduit(event) {
    event.preventDefault();

    if (!estAdminConnecte) {
        alert('Vous devez être connecté comme administrateur');
        return;
    }

    const nom = document.getElementById('input-nom-produit').value.trim();
    const description = document.getElementById('input-description-produit').value.trim();
    const categorie = parseInt(document.getElementById('input-categorie-produit').value);
    const dispo = parseInt(document.getElementById('input-dispo-produit').value);
    const total = parseInt(document.getElementById('input-total-produit').value);
    const vedette = document.getElementById('input-vedette-produit').checked ? 1 : 0;

    // Valider les données
    if (dispo > total) {
        alert('La quantité disponible ne peut pas être supérieure à la quantité totale');
        return;
    }

    const formData = new FormData();
    formData.append('nom_produit', nom);
    formData.append('description_produit', description);
    formData.append('id_categorie', categorie);
    formData.append('quantite_disponible', dispo);
    formData.append('quantite_totale', total);
    formData.append('est_vedette', vedette);

    // Gérer l'image si fournie
    const fichierImage = document.getElementById('input-image-produit').files[0];
    if (fichierImage) {
        const formDataImage = new FormData();
        formDataImage.append('image', fichierImage);
        formDataImage.append('type', 'produit');

        try {
            const reponseImage = await fetch('../backend/api/api_upload_image.php', {
                method: 'POST',
                body: formDataImage
            });

            const donneesImage = await reponseImage.json();

            if (donneesImage.succes) {
                formData.append('image_url_produit', donneesImage.donnees.url);
            } else {
                alert('Erreur lors de l\'upload de l\'image');
                return;
            }
        } catch (erreur) {
            alert('Erreur lors de l\'upload de l\'image');
            console.error(erreur);
            return;
        }
    }

    // Envoyer les données du produit
    try {
        const reponse = await fetch('../backend/api/api_ajouter_produit.php', {
            method: 'POST',
            body: formData
        });

        const donnees = await reponse.json();

        if (donnees.succes) {
            alert('Produit ajouté avec succès !');
            fermerModalAjouter();
            location.reload();
        } else {
            alert('Erreur: ' + donnees.message);
        }
    } catch (erreur) {
        alert('Erreur lors de l\'ajout du produit');
        console.error(erreur);
    }
}

/**
 * Ouvre la modal d'édition des quantités
 */
function ouvrirModalEditerQuantites(idProduit, dispo, total) {
    if (!estAdminConnecte) {
        alert('Vous devez être connecté comme administrateur');
        return;
    }

    document.getElementById('edit-id-produit').value = idProduit;
    document.getElementById('edit-dispo-produit').value = dispo;
    document.getElementById('edit-total-produit').value = total;
    document.getElementById('msg-validation-quantites').style.display = 'none';

    document.getElementById('modal-editer-quantites').style.display = 'flex';
}

/**
 * Ferme la modal d'édition des quantités
 */
function fermerModalEditerQuantites() {
    document.getElementById('modal-editer-quantites').style.display = 'none';
}

/**
 * Sauvegarde les quantités modifiées
 */
async function sauvegarderQuantites(event) {
    event.preventDefault();

    if (!estAdminConnecte) {
        alert('Vous devez être connecté comme administrateur');
        return;
    }

    const idProduit = parseInt(document.getElementById('edit-id-produit').value);
    const dispo = parseInt(document.getElementById('edit-dispo-produit').value);
    const total = parseInt(document.getElementById('edit-total-produit').value);
    const msgValidation = document.getElementById('msg-validation-quantites');

    // Valider
    if (dispo > total) {
        msgValidation.textContent = 'La quantité disponible ne peut pas être supérieure à la quantité totale';
        msgValidation.style.display = 'block';
        return;
    }

    try {
        const reponse = await fetch('../backend/api/api_update_quantites.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_produit: idProduit,
                quantite_disponible: dispo,
                quantite_totale: total
            })
        });

        const donnees = await reponse.json();

        if (donnees.succes) {
            alert('Quantités mises à jour avec succès !');
            fermerModalEditerQuantites();
            location.reload();
        } else {
            msgValidation.textContent = 'Erreur: ' + donnees.message;
            msgValidation.style.display = 'block';
        }
    } catch (erreur) {
        msgValidation.textContent = 'Erreur lors de la sauvegarde';
        msgValidation.style.display = 'block';
        console.error(erreur);
    }
}

/**
 * Supprime un produit
 */
async function supprimerProduit(idProduit) {
    if (!estAdminConnecte) {
        alert('Vous devez être connecté comme administrateur');
        return;
    }

    if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        return;
    }

    try {
        const reponse = await fetch('../backend/api/api_supprimer_produit.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_produit: idProduit
            })
        });

        const donnees = await reponse.json();

        if (donnees.succes) {
            alert('Produit supprimé avec succès !');
            location.reload();
        } else {
            alert('Erreur: ' + donnees.message);
        }
    } catch (erreur) {
        alert('Erreur lors de la suppression');
        console.error(erreur);
    }
}

