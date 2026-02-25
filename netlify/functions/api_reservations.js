const { supabase } = require('./_utils/supabase');
const { getUserFromEvent } = require('./_utils/auth');

const HEADERS = {
  'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, POST, PUT, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

exports.handler = async (event) => {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 200, headers: HEADERS, body: '' };
  }

  try {
    const params = event.queryStringParameters || {};

    if (event.httpMethod === 'GET') {
      let query = supabase
        .from('reservations')
        .select('*, produits(nom_produit, image_url_produit), utilisateurs(nom_utilisateur, prenom_utilisateur, email_utilisateur)')
        .order('date_reservation', { ascending: false });

      if (params.id_utilisateur) {
        query = query.eq('id_utilisateur', params.id_utilisateur);
      }

      const { data, error } = await query;
      if (error) throw error;

      const reservations = data.map(r => {
        const { produits, utilisateurs, ...rest } = r;
        return {
          ...rest,
          nom_produit: produits?.nom_produit ?? null,
          image_url_produit: produits?.image_url_produit ?? null,
          nom_utilisateur: utilisateurs?.nom_utilisateur ?? null,
          prenom_utilisateur: utilisateurs?.prenom_utilisateur ?? null,
          email_utilisateur: utilisateurs?.email_utilisateur ?? null,
        };
      });
      return ok({ success: true, reservations });
    }

    if (event.httpMethod === 'POST') {
      const d = parseBody(event.body);
      if (!d.id_utilisateur || !d.id_produit) {
        return bad({ success: false, message: 'id_utilisateur et id_produit requis' });
      }
      const { error } = await supabase.from('reservations').insert({
        id_utilisateur: d.id_utilisateur,
        id_produit: d.id_produit,
        statut_reservation: 'en_attente',
      });
      if (error) throw error;
      return ok({ success: true, message: 'Réservation créée' });
    }

    if (event.httpMethod === 'PUT') {
      const d = parseBody(event.body);
      if (!d.id_reservation || !d.statut_reservation) {
        return bad({ success: false, message: 'id_reservation et statut_reservation requis' });
      }

      // Lire l'ancienne réservation pour ajuster les quantités
      const { data: ancien, error: readErr } = await supabase
        .from('reservations')
        .select('id_produit, statut_reservation')
        .eq('id_reservation', d.id_reservation)
        .single();
      if (readErr || !ancien) return bad({ success: false, message: 'Réservation non trouvée' });

      const { error } = await supabase
        .from('reservations')
        .update({
          statut_reservation: d.statut_reservation,
          date_modification_statut: new Date().toISOString(),
        })
        .eq('id_reservation', d.id_reservation);
      if (error) throw error;

      // Ajuster quantite_disponible
      if (ancien.statut_reservation !== 'accepte' && d.statut_reservation === 'accepte') {
        await supabase.rpc('decrementer_quantite', { produit_id: ancien.id_produit });
      } else if (ancien.statut_reservation === 'accepte' && d.statut_reservation !== 'accepte') {
        await supabase.rpc('incrementer_quantite', { produit_id: ancien.id_produit });
      }

      return ok({ success: true, message: 'Statut mis à jour' });
    }

    return { statusCode: 405, headers: HEADERS, body: JSON.stringify({ success: false, message: 'Méthode non autorisée' }) };

  } catch (e) {
    console.error('api_reservations error:', e);
    return { statusCode: 500, headers: HEADERS, body: JSON.stringify({ success: false, message: 'Erreur serveur' }) };
  }
};

const ok  = body => ({ statusCode: 200, headers: HEADERS, body: JSON.stringify(body) });
const bad = body => ({ statusCode: 400, headers: HEADERS, body: JSON.stringify(body) });
const parseBody = raw => { try { return JSON.parse(raw || '{}'); } catch { return {}; } };
