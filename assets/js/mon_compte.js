document.addEventListener('DOMContentLoaded', async function() {
    await initialiserPageMonCompte();
});

async function initialiserPageMonCompte() {
    try {
        const reponse = await fetch('../backend/api/api_verifier_session.php');
        const donnees = await reponse.json();
        
        if (!donnees.connecte) {
            window.location.href = 'connexion.html';
            return;
        }
        
        const utilisateur = donnees.utilisateur;
        document.getElementById('zone-chargement').style.display = 'none';
        
        if (utilisateur.role_utilisateur === 'administrateur') {
            afficherVueAdministrateur();
        } else {
            afficherVueUtilisateur(utilisateur);
        }
        
    } catch (erreur) {
        console.error('Erreur:', erreur);
        document.getElementById('zone-chargement').innerHTML = '<p>Erreur de chargement</p>';
    }
}

async function afficherVueAdministrateur() {
    const vueAdmin = document.getElementById('vue-administrateur');
    vueAdmin.style.display = 'block';
    
    await chargerReservationsAdmin();
}

async function chargerReservationsAdmin() {
    try {
        const reponse = await fetch('../backend/api/api_reservations.php');
        const donnees = await reponse.json();
        
        const conteneur = document.getElementById('liste-reservations-admin');
        
        if (donnees.success && donnees.reservations && donnees.reservations.length > 0) {
            conteneur.innerHTML = '';
            donnees.reservations.forEach(reservation => {
                conteneur.appendChild(creerCarteReservationAdmin(reservation));
            });
        } else {
            conteneur.innerHTML = '<div class="message-vide">Aucune réservation pour le moment</div>';
        }
        
    } catch (erreur) {
        console.error('Erreur:', erreur);
        document.getElementById('liste-reservations-admin').innerHTML = 
            '<div class="message-vide">Erreur lors du chargement des réservations</div>';
    }
}

function creerCarteReservationAdmin(reservation) {
    const card = document.createElement('div');
    card.className = 'reservation-card';
    
    const nomComplet = `${reservation.prenom_utilisateur || ''} ${reservation.nom_utilisateur || ''}`.trim();
    const dateFormatee = new Date(reservation.date_reservation).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    card.innerHTML = `
        <div class="reservation-infos">
            <div class="reservation-produit">${reservation.nom_produit}</div>
            <div class="reservation-client">Client : ${nomComplet || reservation.email_utilisateur}</div>
            <div class="reservation-date">Réservé le ${dateFormatee}</div>
        </div>
        <div class="reservation-statut">
            <select class="selecteur-statut" onchange="changerStatutReservation(${reservation.id_reservation}, this.value)">
                <option value="en_attente" ${reservation.statut_reservation === 'en_attente' ? 'selected' : ''}>En attente</option>
                <option value="accepte" ${reservation.statut_reservation === 'accepte' ? 'selected' : ''}>Accepté</option>
                <option value="refuse" ${reservation.statut_reservation === 'refuse' ? 'selected' : ''}>Refusé</option>
                <option value="cloture" ${reservation.statut_reservation === 'cloture' ? 'selected' : ''}>Clôturé</option>
            </select>
        </div>
    `;
    
    return card;
}

async function changerStatutReservation(idReservation, nouveauStatut) {
    try {
        const reponse = await fetch('../backend/api/api_reservations.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_reservation: idReservation,
                statut_reservation: nouveauStatut
            })
        });
        
        const donnees = await reponse.json();
        
        if (donnees.success) {
            await chargerReservationsAdmin();
        } else {
            alert('Erreur lors de la modification du statut');
        }
        
    } catch (erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion');
    }
}

async function afficherVueUtilisateur(utilisateur) {
    const vueUtilisateur = document.getElementById('vue-utilisateur');
    vueUtilisateur.style.display = 'block';
    
    afficherInfosUtilisateur(utilisateur);
    await chargerReservationsUtilisateur(utilisateur.id_utilisateur);
}

function afficherInfosUtilisateur(utilisateur) {
    const conteneur = document.getElementById('infos-utilisateur');
    
    conteneur.innerHTML = `
        <div class="info-item">
            <span class="info-label">Email :</span>
            <span class="info-value">${utilisateur.email_utilisateur}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Prénom :</span>
            <span class="info-value">${utilisateur.prenom_utilisateur || 'Non renseigné'}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Nom :</span>
            <span class="info-value">${utilisateur.nom_utilisateur || 'Non renseigné'}</span>
        </div>
    `;
}

async function chargerReservationsUtilisateur(idUtilisateur) {
    try {
        const reponse = await fetch(`../backend/api/api_reservations.php?id_utilisateur=${idUtilisateur}`);
        const donnees = await reponse.json();
        
        const conteneur = document.getElementById('liste-reservations-utilisateur');
        
        if (donnees.success && donnees.reservations && donnees.reservations.length > 0) {
            conteneur.innerHTML = '';
            donnees.reservations.forEach(reservation => {
                conteneur.appendChild(creerCarteReservationUtilisateur(reservation));
            });
        } else {
            conteneur.innerHTML = '<div class="message-vide">Vous n\'avez pas encore de réservation</div>';
        }
        
    } catch (erreur) {
        console.error('Erreur:', erreur);
        document.getElementById('liste-reservations-utilisateur').innerHTML = 
            '<div class="message-vide">Erreur lors du chargement de l\'historique</div>';
    }
}

function creerCarteReservationUtilisateur(reservation) {
    const card = document.createElement('div');
    card.className = 'reservation-card';
    
    const dateFormatee = new Date(reservation.date_reservation).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    const statutLabels = {
        'en_attente': 'En attente',
        'accepte': 'Accepté',
        'refuse': 'Refusé',
        'cloture': 'Clôturé'
    };
    
    card.innerHTML = `
        <div class="reservation-infos">
            <div class="reservation-produit">${reservation.nom_produit}</div>
            <div class="reservation-date">Réservé le ${dateFormatee}</div>
        </div>
        <div class="reservation-statut">
            <span class="badge-statut statut-${reservation.statut_reservation}">
                ${statutLabels[reservation.statut_reservation] || 'En attente'}
            </span>
        </div>
    `;
    
    return card;
}
