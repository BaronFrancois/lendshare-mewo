/**
 * Script JavaScript pour la gestion des réservations (admin)
 */

// Variables globales
let toutes_les_reservations = [];
let reservation_selectionnee = null;

document.addEventListener('DOMContentLoaded', function() {
    initialiserPageReservations();
});

/**
 * Initialise la page de gestion des réservations
 */
async function initialiserPageReservations() {
    await chargerToutesLesReservations();
    initialiserEvenements();
    calculerStatistiques();
}

/**
 * Charge toutes les réservations
 */
async function chargerToutesLesReservations() {
    try {
        const reponse = await fetch('../../backend/api/api_reservations.php?toutes');
        const donnees = await reponse.json();
        
        if (donnees.succes && donnees.donnees) {
            toutes_les_reservations = donnees.donnees;
            afficherTableauReservations(toutes_les_reservations);
        }
    } catch (erreur) {
        console.error('Erreur lors du chargement des réservations:', erreur);
        afficherMessage('Erreur lors du chargement', 'erreur');
    }
}

/**
 * Affiche le tableau des réservations
 */
function afficherTableauReservations(reservations) {
    const conteneur = document.getElementById('conteneur-tableau-reservations');
    if (!conteneur) return;
    
    if (reservations.length === 0) {
        conteneur.innerHTML = '<p class="message-vide">Aucune réservation trouvée</p>';
        return;
    }
    
    let html = `
        <table class="tableau-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    reservations.forEach(resa => {
        const date = new Date(resa.date_reservation);
        const date_formatee = date.toLocaleDateString('fr-FR');
        
        html += `
            <tr>
                <td><strong>#${resa.id_reservation}</strong></td>
                <td>${escapeHtml(resa.prenom_utilisateur)} ${escapeHtml(resa.nom_utilisateur)}<br>
                    <small style="color: #999;">${escapeHtml(resa.email_utilisateur)}</small>
                </td>
                <td>${escapeHtml(resa.nom_produit)}</td>
                <td>${resa.quantite_reservee}</td>
                <td>${date_formatee}</td>
                <td>
                    <select 
                        class="select-statut-inline" 
                        onchange="changerStatut(${resa.id_reservation}, this.value)"
                        data-statut-actuel="${resa.statut_reservation}"
                    >
                        <option value="en_attente" ${resa.statut_reservation === 'en_attente' ? 'selected' : ''}>En attente</option>
                        <option value="confirmee" ${resa.statut_reservation === 'confirmee' ? 'selected' : ''}>Confirmée</option>
                        <option value="recuperee" ${resa.statut_reservation === 'recuperee' ? 'selected' : ''}>Récupérée</option>
                        <option value="rendue" ${resa.statut_reservation === 'rendue' ? 'selected' : ''}>Rendue</option>
                        <option value="annulee" ${resa.statut_reservation === 'annulee' ? 'selected' : ''}>Annulée</option>
                    </select>
                </td>
                <td class="actions-tableau">
                    <button 
                        class="bouton-action-petit bouton-modifier" 
                        onclick="afficherDetails(${resa.id_reservation})"
                        title="Voir détails"
                    >
                        👁️
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    conteneur.innerHTML = html;
}

/**
 * Initialise les événements
 */
function initialiserEvenements() {
    // Recherche
    const champ_recherche = document.getElementById('recherche-reservations');
    if (champ_recherche) {
        champ_recherche.addEventListener('input', filtrerReservations);
    }
    
    // Filtre statut
    const filtre_statut = document.getElementById('filtre-statut');
    if (filtre_statut) {
        filtre_statut.addEventListener('change', filtrerReservations);
    }
}

/**
 * Change le statut d'une réservation
 */
async function changerStatut(id_reservation, nouveau_statut) {
    const select = event.target;
    const ancien_statut = select.dataset.statutActuel;
    
    if (ancien_statut === nouveau_statut) return;
    
    if (!confirm(`Changer le statut de la réservation #${id_reservation} ?`)) {
        select.value = ancien_statut;
        return;
    }
    
    try {
        const reponse = await fetch('../../backend/api/api_reservations.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_reservation: id_reservation,
                statut: nouveau_statut
            })
        });
        
        const donnees = await reponse.json();
        
        if (donnees.succes) {
            select.dataset.statutActuel = nouveau_statut;
            await chargerToutesLesReservations();
            calculerStatistiques();
        } else {
            alert(donnees.message);
            select.value = ancien_statut;
        }
    } catch (erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur lors du changement de statut');
        select.value = ancien_statut;
    }
}

/**
 * Affiche les détails d'une réservation
 */
async function afficherDetails(id_reservation) {
    try {
        const reponse = await fetch(`../../backend/api/api_reservations.php?id=${id_reservation}`);
        const donnees = await reponse.json();
        
        if (donnees.succes && donnees.donnees) {
            reservation_selectionnee = donnees.donnees;
            afficherModalDetails(reservation_selectionnee);
        }
    } catch (erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur lors du chargement des détails');
    }
}

