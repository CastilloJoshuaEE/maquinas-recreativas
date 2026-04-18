# Manual Técnico - Recrea Sys

**Sistema de Gestión de Máquinas Recreativas**
Arquitectura Hexagonal + DDD + CQRS

---

## 📌 Tabla de Contenidos

1. Arquitectura del Sistema
2. Estructura de Directorios
3. Flujo de Trabajo para Nueva Funcionalidad
4. Ejemplo Práctico: CRUD de Comercios
5. Patrones y Convenciones
6. Comandos Útiles
7. Solución de Problemas Comunes

---

## 🏗️ Arquitectura del Sistema

El sistema sigue **Arquitectura Hexagonal (Ports & Adapters)** con **Domain-Driven Design (DDD)** y **CQRS** (Command Query Responsibility Segregation).

┌─────────────────────────────────────────────────────────────────┐
│                      Frontend (Angular)                         │
│                   http://localhost:4200                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API Gateway (PHP)                          │
│                   http://localhost:8000                         │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │ Controllers │  │ Middleware  │  │   Routes    │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    Application Layer                     │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │    │
│  │  │   Commands   │  │   Queries    │  │   Handlers   │   │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │    │
│  └─────────────────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                     Domain Layer                         │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │    │
│  │  │  Entities    │  │Value Objects │  │ Repositories │   │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │    │
│  └─────────────────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                 Infrastructure Layer                     │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │    │
│  │  │   Database   │  │   Cache      │  │  Security    │   │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘


## 📁 Estructura de Directorios

backend/
├── Application/
│   ├── Commands/                    # Comandos CQRS (escritura)
│   │   └── [Modulo]/
│   │       ├── *Command.php         # DTO de entrada
│   │       └── *Handler.php         # Lógica de negocio
│   └── Queries/                     # Queries CQRS (lectura)
│       └── [Modulo]/
│           ├── *Query.php           # DTO de consulta
│           └── *Handler.php         # Lógica de consulta
│
├── Domain/
│   └── [Modulo]/
│       ├── *Entity.php              # Entidad de dominio
│       └── *Repository.php          # Interfaz del repositorio
│
├── Infrastructure/
│   ├── Database/                    # Conexión a BD
│   ├── Repositories/                # Implementación de repositorios
│   │   └── MySQL*Repository.php
│   └── Security/                    # Helpers de seguridad
│
├── Interfaces/
│   └── Http/
│       ├── Controllers/             # Controladores
│       │   └── *Controller.php
│       └── Routes/                  # Definición de rutas
│           └── *.routes.php
│
└── Core/                            # Núcleo de la aplicación
    ├── App.php
    ├── Router.php
    ├── Request.php
    └── Response.php


## Flujo de Trabajo para Nueva Funcionalidad

### **Paso 1: Definir la Entidad de Dominio**


// Domain/[Modulo]/[Entidad].php
namespace maquinas_recreativas\Domain\[Modulo];

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

class [Entidad]
{
    private string $id;
    private string $campo1;
    private string $campo2;

    private function __construct(string$id, string $campo1, string $campo2)
    {
        $this->id = $id;
        $this->campo1 = $campo1;
        $this->campo2 = $campo2;
    }

    public static function crear(Uuid$id, string $campo1, string $campo2): self
    {
        return new self($id->value(), $campo1, $campo2);
    }

    public static function fromArray(array $data): self
    {
        return new self($data['ID'], $data['campo1'], $data['campo2']);
    }

    // Getters y métodos de negocio...
}


### **Paso 2: Definir la Interfaz del Repositorio**


// Domain/[Modulo]/[Entidad]Repository.php
namespace maquinas_recreativas\Domain\[Modulo];

interface [Entidad]Repository
{
    public function guardar([Entidad] $entidad): void;
    public function buscarPorId(string $id): ?[Entidad];
    public function obtenerTodos(array $filtros = []): array;
    public function eliminar(string $id): void;
}


### **Paso 3: Crear el Comando (para escritura)**


// Application/Commands/[Modulo]/[Accion][Entidad]Command.php
namespace maquinas_recreativas\Application\Commands\[Modulo];

use maquinas_recreativas\Application\Commands\Command;

final class [Accion][Entidad]Command implements Command
{
    private string $campo1;
    private string $campo2;

    public function __construct(string$campo1, string $campo2)
    {
        $this->campo1 = $campo1;
        $this->campo2 = $campo2;
    }

    public function getCampo1(): string { return $this->campo1; }
    public function getCampo2(): string { return $this->campo2; }
}


### **Paso 4: Crear el Handler del Comando**


