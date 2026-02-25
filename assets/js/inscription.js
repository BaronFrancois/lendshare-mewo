/**
 * Script JavaScript pour la page d'inscription
 * Gère la validation du formulaire et l'envoi des données à l'API
 */

// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    initialiserPageInscription();
});

/**
 * Initialise tous les événements de la page d'inscription
 */
function initialiserPageInscription() {
    // Récupérer les éléments du formulaire
    const formulaireInscription = document.getElementById('formulaire-inscription');
    const champMotDePasse = document.getElementById('champ-mot-de-passe');
    const champConfirmationMotDePasse = document.getElementById('champ-confirmation-mot-de-passe');
    const champEmail = document.getElementById('champ-email');
    const champPrenom = document.getElementById('champ-prenom');
    const champNom = document.getElementById('champ-nom');
    
    // Ajouter l'écouteur sur la soumission du formulaire
    formulaireInscription.addEventListener('submit', gererSoumissionFormulaireInscription);
    
    // Ajouter la validation en temps réel du mot de passe
    champMotDePasse.addEventListener('input', function() {
        validerMotDePasseEnTempsReel(this.value);
    });
    
    // Vérifier la correspondance des mots de passe
    champConfirmationMotDePasse.addEventListener('input', function() {
        verifierCorrespondanceMotsDePasse();
    });
    
    champMotDePasse.addEventListener('input', function() {
        if (champConfirmationMotDePasse.value) {
            verifierCorrespondanceMotsDePasse();
        }
    });
    
    // Valider les autres champs au blur
    champEmail.addEventListener('blur', function() {
        validerChampEmail();
    });
    
    champPrenom.addEventListener('blur', function() {
        validerChampPrenomOuNom('prenom');
    });
    
    champNom.addEventListener('blur', function() {
        validerChampPrenomOuNom('nom');
    });
}

/**
 * Valide un champ prénom ou nom
 * @param {string} typeChamp - 'prenom' ou 'nom'
 * @returns {boolean} true si valide, false sinon
 */
function validerChampPrenomOuNom(typeChamp) {
    const champ = document.getElementById('champ-' + typeChamp);
    const messageErreur = document.getElementById('erreur-' + typeChamp);
    const valeur = champ.value.trim();
    const nomChamp = typeChamp === 'prenom' ? 'prénom' : 'nom';
    
    // Réinitialiser le message d'erreur
    messageErreur.textContent = '';
    champ.classList.remove('champ-invalide', 'champ-valide');
    
    // Vérifier si le champ est vide
    if (valeur === '') {
        afficherErreurChamp(champ, messageErreur, `Le ${nomChamp} ne peut pas être vide`);
        return false;
    }
    
    // Vérifier la longueur minimale
    if (valeur.length < 2) {
        afficherErreurChamp(champ, messageErreur, `Le ${nomChamp} doit contenir au moins 2 caractères`);
        return false;
    }
    
    // Vérifier la longueur maximale
    if (valeur.length > 50) {
        afficherErreurChamp(champ, messageErreur, `Le ${nomChamp} est trop long (maximum 50 caractères)`);
        return false;
    }
    
    // Le champ est valide
    champ.classList.add('champ-valide');
    return true;
}

/**
 * Valide le champ email
 * @returns {boolean} true si valide, false sinon
 */
function validerChampEmail() {
    const champEmail = document.getElementById('champ-email');
    const messageErreur = document.getElementById('erreur-email');
    const valeur = champEmail.value.trim();
    
    // Réinitialiser le message d'erreur
    messageErreur.textContent = '';
    champEmail.classList.remove('champ-invalide', 'champ-valide');
    
    // Vérifier si le champ est vide
    if (valeur === '') {
        afficherErreurChamp(champEmail, messageErreur, "L'email ne peut pas être vide");
        return false;
    }
    
    // Expression régulière pour valider l'email
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!regexEmail.test(valeur)) {
        afficherErreurChamp(champEmail, messageErreur, "Le format de l'email n'est pas valide");
        return false;
    }
    
    // Le champ est valide
    champEmail.classList.add('champ-valide');
    return true;
}

/**
 * Valide le mot de passe en temps réel et met à jour les indicateurs visuels
 * @param {string} motDePasse - Le mot de passe à valider
 */
function validerMotDePasseEnTempsReel(motDePasse) {
    // Critères de validation
    const aLongueurMinimale = motDePasse.length >= 8;
    const aUneMajuscule = /[A-Z]/.test(motDePasse);
    const aUneMinuscule = /[a-z]/.test(motDePasse);
    const aUnChiffre = /[0-9]/.test(motDePasse);
    
    // Mettre à jour les indicateurs visuels
    const elementLongueur = document.getElementById('validation-longueur');
    const elementMajuscule = document.getElementById('validation-majuscule');
    const elementMinuscule = document.getElementById('validation-minuscule');
    const elementChiffre = document.getElementById('validation-chiffre');
    
    mettreAJourIndicateurValidation(elementLongueur, aLongueurMinimale);
    mettreAJourIndicateurValidation(elementMajuscule, aUneMajuscule);
    mettreAJourIndicateurValidation(elementMinuscule, aUneMinuscule);
    mettreAJourIndicateurValidation(elementChiffre, aUnChiffre);
    
    // Mettre à jour la classe du champ
    const champMotDePasse = document.getElementById('champ-mot-de-passe');
    champMotDePasse.classList.remove('champ-invalide', 'champ-valide');
    
    if (motDePasse.length > 0) {
        if (aLongueurMinimale && aUneMajuscule && aUneMinuscule && aUnChiffre) {
            champMotDePasse.classList.add('champ-valide');
        } else {
            champMotDePasse.classList.add('champ-invalide');
        }
    }
}

