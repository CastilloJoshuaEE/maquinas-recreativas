<?php
/**
 * backend/Bootstrap/rate_limit.php
 */

use maquinas_recreativas\Infrastructure\Security\RateLimiter;

function applyRateLimit(string $requestUri): bool {
    
    $strictRateLimitRoutes = [
        '/usuario/login',
        '/usuario/register'
    ];
    
    $rateLimiter = RateLimiter::getInstance();
    
    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $clientKey = $clientIP;
    
    $isLocalRequest = in_array($clientIP, ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1']);
    
    if (in_array($requestUri, $strictRateLimitRoutes)) {
        $maxRequests = $isLocalRequest ? 60 : 5;
        $timeWindow = $isLocalRequest ? 60 : 300;
    } else {
        $maxRequests = $isLocalRequest ? 300 : 60;
        $timeWindow = 60;
    }
    
    if (defined('TEST_ENVIRONMENT') && TEST_ENVIRONMENT === true) {
        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: 9999');
        return true;
    }
    
    if (!$rateLimiter->check($clientKey, $maxRequests, $timeWindow)) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.'
        ]);
        return false;
    }
    
    header('X-RateLimit-Limit: ' . $maxRequests);
    header('X-RateLimit-Remaining: ' . $rateLimiter->getRemaining($clientKey, $maxRequests, $timeWindow));
    
    return true;
}

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