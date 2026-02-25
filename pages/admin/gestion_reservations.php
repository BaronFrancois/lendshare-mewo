<?php
/**
 * Page de gestion des réservations (admin)
 * Permet de visualiser et gérer toutes les réservations
 */

// Démarrer la session
session_start();

// Vérifier que l'utilisateur est connecté et est admin
if (!isset($_SESSION['utilisateur_connecte']) || $_SESSION['role_utilisateur'] !== 'administrateur') {
    header('Location: ../connexion.html');
    exit;
}

$utilisateur = $_SESSION;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Réservations - Admin Lend&Share</title>
    <link rel="stylesheet" href="../../assets/common.css">
    <link rel="stylesheet" href="../../assets/admin.css">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
</head>
<body class="page-admin">
    <!-- Sidebar navigation -->
    <aside class="sidebar-admin">
        <div class="sidebar-header">
            <h2>🎛️ Admin</h2>
            <p class="nom-admin"><?php echo htmlspecialchars($utilisateur['prenom_utilisateur'] . ' ' . $utilisateur['nom_utilisateur']); ?></p>
        </div>
        
        <nav class="menu-admin">
            <a href="gestion_produits.php" class="menu-item">
                <span class="icone">📦</span>
                <span>Produits</span>
            </a>
            <a href="gestion_reservations.php" class="menu-item actif">
                <span class="icone">📅</span>
                <span>Commandes</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../categories.php" class="bouton-retour-site">
                ← Retour au site
            </a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <main class="contenu-principal-admin">
        <header class="header-admin">
            <h1>📅 Gestion des réservations</h1>
        </header>

        <!-- Filtres -->
        <section class="section-filtres">
            <div class="barre-recherche-admin">
                <input 
                    type="search" 
                    id="recherche-reservations" 
                    placeholder="Rechercher (utilisateur, produit...)"
                    class="champ-recherche-admin"
                >
            </div>
            <div class="filtres-categories">
                <label>Filtrer par statut :</label>
                <select id="filtre-statut" class="select-admin">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="confirmee">Confirmées</option>
                    <option value="recuperee">Récupérées</option>
                    <option value="rendue">Rendues</option>
                    <option value="annulee">Annulées</option>
                </select>
            </div>
        </section>

        <!-- Statistiques rapides -->
        <section class="stats-rapides-reservations">
            <div class="stat-rapide">
                <span class="stat-label">En attente</span>
                <span class="stat-valeur" id="stat-en-attente">-</span>
            </div>
            <div class="stat-rapide">
                <span class="stat-label">Confirmées</span>
                <span class="stat-valeur" id="stat-confirmees">-</span>
            </div>
            <div class="stat-rapide">
                <span class="stat-label">En cours</span>
                <span class="stat-valeur" id="stat-en-cours">-</span>
            </div>
            <div class="stat-rapide">
                <span class="stat-label">Rendues</span>
                <span class="stat-valeur" id="stat-rendues">-</span>
            </div>
        </section>

        <!-- Tableau des réservations -->
        <section class="section-dashboard">
            <div id="conteneur-tableau-reservations">
                <p class="message-chargement">Chargement des réservations...</p>
            </div>
        </section>
    </main>

    <!-- Modal détails réservation -->
    <div id="modal-details-reservation" class="modal-overlay" style="display: none;">
        <div class="modal-contenu modal-admin-medium">
            <button class="modal-bouton-fermer" onclick="fermerModalDetails()">&times;</button>
            
            <h2>Détails de la réservation</h2>
            
            <div id="contenu-details-reservation" class="details-reservation">
                <!-- Chargé dynamiquement -->
            </div>
            
            <div class="actions-formulaire">
                <button class="bouton-action bouton-secondaire" onclick="fermerModalDetails()">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/admin/gestion_reservations.js"></script>
</body>
</html>
