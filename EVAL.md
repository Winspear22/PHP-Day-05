✅ PHP Day 05 – Évaluation complète (42)

Ce document contient les règles officielles à respecter pour réussir l'évaluation de chaque exercice du projet "Training PHP Symfony - 1 (SQL)" (aussi appelé PHP Day 05).

📊 Règles générales (valables pour tous les exercices)

Le projet doit être réalisé dans une machine virtuelle.

Tous les exercices doivent être faits dans un seul projet Symfony.

Chaque exercice est dans son propre bundle : Ex00Bundle, Ex01Bundle, etc.

Chaque exercice a sa propre route de base : /e00, /e01, etc.

Chaque exercice doit avoir sa propre table dans la base de données.

Utiliser SQL ou Doctrine ORM uniquement selon ce que l'exercice impose (pas de mélange !).

Les routes doivent fonctionner avec et sans / final.

Le HTML des pages doit être valide (doctype, html, head, body...).

Le projet ne doit provoquer aucune erreur fatale : segmentation fault, bus error, etc.

Les messages de succès/échec doivent être affichés sur les pages concernées.

Aucun accès direct à la base ne doit être fait dans le contrôleur si c'est interdit.

📒 Détails des exercices (sujet + règles de correction)

⬛ ex00 : Créer une table avec du SQL pur

Utiliser uniquement du SQL (pas de Doctrine)

La table doit s'appeler persons

Champs : id, username, name, email, enable, birthdate, address

Route pour créer la table, avec message déjà créé si besoin

⬜ ex01 : Créer une table avec ORM (Doctrine)

Créer une entité Doctrine correspondant à la table persons

Champs identiques à ex00

Créer la table via migration

Aucun SQL ne doit être utilisé

⬛ ex02 : Insérer et lire des données avec SQL

Créer une nouvelle table (structure ex00)

Utiliser uniquement SQL pour insérer et afficher

Formulaire Symfony pour saisir les données

Tableau HTML pour afficher les données

⬜ ex03 : Insérer et lire avec ORM

Créer une nouvelle entité Doctrine

Utiliser uniquement ORM (pas de SQL)

Formulaire lié à l'entité (FormType)

Affichage des données dans un tableau

Pas d'appel direct au manager/ORM dans le contrôleur (via Repository)

⬛ ex04 : Suppression conditionnelle avec SQL

Nouvelle table dédiée

Suppression via SQL uniquement

Route avec paramètre /delete?id=...

Bouton "Supprimer" sur chaque ligne

Message déjà supprimé / réussi / échec

⬜ ex05 : Suppression avec ORM

Nouvelle entité

Utiliser uniquement Doctrine

Route avec paramètre pour supprimer

Pas de suppression dans le contrôleur directement

Affichage du résultat avec message

⬛ ex06 : Mise à jour avec SQL

Nouvelle table dédiée

Affichage sous forme de tableau avec bouton "Modifier"

Formulaire pré-rempli

Mise à jour via SQL uniquement

Succès/échec affiché

⬜ ex07 : Mise à jour avec ORM

Nouvelle entité Doctrine

Affichage + formulaire éditable

Update via ORM uniquement

Messages d’état affichés sur la page

⬛ ex08 : SQL - Altération de table et relations

Créer table persons (sans address)

Ajouter un champ marital_status (ENUM)

Créer bank_accounts et addresses

Créer une relation one-to-one et one-to-many via SQL uniquement

⬜ ex09 : ORM - Altération de table et relations

Entité Person avec nouveau champ

Créer BankAccount & Address

Relations bidirectionnelles Doctrine

Doctrine migrations pour les modifs

⬛ ex10 : Lecture depuis un fichier (SQL & ORM)

Lire un fichier (txt, CSV...)

Insérer les données à la fois en SQL et via ORM

Affichage des deux tables

⬜ ex11 : Requête complexe SQL (JOIN, sort, filter)

Créer une requête SQL complexe avec JOIN, ORDER BY, WHERE

Affichage dans un tableau HTML

Filtres / tri configurables

⬜ ex12 : Requête complexe ORM

Même but que ex11 mais avec ORM uniquement

Utiliser les relations et Repository personnalisés

⬜ ex13 : CRUD complet en ORM

Entité Employee avec types variés (string, datetime, ENUM, etc.)

Formulaire complet avec validation

CRUD complet : créer, lire, modifier, supprimer

Messages d’erreur si champ dupliqué ou invalide

⬛ ex14 : SQL Injection (démo)

Créer une table + formulaire

Injection SQL fonctionnelle en JS (sur submit)

Effet visible (drop, alter, etc.) sur la page d’accueil

🚀 Evaluation via CodeX ou Deepthought

Utilisez ce fichier pour guider les tests automatiques ou manuels :

Lire la section de chaque exercice

Vérifier si la route fonctionne, le bundle existe, le rendu est HTML5

Exécuter la logique métier : création, suppression, update, etc.

Vérifier que l'approche technique (SQL/ORM) est la bonne

🚫 Aucun SQL dans les exos ORM, aucun ORM dans les exos SQL !
📅 Tous les exercices doivent être livrés dans le repo avant peer-eval

Bonne chance pour ton évaluation ✨ !


