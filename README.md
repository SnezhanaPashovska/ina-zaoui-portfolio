# Portfolio d'Ina Zaoui ✨

Bienvenue dans le projet **Portfolio d'Ina Zaoui** ! Il s'agit d'un portfolio personnel développé avec **Symfony**. Ce site présente divers projets et compétences en photographie, ainsi que des photos ajoutées par des invités.

Le site permet à **Ina Zaoui**, une photographe, de gérer ses propres photos, mais aussi de partager des photos soumises par d'autres invités. Les visiteurs peuvent découvrir une galerie dynamique et enrichie de contenus visuels.

---

## 📌 Présentation

Ce projet est une application Symfony permettant aux utilisateurs de gérer des albums et d'ajouter des médias.

## 🚀 Prérequis

- PHP 8.1 ou supérieur
- Composer
- PostgreSQL (ou une autre base de données de votre choix)
- Symfony CLI pour exécuter l'application localement

---

## 🛠️ Installation

### 1. Cloner le Répertoire

Clonez le dépôt sur votre machine locale :

```bash
git clone https://github.com/SnezhanaPashovska/ina-zaoui-portfolio.git
cd ina-zaoui-portfolio
```

### 2. Installer les Dépendances

Exécutez la commande suivante pour installer les dépendances PHP nécessaires :

```bash
composer install
```

## ⚙️ Configuration

### 1. Configurer la Base de Données :

Ouvrez le fichier .env et mettez à jour l'URL de la base de données avec vos informations PostgreSQL :

DATABASE_URL="postgresql://<utilisateur>:<mot_de_passe>@127.0.0.1:5432/ina_zaoui"

Remplacez <utilisateur> et <mot_de_passe> par vos identifiants.

## 🚀 Utilisation

### 1. Démarrer le Serveur Symfony :

Lancez le serveur avec :

```bash
symfony server:start
```

### 2. Accéder au Projet :

Une fois le serveur démarré, ouvrez votre navigateur et accédez à http://localhost:8000 pour voir le portfolio.

## 🧪 Tests

Pour exécuter les tests :

```bash
php bin/phpunit
```

## ## 🤝 Contribuer

Si vous souhaitez contribuer, veuillez consulter le fichier [CONTRIBUTING.md](./CONTRIBUTING.md) pour des instructions détaillées sur la manière de contribuer au projet.