/**
 * Met à jour l'indicateur visuel de validation
 * @param {HTMLElement} element - L'élément à mettre à jour
 * @param {boolean} estValide - true si le critère est validé
 */
function mettreAJourIndicateurValidation(element, estValide) {
    if (estValide) {
        element.classList.add('validation-reussie');
    } else {
        element.classList.remove('validation-reussie');
    }
}

/**
 * Vérifie que les deux mots de passe correspondent
 * @returns {boolean} true si les mots de passe correspondent, false sinon
 */
function verifierCorrespondanceMotsDePasse() {
    const champMotDePasse = document.getElementById('champ-mot-de-passe');
    const champConfirmation = document.getElementById('champ-confirmation-mot-de-passe');
    const messageErreur = document.getElementById('erreur-confirmation');
    
    // Réinitialiser le message d'erreur
    messageErreur.textContent = '';
    champConfirmation.classList.remove('champ-invalide', 'champ-valide');
    
    // Ne rien faire si le champ de confirmation est vide
    if (champConfirmation.value === '') {
        return false;
    }
    
    // Vérifier la correspondance
    if (champMotDePasse.value !== champConfirmation.value) {
        afficherErreurChamp(champConfirmation, messageErreur, "Les mots de passe ne correspondent pas");
        return false;
    }
    
    // Les mots de passe correspondent
    champConfirmation.classList.add('champ-valide');
    return true;
}

/**
 * Affiche une erreur sur un champ spécifique
 * @param {HTMLElement} champ - Le champ à marquer comme invalide
 * @param {HTMLElement} elementMessageErreur - L'élément où afficher le message
 * @param {string} message - Le message d'erreur à afficher
 */
function afficherErreurChamp(champ, elementMessageErreur, message) {
    champ.classList.add('champ-invalide');
    champ.classList.remove('champ-valide');
    elementMessageErreur.textContent = message;
}

/**
 * Gère la soumission du formulaire d'inscription
 * @param {Event} evenement - L'événement de soumission
 */
function gererSoumissionFormulaireInscription(evenement) {
    // Empêcher le rechargement de la page
    evenement.preventDefault();
    
    // Valider tous les champs
    const emailValide = validerChampEmail();
    const prenomValide = validerChampPrenomOuNom('prenom');
    const nomValide = validerChampPrenomOuNom('nom');
    const motsDePasseCorrespondent = verifierCorrespondanceMotsDePasse();
    
    const champMotDePasse = document.getElementById('champ-mot-de-passe');
    const motDePasseValide = champMotDePasse.classList.contains('champ-valide');
    
    // Si un champ est invalide, arrêter
    if (!emailValide || !prenomValide || !nomValide || !motDePasseValide || !motsDePasseCorrespondent) {
        afficherMessage('Veuillez corriger les erreurs dans le formulaire', 'erreur');
        return;
    }
    
    // Récupérer les données du formulaire
    const donneesFormulaire = {
        email_utilisateur: document.getElementById('champ-email').value.trim(),
        prenom_utilisateur: document.getElementById('champ-prenom').value.trim(),
        nom_utilisateur: document.getElementById('champ-nom').value.trim(),
        mot_de_passe: document.getElementById('champ-mot-de-passe').value,
        confirmation_mot_de_passe: document.getElementById('champ-confirmation-mot-de-passe').value
    };
    
    // Envoyer les données à l'API
    envoyerInscriptionVersAPI(donneesFormulaire);
}

/**
 * Envoie les données d'inscription à l'API
 * @param {Object} donneesUtilisateur - Les données de l'utilisateur à inscrire
 */
async function envoyerInscriptionVersAPI(donneesUtilisateur) {
    const boutonInscription = document.getElementById('bouton-inscription');
    
    // Désactiver le bouton et afficher l'état de chargement
    boutonInscription.disabled = true;
    boutonInscription.classList.add('chargement');
    boutonInscription.textContent = 'Inscription en cours';
    
    try {
        // Envoyer la requête POST à l'API
        const reponse = await fetch('../backend/api/api_inscription.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(donneesUtilisateur)
        });
        
        // Récupérer la réponse JSON
        const donneesReponse = await reponse.json();
        
        if (donneesReponse.succes) {
            // Inscription réussie
            afficherMessage(donneesReponse.message, 'succes');
            
            // Attendre 2 secondes puis rediriger vers la page de connexion
            setTimeout(function() {
                window.location.href = 'connexion.html';
            }, 2000);
        } else {
            // Erreur lors de l'inscription
            afficherMessage(donneesReponse.message, 'erreur');
            
            // Réactiver le bouton
            boutonInscription.disabled = false;
            boutonInscription.classList.remove('chargement');
            boutonInscription.textContent = "S'inscrire";
        }
        
    } catch (erreur) {
        console.error('Erreur lors de la requête:', erreur);
        afficherMessage('Une erreur est survenue. Veuillez réessayer.', 'erreur');
        
        // Réactiver le bouton
        boutonInscription.disabled = false;
        boutonInscription.classList.remove('chargement');
        boutonInscription.textContent = "S'inscrire";
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
