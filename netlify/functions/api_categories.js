const { supabase } = require('./_utils/supabase');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  if (event.httpMethod !== 'GET') {
    return { statusCode: 405, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Méthode non autorisée' }) };
  }

  try {
    const params = event.queryStringParameters || {};

    if (params.id) {
      const { data, error } = await supabase
        .from('categories')
        .select('*')
        .eq('id_categorie', params.id)
        .single();
      if (error) throw error;
      return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: true, donnees: data }) };
    }

    const { data, error } = await supabase
      .from('categories')
      .select('*')
      .order('nom_categorie');
    if (error) throw error;
    return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: true, donnees: data }) };

  } catch (err) {
    console.error('api_categories error:', err);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Erreur serveur' }) };
  }
};
