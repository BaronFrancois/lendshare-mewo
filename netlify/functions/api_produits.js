const { supabase } = require('./_utils/supabase');
const { getUserFromEvent } = require('./_utils/auth');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

// Aplatit le sous-objet categories pour avoir nom_categorie au niveau racine
function flattenCategory(p) {
  if (!p) return p;
  const { categories, ...rest } = p;
  return { ...rest, nom_categorie: categories?.nom_categorie ?? null };
}

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  try {
    const params = event.queryStringParameters || {};

    // ── GET (public) ─────────────────────────────────────────────────────────
    if (event.httpMethod === 'GET') {

      if (params.id) {
        const { data, error } = await supabase
          .from('produits')
          .select('*, categories(nom_categorie)')
          .eq('id_produit', params.id)
          .single();
        if (error) throw error;
        return ok({ success: true, produit: flattenCategory(data) });
      }

      if (params.ids) {
        const ids = params.ids.split(',').map(Number).filter(Boolean);
        const { data, error } = await supabase
          .from('produits')
          .select('*, categories(nom_categorie)')
          .in('id_produit', ids);
        if (error) throw error;
        const produits = data.map(flattenCategory);
        const ordered = ids.map(id => produits.find(p => p.id_produit === id)).filter(Boolean);
        return ok({ success: true, produits: ordered });
      }

      let query = supabase.from('produits').select('*, categories(nom_categorie)');
      if (params.vedettes) {
        query = query.eq('est_vedette', true).order('date_ajout_produit', { ascending: false });
      } else if (params.categorie) {
        query = query.eq('id_categorie', Number(params.categorie)).order('nom_produit');
      } else {
        query = query.order('nom_produit');
      }

      const { data, error } = await query;
      if (error) throw error;
      return ok({ success: true, produits: data.map(flattenCategory) });
    }

    // ── Mutations (admin uniquement) ─────────────────────────────────────────
    const user = getUserFromEvent(event);
    if (!user || user.role !== 'administrateur') {
      return err(401, { success: false, message: 'Non autorisé' });
    }

    if (event.httpMethod === 'POST') {
      const d = parseBody(event.body);
      if (!d.nom_produit?.trim() || !d.id_categorie) {
        return err(400, { success: false, message: 'Nom et catégorie requis' });
      }
      const qTot = Math.max(0, parseInt(d.quantite_totale) || 1);
      const qDis = Math.min(Math.max(0, parseInt(d.quantite_disponible) ?? qTot), qTot);

      const { data: produit, error } = await supabase
        .from('produits')
        .insert({
          nom_produit: d.nom_produit.trim(),
          description_produit: d.description_produit || '',
          id_categorie: parseInt(d.id_categorie),
          image_url_produit: d.image_url_produit || null,
          est_vedette: Boolean(d.est_vedette),
          quantite_totale: qTot,
          quantite_disponible: qDis,
        })
        .select('*, categories(nom_categorie)')
        .single();
      if (error) throw error;
      return { statusCode: 201, headers: HEADERS, body: JSON.stringify({ success: true, message: 'Produit créé', produit: flattenCategory(produit) }) };
    }

    if (event.httpMethod === 'PUT') {
      const d = parseBody(event.body);
      if (!d.id_produit) return err(400, { success: false, message: 'ID requis' });

      const { data: existing, error: fErr } = await supabase
        .from('produits').select('*').eq('id_produit', d.id_produit).single();
      if (fErr || !existing) return err(404, { success: false, message: 'Produit non trouvé' });

      const qTot = parseInt(d.quantite_totale ?? existing.quantite_totale);
      const qDis = Math.min(parseInt(d.quantite_disponible ?? existing.quantite_disponible), qTot);

      const { data: produit, error } = await supabase
        .from('produits')
        .update({
          nom_produit: d.nom_produit ?? existing.nom_produit,
          description_produit: d.description_produit ?? existing.description_produit,
          id_categorie: parseInt(d.id_categorie ?? existing.id_categorie),
          image_url_produit: d.image_url_produit ?? existing.image_url_produit,
          est_vedette: d.est_vedette !== undefined ? Boolean(d.est_vedette) : existing.est_vedette,
          quantite_totale: qTot,
          quantite_disponible: qDis,
        })
        .eq('id_produit', d.id_produit)
        .select('*, categories(nom_categorie)')
        .single();
      if (error) throw error;
      return ok({ success: true, message: 'Produit mis à jour', produit: flattenCategory(produit) });
    }

    if (event.httpMethod === 'DELETE') {
      const d = parseBody(event.body);
      if (!d.id_produit) return err(400, { success: false, message: 'ID requis' });

      const { count } = await supabase
        .from('reservations')
        .select('*', { count: 'exact', head: true })
        .eq('id_produit', d.id_produit)
        .in('statut_reservation', ['en_attente', 'accepte']);
      if (count > 0) {
        return err(400, { success: false, message: 'Impossible de supprimer : réservations actives' });
      }

      const { error } = await supabase.from('produits').delete().eq('id_produit', d.id_produit);
      if (error) throw error;
      return ok({ success: true, message: 'Produit supprimé' });
    }

    return err(405, { success: false, message: 'Méthode non autorisée' });

  } catch (e) {
    console.error('api_produits error:', e);
    return err(500, { success: false, message: 'Erreur serveur' });
  }
};

const ok  = body => ({ statusCode: 200, headers: HEADERS, body: JSON.stringify(body) });
const err = (code, body) => ({ statusCode: code, headers: HEADERS, body: JSON.stringify(body) });
const parseBody = raw => { try { return JSON.parse(raw || '{}'); } catch { return {}; } };
