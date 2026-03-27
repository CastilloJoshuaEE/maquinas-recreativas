<?php
/**
 * backend/Bootstrap/rate_limit.php
 * maquinas_recreativas - Rate Limiting Configuration
 * 
 * Configura y aplica las reglas de rate limiting.
 * 
 * @package maquinas_recreativas\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */

use maquinas_recreativas\Infrastructure\Security\RateLimiter;

/**
 * Aplica rate limiting a la petición actual
 * 
 * @param string $requestUri URI de la petición
 * @return bool True si pasa el rate limit, false si excede
 */
function applyRateLimit(string $requestUri): bool {
    
    // Endpoints con rate limit más estricto (públicos)
    $strictRateLimitRoutes = [
        '/usuario/login',
        '/usuario/register'
    ];
    
    $rateLimiter = RateLimiter::getInstance();
    
    // Obtener IP del cliente
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $clientKey = $clientIP;
    
    // Detectar si es petición local (para límites más altos en desarrollo)
    $isLocalRequest = in_array($clientIP, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);
    
    // Configurar límites según el endpoint y entorno
    if (in_array($requestUri, $strictRateLimitRoutes)) {
        // Endpoints sensibles: límite más bajo
        $maxRequests = $isLocalRequest ? 60 : 5;
        $timeWindow = $isLocalRequest ? 60 : 300; // 1 min local, 5 min producción
    } else {
        // Endpoints normales
        $maxRequests = $isLocalRequest ? 300 : 60;
        $timeWindow = 60;
    }
    
    // Desactivar rate limiting en tests
    if (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true) {
        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: 9999');
        return true;
    }
    
    // Verificar rate limit
    if (!$rateLimiter->check($clientKey, $maxRequests, $timeWindow)) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.'
        ]);
        return false;
    }
    
    // Añadir headers informativos
    header('X-RateLimit-Limit: ' . $maxRequests);
    header('X-RateLimit-Remaining: ' . $rateLimiter->getRemaining($clientKey, $maxRequests, $timeWindow));
    
    return true;
}

/**
 * Endpoint especial para resetear rate limits (solo local)
 */
function handleRateLimitReset(): void {
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $isLocal = in_array($clientIP, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);
    
    if (!$isLocal) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit();
    }
    
    header('Content-Type: application/json');
    $rateLimiter = RateLimiter::getInstance();
    $rateLimiter->resetAll();
    
    echo json_encode(['success' => true, 'message' => 'Rate limits reseteados']);
    exit();
}