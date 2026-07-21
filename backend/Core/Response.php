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
    
    public function isSent(): bool
    {
        return $this->sent;
    }
    
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
        
        if (is_array($data) && !isset($data['success'])) {
            $data['success'] = $statusCode >= 200 && $statusCode < 300;
        }
        
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

        // ✅ Limpiar buffers de salida
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // ✅ Aplicar código de estado
        http_response_code($this->statusCode);

        // ✅ Enviar headers
        foreach ($this->headers as $name => $value) {
            if (!headers_sent()) {
                header("{$name}: {$value}");
            }
        }

        // ✅ Enviar contenido
        if ($this->content !== null) {
            echo $this->content;
        }
        
        // ✅ Forzar flush
        flush();
        
        // ✅ No llamar a exit() aquí para permitir que el controlador continúe
        // exit;
    }
    
    public function getContent(): mixed
    {
        return $this->content;
    }
}