// Application/Commands/[Modulo]/[Accion][Entidad]Handler.php
namespace maquinas_recreativas\Application\Commands\[Modulo];

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\[Modulo]\[Entidad];
use maquinas_recreativas\Domain\[Modulo]\[Entidad]Repository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class [Accion][Entidad]Handler implements CommandHandler
{
    private [Entidad]Repository $repository;

    public function __construct([Entidad]Repository $repository)
    {$this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof [Accion][Entidad]Command) {
            throw new DomainException('Comando inválido');
        }

    // Validaciones de negocio
        // ...

    $id = Uuid::v4();$entidad = [Entidad]::crear($id, $command->getCampo1(), $command->getCampo2());
        $this->repository->guardar($entidad);
    }
}


### **Paso 5: Crear el Query (para lectura)**


// Application/Queries/[Modulo]/Obtener[Entidad]sQuery.php
namespace maquinas_recreativas\Application\Queries\[Modulo];

use maquinas_recreativas\Application\Queries\Query;

final class Obtener[Entidad]sQuery implements Query
{
    private array $filtros;

    public function __construct(array $filtros = [])
    {$this->filtros = $filtros;
    }

    public function getFiltros(): array { return $this->filtros; }
}



// Application/Commands/[Modulo]/[Accion][Entidad]Handler.php
namespace maquinas_recreativas\Application\Commands\[Modulo];

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\[Modulo]\[Entidad];
use maquinas_recreativas\Domain\[Modulo]\[Entidad]Repository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class [Accion][Entidad]Handler implements CommandHandler
{
    private [Entidad]Repository $repository;

    public function __construct([Entidad]Repository $repository)
    {$this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof [Accion][Entidad]Command) {
            throw new DomainException('Comando inválido');
        }

    // Validaciones de negocio
        // ...

    $id = Uuid::v4();$entidad = [Entidad]::crear($id, $command->getCampo1(), $command->getCampo2());
        $this->repository->guardar($entidad);
    }
}


### **Paso 6: Crear el Handler del Query**

// Application/Queries/[Modulo]/Obtener[Entidad]sHandler.php
namespace maquinas_recreativas\Application\Queries\[Modulo];

use maquinas_recreativas\Application\Queries\Query;
use maquinas_recreativas\Application\Queries\QueryHandler;
use maquinas_recreativas\Domain\[Modulo]\[Entidad]Repository;

final class Obtener[Entidad]sHandler implements QueryHandler
{
    private [Entidad]Repository $repository;

    public function __construct([Entidad]Repository $repository)
    {$this->repository = $repository;
    }

    public function handle(Query $query): array
    {
        if (!$query instanceof Obtener[Entidad]sQuery) {
            throw new \InvalidArgumentException('Query inválido');
        }

    $entidades = $this->repository->obtenerTodos($query->getFiltros());

    return array_map(fn($e) => $e->toArray(), $entidades);
    }
}



### **Paso 7: Implementar el Repositorio**


// Infrastructure/Repositories/MySQL[Entidad]Repository.php
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\[Modulo]\[Entidad];
use maquinas_recreativas\Domain\[Modulo]\[Entidad]Repository;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Cache\CacheInterface;

class MySQL[Entidad]Repository implements [Entidad]Repository
{
    private Database $db;
    private CacheInterface $cache;

    public function __construct(Database$db, ?CacheInterface $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? CacheFactory::create();
    }

