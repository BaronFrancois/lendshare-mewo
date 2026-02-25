/**
 * Script pour gérer l'affichage de l'interface selon l'état de la session
 * Vérifie si l'utilisateur est connecté et adapte l'interface
 */

document.addEventListener('DOMContentLoaded', function() {
    verifierEtAfficherEtatSession();
});

/**
 * Vérifie l'état de la session et met à jour l'interface
 */
async function verifierEtAfficherEtatSession() {
    try {
        const reponse = await fetch('../backend/api/api_verifier_session.php');
        const donnees = await reponse.json();
        
        const bouton_connexion = document.getElementById('connexion');
        
        if (donnees.connecte && donnees.utilisateur) {
            // Utilisateur connecté
            mettreAJourInterfaceUtilisateurConnecte(donnees.utilisateur, bouton_connexion);
        } else {
            // Utilisateur non connecté
            mettreAJourInterfaceUtilisateurNonConnecte(bouton_connexion);
        }
        
    } catch (erreur) {
        console.error('Erreur lors de la vérification de la session:', erreur);
    }
}

/**
 * Met à jour l'interface pour un utilisateur connecté
 * 
 * @param {Object} utilisateur - Données de l'utilisateur
 * @param {HTMLElement} bouton - Bouton de connexion
 */
function mettreAJourInterfaceUtilisateurConnecte(utilisateur, bouton) {
    if (!bouton) return;
    
    // Créer un menu déroulant utilisateur
    const conteneur_menu = document.createElement('div');
    conteneur_menu.className = 'menu-utilisateur';
    
    const bouton_utilisateur = document.createElement('button');
    bouton_utilisateur.className = 'bouton-utilisateur';
    bouton_utilisateur.textContent = `${utilisateur.prenom_utilisateur} ${utilisateur.nom_utilisateur}`;
    
    const menu_deroulant = document.createElement('div');
    menu_deroulant.className = 'menu-deroulant';
    menu_deroulant.style.display = 'none';
    
    // Lien mon compte
    const lien_mon_compte = document.createElement('a');
    lien_mon_compte.href = 'mon_compte.php';
    lien_mon_compte.textContent = 'Mon compte';
    menu_deroulant.appendChild(lien_mon_compte);
    
    // Lien admin (si administrateur)
    if (utilisateur.role_utilisateur === 'administrateur') {
        const lien_admin = document.createElement('a');
        // Déterminer le chemin relatif selon la page actuelle
        const chemin_admin = window.location.pathname.includes('/admin/')
            ? 'gestion_produits.php'
            : 'admin/gestion_produits.php';
        lien_admin.href = chemin_admin;
        lien_admin.textContent = '⚡ Administration';
        menu_deroulant.appendChild(lien_admin);
    }
    
    // Bouton déconnexion
    const bouton_deconnexion = document.createElement('button');
    bouton_deconnexion.textContent = 'Déconnexion';
    bouton_deconnexion.addEventListener('click', deconnecterUtilisateur);
    menu_deroulant.appendChild(bouton_deconnexion);
    
    // Toggle menu au clic
    bouton_utilisateur.addEventListener('click', function() {
        const estVisible = menu_deroulant.style.display === 'block';
        menu_deroulant.style.display = estVisible ? 'none' : 'block';
    });
    
    // Fermer le menu si clic à l'extérieur
    document.addEventListener('click', function(event) {
        if (!conteneur_menu.contains(event.target)) {
            menu_deroulant.style.display = 'none';
        }
    });
    
    conteneur_menu.appendChild(bouton_utilisateur);
    conteneur_menu.appendChild(menu_deroulant);
    
    // Remplacer le bouton connexion par le menu
    bouton.replaceWith(conteneur_menu);
}

/**
 * Met à jour l'interface pour un utilisateur non connecté
 * 
 * @param {HTMLElement} bouton - Bouton de connexion
 */
function mettreAJourInterfaceUtilisateurNonConnecte(bouton) {
    if (!bouton) return;
    
    bouton.textContent = 'Connexion';
    bouton.addEventListener('click', function() {
        window.location.href = 'connexion.html';
    });
}

/**
 * Déconnecte l'utilisateur
 */
async function deconnecterUtilisateur() {
    try {
        const reponse = await fetch('../backend/api/api_deconnexion.php', {
            method: 'POST'
        });
        
        const donnees = await reponse.json();
        
        if (donnees.succes) {
            // Supprimer les données locales
            localStorage.removeItem('utilisateur');
            
            // Rediriger vers l'accueil
            window.location.href = '../index.html';
        } else {
            alert('Erreur lors de la déconnexion');
        }
        
    } catch (erreur) {
        console.error('Erreur lors de la déconnexion:', erreur);
        alert('Erreur lors de la déconnexion');
    }
}
