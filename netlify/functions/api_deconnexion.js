const { buildSetCookie } = require('./_utils/auth');

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

  return {
    statusCode: 200,
    headers: { ...HEADERS, 'Set-Cookie': buildSetCookie(null) },
    body: JSON.stringify({ succes: true, message: 'Déconnexion réussie' }),
  };
};
