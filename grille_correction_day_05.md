# ✅ Grille de Correction - PHP Day 05 (Symfony SQL & ORM)

Basée sur le document détaillé d’évaluation 42 (Intra Projects Symfony - 2 - SQL Edit), cette grille reprend les points à valider pour chaque exercice, tels qu’attendus pendant la peer-evaluation.

---

## 📜 Prérequis généraux (obligatoires)

- [ ] Le projet est bien présent dans le dépôt Git
- [ ] Le projet est un seul projet Symfony avec des bundles pour chaque exo
- [ ] L'étudiant sait expliquer chaque partie de son code
- [ ] Aucun exercice ne provoque de crash, segfault ou erreur fatale
- [ ] Tous les tests doivent être faits dans une VM ou sur cluster
- [ ] Les règles globales et celles propres à la journée sont respectées


---

## 💡 Critères par exercice

### ex00 - SQL
- [ ] Création d’une table via SQL brut
- [ ] La requête est exécutée depuis un service ou classe, **pas dans le contrôleur**
- [ ] La table ne provoque pas d’erreur si elle existe déjà
- [ ] Une route Symfony crée la table avec message de succès/erreur

### ex01 - ORM
- [ ] Création d’une entité Doctrine conforme aux spécifications
- [ ] Migrations Doctrine exécutées sans erreur
- [ ] Pas de SQL brut utilisé

### ex02 - SQL - insert & read
- [ ] Formulaire HTML fonctionne (Symfony)
- [ ] Les insertions se font avec requêtes SQL uniquement
- [ ] Une table HTML affiche le contenu
- [ ] Aucun plantage en cas de doublon ou de table déjà existante

### ex03 - ORM - insert & read
- [ ] Formulaire lié à une entité Doctrine
- [ ] Les opérations ORM ne sont pas faites dans le contrôleur (service ou repository)
- [ ] Affichage HTML du contenu de la table
- [ ] Aucune requête SQL utilisée

### ex04 - SQL - delete
- [ ] Suppression via SQL brut, avec condition id
- [ ] Route de type `/delete/{id}` fonctionnelle
- [ ] Bouton de suppression présent dans tableau
- [ ] Message de succès/erreur affiché

### ex05 - ORM - delete
- [ ] Suppression via Doctrine ORM uniquement
- [ ] Aucune suppression directe dans le contrôleur
- [ ] Message de retour affiché selon réussite

### ex06 - SQL - update
- [ ] Formulaire pré-rempli
- [ ] Requête SQL de mise à jour exécutée proprement
- [ ] Message d’état affiché

### ex07 - ORM - update
- [ ] Formulaire Doctrine
- [ ] Update avec Repository / EntityManager
- [ ] Message de confirmation

### ex08 - SQL - relations
- [ ] Table persons sans adresse créée via SQL
- [ ] ALTER TABLE pour ajouter un champ
- [ ] Création de bank_accounts et addresses
- [ ] OneToOne et OneToMany en SQL corrects

### ex09 - ORM - relations
- [ ] Entité Person + nouveaux champs
- [ ] Entités Address et BankAccount avec mapping bidirectionnel
- [ ] Utilisation correcte des annotations Doctrine
- [ ] Migration Doctrine fonctionnelle

### ex10 - Lecture de fichier SQL & ORM
- [ ] Lecture d’un fichier réalisée (csv, txt...)
- [ ] Données insérées en SQL ET via ORM
- [ ] Affichage HTML de chaque source

### ex11 - SQL - join / tri / filtrage
- [ ] Jointure sur plusieurs tables
- [ ] Filtres, tri dynamique dans requête
- [ ] Affichage dans un tableau HTML

### ex12 - ORM - jointure & repo custom
- [ ] Requête complexe via Repository Doctrine
- [ ] Filtres et tri intégrés

### ex13 - CRUD ORM complet
- [ ] Entité Employee avec les bons types (ENUM, datetime, etc.)
- [ ] CRUD complet avec FormType Symfony
- [ ] Validation des champs : required, unique, format
- [ ] Sécurité et messages de retour gérés

### ex14 - SQL injection
- [ ] Création d’une table via SQL
- [ ] Formulaire HTML vulnérable à une injection
- [ ] Preuve visible de l’injection (modif ou altération de données)

---

## 🔹 Méthodologie d'évaluation (CodeX ou orale)

- Stopper au premier exercice non valide pour la note finale
- Poursuivre les tests pour discussion et progression
- Noter les erreurs bloquantes, techniques ou de logique
- Valider l’explication des choix techniques par l’étudiant

---

Bonne évaluation ✨

