# Ina Zaoui ✨

## 📌 Présentation
Bienvenue dans le projet Portfolio d'Ina Zaoui !

Il s'agit d'un projet développé dans le cadre de la formation OpenClassrooms. Ce site web, réalisé avec Symfony, simule un portfolio de photographe permettant la gestion d'albums et de médias.

---

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

## 🤝 Contribuer

Si vous souhaitez contribuer, veuillez consulter le fichier [CONTRIBUTING.md](./CONTRIBUTING.md) pour des instructions détaillées sur la manière de contribuer au projet.
