<?php
// Modèle pour gérer les catégories

require_once __DIR__ . '/../config/database.php';

class ModeleCategorie {
    
    // Récupère toutes les catégories
    public function obtenirToutesLesCategories() {
        $connexion_bd = obtenirConnexionBD();
        
        $requete = "SELECT * FROM categories ORDER BY nom_categorie";
        
        $resultat = $connexion_bd->query($requete);
        return $resultat->fetchAll();
    }
    
    // Récupère une catégorie par son ID
    public function obtenirCategorieParId($id_categorie) {
        $connexion_bd = obtenirConnexionBD();
        
        $requete = "SELECT * FROM categories WHERE id_categorie = :id";
        
        $stmt = $connexion_bd->prepare($requete);
        $stmt->bindParam(':id', $id_categorie);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Crée une nouvelle catégorie
    public function creerCategorie($nom, $description = '', $image_url = '') {
        $connexion_bd = obtenirConnexionBD();
        
        $requete = "INSERT INTO categories (nom_categorie, description_categorie, image_url_categorie)
                    VALUES (:nom, :description, :image_url)";
        
        $stmt = $connexion_bd->prepare($requete);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->execute();
        
        return $connexion_bd->lastInsertId();
    }
    
    // Modifie une catégorie
    public function modifierCategorie($id_categorie, $nom, $description = '', $image_url = '') {
        $connexion_bd = obtenirConnexionBD();
        
        $requete = "UPDATE categories 
                    SET nom_categorie = :nom, 
                        description_categorie = :description,
                        image_url_categorie = :image_url
                    WHERE id_categorie = :id";
        
        $stmt = $connexion_bd->prepare($requete);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':id', $id_categorie);
        
        return $stmt->execute();
    }
    
    // Supprime une catégorie
    public function supprimerCategorie($id_categorie) {
        $connexion_bd = obtenirConnexionBD();
        
        $requete = "DELETE FROM categories WHERE id_categorie = :id";
        
        $stmt = $connexion_bd->prepare($requete);
        $stmt->bindParam(':id', $id_categorie);
        
        return $stmt->execute();
    }
}
