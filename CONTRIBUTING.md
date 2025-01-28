# Contribuer à Ina Zaoui Portfolio

Merci de vouloir contribuer à **Ina Zaoui Portfolio** ! Voici les étapes simples pour commencer.

---

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants :

- [PHP](https://www.php.net/) (version 8.1 ou supérieure)
- [Composer](https://getcomposer.org/) (pour gérer les dépendances PHP)
- [PostgreSQL](https://www.postgresql.org/) (pour la base de données)

---

## Étapes pour contribuer

1. **Cloner le projet :**

   - Récupérez le code source en local :
     ```bash
     git clone https://github.com/SnezhanaPashovska/ina-zaoui-portfolio.git
     cd ina-zaoui-portfolio
     ```

2. **Installer les dépendances PHP :**

   - Assurez-vous d'avoir PHP version 8.1 ou plus récent installé.
   - Installez les dépendances nécessaires :
     ```bash
     composer install
     ```

3. **Configurer la base de données :**

   - Ouvrez le fichier `.env` et mettez à jour la ligne `DATABASE_URL` avec vos informations :
     ```env
     DATABASE_URL="postgresql://<utilisateur>:<mot_de_passe>@127.0.0.1:5432/ina_zaoui"
     ```

4. **Exécuter les migrations :**

   - Créez les tables de la base de données :
     ```bash
     php bin/console doctrine:migrations:migrate
     ```

5. **Lancer le projet :**
   - Utilisez Symfony pour démarrer le serveur de développement :
     ```bash
     symfony server:start
     ```

---

## Quelques conseils

- Assurez-vous que vos fichiers PHP fonctionnent sans erreurs.
- Testez vos modifications avant de les partager.
- Restez simple et clair dans vos ajouts de code.

---

Merci d'avoir pris le temps de contribuer ! 🎉
