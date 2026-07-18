<?php
/**
 * Infrastructure/Services/Email/BrevoEmailService.php
 *
 * Servicio para enviar emails transaccionales via Brevo API v3.
 * Usa curl nativo de PHP (no requiere composer adicional).
 *
 * Variables de entorno requeridas en .env:
 *   BREVO_API_KEY=tu-api-key-de-brevo
 *   EMAIL_FROM_EMAIL=correo@tudominio.com
 *   EMAIL_FROM_NAME=RecreaSys
 */

namespace maquinas_recreativas\Infrastructure\Services\Email;

class BrevoEmailService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.brevo.com/v3';
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey    = $_ENV['BREVO_API_KEY']    ?? getenv('BREVO_API_KEY')    ?? '';
        $this->fromEmail = $_ENV['EMAIL_FROM_EMAIL'] ?? getenv('EMAIL_FROM_EMAIL') ?? 'noreply@recreasys.com';
        $this->fromName  = $_ENV['EMAIL_FROM_NAME']  ?? getenv('EMAIL_FROM_NAME')  ?? 'RecreaSys';
    }

    /**
     * Envía un email simple (sin adjuntos).
     *
     * @param string $destinatarioEmail Email del destinatario
     * @param string $destinatarioNombre Nombre del destinatario (puede ser vacío)
     * @param string $asunto Asunto del email
     * @param string $mensajeTexto Mensaje en texto plano
     * @param string|null $mensajeHtml Mensaje HTML opcional; si es null se usa el texto plano
     * @return array{ success: bool, messageId?: string, error?: string }
     */
    public function enviar(
        string $destinatarioEmail,
        string $destinatarioNombre,
        string $asunto,
        string $mensajeTexto,
        ?string $mensajeHtml = null
    ): array {
        if (empty($this->apiKey)) {
            error_log('BrevoEmailService: BREVO_API_KEY no configurada');
            return ['success' => false, 'error' => 'Servicio de email no configurado (falta BREVO_API_KEY)'];
        }

        // Generar HTML desde texto si no se proporciona
        $html = $mensajeHtml ?? $this->textoAHtml($mensajeTexto);

        $payload = [
            'sender'      => ['name' => $this->fromName, 'email' => $this->fromEmail],
            'to'          => [['email' => $destinatarioEmail, 'name' => $destinatarioNombre ?: $destinatarioEmail]],
            'subject'     => $asunto,
            'htmlContent' => $html,
            'textContent' => $mensajeTexto,
            'tags'        => ['recreasys', 'admin']
        ];

        return $this->post('/smtp/email', $payload);
    }

    /**
     * Verifica la conexión con la API de Brevo.
     */
    public function verificarConexion(): bool
    {
        if (empty($this->apiKey)) return false;

        $ch = curl_init($this->baseUrl . '/account');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['api-key: ' . $this->apiKey, 'accept: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function post(string $endpoint, array $data): array
    {
        $url  = $this->baseUrl . $endpoint;
        $json = json_encode($data);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => [
                'api-key: '    . $this->apiKey,
                'Content-Type: application/json',
                'accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("BrevoEmailService curl error: {$curlError}");
            return ['success' => false, 'error' => "Error de red: {$curlError}"];
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            error_log("BrevoEmailService: email enviado OK, messageId=" . ($decoded['messageId'] ?? ''));
            return ['success' => true, 'messageId' => $decoded['messageId'] ?? ''];
        }

        $errorMsg = $decoded['message'] ?? $responseBody;
        error_log("BrevoEmailService HTTP {$httpCode}: {$errorMsg}");
        return ['success' => false, 'error' => $errorMsg, 'code' => $httpCode];
    }

    private function textoAHtml(string $texto): string
    {
        $textoEscapado = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        $parrafos      = explode("\n\n", $textoEscapado);
        $html          = implode('', array_map(fn($p) => "<p>" . nl2br(trim($p)) . "</p>", $parrafos));

        return "<!DOCTYPE html>
<html>
<head><meta charset='utf-8'><style>
  body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
  .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden;
               box-shadow: 0 4px 6px rgba(0,0,0,.1); }
  .header { background: linear-gradient(135deg, #07224c, #124258); color: white; padding: 24px 20px; text-align: center; }
  .header h1 { margin: 0; font-size: 1.4rem; }
  .content { padding: 28px 30px; }
  .footer { text-align: center; padding: 16px; color: #6b7280; font-size: 13px; background: #f8fafc; }
</style></head>
<body>
  <div class='container'>
    <div class='header'><h1>RecreaSys</h1></div>
    <div class='content'>{$html}</div>
    <div class='footer'><p>Recrea Sys S.A. — Sistema de Gestión de Máquinas Recreativas</p></div>
  </div>
</body>
</html>";
    }
}