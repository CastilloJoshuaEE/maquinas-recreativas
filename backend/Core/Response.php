<?php
/**
 * maquinas_recreativas - Core Response
 * 
 * Abstracción de la respuesta HTTP.
 * 
 * @package maquinas_recreativas\Core
 * @author Tu Equipo
 * @version 1.0
 */
namespace maquinas_recreativas\Core;

class Response
{
    private array $headers = [];
    private mixed $content = null;
    private int $statusCode = 200;
    private bool $sent = false;
    
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    public function getHeader(string $name, $default = null)
    {
        return $this->headers[$name] ?? $default;
    }
    
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    public function content(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function json($data, int $statusCode = 200): self
    {
        $this->status($statusCode);
        $this->header('Content-Type', 'application/json; charset=utf-8');
        
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            $json = json_encode([
                'success' => false, 
                'message' => 'Error interno al generar respuesta',
                'error' => json_last_error_msg()
            ]);
        }
        $this->content = $json;
        return $this;
    }
    
    public function send(): void
    {
        if ($this->sent) {
            return;
        }
        $this->sent = true;

        // Limpiar cualquier salida previa
        if (ob_get_level() > 0) {
            ob_clean();
        }

        // Aplicar código de estado
        http_response_code($this->statusCode);

        // Enviar headers
        foreach ($this->headers as $name => $value) {
            if (!headers_sent()) {
                header("{$name}: {$value}");
            }
        }

        // Enviar contenido
        if ($this->content !== null) {
            echo $this->content;
        }

        // Finalizar el buffer
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        exit;
    }
    
    public function getContent(): mixed
    {
        return $this->content;
    }
    /**
     * Respuesta de error estandarizada para el frontend
     */
    public function error(string $message, int $statusCode = 400, ?string $errorCode = null): self
    {
        $data = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($errorCode) {
            $data['error_code'] = $errorCode;
        }

        // En desarrollo, agregar detalles adicionales
        if (getenv('APP_ENV') === 'development' && !empty($errorCode)) {
            $data['debug'] = $this->getErrorDebugInfo($errorCode);
        }

        return $this->json($data, $statusCode);
    }   
    /**
     * Mapeo de errores a mensajes amigables
     */
    private function getErrorDebugInfo(string $errorCode): ?array
    {
        $debugInfo = [
            'USER_NOT_FOUND_BY_EMAIL' => [
                'technical' => 'Usuario no encontrado con ese email',
                'suggestion' => 'Verifica que el email esté registrado en el sistema'
            ],
            'USER_USERNAME_EXISTS' => [
                'technical' => 'Nombre de usuario ya está en uso',
                'suggestion' => 'Prueba con otro nombre de usuario'
            ],
            'USER_USERNAME_TOO_SHORT' => [
                'technical' => 'El nombre de usuario debe tener al menos 3 caracteres',
                'suggestion' => 'Elige un nombre más largo'
            ],
            'USER_INVALID_EMAIL' => [
                'technical' => 'Formato de email inválido',
                'suggestion' => 'Ejemplo: usuario@correo.com'
            ]
        ];

        return $debugInfo[$errorCode] ?? null;
    } 
}