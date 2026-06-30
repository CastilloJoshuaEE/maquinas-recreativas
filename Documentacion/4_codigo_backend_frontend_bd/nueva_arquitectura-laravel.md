
backend/

├── .env                                    # Variables
de entorno (Laravel + tu app)

├── .env.example                            # Template de
variables

├── .gitignore

├── artisan                                 # CLI de Laravel

├── composer.json                           # Dependencias
actualizado

├── composer.lock

├── package.json                            # Para assets (si
usas Vite)

│

├── app/                                    # 🆕
Núcleo de Laravel

│   ├──
Console/

│   │   ├── Kernel.php

│   │   └── Commands/

│   │       ├── RunTestsCommand.php

# Ejecutar pruebas

│   │       ├── SetupDatabaseCommand.php

# Configurar BD

│   │       ├── SeedUsersCommand.php

# Insertar usuarios iniciales

│   │       └── GenerateDocsCommand.php     # Generar documentación

│   │

│   ├──
Http/

│   │   ├── Kernel.php

│   │   ├── Controllers/

│   │   │   ├──
Controller.php

│   │   │
└── Api/                        # 🆕
Controladores Laravel (adaptadores)

│   │   │
├── AuthController.php

│   │   │
├── MaquinaController.php

│   │   │
├── ComercioController.php

│   │   │
├── ComponenteController.php

│   │   │
├── ReporteController.php

│   │   │
├── ComentarioController.php

│   │   │
├── NotificacionController.php

│   │   │
├── RecaudacionController.php

│   │   │
├── InformeController.php

│   │   │
├── DistribucionController.php

│   │   │
├── MontajeController.php

│   │   │
├── HistorialController.php

│   │   │
├── UsuarioController.php

│   │   │
├── AdministradorController.php

│   │   │
├── TecnicoController.php

│   │   │
└── ContabilidadController.php

│   │   │

│   │   ├── Middleware/

│   │   │   ├──
AuthMiddleware.php          # 🆕
Adaptadores de tus middlewares

│   │   │   ├──
CorsMiddleware.php

│   │   │   ├──
JsonResponseMiddleware.php

│   │   │   ├──
RateLimitMiddleware.php

│   │   │
└── RoleMiddleware.php

│   │   │

│   │   └── Requests/

│   │       ├── LoginRequest.php

│   │       ├── RegisterRequest.php

│   │       ├── MaquinaRequest.php

│   │       └── ReporteRequest.php

│   │

│   ├──
Providers/

│   │   ├── AppServiceProvider.php

│   │   ├── AuthServiceProvider.php

│   │   ├── EventServiceProvider.php

│   │   ├── RouteServiceProvider.php

│   │   └── HexagonalServiceProvider.php    # 🆕 Integración con tu
arquitectura

│   │

│   └── Exceptions/

│       └──
Handler.php                     # Manejo
de excepciones personalizado

│

├── src/                                    # 📦
TU CÓDIGO EXISTENTE (sin cambios)

│   ├──
Application/

│   │   ├── Commands/

│   │   │   ├──
Command.php

│   │   │   ├──
CommandHandler.php

│   │   │   ├──
Comentario/

│   │   │   ├──
Comercio/

│   │   │   ├──
Componente/

│   │   │   ├──
Maquina/

│   │   │   ├──
Notificacion/

│   │   │   ├──
Recaudacion/

│   │   │   ├──
Reporte/

│   │   │   ├──
Usuario/

│   │   │
└── Email/

│   │   └── Queries/

│   │       ├── Query.php

│   │       ├── QueryHandler.php

│   │       └── [todos los módulos de queries]

│   │

│   ├──
Domain/

│   │   ├── Comentario/

│   │   ├── Comercio/

│   │   ├── Componente/

│   │   ├── Distribucion/

│   │   ├── Historial/

│   │   ├── Maquina/

│   │   ├── Montaje/

