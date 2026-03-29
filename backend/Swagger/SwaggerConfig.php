<?php

namespace maquinas_recreativas\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Maquinas Recreativas",
    version: "1.0.0",
    description: "Documentación de la API"
)]
#[OA\Server(
    url: "http://localhost/maquinas-recreativas/backend/public",
    description: "Servidor local"
)]

#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]

class SwaggerConfig {}