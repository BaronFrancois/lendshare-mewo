const bcrypt = require('bcryptjs');
const { supabase } = require('./_utils/supabase');
const { createJwt, buildSetCookie } = require('./_utils/auth');

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

  if (!data.email || !data.mot_de_passe) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Email et mot de passe requis' }) };
  }

  try {
    const { data: user, error } = await supabase
      .from('utilisateurs')
      .select('*')
      .eq('email_utilisateur', data.email)
      .single();

    if (error || !user) {
      return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Email ou mot de passe incorrect' }) };
    }

    const motDePasseValide = bcrypt.compareSync(data.mot_de_passe, user.mot_de_passe_utilisateur);
    if (!motDePasseValide) {
      return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Email ou mot de passe incorrect' }) };
    }

    const token = createJwt(user);

    return {
      statusCode: 200,
      headers: { ...HEADERS, 'Set-Cookie': buildSetCookie(token) },
      body: JSON.stringify({
        succes: true,
        message: 'Connexion réussie',
        utilisateur: {
          id: user.id_utilisateur,
          email: user.email_utilisateur,
          role_utilisateur: user.role_utilisateur,
          nom_utilisateur: user.nom_utilisateur,
          prenom_utilisateur: user.prenom_utilisateur,
        },
      }),
    };
  } catch (err) {
    console.error('api_connexion error:', err);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Erreur serveur' }) };
  }
};
