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

4. **Initialisation de base de données:**

5. Créer la base de données: php bin/console doctrine:database:create
6. Générer la migration: php bin/console make:migration
7. Exécuter la migration : php bin/console doctrine:migrations:migrate
8. Charger les fixtures: php bin/console doctrine:fixtures:load

9. **Lancer le projet :**

   - Utilisez Symfony pour démarrer le serveur de développement :

     ```bash
     symfony server:start
     ```

---

## Workflow GitHub

1. **Issues :** Avant de commencer à travailler sur une fonctionnalité ou une correction de bug, ouvrez une issue pour discuter de l'approche avec l'équipe.

2. **Pull Requests (PR) :** Créez une pull request pour chaque modification importante. Assurez-vous que vos commits sont clairs et atomiques.

3. **Code Review :** Après avoir soumis votre PR, un autre contributeur procédera à une revue de code. Vous devrez répondre aux commentaires et apporter des modifications si nécessaire.

## Respect des bonnes pratiques

Pour garantir la qualité du code et la cohérence du projet, voici quelques bonnes pratiques à suivre :

- **Lisibilité :** Utilisez des noms de variables et de fonctions explicites. Evitez les abréviations ambiguës.

- **Convention de codage :** Respectez les conventions de codage PHP recommandées par PSR (PSR-1, PSR-2, PSR-12). Cela garantit que le code reste uniforme et facile à lire pour tous les contributeurs.

- **Documentation :** Documentez les nouvelles fonctionnalités, les classes et les fonctions avec des commentaires clairs et utiles. N'oubliez pas de mettre à jour les fichiers de documentation si nécessaire (ex : README.md, CONTRIBUTING.md).

- **Tests unitaires :** Ajoutez des tests unitaires pour valider les nouvelles fonctionnalités ou les corrections. Cela assure la stabilité du projet.

- **Sécurité :** Soyez vigilant à la sécurité des données. Par exemple, validez toujours les entrées utilisateur et protégez le code contre les injections SQL et autres vulnérabilités.

## Utilisation des tests et outils d’analyse statique

Afin d’assurer que le code est fonctionnel, robuste, et sécurisé, il est essentiel de suivre les bonnes pratiques en matière de tests et d’analyse statique.

## Tests unitaires avec PHPUnit

Avant de soumettre une pull request, exécutez les tests pour vous assurer que vos modifications ne cassent rien :

```bash
php bin/phpunit
```

Ajoutez des tests unitaires pour toute nouvelle fonctionnalité ou correction de bug.

## Analyse statique du code

L'analyse statique permet d’identifier des erreurs potentielles dans le code sans l'exécuter. Utilisez PHPStan ou Psalm pour améliorer la qualité du code.

Exemple d'exécution avec PHPStan :

```bash
vendor/bin/phpstan analyse
```

Cela vous aidera à détecter des erreurs telles que des variables non initialisées ou des types incohérents.

## Documentation pour les développeurs

Une bonne documentation est essentielle pour le bon fonctionnement du projet, tant pour les nouveaux contributeurs que pour ceux qui maintiendront le code dans le futur.

### Comment maintenir le projet

- **Mises à jour des dépendances :** Lors de l’ajout de nouvelles dépendances, veillez à mettre à jour le fichier composer.json et à tester les nouvelles versions des packages.

- **Migration de la base de données :** Si vous ajoutez ou modifiez des entités Doctrine, assurez-vous de générer et exécuter les migrations.

- **Maintenir la cohérence :** Chaque nouvelle fonctionnalité doit suivre la structure existante du projet. Si vous ajoutez de nouvelles entités ou logiques, respectez les conventions du projet pour que tout soit clair et organisé.

## Quelques conseils

- Assurez-vous que vos fichiers PHP fonctionnent sans erreurs avant de soumettre vos modifications.
- Testez vos modifications localement avant de soumettre une pull request.
- Veillez à ce que vos commits soient bien détaillés et décrivent précisément ce qui a été modifié.
- Documentez toutes les nouvelles fonctionnalités ou les changements importants de manière claire.
- Utilisez des commentaires dans le code pour expliquer des sections complexes.

---

Merci d'avoir pris le temps de contribuer ! 🎉
