# Documentation des routes de l’API CarbuSmart

## 1. Informations générales

### 1.1 URL de base

L’URL de base utilisée dans l’environnement de développement est :

`http://carbusmart.test/api`

L’URL de production sera définie ultérieurement lors du déploiement de l’application.

### 1.2 Format des données

Les données envoyées et reçues par l’API utilisent le format JSON.

Les requêtes contenant des données doivent utiliser l’en-tête HTTP suivant :

`Content-Type: application/json`

### 1.3 Authentification

L’authentification sera assurée par Laravel Sanctum.

Après sa connexion, l’utilisateur recevra un token d’accès. Ce token devra être transmis pour accéder aux routes protégées :

`Authorization: Bearer {token}`

Les routes d’inscription, de connexion, de consultation des types de carburants et de consultation des stations resteront accessibles sans authentification.

L’identifiant de l’utilisateur ne devra pas être envoyé manuellement dans les requêtes protégées. L’API le récupérera automatiquement à partir du token Sanctum.

### 1.4 Structure générale des réponses

Les réponses de l’API contiendront :

- un code de statut HTTP ;
- un message indiquant le résultat de l’opération ;
- les données demandées ou créées, lorsque cela est pertinent.

Exemple général d’une réponse réussie :

```json
{
  "message": "Opération effectuée avec succès.",
  "data": {}
}
```

---

## 2. Vue d’ensemble des routes

| Méthode | Chemin | Description | Authentification |
|---|---|---|---|
| `POST` | `/auth/register` | Créer un compte | Non |
| `POST` | `/auth/login` | Se connecter | Non |
| `GET` | `/auth/me` | Consulter son profil | Oui |
| `PATCH` | `/auth/me` | Modifier son profil | Oui |
| `POST` | `/auth/logout` | Se déconnecter | Oui |
| `GET` | `/fuel-types` | Consulter les types de carburants | Non |
| `GET` | `/vehicles` | Consulter ses véhicules | Oui |
| `GET` | `/vehicles/{id}` | Consulter un véhicule | Oui |
| `POST` | `/vehicles` | Ajouter un véhicule | Oui |
| `PATCH` | `/vehicles/{id}` | Modifier un véhicule | Oui |
| `DELETE` | `/vehicles/{id}` | Désactiver un véhicule | Oui |
| `PATCH` | `/vehicles/{id}/default` | Définir le véhicule par défaut | Oui |
| `GET` | `/favorites` | Consulter ses favoris | Oui |
| `POST` | `/favorites` | Ajouter un favori | Oui |
| `DELETE` | `/favorites/{id}` | Retirer un favori | Oui |
| `GET` | `/stations` | Consulter les stations | Non |
| `GET` | `/stations/{stationStrapiId}` | Consulter une station | Non |
| `POST` | `/recommendations` | Calculer la station la plus avantageuse | Oui |

---

## 3. Routes d’authentification et du profil

### 3.1 Créer un compte utilisateur

Cette route permet à un nouvel utilisateur de créer un compte.

- **Méthode HTTP :** `POST`
- **Chemin :** `/auth/register`
- **URL complète :** `http://carbusmart.test/api/auth/register`
- **Authentification requise :** non

#### Données envoyées

```json
{
  "nom": "Sophie Test",
  "email": "sophie.test@carbusmart.local",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

#### Réponse en cas de succès

**Code HTTP :** `201 Created`

```json
{
  "message": "Compte créé avec succès.",
  "data": {
    "user": {
      "id": 1,
      "nom": "Sophie Test",
      "email": "sophie.test@carbusmart.local"
    },
    "token": "1|exemple_de_token_sanctum"
  }
}
```

### 3.2 Connecter un utilisateur

Cette route vérifie les identifiants de l’utilisateur et génère un token Sanctum.

- **Méthode HTTP :** `POST`
- **Chemin :** `/auth/login`
- **URL complète :** `http://carbusmart.test/api/auth/login`
- **Authentification requise :** non

#### Données envoyées

