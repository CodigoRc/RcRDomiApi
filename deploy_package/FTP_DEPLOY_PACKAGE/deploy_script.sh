#!/bin/bash

# 🚀 SCRIPT DE DEPLOY AUTOMATIZADO - RcRDomiApi
# Versión: 1.0
# Fecha: $(date)

echo "🎵 RDOMI BROADCASTING - Deploy Script"
echo "======================================"
echo ""

# Variables
BACKUP_DIR="./backups/$(date +%Y%m%d_%H%M%S)"
LARAVEL_ROOT="."
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para logging
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

# Función de respaldo
backup_files() {
    log "Creando respaldo de archivos actuales..."
    
    mkdir -p "$BACKUP_DIR"
    
    # Respaldar archivos críticos
    if [ -f "$LARAVEL_ROOT/app/Http/Controllers/RdomiServiceStatsController.php" ]; then
        cp "$LARAVEL_ROOT/app/Http/Controllers/RdomiServiceStatsController.php" "$BACKUP_DIR/"
        success "Controlador respaldado"
    else
        warning "Controlador no encontrado (primera instalación)"
    fi
    
    if [ -f "$LARAVEL_ROOT/routes/api.php" ]; then
        cp "$LARAVEL_ROOT/routes/api.php" "$BACKUP_DIR/"
        success "Rutas respaldadas"
    else
        error "Archivo de rutas no encontrado!"
        exit 1
    fi
    
    if [ -f "$LARAVEL_ROOT/app/Models/ServiceStats.php" ]; then
        cp "$LARAVEL_ROOT/app/Models/ServiceStats.php" "$BACKUP_DIR/"
        success "Modelo respaldado"
    else
        warning "Modelo no encontrado (primera instalación)"
    fi
    
    success "Respaldo completado en: $BACKUP_DIR"
}

# Función para verificar dependencias
check_dependencies() {
    log "Verificando dependencias..."
    
    # Verificar PHP
    if ! command -v php &> /dev/null; then
        error "PHP no está instalado"
        exit 1
    fi
    success "PHP encontrado: $(php -v | head -n1)"
    
    # Verificar Composer
    if ! command -v composer &> /dev/null; then
        error "Composer no está instalado"
        exit 1
    fi
    success "Composer encontrado: $(composer --version | head -n1)"
    
    # Verificar MySQL
    if ! command -v mysql &> /dev/null; then
        warning "MySQL client no encontrado (ejecutar SQL manualmente)"
    else
        success "MySQL client encontrado"
    fi
}

# Función para limpiar cache
clear_cache() {
    log "Limpiando cache de Laravel..."
    
    cd "$LARAVEL_ROOT"
    
    php artisan config:clear
    php artisan route:clear
    php artisan cache:clear
    php artisan view:clear
    
    success "Cache limpiado"
}

# Función para verificar conectividad WebSocket
check_websocket() {
    log "Verificando conectividad con servidor WebSocket..."
    
    if curl -s -o /dev/null -w "%{http_code}" "https://rx.netdomi.com:3001/health" | grep -q "200"; then
        success "Servidor WebSocket accesible"
    else
        warning "Servidor WebSocket no accesible - verificar configuración"
    fi
}

# Función para verificar endpoints
test_endpoints() {
    log "Verificando endpoints..."
    
    local base_url="https://$(hostname)"
    
    # Test endpoint básico
    if curl -s -X POST "$base_url/api/rdomi/sts/service/ping" \
        -H "Content-Type: application/json" \
        -d '{"service_id": 1}' | grep -q "200"; then
        success "Endpoint básico funcionando"
    else
        error "Endpoint básico falló"
    fi
    
    # Test endpoint avanzado
    if curl -s -X POST "$base_url/api/rdomi/sts/service/ping-advanced" \
        -H "Content-Type: application/json" \
        -d '{"service_id": 1}' | grep -q "200"; then
        success "Endpoint avanzado funcionando"
    else
        error "Endpoint avanzado falló"
    fi
}

# Función principal
main() {
    echo "🚀 Iniciando deploy de RcRDomiApi..."
    echo ""
    
    # Verificar que estamos en el directorio correcto
    if [ ! -f "artisan" ]; then
        error "No se encontró artisan.php - asegúrate de estar en el directorio raíz de Laravel"
        exit 1
    fi
    
    # Ejecutar pasos
    check_dependencies
    backup_files
    clear_cache
    check_websocket
    
    echo ""
    success "Deploy completado exitosamente!"
    echo ""
    echo "📋 Próximos pasos:"
    echo "1. Ejecutar SQL: source database_stats_tables.sql"
    echo "2. Verificar endpoints con: ./deploy_script.sh --test"
    echo "3. Revisar logs en: storage/logs/laravel.log"
    echo ""
    echo "🔄 Para rollback: ./deploy_script.sh --rollback"
}

# Función de rollback
rollback() {
    log "Iniciando rollback..."
    
    if [ ! -d "$BACKUP_DIR" ]; then
        error "No se encontró directorio de respaldo"
        exit 1
    fi
    
    # Restaurar archivos
    if [ -f "$BACKUP_DIR/RdomiServiceStatsController.php" ]; then
        cp "$BACKUP_DIR/RdomiServiceStatsController.php" "$LARAVEL_ROOT/app/Http/Controllers/"
        success "Controlador restaurado"
    fi
    
    if [ -f "$BACKUP_DIR/api.php" ]; then
        cp "$BACKUP_DIR/api.php" "$LARAVEL_ROOT/routes/"
        success "Rutas restauradas"
    fi
    
    if [ -f "$BACKUP_DIR/ServiceStats.php" ]; then
        cp "$BACKUP_DIR/ServiceStats.php" "$LARAVEL_ROOT/app/Models/"
        success "Modelo restaurado"
    fi
    
    clear_cache
    success "Rollback completado"
}

# Función de test
test() {
    log "Ejecutando tests de endpoints..."
    test_endpoints
}

# Manejo de argumentos
case "$1" in
    --rollback)
        rollback
        ;;
    --test)
        test
        ;;
    --help)
        echo "Uso: $0 [opción]"
        echo "Opciones:"
        echo "  --rollback    Revertir cambios"
        echo "  --test        Probar endpoints"
        echo "  --help        Mostrar esta ayuda"
        ;;
    *)
        main
        ;;
esac 