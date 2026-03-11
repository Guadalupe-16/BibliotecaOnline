#!/bin/bash
# =============================================================================
# Script de restauracion de base de datos - BibliotecaOnline
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

# Verificar que mysql esta instalado
if ! command -v mysql &> /dev/null; then
    echo "ERROR: mysql client no esta instalado. Instala MySQL client para continuar."
    exit 1
fi

# Validar argumento: archivo SQL a restaurar
if [ -z "$1" ]; then
    BACKUP_DIR="$(dirname "$0")/../storage/backups"
    echo "Uso: $0 <archivo.sql>"
    echo ""
    echo "Backups disponibles en ${BACKUP_DIR}:"
    ls -lh "$BACKUP_DIR"/*.sql 2>/dev/null || echo "  (no hay backups disponibles)"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERROR: El archivo '${BACKUP_FILE}' no existe."
    exit 1
fi

echo "ADVERTENCIA: Esto sobreescribira la base de datos '${DB_DATABASE}'."
read -r -p "¿Deseas continuar? (s/N): " CONFIRM

if [ "$CONFIRM" != "s" ] && [ "$CONFIRM" != "S" ]; then
    echo "Restauracion cancelada."
    exit 0
fi

echo "Iniciando restauracion desde: ${BACKUP_FILE}"

MYSQL_PWD="$DB_PASSWORD" mysql \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    "$DB_DATABASE" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo "Restauracion completada exitosamente."
else
    echo "ERROR: Fallo la restauracion de la base de datos."
    exit 1
fi