│   │   ├── Notificacion/

│   │   ├── Recaudacion/

│   │   ├── Reporte/

│   │   └── Shared/

│   │

│   ├──
Infrastructure/

│   │   ├── Database/

│   │   ├── Repositories/

│   │   ├── Security/

│   │   └── Services/

│   │

│   └── Core/

│       ├──
App.php

│       ├──
MiddlewarePipeline.php

│       ├──
Request.php

│       ├──
Response.php

│       └── Router.php

│

├── Bootstrap/                              # 🔧
Bootstrapping (se mantiene)

│   ├──
app.php                             # ← Ahora integrado con Laravel

│   ├──
cors.php

│   ├──
env.php

│   ├──
rate_limit.php

│   ├──
security.php

│   └── session.php

│

├── Config/                                 # ⚙️
Configuración (se mantiene)

│   ├──
.usuarios_iniciales.lock

│   ├──
app.php

│   ├── constants.php

│   ├──
database.php

│   ├──
dependencies.php

│   ├──
env.php

│   └── Inserter.php

│

├── database/
    # 🗄️
Migraciones y seeds (Laravel)

│   ├──
migrations/

│   │   └── [Tus migraciones desde MySQL a
PostgreSQL]

│   ├──
seeders/

│   │   └── DatabaseSeeder.php

│   └── factories/

│

├── public/                                 # 🌍
Público (se mantiene)

│   ├──
index.php                           # ← Ahora con Laravel + tu código

│   ├──
.htaccess

│   ├──
robots.txt

│   ├──
serve.php

│   └── docs/

│

├── storage/                                # 💾
Almacenamiento

│   ├──
app/

│   ├──
framework/

│   ├──
logs/

│   └──
rate_limits.json

│

├── tests/                                  # 🧪
Pruebas (Laravel + tu código)

│   ├──
Unit/                               #
Nuevas pruebas Laravel

│   │   ├── ExampleTest.php

│   │   └── Hexagonal/

│   │       ├── DomainTest.php

│   │       └── ApplicationTest.php

│   │   └── Api/

│   │

│   ├──
Smoke/                              # 🔥
Pruebas Smoke (se mantienen)

│   │   ├── SmokeTestCase.php

│   │   ├── SmokeHealthTest.php

│   │   ├── SmokeAuthTest.php

│   │   ├── SmokeMaquinaTest.php

│   │   └── SmokeReporteTest.php

│   │

│   ├──
Functional/                         #
Pruebas funcionales

│   │   ├── HttpTestCase.php

│   │   ├── UserFlowsTest.php

│   │   ├── AdminFlowsTest.php

│   │   └── ReporteFlowsTest.php

│   │

│   ├──
Domain/                             #
Pruebas de dominio

│   │   ├── UsuarioTest.php

│   │   ├── MaquinaTest.php

│   │   └── [todos los Domain tests]

│   │

│   ├──
Application/                        #
Pruebas de aplicación

│   │   ├── Commands/

│   │   └── Queries/

│   │

│   ├──
Integration/                        #
Pruebas de integración

│   │   ├── ChatUsuarioIntegrationTest.php

│   │   └── [todos los Integration tests]

│   │

│   ├──
Security/                           #
Pruebas de seguridad

│   │   └── security-test.php

│   │

│   ├──
Performance/                        #
Pruebas de rendimiento

│   │   ├── StressTest.php

│   │   ├── HttpStressTestCase.php

│   │   └── run_stress_test.php

│   │

│   ├──
TestDatabase.php

│   ├──
bootstrap.php

│   └──
phpunit.xml                         # ←
Configuración unificada

│

├── Scripts/                                # 📜
Scripts de utilidad

│   ├──
hash.php

│   └── insertar-usuarios-iniciales.php

│

├── Swagger/                                # 📚
Documentación API

│   └──
SwaggerConfig.php

│

└── vendor/                                 # 📦
Dependencias de Composer