# Système de Logs - Lend&Share

Ce dossier contient les fichiers de logs de l'application pour faciliter le débogage.

## Fichier principal

- `app.log` : Contient tous les logs de l'application

## Niveaux de logs

- **DEBUG** : Informations détaillées pour le débogage
- **INFO** : Informations générales sur le fonctionnement
- **WARNING** : Avertissements (non bloquants)
- **ERROR** : Erreurs nécessitant une attention

## Format des logs

Chaque ligne de log suit ce format :
```
[YYYY-MM-DD HH:MM:SS] [NIVEAU] Message | Contexte: {"clé":"valeur"}
```

Exemple :
```
[2025-01-15 14:30:22] [INFO] Connexion réussie | Contexte: {"utilisateur_id":"1","email":"admin@lendshare.fr","role":"administrateur"}
```

## Comment consulter les logs

### En local
```bash
# Voir les dernières lignes
tail -n 50 logs/app.log

# Suivre les logs en temps réel
tail -f logs/app.log

# Rechercher des erreurs
grep ERROR logs/app.log
```

### Sur Railway
Les logs sont également disponibles via :
```bash
railway logs
```

## Utilisation dans le code

Le système de logs est disponible via `backend/utils/logger.php` :

```php
require_once __DIR__ . '/../utils/logger.php';

// Fonctions disponibles
logDebug("Message de debug", ['contexte' => 'valeur']);
logInfo("Information générale");
logWarning("Attention", ['détails' => 'info']);
logError("Erreur critique", ['erreur' => 'description']);
logException($exception, "Message additionnel");
```

## Maintenance

Les fichiers de logs peuvent devenir volumineux. Pour nettoyer :

```bash
# Vider le fichier de log
> logs/app.log

# Ou supprimer et recréer
rm logs/app.log
touch logs/app.log
```

## Note pour Railway

Sur Railway, les logs sont stockés dans le conteneur éphémère. Ils seront perdus lors d'un redémarrage ou redéploiement. Pour une solution de production, considérez :
- Un service de logs externe (Papertrail, Loggly, etc.)
- Une base de données pour stocker les logs critiques
- Railway Logs pour consulter les logs en temps réel
