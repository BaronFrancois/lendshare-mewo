<?php
echo "=== Ajout de produits supplémentaires ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    $produits = [
        // Plus de chaises
        [
            'nom' => 'Chaise Pliante Blanche',
            'description' => 'Chaise pliante pratique et légère pour événements',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_pliante.jpg',
            'vedette' => 0
        ],
        [
            'nom' => 'Chaise Empilable Grise',
            'description' => 'Chaise empilable moderne et confortable',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_grise.jpg',
            'vedette' => 1
        ],
        // Outils
        [
            'nom' => 'Perceuse Sans Fil',
            'description' => 'Perceuse professionnelle avec batterie 18V',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/perceuse.jpg',
            'vedette' => 0
        ],
        [
            'nom' => 'Scie Circulaire',
            'description' => 'Scie circulaire pour découpes précises',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/scie.jpg',
            'vedette' => 0
        ],
        [
            'nom' => 'Echelle Télescopique',
            'description' => 'Échelle aluminium 3m50 extensible',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/echelle.jpg',
            'vedette' => 1
        ],
        [
            'nom' => 'Tondeuse Électrique',
            'description' => 'Tondeuse à gazon électrique 1600W',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/tondeuse.jpg',
            'vedette' => 0
        ],
        // Vidéoprojecteurs
        [
            'nom' => 'Vidéoprojecteur HD',
            'description' => 'Projecteur Full HD 1080p avec HDMI',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoprojecteurs/projecteur_hd.jpg',
            'vedette' => 1
        ],
        [
            'nom' => 'Écran de Projection',
            'description' => 'Écran rétractable 200x150cm',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoprojecteurs/ecran.jpg',
            'vedette' => 0
        ],
        [
            'nom' => 'Enceinte Portable Bluetooth',
            'description' => 'Enceinte puissante 100W pour événements',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoprojecteurs/enceinte.jpg',
            'vedette' => 0
        ],
        // Plus de barnums
        [
            'nom' => 'Barnum Premium 5x5m',
            'description' => 'Grand barnum professionnel avec parois',
            'categorie' => 1,
            'image' => 'assets/images/produits/barnums/barnum_premium.jpg',
            'vedette' => 1
        ],
        [
            'nom' => 'Barnum Compact 2x2m',
            'description' => 'Petit barnum facile à monter',
            'categorie' => 1,
            'image' => 'assets/images/produits/barnums/barnum_compact.jpg',
            'vedette' => 0
        ]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO produits (
            nom_produit, 
            description_produit, 
            id_categorie, 
            image_url_produit,
            est_vedette,
            quantite_disponible,
            quantite_totale
        ) VALUES (
            :nom, 
            :description, 
            :categorie, 
            :image,
            :vedette,
            1,
            1
        )
    ");
    
    $count = 0;
    foreach ($produits as $produit) {
        $stmt->execute([
            'nom' => $produit['nom'],
            'description' => $produit['description'],
            'categorie' => $produit['categorie'],
            'image' => $produit['image'],
            'vedette' => $produit['vedette']
        ]);
        $count++;
        echo "✓ Ajouté : {$produit['nom']}\n";
    }
    
    echo "\n✓✓✓ {$count} produits ajoutés avec succès ! ✓✓✓\n";
    
    // Afficher le total
    $total = $db->query("SELECT COUNT(*) FROM produits")->fetchColumn();
    echo "Total de produits dans la base : {$total}\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
