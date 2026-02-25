const { supabase } = require('./_utils/supabase');
const { getUserFromEvent } = require('./_utils/auth');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'PUT, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  const user = getUserFromEvent(event);
  if (!user || user.role !== 'administrateur') {
    return { statusCode: 401, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Non autorisé' }) };
  }

  if (event.httpMethod !== 'PUT') {
    return { statusCode: 405, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Méthode non autorisée' }) };
  }

  let d;
  try { d = JSON.parse(event.body || '{}'); } catch { d = {}; }

  if (!d.id_produit || d.quantite_disponible === undefined || d.quantite_totale === undefined) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Données manquantes' }) };
  }

  const qDis = parseInt(d.quantite_disponible);
  const qTot = parseInt(d.quantite_totale);

  if (qDis < 0 || qTot < 0 || qDis > qTot) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Quantités invalides' }) };
  }

  try {
    const { error } = await supabase
      .from('produits')
      .update({ quantite_disponible: qDis, quantite_totale: qTot })
      .eq('id_produit', d.id_produit);

    if (error) throw error;

    return {
      statusCode: 200,
      headers: HEADERS,
      body: JSON.stringify({
        succes: true,
        message: 'Quantités mises à jour avec succès',
        donnees: { id_produit: d.id_produit, quantite_disponible: qDis, quantite_totale: qTot },
      }),
    };
  } catch (err) {
    console.error('api_update_quantites error:', err);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Erreur serveur' }) };
  }
};