    public function guardar([Entidad] $entidad): void
    {$conn = $this->db->getConnection();
        $data = $entidad->toArray();

    // Verificar si existe$checkStmt = $conn->prepare("SELECT COUNT(*) FROM tabla WHERE ID = ?");
        $checkStmt->bind_param('s', $data['id']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $exists = $result->fetch_assoc()['total'] > 0;
        $result->free();
        $checkStmt->close();

    if ($exists) {$stmt = $conn->prepare("UPDATE tabla SET campo1=?, campo2=? WHERE ID=?");
            $stmt->bind_param('sss', $data['campo1'], $data['campo2'], $data['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO tabla (ID, campo1, campo2) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $data['id'], $data['campo1'], $data['campo2']);
        }

    $stmt->execute();
        $stmt->close();

    // Invalidar caché$this->cache->delete("entidad:id:{$data['id']}");
    }

    // Otros métodos...
}


### **Paso 8: Crear el Controlador**


// Interfaces/Http/Controllers/[Modulo]Controller.php
namespace maquinas_recreativas\Interfaces\Http\Controllers;

use maquinas_recreativas\Application\Commands\[Modulo]\[Accion][Entidad]Command;
use maquinas_recreativas\Application\Commands\[Modulo]\[Accion][Entidad]Handler;
use maquinas_recreativas\Application\Queries\[Modulo]\Obtener[Entidad]sQuery;
use maquinas_recreativas\Application\Queries\[Modulo]\Obtener[Entidad]sHandler;
use maquinas_recreativas\Core\Request;
use maquinas_recreativas\Core\Response;

class [Modulo]Controller
{
    private [Accion][Entidad]Handler $handler;
    private Obtener[Entidad]sHandler $queryHandler;

    public function __construct(
        [Accion][Entidad]Handler $handler,
        Obtener[Entidad]sHandler $queryHandler
    ) {$this->handler = $handler;
        $this->queryHandler = $queryHandler;
    }

    public function crear(Request $request): Response
    {$data = $request->json();

    // Validaciones...

    $command = new [Accion][Entidad]Command($data['campo1'], $data['campo2']);
        $this->handler->handle($command);

    $response = new Response();
        $response->json(['success' => true, 'message' => 'Creado correctamente'], 201);
        return $response;
    }

    public function obtenerTodos(Request $request): Response
    {
        $query = new Obtener[Entidad]sQuery();$result = $this->queryHandler->handle($query);

    $response = new Response();$response->json(['success' => true, 'data' => $result]);
        return $response;
    }
}


### **Paso 9: Definir las Rutas**

// Interfaces/Http/Routes/[modulo].routes.php
return [
    [
        'method' => 'POST',
        'path' => '/[modulo]/crear',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\[Modulo]Controller::class, 'crear'],
        'middleware' => []
    ],
    [
        'method' => 'GET',
        'path' => '/[modulo]/todos',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\[Modulo]Controller::class, 'obtenerTodos'],
        'middleware' => []
    ]
];


### **Paso 10: Registrar Dependencias**


// Config/dependencies.php

// Registrar repositorio
Dependencies::register(MySQL[Entidad]Repository::class, function() use ($database) {
    return new MySQL[Entidad]Repository($database);
});

// Registrar handlers
Dependencies::register([Accion][Entidad]Handler::class, function() {
    return new [Accion][Entidad]Handler(Dependencies::get(MySQL[Entidad]Repository::class));
});

Dependencies::register(Obtener[Entidad]sHandler::class, function() {
    return new Obtener[Entidad]sHandler(Dependencies::get(MySQL[Entidad]Repository::class));
});

// Registrar controlador
Dependencies::register([Modulo]Controller::class, function() {
    return new [Modulo]Controller(
        Dependencies::get([Accion][Entidad]Handler::class),
        Dependencies::get(Obtener[Entidad]sHandler::class)
    );
});


## Ejemplo Práctico: CRUD de Comercios

### **1. Comando para Actualizar Comercio**

// Application/Commands/Comercio/ActualizarComercioCommand.php
final class ActualizarComercioCommand implements Command
{
    private string $id;
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private string $telefono;

    public function __construct(string$id, string $nombre, string $tipo, string $direccion, string $telefono)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
    }

    public function getId(): string { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getDireccion(): string { return $this->direccion; }
    public function getTelefono(): string { return $this->telefono; }
}


### **2. Handler para Actualizar Comercio**

// Application/Commands/Comercio/ActualizarComercioHandler.php
final class ActualizarComercioHandler implements CommandHandler
{
    private ComercioRepository $comercioRepository;

    public function __construct(ComercioRepository $comercioRepository)
    {$this->comercioRepository = $comercioRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command instanceof ActualizarComercioCommand) {
            throw new DomainException('Comando inválido');
        }

    $comercio = $this->comercioRepository->buscarPorId($command->getId());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

    if ($this->comercioRepository->existePorNombre($command->getNombre(), $command->getId())) {
            throw new DomainException('Ya existe un comercio con ese nombre');
        }

    $comercio->actualizar(
            $command->getNombre(),
            $command->getTipo(),
            $command->getDireccion(),
            $command->getTelefono()
        );

    $this->comercioRepository->guardar($comercio);
    }
}


### **3. Método en el Controlador**


// Interfaces/Http/Controllers/ComercioController.php
public function actualizar(Request $request, string $id): Response
{
    try {
        $data = $request->json();

    $command = new ActualizarComercioCommand(
            $id,
            $data['nombre'],
            $data['tipo'],
            $data['direccion'],
            $data['telefono'] ?? ''
        );

    $this->actualizarComercioHandler->handle($command);

    $response = new Response();
        $response->json(['success' => true, 'message' => 'Comercio actualizado']);
        return $response;

    } catch (DomainException $e) {$response = new Response();$response->json(['success' => false, 'message' => $e->getMessage()], 400);
        return $response;
    }
}


### **4. Ruta**

// Routes/comercio.routes.php
[
    'method' => 'PUT',
    'path' => '/comercio/actualizar/:uuid',
    'handler' => [ComercioController::class, 'actualizar'],
    'middleware' => []
]


## Patrones y Convenciones

### **Nomenclatura**



