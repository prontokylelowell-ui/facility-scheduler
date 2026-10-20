#!/bin/bash
# =============================================================================
# Facility Scheduler - PostgreSQL Backup Script
# Uses pg_dump with custom format (-Fc) for pg_restore compatibility
# Logical backup: dumps schema + data in a portable, compressed format
# =============================================================================

set -euo pipefail

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_NAME="${DB_NAME:-facility_scheduler}"
DB_USER="${DB_USER:-postgres}"
BACKUP_DIR="${BACKUP_DIR:-./backups}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.dump"
LOG_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.log"

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Starting backup of ${DB_NAME}..." | tee "${LOG_FILE}"
echo "  Host:     ${DB_HOST}:${DB_PORT}" | tee -a "${LOG_FILE}"
echo "  User:     ${DB_USER}" | tee -a "${LOG_FILE}"
echo "  Format:   Custom (-Fc) compressed" | tee -a "${LOG_FILE}"
echo "  Output:   ${BACKUP_FILE}" | tee -a "${LOG_FILE}"

pg_dump \
    --host="${DB_HOST}" \
    --port="${DB_PORT}" \
    --username="${DB_USER}" \
    --format=custom \
    --compress=9 \
    --verbose \
    --file="${BACKUP_FILE}" \
    "${DB_NAME}" 2>> "${LOG_FILE}"

BACKUP_SIZE="$(du -h "${BACKUP_FILE}" | awk '{print $1}')"

echo "" | tee -a "${LOG_FILE}"
echo "[$(date)] Backup completed successfully." | tee -a "${LOG_FILE}"
echo "  File:     ${BACKUP_FILE}" | tee -a "${LOG_FILE}"
echo "  Size:     ${BACKUP_SIZE}" | tee -a "${LOG_FILE}"

echo "" | tee -a "${LOG_FILE}"
echo "=== Restore instructions ===" | tee -a "${LOG_FILE}"
echo "  1. createdb -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USER} test_restore" | tee -a "${LOG_FILE}"
echo "  2. pg_restore -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USER} -d test_restore -v ${BACKUP_FILE}" | tee -a "${LOG_FILE}"
echo "  3. Compare row counts:" | tee -a "${LOG_FILE}"
echo "     SELECT 'bookings' AS tbl, COUNT(*) FROM ${DB_NAME}.public.bookings" | tee -a "${LOG_FILE}"
echo "     UNION ALL SELECT 'users', COUNT(*) FROM ${DB_NAME}.public.users;" | tee -a "${LOG_FILE}"

exit 0
