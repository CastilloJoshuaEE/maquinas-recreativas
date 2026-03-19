<?php
/**
 * RecreaSys - Security Headers
 * 
 * Aplica todos los headers de seguridad necesarios.
 * 
 * @package RecreaSys\Bootstrap
 * @author Tu Equipo
 * @version 1.0
 */
/**
 * Aplica todos los headers de seguridad
 * 
 * @return void
 */
function applySecurityHeaders():void{
    // Ocultar información del servidor
    header("Server:SecureServer");
    // Prevenir MIME sniffing
    header("X-Content-Type-Options:nosniff");
    // Prevenir clickjacking
    header("X-Frame-Options:DENY");
    // Protección XSS para navegadores antiguos
    header("X-XSS-Protection:1;mode=block");
    // Política de referer
    header("Referrer-Policy:strict-origin-when-cross-origin");
    // Política de permisos
    header("Permissions-Policy:geolocation=(), microphone=(), camera=()");
    // Política de recursos cruzados
    header("X-Permitted-Cross-Domain-Policies:none");
    // Content Security Policy
    $cspRules = [
        "default-src 'self'",
        "connect-src 'self' http://localhost:5173 https://recreasys.infinityfree.me",
        "img-src 'self' data:",
        "script-src 'self'",
        "style-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "object-src 'none'",
        "font-src 'self'"
    ];
    header("Content-Security-Policy: " . implode('; ', $cspRules));    
    // Cache control
    header("Cache-Control:no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma:no-cache");
    header("Expires:0");
    // HSTS (Solo en HTTPS)
    // HSTS (solo en HTTPS)
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
    
    if ($isHttps) {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}    
// Aplicar headers
applySecurityHeaders();