/**
 * Affiche le modal de détails
 */
function afficherModalDetails(resa) {
    const conteneur = document.getElementById('contenu-details-reservation');
    
    const date_resa = new Date(resa.date_reservation).toLocaleString('fr-FR');
    const date_recup = resa.date_recuperation ? new Date(resa.date_recuperation).toLocaleString('fr-FR') : '-';
    const date_retour = resa.date_retour ? new Date(resa.date_retour).toLocaleString('fr-FR') : '-';
    
    conteneur.innerHTML = `
        <div class="info-groupe">
            <h3>📋 Informations générales</h3>
            <div class="info-ligne">
                <span class="info-label">ID Réservation :</span>
                <span class="info-valeur">#${resa.id_reservation}</span>
            </div>
            <div class="info-ligne">
                <span class="info-label">Date de réservation :</span>
                <span class="info-valeur">${date_resa}</span>
            </div>
            <div class="info-ligne">
                <span class="info-label">Statut :</span>
                <span class="info-valeur"><span class="badge-statut badge-${resa.statut_reservation}">${formaterStatut(resa.statut_reservation)}</span></span>
            </div>
        </div>
        
        <div class="info-groupe">
            <h3>👤 Utilisateur</h3>
            <div class="info-ligne">
                <span class="info-label">Nom :</span>
                <span class="info-valeur">${escapeHtml(resa.prenom_utilisateur)} ${escapeHtml(resa.nom_utilisateur)}</span>
            </div>
            <div class="info-ligne">
                <span class="info-label">Email :</span>
                <span class="info-valeur">${escapeHtml(resa.email_utilisateur)}</span>
            </div>
        </div>
        
        <div class="info-groupe">
            <h3>📦 Produit</h3>
            <div class="info-ligne">
                <span class="info-label">Nom :</span>
                <span class="info-valeur">${escapeHtml(resa.nom_produit)}</span>
            </div>
            <div class="info-ligne">
                <span class="info-label">Quantité réservée :</span>
                <span class="info-valeur">${resa.quantite_reservee}</span>
            </div>
        </div>
        
        <div class="info-groupe">
            <h3>📅 Dates</h3>
            <div class="info-ligne">
                <span class="info-label">Date de récupération :</span>
                <span class="info-valeur">${date_recup}</span>
            </div>
            <div class="info-ligne">
                <span class="info-label">Date de retour :</span>
                <span class="info-valeur">${date_retour}</span>
            </div>
        </div>
        
        ${resa.notes_reservation ? `
        <div class="info-groupe">
            <h3>📝 Notes</h3>
            <p>${escapeHtml(resa.notes_reservation)}</p>
        </div>
        ` : ''}
    `;
    
    document.getElementById('modal-details-reservation').style.display = 'flex';
}

/**
 * Ferme le modal de détails
 */
function fermerModalDetails() {
    document.getElementById('modal-details-reservation').style.display = 'none';
    reservation_selectionnee = null;
}

/**
 * Filtre les réservations
 */
function filtrerReservations() {
    const recherche = document.getElementById('recherche-reservations').value.toLowerCase();
    const statut_filtre = document.getElementById('filtre-statut').value;
    
    let reservations_filtrees = toutes_les_reservations;
    
    // Filtre par recherche
    if (recherche) {
        reservations_filtrees = reservations_filtrees.filter(r => 
            r.nom_produit.toLowerCase().includes(recherche) ||
            r.prenom_utilisateur.toLowerCase().includes(recherche) ||
            r.nom_utilisateur.toLowerCase().includes(recherche) ||
            r.email_utilisateur.toLowerCase().includes(recherche) ||
            r.id_reservation.toString().includes(recherche)
        );
    }
    
    // Filtre par statut
    if (statut_filtre) {
        reservations_filtrees = reservations_filtrees.filter(r => r.statut_reservation === statut_filtre);
    }
    
    afficherTableauReservations(reservations_filtrees);
}

/**
 * Calcule et affiche les statistiques
 */
function calculerStatistiques() {
    const stats = {
        en_attente: 0,
        confirmee: 0,
        recuperee: 0,
        rendue: 0
    };
    
    toutes_les_reservations.forEach(r => {
        if (stats.hasOwnProperty(r.statut_reservation)) {
            stats[r.statut_reservation]++;
        }
    });
    
    document.getElementById('stat-en-attente').textContent = stats.en_attente;
    document.getElementById('stat-confirmees').textContent = stats.confirmee;
    document.getElementById('stat-en-cours').textContent = stats.recuperee;
    document.getElementById('stat-rendues').textContent = stats.rendue;
}

/**
 * Formate un statut
 */
function formaterStatut(statut) {
    const statuts = {
        'en_attente': 'En attente',
        'confirmee': 'Confirmée',
        'recuperee': 'Récupérée',
        'rendue': 'Rendue',
        'annulee': 'Annulée'
    };
    return statuts[statut] || statut;
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(texte) {
    const div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}