```json
{
  "email": "sophie.test@carbusmart.local",
  "password": "Password123!"
}
```

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Connexion réussie.",
  "data": {
    "user": {
      "id": 1,
      "nom": "Sophie Test",
      "email": "sophie.test@carbusmart.local"
    },
    "token": "1|exemple_de_token_sanctum"
  }
}
```

### 3.3 Consulter le profil connecté

Cette route retourne les informations de l’utilisateur authentifié.

- **Méthode HTTP :** `GET`
- **Chemin :** `/auth/me`
- **URL complète :** `http://carbusmart.test/api/auth/me`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Profil récupéré avec succès.",
  "data": {
    "id": 1,
    "nom": "Sophie Test",
    "email": "sophie.test@carbusmart.local"
  }
}
```

### 3.4 Modifier le profil connecté

Cette route modifie les informations du profil de l’utilisateur authentifié.

- **Méthode HTTP :** `PATCH`
- **Chemin :** `/auth/me`
- **URL complète :** `http://carbusmart.test/api/auth/me`
- **Authentification requise :** oui

#### Exemple de données envoyées

```json
{
  "nom": "Sophie Borgeaud",
  "email": "sophie.borgeaud@carbusmart.local"
}
```

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Profil modifié avec succès.",
  "data": {
    "id": 1,
    "nom": "Sophie Borgeaud",
    "email": "sophie.borgeaud@carbusmart.local"
  }
}
```

### 3.5 Déconnecter un utilisateur

Cette route supprime le token Sanctum utilisé pour la requête.

- **Méthode HTTP :** `POST`
- **Chemin :** `/auth/logout`
- **URL complète :** `http://carbusmart.test/api/auth/logout`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Déconnexion réussie."
}
```

---

## 4. Routes des types de carburants

### 4.1 Consulter les types de carburants

Cette route retourne la liste des types de carburants disponibles.

- **Méthode HTTP :** `GET`
- **Chemin :** `/fuel-types`
- **URL complète :** `http://carbusmart.test/api/fuel-types`
- **Authentification requise :** non

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Types de carburants récupérés avec succès.",
  "data": [
    {
      "id": 1,
      "nom": "Essence sans plomb 95",
      "code": "SP95"
    },
    {
      "id": 2,
      "nom": "Essence sans plomb 98",
      "code": "SP98"
    },
    {
      "id": 3,
      "nom": "Diesel",
      "code": "DIESEL"
    }
  ]
}
```

---

## 5. Routes des véhicules

Toutes les routes de cette section nécessitent une authentification.

L’utilisateur ne peut accéder qu’aux véhicules liés à son propre compte.

### 5.1 Consulter ses véhicules

Cette route retourne les véhicules actifs de l’utilisateur connecté.

- **Méthode HTTP :** `GET`
- **Chemin :** `/vehicles`
- **URL complète :** `http://carbusmart.test/api/vehicles`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Véhicules récupérés avec succès.",
  "data": [
    {
      "id": 1,
      "nom_vehicule": "Ma Yaris",
      "conso_moyenne": 5.4,
      "marque": "Toyota",
      "modele": "Yaris",
      "type_vehicule": "Citadine",
      "num_plaque": "JU 12345",
      "est_defaut": true,
      "est_actif": true,
      "type_carburant": {
        "id": 1,
        "nom": "Essence sans plomb 95",
        "code": "SP95"
      }
    }
  ]
}
```

### 5.2 Consulter un véhicule

Cette route retourne un véhicule appartenant à l’utilisateur connecté.

- **Méthode HTTP :** `GET`
- **Chemin :** `/vehicles/{id}`
- **Exemple :** `http://carbusmart.test/api/vehicles/1`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Véhicule récupéré avec succès.",
  "data": {
    "id": 1,
    "nom_vehicule": "Ma Yaris",
    "conso_moyenne": 5.4,
    "marque": "Toyota",
    "modele": "Yaris",
    "type_vehicule": "Citadine",
    "num_plaque": "JU 12345",
    "est_defaut": true,
    "est_actif": true,
    "type_carburant": {
      "id": 1,
      "nom": "Essence sans plomb 95",
      "code": "SP95"
    }
  }
}
```

### 5.3 Ajouter un véhicule

Cette route ajoute un véhicule au compte de l’utilisateur connecté.

- **Méthode HTTP :** `POST`
- **Chemin :** `/vehicles`
- **URL complète :** `http://carbusmart.test/api/vehicles`
- **Authentification requise :** oui

