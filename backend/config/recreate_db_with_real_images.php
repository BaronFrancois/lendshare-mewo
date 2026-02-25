<?php
echo "=== Recréation de la base avec UNIQUEMENT les produits ayant des images ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    // Vider les tables existantes
    echo "Suppression des données existantes...\n";
    $db->exec("DELETE FROM reservations");
    $db->exec("DELETE FROM produits");
    echo "✓ Tables vidées\n\n";
    
    // Produits avec images RÉELLES uniquement
    $produits = [
        // BARNUMS (3 produits)
        [
            'nom' => 'Barnum Blanc 3x3m',
            'description' => 'Barnum blanc professionnel 3x3 mètres, idéal pour événements extérieurs',
            'categorie' => 1,
            'image' => 'assets/images/produits/barnums/barnum_blanc.png',
            'vedette' => 1,
            'quantite_dispo' => 2,
            'quantite_total' => 2
        ],
        [
            'nom' => 'Barnum Bleu 4x4m',
            'description' => 'Grand barnum bleu 4x4 mètres avec structure renforcée',
            'categorie' => 1,
            'image' => 'assets/images/produits/barnums/barnum_bleu.png',
            'vedette' => 0,
            'quantite_dispo' => 1,
            'quantite_total' => 1
        ],
        [
            'nom' => 'Barnum Standard',
            'description' => 'Barnum standard pour petits événements',
            'categorie' => 1,
            'image' => 'assets/images/produits/barnums/barnum.jpg',
            'vedette' => 0,
            'quantite_dispo' => 3,
            'quantite_total' => 3
        ],
        
        // CHAISES (5 produits)
        [
            'nom' => 'Chaise Bleue Moderne',
            'description' => 'Chaise design bleue confortable et empilable',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_bleu.jpg',
            'vedette' => 1,
            'quantite_dispo' => 20,
            'quantite_total' => 20
        ],
        [
            'nom' => 'Chaise en Bois Naturel',
            'description' => 'Chaise classique en bois massif',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_bois.jpg',
            'vedette' => 0,
            'quantite_dispo' => 15,
            'quantite_total' => 15
        ],
        [
            'nom' => 'Chaise Marron Vintage',
            'description' => 'Chaise vintage au style rétro',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_marron.jpg',
            'vedette' => 0,
            'quantite_dispo' => 10,
            'quantite_total' => 10
        ],
        [
            'nom' => 'Chaise en Osier',
            'description' => 'Chaise tressée en osier naturel',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise_osier.jpg',
            'vedette' => 1,
            'quantite_dispo' => 0,
            'quantite_total' => 8
        ],
        [
            'nom' => 'Chaise Standard',
            'description' => 'Chaise polyvalente pour tous événements',
            'categorie' => 2,
            'image' => 'assets/images/produits/chaises/chaise.jpg',
            'vedette' => 0,
            'quantite_dispo' => 25,
            'quantite_total' => 25
        ],
        
        // OUTILS (4 produits)
        [
            'nom' => 'Perceuse Sans Fil',
            'description' => 'Perceuse professionnelle 18V avec batterie incluse',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/perceuse.jpg',
            'vedette' => 1,
            'quantite_dispo' => 2,
            'quantite_total' => 2
        ],
        [
            'nom' => 'Scie Circulaire',
            'description' => 'Scie circulaire puissante pour découpes précises',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/scie.jpg',
            'vedette' => 0,
            'quantite_dispo' => 1,
            'quantite_total' => 1
        ],
        [
            'nom' => 'Marteau Professionnel',
            'description' => 'Marteau robuste pour travaux de construction',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/marteau.jpg',
            'vedette' => 0,
            'quantite_dispo' => 5,
            'quantite_total' => 5
        ],
        [
            'nom' => 'Set de Tournevis',
            'description' => 'Coffret complet de tournevis professionnels',
            'categorie' => 3,
            'image' => 'assets/images/produits/outils/tourne_vis.jpg',
            'vedette' => 0,
            'quantite_dispo' => 0,
            'quantite_total' => 3
        ],
        
        // VIDÉOPROJECTEURS (3 produits)
        [
            'nom' => 'Vidéoprojecteur HD Premium',
            'description' => 'Projecteur Full HD 1080p avec HDMI et VGA',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoProjecteur/videoProjecteur.jpg',
            'vedette' => 1,
            'quantite_dispo' => 2,
            'quantite_total' => 2
        ],
        [
            'nom' => 'Vidéoprojecteur Compact',
            'description' => 'Projecteur compact portable pour présentations',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoProjecteur/videoProjecteur2.jpg',
            'vedette' => 0,
            'quantite_dispo' => 1,
            'quantite_total' => 1
        ],
        [
            'nom' => 'Vidéoprojecteur Professionnel',
            'description' => 'Projecteur haute luminosité pour grandes salles',
            'categorie' => 4,
            'image' => 'assets/images/produits/videoProjecteur/istockphoto-157280249-612x612.jpg',
            'vedette' => 0,
            'quantite_dispo' => 1,
            'quantite_total' => 1
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
            :quantite_dispo,
            :quantite_total
        )
    ");
    
    echo "Ajout des produits avec images réelles :\n";
    echo "----------------------------------------\n";
    $count = 0;
    foreach ($produits as $produit) {
        $stmt->execute([
            'nom' => $produit['nom'],
            'description' => $produit['description'],
            'categorie' => $produit['categorie'],
            'image' => $produit['image'],
            'vedette' => $produit['vedette'],
            'quantite_dispo' => $produit['quantite_dispo'],
            'quantite_total' => $produit['quantite_total']
        ]);
        $count++;
        
        $statut = $produit['quantite_dispo'] > 0 ? '✓ Disponible' : '✗ Indisponible';
        $vedette = $produit['vedette'] ? '⭐' : '';
        echo sprintf(
            "[%d] %s %s - %s (%d/%d)\n",
            $count,
            $produit['nom'],
            $vedette,
            $statut,
            $produit['quantite_dispo'],
            $produit['quantite_total']
        );
    }
    
    echo "\n✓✓✓ Base de données recréée avec succès ! ✓✓✓\n";
    echo "Total : {$count} produits avec images réelles\n";
    echo "\nRépartition :\n";
    echo "- 3 Barnums\n";
    echo "- 5 Chaises\n";
    echo "- 4 Outils\n";
    echo "- 3 Vidéoprojecteurs\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
