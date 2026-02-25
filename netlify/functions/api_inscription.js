const bcrypt = require('bcryptjs');
const { supabase } = require('./_utils/supabase');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Méthode non autorisée' }) };
  }

  let data;
  try {
    data = JSON.parse(event.body || '{}');
  } catch {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Corps de requête invalide' }) };
  }

  if (!data.nom || !data.prenom || !data.email || !data.mot_de_passe) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Tous les champs sont requis' }) };
  }

  try {
    // Vérifier si l'email existe déjà
    const { data: existing } = await supabase
      .from('utilisateurs')
      .select('id_utilisateur')
      .eq('email_utilisateur', data.email)
      .maybeSingle();

    if (existing) {
      return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Cet email est déjà utilisé' }) };
    }

    const hash = bcrypt.hashSync(data.mot_de_passe, 12);

    const { error } = await supabase.from('utilisateurs').insert({
      nom_utilisateur: data.nom,
      prenom_utilisateur: data.prenom,
      email_utilisateur: data.email,
      mot_de_passe_utilisateur: hash,
      role_utilisateur: 'utilisateur',
    });

    if (error) throw error;

    return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: true, message: 'Inscription réussie' }) };
  } catch (err) {
    console.error('api_inscription error:', err);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Erreur serveur' }) };
  }
};