#### Données envoyées

```json
{
  "type_carburant_id": 1,
  "nom_vehicule": "Ma Yaris",
  "conso_moyenne": 5.4,
  "marque": "Toyota",
  "modele": "Yaris",
  "type_vehicule": "Citadine",
  "num_plaque": "JU 12345",
  "est_defaut": true
}
```

L’identifiant de l’utilisateur sera récupéré automatiquement à partir du token Sanctum. Le champ `est_actif` sera automatiquement initialisé à `true`.

#### Réponse en cas de succès

**Code HTTP :** `201 Created`

```json
{
  "message": "Véhicule créé avec succès.",
  "data": {
    "id": 1,
    "nom_vehicule": "Ma Yaris",
    "conso_moyenne": 5.4,
    "marque": "Toyota",
    "modele": "Yaris",
    "type_vehicule": "Citadine",
    "num_plaque": "JU 12345",
    "est_defaut": true,
    "est_actif": true,
    "type_carburant": {
      "id": 1,
      "nom": "Essence sans plomb 95",
      "code": "SP95"
    }
  }
}
```

### 5.4 Modifier un véhicule

Cette route modifie une ou plusieurs informations d’un véhicule appartenant à l’utilisateur connecté.

- **Méthode HTTP :** `PATCH`
- **Chemin :** `/vehicles/{id}`
- **Exemple :** `http://carbusmart.test/api/vehicles/1`
- **Authentification requise :** oui

#### Exemple de données envoyées

```json
{
  "nom_vehicule": "Yaris principale",
  "conso_moyenne": 5.2,
  "num_plaque": "JU 12345"
}
```

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Véhicule modifié avec succès.",
  "data": {
    "id": 1,
    "nom_vehicule": "Yaris principale",
    "conso_moyenne": 5.2,
    "marque": "Toyota",
    "modele": "Yaris",
    "type_vehicule": "Citadine",
    "num_plaque": "JU 12345",
    "est_defaut": true,
    "est_actif": true,
    "type_carburant": {
      "id": 1,
      "nom": "Essence sans plomb 95",
      "code": "SP95"
    }
  }
}
```

### 5.5 Désactiver un véhicule

Cette route effectue une suppression logique. Le véhicule n’est pas supprimé physiquement de la base de données : son champ `est_actif` passe à `false`.

- **Méthode HTTP :** `DELETE`
- **Chemin :** `/vehicles/{id}`
- **Exemple :** `http://carbusmart.test/api/vehicles/1`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Véhicule désactivé avec succès."
}
```

### 5.6 Définir un véhicule par défaut

Cette route définit le véhicule sélectionné comme véhicule par défaut. Les autres véhicules de l’utilisateur passent automatiquement à `est_defaut = false`.

- **Méthode HTTP :** `PATCH`
- **Chemin :** `/vehicles/{id}/default`
- **Exemple :** `http://carbusmart.test/api/vehicles/1/default`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Véhicule défini par défaut avec succès.",
  "data": {
    "id": 1,
    "nom_vehicule": "Ma Yaris",
    "est_defaut": true
  }
}
```

---

## 6. Routes des favoris

Toutes les routes de cette section nécessitent une authentification.

Les stations sont enregistrées dans Strapi. La base Laravel conserve uniquement leur identifiant Strapi.

### 6.1 Consulter ses stations favorites

Cette route retourne les favoris de l’utilisateur connecté.

- **Méthode HTTP :** `GET`
- **Chemin :** `/favorites`
- **URL complète :** `http://carbusmart.test/api/favorites`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Favoris récupérés avec succès.",
  "data": [
    {
      "id": 1,
      "station_strapi_id": "demo-station-001",
      "created_at": "2026-10-01T12:00:00Z"
    },
    {
      "id": 2,
      "station_strapi_id": "demo-station-002",
      "created_at": "2026-10-01T12:00:00Z"
    }
  ]
}
```

Les valeurs `demo-station-001` et `demo-station-002` sont temporaires. Elles devront être remplacées par de véritables `documentId` provenant de Strapi.

### 6.2 Ajouter une station aux favoris

Cette route ajoute une station Strapi aux favoris de l’utilisateur connecté.

- **Méthode HTTP :** `POST`
- **Chemin :** `/favorites`
- **URL complète :** `http://carbusmart.test/api/favorites`
- **Authentification requise :** oui

