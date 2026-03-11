#!/bin/bash
# =============================================================================
# Script de respaldo de base de datos - BibliotecaOnline
# Issue #30 - Autor: Javier Antonio Romo Bernal
# =============================================================================

# Cargar variables del .env de Laravel
ENV_FILE="$(dirname "$0")/../.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "ERROR: No se encontro el archivo .env en la raiz del proyecto."
    exit 1
fi

DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" "$ENV_FILE" | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d '=' -f2)

# Valores por defecto si estan vacios
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}

# Verificar que mysqldump esta instalado
if ! command -v mysqldump &> /dev/null; then
    echo "ERROR: mysqldump no esta instalado. Instala MySQL client para continuar."
    exit 1
fi

# Directorio donde se guardan los backups
BACKUP_DIR="$(dirname "$0")/../storage/backups"
mkdir -p "$BACKUP_DIR"

# Nombre del archivo con timestamp
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_DATABASE}_${TIMESTAMP}.sql"

echo "Iniciando respaldo de la base de datos: ${DB_DATABASE}"
echo "Archivo de salida: ${BACKUP_FILE}"

# Ejecutar mysqldump
MYSQL_PWD="$DB_PASSWORD" mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    "$DB_DATABASE" > "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "Respaldo completado exitosamente."
    echo "Archivo: ${BACKUP_FILE}"
    echo "Tamano: $(du -h "$BACKUP_FILE" | cut -f1)"
else
    echo "ERROR: Fallo el respaldo de la base de datos."
    rm -f "$BACKUP_FILE"
    exit 1
fi
