const { supabase } = require('./_utils/supabase');
const { getUserFromEvent } = require('./_utils/auth');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

/**
 * Upload d'image vers Supabase Storage.
 * Reçoit un body JSON : { imageBase64, filename, type, mimeType }
 * Retourne : { succes, donnees: { url } }
 */
exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  const user = getUserFromEvent(event);
  if (!user || user.role !== 'administrateur') {
    return { statusCode: 401, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Non autorisé' }) };
  }

  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Méthode non autorisée' }) };
  }

  let d;
  try { d = JSON.parse(event.body || '{}'); } catch {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Corps invalide' }) };
  }

  if (!d.imageBase64 || !d.filename) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'imageBase64 et filename requis' }) };
  }

  const typesAutorisés = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  const mimeType = d.mimeType || 'image/jpeg';
  if (!typesAutorisés.includes(mimeType)) {
    return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Type de fichier non autorisé' }) };
  }

  try {
    const buffer = Buffer.from(d.imageBase64, 'base64');
    if (buffer.length > 5 * 1024 * 1024) {
      return { statusCode: 400, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Fichier trop volumineux (5 Mo max)' }) };
    }

    const bucket = d.type === 'categorie' ? 'categories' : 'products';
    const ext = mimeType.split('/')[1].replace('jpeg', 'jpg');
    const path = `${Date.now()}_${d.filename.replace(/[^a-zA-Z0-9._-]/g, '_')}.${ext}`;

    const { error: upErr } = await supabase.storage
      .from(bucket)
      .upload(path, buffer, { contentType: mimeType, upsert: false });

    if (upErr) throw upErr;

    const { data: urlData } = supabase.storage.from(bucket).getPublicUrl(path);

    return {
      statusCode: 200,
      headers: HEADERS,
      body: JSON.stringify({
        succes: true,
        message: 'Image uploadée avec succès',
        donnees: { url: urlData.publicUrl, nom_fichier: path, type: bucket },
      }),
    };
  } catch (err) {
    console.error('api_upload_image error:', err);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ succes: false, message: 'Erreur lors de l\'upload' }) };
  }
};
