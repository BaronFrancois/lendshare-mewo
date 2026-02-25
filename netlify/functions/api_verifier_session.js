const { supabase } = require('./_utils/supabase');
const { getUserFromEvent } = require('./_utils/auth');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  const payload = getUserFromEvent(event);
  if (!payload) {
    return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ connecte: false, utilisateur: null }) };
  }

  try {
    const { data: user, error } = await supabase
      .from('utilisateurs')
      .select('id_utilisateur, nom_utilisateur, prenom_utilisateur, email_utilisateur, role_utilisateur')
      .eq('id_utilisateur', payload.id)
      .single();

    if (error || !user) {
      return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ connecte: false, utilisateur: null }) };
    }

    return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ connecte: true, utilisateur: user }) };
  } catch (err) {
    console.error('api_verifier_session error:', err);
    return { statusCode: 200, headers: HEADERS, body: JSON.stringify({ connecte: false, utilisateur: null }) };
  }
};
