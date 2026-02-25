/**
 * Script JavaScript pour la page de connexion
 * Gère la validation du formulaire et l'envoi des données à l'API
 */

// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    initialiserPageConnexion();
    verifierSiDejaConnecte();
});

/**
 * Initialise tous les événements de la page de connexion
 */
function initialiserPageConnexion() {
    // Récupérer les éléments du formulaire
    const formulaireConnexion = document.getElementById('formulaire-connexion');
    
    // Ajouter l'écouteur sur la soumission du formulaire
    formulaireConnexion.addEventListener('submit', gererSoumissionFormulaireConnexion);
}

/**
 * Vérifie si l'utilisateur est déjà connecté et redirige si nécessaire
 */
async function verifierSiDejaConnecte() {
    try {
        const reponse = await fetch('../backend/api/api_verifier_session.php');
        const donnees = await reponse.json();
        
        if (donnees.connecte) {
            // Utilisateur déjà connecté, rediriger vers l'accueil
            window.location.href = '../index.html';
        }
    } catch (erreur) {
        // Erreur silencieuse, l'utilisateur peut continuer
        console.log('Impossible de vérifier la session:', erreur);
    }
}

/**
 * Gère la soumission du formulaire de connexion
 * @param {Event} evenement - L'événement de soumission
 */
function gererSoumissionFormulaireConnexion(evenement) {
    // Empêcher le rechargement de la page
    evenement.preventDefault();
    
    // Réinitialiser les messages d'erreur
    document.getElementById('erreur-email').textContent = '';
    document.getElementById('erreur-mot-de-passe').textContent = '';
    
    // Récupérer les valeurs des champs
    const emailUtilisateur = document.getElementById('champ-email').value.trim();
    const motDePasse = document.getElementById('champ-mot-de-passe').value;
    
    // Validation basique côté client
    let formulaireValide = true;
    
    if (emailUtilisateur === '') {
        afficherErreurChamp('email', "L'email ne peut pas être vide");
        formulaireValide = false;
    }
    
    if (motDePasse === '') {
        afficherErreurChamp('mot-de-passe', "Le mot de passe ne peut pas être vide");
        formulaireValide = false;
    }
    
    if (!formulaireValide) {
        afficherMessage('Veuillez remplir tous les champs', 'erreur');
        return;
    }
    
    // Préparer les données
    const donneesConnexion = {
        email: emailUtilisateur,
        mot_de_passe: motDePasse
    };
    
    // Envoyer les données à l'API
    envoyerConnexionVersAPI(donneesConnexion);
}

/**
 * Affiche une erreur sur un champ spécifique
 * @param {string} nomChamp - Le nom du champ ('email' ou 'mot-de-passe')
 * @param {string} message - Le message d'erreur à afficher
 */
function afficherErreurChamp(nomChamp, message) {
    const champ = document.getElementById('champ-' + nomChamp);
    const messageErreur = document.getElementById('erreur-' + nomChamp);
    
    champ.classList.add('champ-invalide');
    messageErreur.textContent = message;
}

/**
 * Envoie les données de connexion à l'API
 * @param {Object} donneesConnexion - Les données de connexion
 */
async function envoyerConnexionVersAPI(donneesConnexion) {
    const boutonConnexion = document.getElementById('bouton-connexion');
    
    // Désactiver le bouton et afficher l'état de chargement
    boutonConnexion.disabled = true;
    boutonConnexion.classList.add('chargement');
    boutonConnexion.textContent = 'Connexion en cours';
    
    try {
        // Envoyer la requête POST à l'API
        const reponse = await fetch('../backend/api/api_connexion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(donneesConnexion)
        });
        
        // Récupérer la réponse JSON
        const donneesReponse = await reponse.json();
        
        if (donneesReponse.succes) {
            // Connexion réussie
            afficherMessage('Connexion réussie ! Redirection...', 'succes');
            
            // Stocker les informations de l'utilisateur dans le localStorage (optionnel)
            if (donneesReponse.utilisateur) {
                localStorage.setItem('utilisateur', JSON.stringify(donneesReponse.utilisateur));
            }
            
            // Attendre 1 seconde puis rediriger
            setTimeout(function() {
                // Rediriger vers la page des catégories si administrateur, sinon vers l'accueil
                if (donneesReponse.utilisateur && donneesReponse.utilisateur.role_utilisateur === 'administrateur') {
                    window.location.href = 'categories.php';
                } else {
                    window.location.href = '../index.html';
                }
            }, 1000);
        } else {
            // Erreur lors de la connexion
            afficherMessage(donneesReponse.message, 'erreur');
            
            // Réactiver le bouton
            boutonConnexion.disabled = false;
            boutonConnexion.classList.remove('chargement');
            boutonConnexion.textContent = 'Se connecter';
        }
        
    } catch (erreur) {
        console.error('Erreur lors de la requête:', erreur);
        afficherMessage('Une erreur est survenue. Veuillez réessayer.', 'erreur');
        
        // Réactiver le bouton
        boutonConnexion.disabled = false;
        boutonConnexion.classList.remove('chargement');
        boutonConnexion.textContent = 'Se connecter';
    }
}

/**
 * Affiche un message de succès ou d'erreur à l'utilisateur
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de message ('succes' ou 'erreur')
 */
function afficherMessage(message, type) {
    const zoneMessage = document.getElementById('zone-message');
    
    // Réinitialiser les classes
    zoneMessage.className = 'zone-message';
    
    // Ajouter la classe appropriée
    if (type === 'succes') {
        zoneMessage.classList.add('message-succes');
    } else if (type === 'erreur') {
        zoneMessage.classList.add('message-erreur');
    }
    
    // Afficher le message
    zoneMessage.textContent = message;
    
    // Faire défiler vers le haut pour voir le message
    zoneMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
