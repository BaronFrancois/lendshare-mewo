<?php
/**
 * Page de gestion des produits (admin)
 * Permet de créer, modifier, supprimer des produits
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
    <title>Gestion Produits - Admin Lend&Share</title>
    <link rel="stylesheet" href="../../assets/common.css">
    <link rel="stylesheet" href="../../assets/admin.css">
    <link rel="stylesheet" href="../../assets/modal_produit.css">
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
            <a href="gestion_produits.php" class="menu-item actif">
                <span class="icone">📦</span>
                <span>Produits</span>
            </a>
            <a href="gestion_reservations.php" class="menu-item">
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
            <h1>📦 Gestion des produits</h1>
            <div class="actions-header">
                <button id="bouton-ajouter-produit" class="bouton-action bouton-primaire">
                    + Ajouter un produit
                </button>
            </div>
        </header>

        <!-- Filtres et recherche -->
        <section class="section-filtres">
            <div class="barre-recherche-admin">
                <input 
                    type="search" 
                    id="recherche-produits" 
                    placeholder="Rechercher un produit..."
                    class="champ-recherche-admin"
                >
            </div>
            <div class="filtres-categories">
                <label>Filtrer par catégorie :</label>
                <select id="filtre-categorie" class="select-admin">
                    <option value="">Toutes les catégories</option>
                    <!-- Chargé dynamiquement -->
                </select>
            </div>
            <div class="filtres-disponibilite">
                <label>Disponibilité :</label>
                <select id="filtre-disponibilite" class="select-admin">
                    <option value="">Tous</option>
                    <option value="disponible">Disponibles</option>
                    <option value="indisponible">Indisponibles</option>
                </select>
            </div>
        </section>

        <!-- Tableau des produits -->
        <section class="section-dashboard">
            <div id="conteneur-tableau-produits">
                <p class="message-chargement">Chargement des produits...</p>
            </div>
        </section>
    </main>

    <!-- Modal ajout/modification produit -->
    <div id="modal-produit-admin" class="modal-overlay" style="display: none;">
        <div class="modal-contenu modal-admin-large">
            <button class="modal-bouton-fermer" onclick="fermerModalProduitAdmin()">&times;</button>
            
            <h2 id="modal-titre">Ajouter un produit</h2>
            
            <form id="formulaire-produit-admin" class="formulaire-admin">
                <input type="hidden" id="id-produit-modif" value="">
                
                <div class="grille-formulaire">
                    <div class="groupe-champ">
                        <label for="nom-produit">Nom du produit *</label>
                        <input 
                            type="text" 
                            id="nom-produit" 
                            required
                            placeholder="Ex: Barnum 3x3m blanc"
                        >
                    </div>
                    
                    <div class="groupe-champ">
                        <label for="categorie-produit">Catégorie *</label>
                        <select id="categorie-produit" required>
                            <option value="">Sélectionner une catégorie</option>
                            <!-- Chargé dynamiquement -->
                        </select>
                    </div>
                    
                    <div class="groupe-champ">
                        <label for="quantite-totale">Quantité totale *</label>
                        <input 
                            type="number" 
                            id="quantite-totale" 
                            min="0"
                            required
                            placeholder="0"
                        >
                    </div>
                    
                    <div class="groupe-champ">
                        <label for="quantite-disponible">Quantité disponible *</label>
                        <input 
                            type="number" 
                            id="quantite-disponible" 
                            min="0"
                            required
                            placeholder="0"
                        >
                    </div>
                </div>
                
                <div class="groupe-champ">
                    <label for="description-produit">Description</label>
                    <textarea 
                        id="description-produit" 
                        rows="4"
                        placeholder="Description détaillée du produit..."
                    ></textarea>
                </div>
                
                <div class="groupe-champ">
                    <label>Image du produit</label>
                    <div class="zone-upload-image">
                        <input 
                            type="file" 
                            id="input-image-produit" 
                            accept="image/*"
                            style="display: none;"
                        >
                        <div id="apercu-image" class="apercu-image">
                            <p>Cliquez pour sélectionner une image</p>
                            <small>JPEG, PNG, GIF, WEBP - Max 5 Mo</small>
                        </div>
                        <input type="hidden" id="url-image-produit" value="">
                    </div>
                </div>
                
                <div id="message-formulaire-produit" class="message-cache"></div>
                
                <div class="actions-formulaire">
                    <button type="button" class="bouton-action bouton-secondaire" onclick="fermerModalProduitAdmin()">
                        Annuler
                    </button>
                    <button type="submit" class="bouton-action bouton-primaire" id="bouton-submit-produit">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin/gestion_produits.js"></script>
</body>
</html>