| Elemento      | Convención                  | Ejemplo                      |
| ------------- | ---------------------------- | ---------------------------- |
| Comandos      | `[Accion][Entidad]Command` | `RegistrarComercioCommand` |
| Handlers      | `[Accion][Entidad]Handler` | `RegistrarComercioHandler` |
| Queries       | `Obtener[Entidad]sQuery`   | `ObtenerComerciosQuery`    |
| Repositorios  | `MySQL[Entidad]Repository` | `MySQLComercioRepository`  |
| Controladores | `[Entidad]Controller`      | `ComercioController`       |
| Rutas         | `[entidad].routes.php`     | `comercio.routes.php`      |



### **Estructura de Respuesta API**

// Éxito
{
    "success": true,
    "message": "Mensaje descriptivo",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Mensaje de error"
}


### **Códigos HTTP**

| Código | Uso                                        |
| ------- | ------------------------------------------ |
| 200     | OK - Éxito en GET/PUT/PATCH/DELETE        |
| 201     | Created - Éxito en POST                   |
| 400     | Bad Request - Datos inválidos             |
| 401     | Unauthorized - No autenticado              |
| 403     | Forbidden - Sin permisos                   |
| 404     | Not Found - Recurso no existe              |
| 500     | Internal Server Error - Error del servidor |



## Comandos Útiles 


# Iniciar servidor PHP

php -S localhost:8000 -t public/

# Ver logs

tail -f storage/logs/error.log

# Limpiar caché

rm -rf storage/cache/*

# Ejecutar pruebas (si existen)

php vendor/bin/phpunit



### **Frontend**


# Instalar dependencias

npm install

# Iniciar servidor de desarrollo

ng serve

# Construir para producción

ng build --prod

# Ejecutar pruebas

ng test


### **Base de Datos**


-- Verificar técnicos disponibles
SELECT u.ID_Usuario, u.nombre, u.apellido, t.Especialidad
FROM usuario u
INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico;

-- Verificar componentes disponibles
SELECT * FROM componente WHERE tipo = 'Estructural' AND ID_Componente NOT IN (
    SELECT ID_Componente FROM componente_usuario WHERE fecha_liberacion IS NULL
);


## Solución de Problemas Comunes

### **Error: `Commands out of sync`**

**Causa:** Resultados de consulta no liberados correctamente.

**Solución:**

$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // procesar
}
$result->free();  //  Liberar resultado
$stmt->close();   //  Cerrar statement


### **Error: `Cannot read properties of null (reading 'success')`**

**Causa:** La respuesta del backend es `null`.

**Solución en frontend:**

map(response => {
    if (response && response.success) {
        return response.data;
    }
    return [];
})


### **Error: `Undefined array key` en Comercio**

**Causa:** La entidad espera claves en minúsculas pero la BD devuelve mayúsculas.

**Solución:**

public static function fromArray(array $data): self
{
    return new self(
        $data['ID_Comercio'],
        $data['Nombre'] ?? $data['nombre'] ?? '',
        $data['Tipo'] ?? $data['tipo'] ?? '',
        // ...
    );
}


## Referencias Rápidas

### **Estructura de Tablas Principales**


-- Usuario
usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)

-- Técnico
Tecnico (ID_Tecnico, Especialidad, Cantidad_Actividades)

-- Comercio
Comercio (ID_Comercio, Nombre, Tipo, Direccion, Telefono, Fecha_Registro)

-- Máquina
MaquinaRecreativa (ID_Maquina, Nombre_Maquina, Tipo, Estado, Etapa, ID_Comercio, ...)

-- Componente
componente (ID_Componente, tipo, nombre, precio)

-- Asignación de componente
componente_usuario (ID_Registro, ID_Componente, ID_Usuario, ID_Maquina, fecha_asignacion, fecha_liberacion)


### **Flujo de Registro de Máquina**

1. Usuario logística crea placa (`POST /maquina/generar-placa`)
2. Usuario logística asigna carcasa (`POST /componentes/asignar-carcasa`)
3. Usuario logística registra máquina (`POST /maquina/register`)
4. Técnico ensamblador monta componentes (`POST /tecnico/ensamblador/montaje`)
5. Técnico ensamblador envía a comprobación (`POST /tecnico/ensamblador/mandar-comprobacion`)
6. Técnico comprobador aprueba/rechaza (`POST /tecnico/comprobador/aprobar-distribucion` o `.../rechazar-reensamblar`)
7. Logística pone operativa (`POST /logistica/poner-operativa`)

---

##  Checklist para Nueva Funcionalidad

* Definir entidad en `Domain/`
* Definir interfaz de repositorio
* Crear comando(s) en `Application/Commands/`
* Crear handler(s) de comandos
* Crear query(es) en `Application/Queries/`
* Crear handler(s) de queries
* Implementar repositorio en `Infrastructure/Repositories/`
* Crear controlador en `Interfaces/Http/Controllers/`
* Definir rutas en `Interfaces/Http/Routes/`
* Registrar dependencias en `Config/dependencies.php`
* Probar con Postman o frontend
