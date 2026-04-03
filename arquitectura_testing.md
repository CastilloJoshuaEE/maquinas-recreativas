backend/tests/Perfomance/breakpoint-analyzer.php, HttpStressTestCase.php, run_stress_test.php, StressTest.php

backend/tests/
├── Smoke/
│   ├── SmokeTestCase.php          (clase base con helpers)
│   ├── SmokeHealthTest.php        (endpoints públicos)
│   ├── SmokeAuthTest.php          (login, registro, logout)
│   ├── SmokeMaquinaTest.php       (endpoints clave de máquinas)
│   └── SmokeReporteTest.php       (reportes y comentarios)
├── Functional/
│   └── UserFlowsTest.php          (tu prueba completa existente)
└── bootstrap.php                   (configuración común)

backend/tests/Security/security-test.php

backend/tests/Functional/AdminFlowsTest.php, HttpTestCase.php, ReporteFlowsTest.php, run_tests.php, UserFlowsTest.php

backend/tests/
├── bootstrap.php
├── TestDatabase.php
├── Domain/
│   ├── UsuarioTest.php
│   ├── MaquinaTest.php
│   ├── ComponenteTest.php
│   ├── ReporteTest.php
│   └── ComentarioTest.php
├── Application/
│   ├── Commands/
│   │   ├── UsuarioCommandTest.php
│   │   ├── MaquinaCommandTest.php
│   │   └── ComercioCommandTest.php
│   └── Queries/
│       ├── UsuarioQueryTest.php
│       └── MaquinaQueryTest.php
├── Infrastructure/
│   └── Repositories/
│       └── RepositoryTest.php
└── Integration/ChatUsuarioIntegrationTest.php, NotificacionIntegrationTest.php, ReporteComentario.php,
    └── FlujoCompletoTest.php

HERRAMIENTAS

D:\xampp\htdocs\maquinas-recreativas\backend>composer require --dev phpunit/phpunit:^11.5.55

## 1. Ve a la página oficial

👉 [https://xdebug.org/download](https://xdebug.org/download)

---

## 2. Descarga la versión correcta

Tú tienes:

<pre class="overflow-visible! px-0!" data-start="564" data-end="596"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute end-1.5 top-1 z-2 md:end-2 md:top-1"></div><div class="w-full overflow-x-hidden overflow-y-auto pe-11 pt-3"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>PHP 8.2.12 (ZTS) x64</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

👉 Necesitas algo como:

`<pre class="overflow-visible! px-0!" data-start="623" data-end="674"><div class="relative w-full mt-4 mb-1">``<div class=""><div class="relative">``<div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0">``<div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback">``<div class="pointer-events-none absolute end-1.5 top-1 z-2 md:end-2 md:top-1"></div>``<div class="w-full overflow-x-hidden overflow-y-auto pe-11 pt-3"><div class="relative z-0 flex max-w-full">``<div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller">``<div class="cm-content q9tKkq_readonly"><span>`php_xdebug-3.x.x-8.2-ts-vs16-x86_64.dll `</div></div>``</div></div>``</div></div>``</div></div>``</div><div class="">``<div class=""></div>``</div></div>``</div></div>``</pre>`

Renombralo a como: php_xdebug.dll

---

## 3. Copia el archivo

Pon el `.dll` en:

<pre class="overflow-visible! px-0!" data-start="724" data-end="752"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute end-1.5 top-1 z-2 md:end-2 md:top-1"></div><div class="w-full overflow-x-hidden overflow-y-auto pe-11 pt-3"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>D:\xampp\php\ext</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

---

## 4. Edita tu `php.ini`

Agrega esto:

<pre class="overflow-visible! px-0!" data-start="799" data-end="852"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>zend_extension=xdebug</span><br/><span>xdebug.mode=coverage</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

---

## 5. Reinicia Apache (IMPORTANTE)

---

## 6. Verifica

<pre class="overflow-visible! px-0!" data-start="916" data-end="934"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>php </span><span class="ͼf">-v</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

Debe salir algo como:

<pre class="overflow-visible! px-0!" data-start="959" data-end="989"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute end-1.5 top-1 z-2 md:end-2 md:top-1"></div><div class="w-full overflow-x-hidden overflow-y-auto pe-11 pt-3"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>with Xdebug v3.x.x</span></div></div></div></div></div></div></div></div></div><div class=""><div class=""></div></div></div></div></div></pre>

---

# DESPUÉS DE ESO

Ya puedes usar coverage:

<pre class="overflow-visible! px-0!" data-start="1043" data-end="1102"><div class="relative w-full mt-4 mb-1"><div class=""><div class="relative"><div class="h-full min-h-0 min-w-0"><div class="h-full min-h-0 min-w-0"><div class="border border-token-border-light border-radius-3xl corner-superellipse/1.1 rounded-3xl"><div class="h-full w-full border-radius-3xl bg-token-bg-elevated-secondary corner-superellipse/1.1 overflow-clip rounded-3xl lxnfua_clipPathFallback"><div class="pointer-events-none absolute inset-x-4 top-12 bottom-4"><div class="pointer-events-none sticky z-40 shrink-0 z-1!"><div class="sticky bg-token-border-light"></div></div></div><div class="w-full overflow-x-hidden overflow-y-auto"><div class="relative z-0 flex max-w-full"><div id="code-block-viewer" dir="ltr" class="q9tKkq_viewer cm-editor z-10 light:cm-light dark:cm-light flex h-full w-full flex-col items-stretch ͼ5 ͼj"><div class="cm-scroller"><div class="cm-content q9tKkq_readonly"><span>vendor\b</span><span class="ͼ8">in</span><span>\phpunit.bat </span><span class="ͼf">--coverage-html</span><span> coverage</span></div></div></div></div></div></div></div></div></div></div></div></div></pre>

# Ejecutar todos los tests

vendor\b**in**\phpunit.bat

# Ejecutar solo tests de dominio

vendor\bin\phpunit.bat -c tests/phpunit.xml --testsuite Domain

vendor\bin\phpunit.bat -c tests/phpunit.xml --testsuite Application

vendor\bin\phpunit --testsuite Security

EJECUTAR TEST INTEGRATION

vendor\bin\phpunit.bat -c tests/phpunit.xml --testsuite Integration

Ejecutar tests con coverage

vendor\bin\phpunit.bat -c tests/phpunit.xml --coverage-html coverage

# Ejecutar un test específico

vendor\bin\phpunit.bat --filter tests/testCrearUsuarioAdministrador

EJECUTAR TEST DE SEGURIDAD

cd D:\xampp\htdocs\maquinas-recreativas\backend\tests\Security

php security-test.php

# Iniciar el servidor en una terminal

php -S localhost:8000 -t backend/public

# En otra terminal, ejecutar las pruebas de integración

cd tests/Functional
php run_tests.php

o

cd D:\xampp\htdocs\maquinas-recreativas\backend
vendor\bin\phpunit -c tests/phpunit.xml

EJECUTAR PRUEBAS DE SMOKE

cd D:\xampp\htdocs\maquinas-recreativas\backend vendor\bin\phpunit -c tests/phpunit.xml --testsuite Smoke

EJECUTAR LAS PRUEBAS DE PERFORMANCE

D:\xampp\htdocs\maquinas-recreativas\backend\tests\Performance>php run_stress_test.php
