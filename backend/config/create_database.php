<?php
echo "=== Création de la base de données Lend&Share ===\n\n";

require_once __DIR__ . '/config.php';

try {
    $database_path = CHEMIN_BASE_DONNEES;
    
    if (file_exists($database_path)) {
        echo "⚠️  La base de données existe déjà\n";
        echo "Supprimez-la d'abord si vous voulez la recréer.\n";
        exit;
    }
    
    $folder_path = dirname($database_path);
    if (!is_dir($folder_path)) {
        mkdir($folder_path, 0755, true);
        echo "✓ Dossier 'database' créé\n";
    }
    
    $db_connection = new PDO('sqlite:' . $database_path);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Fichier de base de données créé\n\n";
    
    echo "Création de la table 'utilisateurs'...\n";
    $create_users_query = "
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id_utilisateur INTEGER PRIMARY KEY AUTOINCREMENT,
            nom_utilisateur TEXT NOT NULL,
            prenom_utilisateur TEXT NOT NULL,
            email_utilisateur TEXT NOT NULL UNIQUE,
            mot_de_passe_utilisateur TEXT NOT NULL,
            role_utilisateur TEXT NOT NULL DEFAULT 'utilisateur' CHECK(role_utilisateur IN ('utilisateur', 'administrateur')),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $db_connection->exec($create_users_query);
    echo "✓ Table 'utilisateurs' créée\n\n";
    
    echo "Création de la table 'categories'...\n";
    $create_categories_query = "
        CREATE TABLE IF NOT EXISTS categories (
            id_categorie INTEGER PRIMARY KEY AUTOINCREMENT,
            nom_categorie TEXT NOT NULL,
            description_categorie TEXT,
            image_url_categorie TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $db_connection->exec($create_categories_query);
    echo "✓ Table 'categories' créée\n\n";
    
    echo "Création de la table 'produits'...\n";
    $create_products_query = "
        CREATE TABLE IF NOT EXISTS produits (
            id_produit INTEGER PRIMARY KEY AUTOINCREMENT,
            nom_produit TEXT NOT NULL,
            description_produit TEXT,
            id_categorie INTEGER NOT NULL,
            image_url_produit TEXT,
            est_vedette INTEGER DEFAULT 0,
            quantite_disponible INTEGER DEFAULT 1,
            quantite_totale INTEGER DEFAULT 1,
            date_ajout_produit DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie)
        )
    ";
    $db_connection->exec($create_products_query);
    echo "✓ Table 'produits' créée\n\n";
    
    echo "Création de la table 'reservations'...\n";
    $create_bookings_query = "
        CREATE TABLE IF NOT EXISTS reservations (
            id_reservation INTEGER PRIMARY KEY AUTOINCREMENT,
            id_utilisateur INTEGER NOT NULL,
            id_produit INTEGER NOT NULL,
            date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP,
            statut_reservation TEXT DEFAULT 'en_attente' CHECK(statut_reservation IN ('en_attente', 'accepte', 'refuse', 'cloture')),
            date_modification_statut DATETIME,
            FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur),
            FOREIGN KEY (id_produit) REFERENCES produits(id_produit)
        )
    ";
    $db_connection->exec($create_bookings_query);
    echo "✓ Table 'reservations' créée\n\n";
    
    echo "=== Données Initiales ===\n\n";
    
    echo "Création du compte administrateur...\n";
    $admin_password = password_hash('Admin123!', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe_utilisateur, role_utilisateur) 
                     VALUES ('Admin', 'Lend&Share', 'admin@lendshare.fr', :password, 'administrateur')";
    $stmt = $db_connection->prepare($insert_admin);
    $stmt->bindParam(':password', $admin_password);
    $stmt->execute();
    echo "✓ Compte administrateur créé (admin@lendshare.fr / Admin123!)\n\n";
    
    echo "Création d'un utilisateur test...\n";
    $user_password = password_hash('Test123!', PASSWORD_DEFAULT);
    $insert_user = "INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe_utilisateur, role_utilisateur) 
                    VALUES ('test', 'test', 'test@test.fr', :password, 'utilisateur')";
    $stmt = $db_connection->prepare($insert_user);
    $stmt->bindParam(':password', $user_password);
    $stmt->execute();
    echo "✓ Utilisateur test créé (test@test.fr / Test123!)\n\n";
    
    echo "Création des catégories par défaut...\n";
    $default_categories = [
        ['name' => 'Barnums', 'description' => 'Tentes et barnums pour événements extérieurs'],
        ['name' => 'Chaises', 'description' => 'Chaises pliantes et mobilier d\'assise'],
        ['name' => 'Outils', 'description' => 'Outils de bricolage et jardinage'],
        ['name' => 'Vidéoprojecteurs', 'description' => 'Matériel de projection et audiovisuel']
    ];
    
    $insert_category = "INSERT INTO categories (nom_categorie, description_categorie) VALUES (:name, :description)";
    $stmt = $db_connection->prepare($insert_category);
    
    foreach ($default_categories as $category) {
        $stmt->bindParam(':name', $category['name']);
        $stmt->bindParam(':description', $category['description']);
        $stmt->execute();
        echo "  ✓ Catégorie '{$category['name']}' créée\n";
    }
    
    echo "\n✓ Toutes les catégories créées\n\n";
    
    echo "Création de produits d'exemple...\n";
    $sample_products = [
        ['name' => 'Barnum Blanc 3x3m', 'description' => 'Barnum pliant de 3x3 mètres, structure robuste en aluminium, toile blanche imperméable', 'category' => 'Barnums', 'image' => 'assets/images/produits/barnums/barnum_blanc.png', 'featured' => 1],
        ['name' => 'Barnum Bleu 4x4m', 'description' => 'Grand barnum de 4x4 mètres, idéal pour grands événements, toile bleue résistante aux UV', 'category' => 'Barnums', 'image' => 'assets/images/produits/barnums/barnum_bleu.png', 'featured' => 0],
        ['name' => 'Barnum Standard', 'description' => 'Barnum polyvalent 3x3m pour tous types d\'événements', 'category' => 'Barnums', 'image' => 'assets/images/produits/barnums/barnum.jpg', 'featured' => 0],
        ['name' => 'Chaise Bleue Moderne', 'description' => 'Chaise empilable au design moderne, assise confortable en polypropylène bleu', 'category' => 'Chaises', 'image' => 'assets/images/produits/chaises/chaise_bleu.jpg', 'featured' => 1],
        ['name' => 'Chaise en Bois Naturel', 'description' => 'Chaise en bois massif, finition naturelle, design élégant et intemporel', 'category' => 'Chaises', 'image' => 'assets/images/produits/chaises/chaise_bois.jpg', 'featured' => 0]
    ];
    
    foreach ($sample_products as $product) {
        $get_cat_id = "SELECT id_categorie FROM categories WHERE nom_categorie = :name";
        $stmt_cat = $db_connection->prepare($get_cat_id);
        $stmt_cat->bindParam(':name', $product['category']);
        $stmt_cat->execute();
        $category_id = $stmt_cat->fetchColumn();
        
        $insert_product = "INSERT INTO produits (nom_produit, description_produit, id_categorie, image_url_produit, est_vedette, quantite_disponible, quantite_totale) 
                          VALUES (:name, :description, :category_id, :image, :featured, :available_qty, :total_qty)";
        
        $stmt_prod = $db_connection->prepare($insert_product);
        $stmt_prod->bindParam(':name', $product['name']);
        $stmt_prod->bindParam(':description', $product['description']);
        $stmt_prod->bindParam(':category_id', $category_id);
        $stmt_prod->bindParam(':image', $product['image']);
        $stmt_prod->bindParam(':featured', $product['featured']);
        
        $qty_total = 1;
        $qty_available = 1;
        $stmt_prod->bindParam(':total_qty', $qty_total);
        $stmt_prod->bindParam(':available_qty', $qty_available);
        $stmt_prod->execute();
        
        echo "  ✓ Produit '{$product['name']}' créé\n";
    }
    
    echo "\n✓ Produits d'exemple créés\n\n";
    
    echo "===========================================\n";
    echo "✓✓✓ BASE DE DONNÉES CRÉÉE AVEC SUCCÈS ✓✓✓\n";
    echo "===========================================\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