#### Données envoyées

```json
{
  "station_strapi_id": "demo-station-001"
}
```

#### Réponse en cas de succès

**Code HTTP :** `201 Created`

```json
{
  "message": "Station ajoutée aux favoris.",
  "data": {
    "id": 1,
    "station_strapi_id": "demo-station-001",
    "created_at": "2026-10-01T12:00:00Z"
  }
}
```

La combinaison de l’utilisateur et de l’identifiant de la station est unique. Un utilisateur ne peut donc pas ajouter deux fois la même station à ses favoris.

### 6.3 Retirer une station des favoris

Cette route supprime un favori appartenant à l’utilisateur connecté.

- **Méthode HTTP :** `DELETE`
- **Chemin :** `/favorites/{id}`
- **Exemple :** `http://carbusmart.test/api/favorites/1`
- **Authentification requise :** oui

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Station retirée des favoris."
}
```

---

## 7. Routes des stations-services

L’interface d’administration Strapi est actuellement accessible à l’adresse suivante :

`https://stations-732.lehmann-dev.ch/admin`

Cette adresse permet d’administrer les données. Elle ne constitue pas l’URL de l’API REST consommée par Laravel.

L’URL REST de Strapi sera probablement basée sur :

`https://stations-732.lehmann-dev.ch/api`

Le chemin exact de la collection des stations devra être confirmé selon le nom technique défini dans Strapi.

Les routes Laravel présentées ci-dessous permettront de récupérer les données de Strapi et de renvoyer une structure normalisée au front-end.

### 7.1 Consulter les stations-services

Cette route retourne les stations et leurs derniers prix disponibles.

- **Méthode HTTP :** `GET`
- **Chemin :** `/stations`
- **URL complète :** `http://carbusmart.test/api/stations`
- **Authentification requise :** non

#### Paramètres facultatifs

- `type_carburant` : code du carburant recherché ;
- `latitude` : latitude du lieu de départ ;
- `longitude` : longitude du lieu de départ ;
- `rayon_km` : rayon de recherche en kilomètres.

#### Exemple

`http://carbusmart.test/api/stations?type_carburant=SP95&latitude=47.364&longitude=7.344&rayon_km=20`

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Stations récupérées avec succès.",
  "data": [
    {
      "station_strapi_id": "exemple_document_id_strapi",
      "nom": "Station CarbuSmart Delémont",
      "adresse": "Rue Exemple 10",
      "npa": "2800",
      "localite": "Delémont",
      "latitude": 47.364,
      "longitude": 7.344,
      "carburants": [
        {
          "code": "SP95",
          "prix_litre": 1.79,
          "date_mise_a_jour": "2026-10-01T10:00:00Z"
        }
      ]
    }
  ]
}
```

### 7.2 Consulter une station-service

Cette route retourne les informations détaillées d’une station provenant de Strapi.

- **Méthode HTTP :** `GET`
- **Chemin :** `/stations/{stationStrapiId}`
- **Exemple :** `http://carbusmart.test/api/stations/exemple_document_id_strapi`
- **Authentification requise :** non

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Station récupérée avec succès.",
  "data": {
    "station_strapi_id": "exemple_document_id_strapi",
    "nom": "Station CarbuSmart Delémont",
    "adresse": "Rue Exemple 10",
    "npa": "2800",
    "localite": "Delémont",
    "latitude": 47.364,
    "longitude": 7.344,
    "carburants": [
      {
        "code": "SP95",
        "prix_litre": 1.79,
        "date_mise_a_jour": "2026-10-01T10:00:00Z"
      },
      {
        "code": "DIESEL",
        "prix_litre": 1.86,
        "date_mise_a_jour": "2026-10-01T10:00:00Z"
      }
    ]
  }
}
```

---

## 8. Route de recommandation

### 8.1 Rechercher la station la plus avantageuse

Cette route compare les stations compatibles avec le véhicule sélectionné.

Elle utilise :

- le type de carburant du véhicule ;
- sa consommation moyenne ;
- la quantité de carburant souhaitée ;
- le prix du carburant ;
- la distance routière aller-retour ;
- le périmètre de recherche.

- **Méthode HTTP :** `POST`
- **Chemin :** `/recommendations`
- **URL complète :** `http://carbusmart.test/api/recommendations`
- **Authentification requise :** oui

