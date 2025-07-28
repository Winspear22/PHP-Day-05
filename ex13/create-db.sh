#!/bin/bash

# Vérification de l'argument
if [ -z "$1" ]; then
  echo "Usage: ./create-db.sh nom_de_la_base"
  exit 1
fi

DB_NAME=$1
MYSQL_ROOT_PASSWORD="user42"  # <- modifie ici si besoin

echo "👉 Création de la base '$DB_NAME' et de l'utilisateur 'admin'..."

# Exécution sécurisée avec mot de passe root
sudo mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS 'admin'@'127.0.0.1' IDENTIFIED BY 'adminadmin';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO 'admin'@'127.0.0.1';
FLUSH PRIVILEGES;
EOF

# Vérifie si la commande précédente a réussi
if [ $? -eq 0 ]; then
  echo "✅ Base de données '$DB_NAME' et utilisateur 'admin' créés avec succès."
else
  echo "❌ Une erreur est survenue lors de la création."
fi
