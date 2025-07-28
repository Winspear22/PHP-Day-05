#!/bin/bash

# Vérification des paramètres
if [ $# -ne 2 ]; then
  echo "Usage: ./create-controller.sh NomDuController NomDuBundle"
  exit 1
fi

CONTROLLER_NAME="$1"
BUNDLE_NAME="$2"
BUNDLE_DIR="src/${BUNDLE_NAME}"
CONTROLLER_DIR="${BUNDLE_DIR}/Controller"
CONTROLLER_FILE="${CONTROLLER_DIR}/${CONTROLLER_NAME}.php"

# Vérification de l'existence du bundle, sinon le créer
if [ ! -d "$BUNDLE_DIR" ]; then
  echo "Le bundle ${BUNDLE_NAME} n'existe pas, création automatique..."
  ./create-bundle.sh "$BUNDLE_NAME"
  if [ $? -ne 0 ]; then
    echo "Erreur : Impossible de créer le bundle ${BUNDLE_NAME}."
    exit 1
  fi
fi

# Création du dossier Controller si besoin
mkdir -p "${CONTROLLER_DIR}"

# Création du fichier du contrôleur
cat <<EOL > "${CONTROLLER_FILE}"
<?php

namespace App\\${BUNDLE_NAME}\\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ${CONTROLLER_NAME} extends AbstractController
{
    /**
     * @Route("/${BUNDLE_NAME,,}", name="${BUNDLE_NAME,,}_index")
     */
    public function index(): Response
    {
        return new Response("Hello from ${CONTROLLER_NAME}!");
    }
}
EOL

echo "Contrôleur ${CONTROLLER_NAME}.php créé dans ${CONTROLLER_DIR}."