#### Données envoyées

```json
{
  "vehicule_id": 1,
  "quantite_litres": 40,
  "position_depart": {
    "latitude": 47.364,
    "longitude": 7.344
  },
  "rayon_km": 20
}
```

#### Réponse en cas de succès

**Code HTTP :** `200 OK`

```json
{
  "message": "Recommandation calculée avec succès.",
  "data": {
    "vehicule": {
      "id": 1,
      "nom_vehicule": "Ma Yaris",
      "conso_moyenne": 5.4,
      "carburant": "SP95"
    },
    "quantite_litres": 40,
    "stations": [
      {
        "rang": 1,
        "station_strapi_id": "exemple_document_id_strapi",
        "nom": "Station CarbuSmart Delémont",
        "prix_litre": 1.79,
        "date_mise_a_jour": "2026-10-01T10:00:00Z",
        "distance_aller_retour_km": 20,
        "cout_carburant_achete": 71.6,
        "cout_trajet": 1.93,
        "cout_total": 73.53
      }
    ],
    "station_recommandee": {
      "station_strapi_id": "exemple_document_id_strapi",
      "nom": "Station CarbuSmart Delémont",
      "cout_total": 73.53
    }
  }
}
```

Le coût du trajet est calculé selon la formule suivante :

`coût du trajet = (distance aller-retour × consommation moyenne / 100) × prix au litre`

Le coût total est calculé selon la formule suivante :

`coût total = (quantité souhaitée × prix au litre) + coût du trajet`

Les stations sont classées par coût total croissant.

---

## 9. Principaux codes de statut HTTP

| Code | Signification | Utilisation |
|---|---|---|
| `200 OK` | Requête réussie | Consultation, modification ou suppression réussie |
| `201 Created` | Ressource créée | Création d’un compte, d’un véhicule ou d’un favori |
| `400 Bad Request` | Requête incorrecte | Paramètres incompatibles ou calcul impossible |
| `401 Unauthorized` | Non authentifié | Token absent, invalide ou expiré |
| `403 Forbidden` | Accès interdit | Tentative d’accès aux données d’un autre utilisateur |
| `404 Not Found` | Ressource introuvable | Véhicule, favori ou station inexistant |
| `409 Conflict` | Conflit | Station déjà présente dans les favoris |
| `422 Unprocessable Entity` | Données invalides | Erreur de validation des données envoyées |
| `500 Internal Server Error` | Erreur interne | Erreur inattendue de l’application ou d’un service externe |

---

## 10. Remarques et éléments à confirmer

- Les routes protégées devront utiliser le middleware `auth:sanctum`.
- Les mots de passe ne devront jamais être retournés par l’API.
- L’utilisateur authentifié ne pourra accéder qu’à ses propres véhicules et favoris.
- La suppression d’un véhicule sera logique : le champ `est_actif` passera à `false`.
- Les données des stations et des prix seront récupérées depuis Strapi.
- Les identifiants fictifs `demo-station-001` et `demo-station-002` devront être remplacés par de vrais `documentId` Strapi.
- Le chemin REST exact de la collection Strapi doit encore être confirmé.
- La structure JSON réellement renvoyée par Strapi doit encore être confirmée.
- Les autorisations de lecture de l’API Strapi doivent encore être configurées ou vérifiées.
- Le service cartographique utilisé pour calculer les distances routières sera choisi ultérieurement.
- L’URL de production de l’API Laravel sera définie lors du déploiement.
- La vue synthétique du tableau de bord pourra utiliser les routes `/auth/me`, `/vehicles` et `/favorites` sans nécessiter obligatoirement une route supplémentaire.
- Les structures présentées constituent le contrat prévu de l’API. Elles pourront être ajustées pendant le développement si les structures réelles de Strapi ou du service cartographique l’exigent.
