const jwt = require('jsonwebtoken');

const COOKIE_NAME = 'jwt_lendshare';

function parseCookies(header) {
  const cookies = {};
  if (!header) return cookies;
  header.split(';').forEach(c => {
    const [k, ...v] = c.trim().split('=');
    if (k) cookies[k.trim()] = v.join('=');
  });
  return cookies;
}

function getJwtFromEvent(event) {
  const cookieHeader = event.headers.cookie || event.headers.Cookie || '';
  return parseCookies(cookieHeader)[COOKIE_NAME] || null;
}

function verifyJwt(token) {
  try {
    return jwt.verify(token, process.env.JWT_SECRET);
  } catch {
    return null;
  }
}

function createJwt(user) {
  return jwt.sign(
    {
      id: user.id_utilisateur,
      email: user.email_utilisateur,
      role: user.role_utilisateur,
      nom: user.nom_utilisateur,
      prenom: user.prenom_utilisateur
    },
    process.env.JWT_SECRET,
    { expiresIn: '1h' }
  );
}

function buildSetCookie(token) {
  if (!token) {
    return `${COOKIE_NAME}=; Path=/; HttpOnly; Secure; SameSite=Strict; Max-Age=0`;
  }
  return `${COOKIE_NAME}=${token}; Path=/; HttpOnly; Secure; SameSite=Strict; Max-Age=3600`;
}

function getUserFromEvent(event) {
  const token = getJwtFromEvent(event);
  if (!token) return null;
  return verifyJwt(token);
}

module.exports = { createJwt, buildSetCookie, getUserFromEvent };
