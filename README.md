# 🏥 AbricotDepo - Application web de location d'outils

API RESTful développée avec PHP/Slim pour la gestion des locations d'outils.

## Lien vers le dépôt git

[https://github.com/fefe168dd/AbricotDepot]

## Lien vers les maquettes FIGMA

[https://www.figma.com/design/P8keJtN8ZSxD5xoi2tyyQG/AbricotD%C3%A9po?node-id=0-1&t=nYe7mkZAVSljAuu6-1]



## 🚀 Installation et Lancement

### Prérequis
- Docker et Docker Compose
- Git

### Installation
```bash
git clone [URL_DU_REPO]
cd AbricotDepo
# Copier le fichier de configuration
cp app/config/.env.dist app/config/.env
# Lancer les services
docker-compose up -d
```

### Vérification
```bash
curl http://localhost:11000/
```

## 🧪 Tests

### Comptes de Test
- Email: `Denis.Teixeira@hotmail.fr` / Mot de passe: `Denis.Teixeira`
- Email: `Bertrand.Caron@yahoo.fr` / Mot de passe: `Bertrand.Caron`

### Exemple de Test
```bash
# 1. Se connecter
Cliquer sur l'icone de profil une fois dans l'accueil ou mettre http://localhost:11000/connexion puis
rentrer l'email et le mot de passe

# 2. Ajouter au panier
Cliquer sur n'importe quel produit une fois connecté, mettre les dates de début et de fin de réservation
puis choisir la quantité, puis cliquer sur ajouter au panier

# 3. Réserver
Après avoir ajouter des objets au panier, cliquer sur l'icone de panier ou mettre http://localhost:11000/panier, puis cliquer sur le bouton réserver
```

## 📋 Fonctionnalités Implémentées

| # | Fonctionnalité | Endpoint | Statut |
|---|----------------|----------|--------|
| 1 | Inscription | `POST /inscription` | ✅ |
| 2 | Authentification | `POST /connexion` | ✅ |
| 3 | Consulter panier | `GET /panier` | ✅ |
| 4 | Consulter profile | `GET /profile` | ✅ |
| 5 | Consulter détail d'un produit | `GET /{id}` | ✅ |
| 6 | Ajouter au panier | `POST /{id}/ajouterPanier'` | ✅ |
| 7 | Réserver | `POST /panier/reserver` | ✅ |
| 8 | Se déconnecter | `GET /deconnexion` | ✅ |


## 🏗️ Architecture

- **Architecture hexagonale** : Domain, Application, Infrastructure
- **5 bases PostgreSQL** distinctes (auth, outil, panier, reservation, stock)
- **Authentification JWT** avec middlewares d'autorisation
- **API RESTful** avec liens HATEOAS
- **Docker** avec docker-compose

## 🔧 Configuration

### JWT Secret
Le JWT Secret est configuré dans `app/config/.env`.


## 📊 Tableau de Bord des Réalisations

### Fonctionnalités Implémentées
- ✅ Architecture hexagonale + inversion de dépendances
- ✅ API RESTful (URIs, méthodes HTTP, status codes, JSON, HATEOAS)
- ✅ Authentification JWT + middlewares d'autorisation
- ✅ Validation des données + headers CORS
- ✅ Bases de données distinctes + Docker
- ✅ Fonctionnalités minimales toutes implémentées
- Fonctionnalités étendues ajoutée:
  • ✅Pagination du catalogue, filtrage du catalogue
  • ✅Persistence du panier tant que l’utilisateur n’a pas payé
  • ✅Modification du panier

### Réalisations par Membre du Groupe
| Fonctionnalités | Membre | 
|--------|---------------------------|
| Authentification JWT, Middlewares | **Félicien, Léo** |
| API RESTful, Validation des données, HATEOAS, Docker | **Félicien, Léo, Doryann, Ryad** |
| Structure de base de données | **Félicien, Léo** | 
| Catalogue des outils |**Doryann, Léo**
| Détail d'un outil | **Léo** |
| Ajouter outil dans panier | **Léo** |
| Panier | **Ryad** |
| Réserver panier | **Ryad** |
| SCSS | **Doryann** | 
| Authentification | **Doryann, Félicien** |
| UML | **Ryad** |
| Maquettes | **Léo** |
| Readme | **Léo** |
