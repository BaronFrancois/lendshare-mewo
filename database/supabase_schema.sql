-- ============================================================
--  Schéma Supabase pour Lend&Share
--  À exécuter dans l'éditeur SQL du dashboard Supabase
-- ============================================================

-- ── Tables ────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur  SERIAL PRIMARY KEY,
    nom_utilisateur TEXT NOT NULL,
    prenom_utilisateur TEXT NOT NULL,
    email_utilisateur TEXT NOT NULL UNIQUE,
    mot_de_passe_utilisateur TEXT NOT NULL,
    role_utilisateur TEXT NOT NULL DEFAULT 'utilisateur'
        CHECK (role_utilisateur IN ('utilisateur', 'administrateur')),
    date_creation TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS categories (
    id_categorie        SERIAL PRIMARY KEY,
    nom_categorie       TEXT NOT NULL,
    description_categorie TEXT,
    image_url_categorie TEXT,
    date_creation TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS produits (
    id_produit          SERIAL PRIMARY KEY,
    nom_produit         TEXT NOT NULL,
    description_produit TEXT,
    id_categorie        INTEGER NOT NULL REFERENCES categories(id_categorie),
    image_url_produit   TEXT,
    est_vedette         BOOLEAN DEFAULT FALSE,
    quantite_disponible INTEGER DEFAULT 1,
    quantite_totale     INTEGER DEFAULT 1,
    date_ajout_produit  TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reservations (
    id_reservation      SERIAL PRIMARY KEY,
    id_utilisateur      INTEGER NOT NULL REFERENCES utilisateurs(id_utilisateur),
    id_produit          INTEGER NOT NULL REFERENCES produits(id_produit),
    date_reservation    TIMESTAMPTZ DEFAULT NOW(),
    statut_reservation  TEXT DEFAULT 'en_attente'
        CHECK (statut_reservation IN (
            'en_attente','accepte','refuse','cloture',
            'recuperee','rendue','annulee','confirmee'
        )),
    date_modification_statut TIMESTAMPTZ
);

-- ── Fonctions pour ajuster les quantités lors des réservations ────────────────

CREATE OR REPLACE FUNCTION decrementer_quantite(produit_id INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
BEGIN
    UPDATE produits
    SET quantite_disponible = GREATEST(quantite_disponible - 1, 0)
    WHERE id_produit = produit_id;
END;
$$;

CREATE OR REPLACE FUNCTION incrementer_quantite(produit_id INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
BEGIN
    UPDATE produits
    SET quantite_disponible = LEAST(quantite_disponible + 1, quantite_totale)
    WHERE id_produit = produit_id;
END;
$$;

-- ── Désactiver RLS (les fonctions utilisent la clé service qui bypass RLS) ────

ALTER TABLE utilisateurs DISABLE ROW LEVEL SECURITY;
ALTER TABLE categories DISABLE ROW LEVEL SECURITY;
ALTER TABLE produits DISABLE ROW LEVEL SECURITY;
ALTER TABLE reservations DISABLE ROW LEVEL SECURITY;

-- ── Données initiales ─────────────────────────────────────────────────────────

-- Comptes utilisateurs
-- Mots de passe hashés avec bcrypt (compatibles PHP $2y$ et Node.js $2b$)
-- admin@lendshare.fr  → Admin123!
-- test@test.fr        → Test123!
INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe_utilisateur, role_utilisateur) VALUES
  ('Admin', 'Lend&Share', 'admin@lendshare.fr', '$2y$12$mjTXooYEhiAJDNmKJhs9Ve7ASIXe0hlVBw27vLvp2uWtL8PXbE9IO', 'administrateur'),
  ('test',  'test',        'test@test.fr',       '$2y$12$9ML91owTG67u4wQhZOmsvuoa1eNPDknKq4.chFiswN7pHCxt597VW', 'utilisateur');

-- Catégories
INSERT INTO categories (nom_categorie, description_categorie) VALUES
  ('Barnums',         'Tentes et barnums pour événements extérieurs'),
  ('Chaises',         'Chaises pliantes et mobilier d''assise'),
  ('Outils',          'Outils de bricolage et jardinage'),
  ('Vidéoprojecteurs','Matériel de projection et audiovisuel');

-- Produits (référencent les catégories par leur rang d'insertion, id 1-4)
INSERT INTO produits (nom_produit, description_produit, id_categorie, image_url_produit, est_vedette, quantite_disponible, quantite_totale) VALUES
  ('Barnum Blanc 3x3m',     'Barnum blanc professionnel 3x3 mètres, idéal pour événements extérieurs', 1, 'assets/images/produits/barnums/barnum_blanc.png', TRUE,  2, 2),
  ('Barnum Bleu 4x4m',      'Grand barnum bleu 4x4 mètres avec structure renforcée',                   1, 'assets/images/produits/barnums/barnum_bleu.png',  FALSE, 1, 1),
  ('Barnum Standard',       'Barnum standard pour petits événements',                                  1, 'assets/images/produits/barnums/barnum.jpg',       FALSE, 3, 3),
  ('Chaise Bleue Moderne',  'Chaise design bleue confortable et empilable',                            2, 'assets/images/produits/chaises/chaise_bleu.jpg',  TRUE, 20, 20),
  ('Chaise Marron Vintage', 'Chaise vintage au style rétro',                                           2, 'assets/images/produits/chaises/chaise_marron.jpg',FALSE, 10, 10),
  ('Chaise Standard',       'Chaise polyvalente pour tous événements',                                 2, 'assets/images/produits/chaises/chaise.jpg',       FALSE, 25, 25),
  ('Chaise en Bois Naturel','Chaise classique en bois massif',                                         2, 'assets/images/produits/chaises/chaise_bois.jpg',  FALSE, 15, 15),
  ('Chaise en Osier',       'Chaise tressée en osier naturel',                                         2, 'assets/images/produits/chaises/chaise_osier.jpg', TRUE,   0,  8),
  ('Marteau Professionnel', 'Marteau robuste pour travaux de construction',                            3, 'assets/images/produits/outils/marteau.jpg',       FALSE,  5,  5),
  ('Perceuse Sans Fil',     'Perceuse professionnelle 18V avec batterie incluse',                      3, 'assets/images/produits/outils/perceuse.jpg',      TRUE,   2,  2),
  ('Scie Circulaire',       'Scie circulaire puissante pour découpes précises',                        3, 'assets/images/produits/outils/scie.jpg',          FALSE,  1,  1),
  ('Set de Tournevis',      'Coffret complet de tournevis professionnels',                             3, 'assets/images/produits/outils/tourne_vis.jpg',    FALSE,  0,  3),
  ('Vidéoprojecteur Compact',     'Projecteur compact portable pour présentations',                   4, 'assets/images/produits/videoProjecteur/videoProjecteur2.jpg',                   FALSE, 1, 1),
  ('Vidéoprojecteur HD Premium',  'Projecteur Full HD 1080p avec HDMI et VGA',                        4, 'assets/images/produits/videoProjecteur/videoProjecteur.jpg',                    TRUE,  2, 2),
  ('Vidéoprojecteur Professionnel','Projecteur haute luminosité pour grandes salles',                 4, 'assets/images/produits/videoProjecteur/istockphoto-157280249-612x612.jpg',     FALSE, 1, 1);

-- ── Buckets Supabase Storage (à créer depuis le dashboard Storage) ────────────
-- Créer deux buckets publics :
--   • products    (pour les images de produits uploadées par les admins)
--   • categories  (pour les images de catégories)
