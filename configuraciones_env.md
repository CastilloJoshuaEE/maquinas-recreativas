en desarrollo de testing:


*backend/.env:

# Aplicación en desarrollo

#APP_ENV=development

# TESTING

APP_ENV=testing

APP_DEBUG=true

APP_TIMEZONE=America/Guayaquil

# Base de datos principal

# DESARROLLO LOCAL:

DB_HOST=127.0.0.1

# DESARROLLO EN PRODUCCION: DB_HOST=mysql-service

DB_PORT=3306

DB_NAME=bd_recrea_sys

DB_USER=recrea_user

DB_PASS=recrea_pass123

# Base de datos de pruebas

DB_HOST_TEST=mysql_test

DB_PORT_TEST=3307

DB_NAME_TEST=test_bd_recrea_sys

DB_USER_TEST=recrea_user

DB_PASS_TEST=recrea_pass123

# Redis

REDIS_HOST=redis

REDIS_PORT=6379

REDIS_PASSWORD=

# Mail (MailHog)

MAIL_HOST=mailhog

MAIL_PORT=1025

MAIL_USERNAME=

MAIL_PASSWORD=

MAIL_ENCRYPTION=null

# XDebug

XDEBUG_MODE=debug,coverage

XDEBUG_CLIENT_HOST=host.docker.internal

XDEBUG_CLIENT_PORT=9003

# API

API_VERSION=v1

API_BASE_PATH=/api/public

# Rate Limiting

RATE_LIMIT_ENABLED=true

# JWT (si usas)

JWT_SECRET=tu_jwt_secret_aqui

JWT_TTL=3600

# Encriptación

ENCRYPT_METHOD=AES-256-CBC

SECRET_KEY=clave_super_segura_cambiar_en_produccion

SECRET_IV=vector_inicial_16

BREVO_API_KEY=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

EMAIL_FROM_EMAIL="jc9493479@gmail.com"

EMAIL_FROM_NAME="RecreaSys"

ABSTRACT_API_KEY="70101e1639c9437baf8e483b56805994"



En desarrollo local:


# Aplicación en desarrollo

APP_ENV=development

# TESTING

# APP_ENV=testing

APP_DEBUG=true

APP_TIMEZONE=America/Guayaquil

# Base de datos principal

# DESARROLLO LOCAL:

DB_HOST=127.0.0.1

# DESARROLLO EN PRODUCCION: DB_HOST=mysql-service

DB_PORT=3306

DB_NAME=bd_recrea_sys

DB_USER=recrea_user

DB_PASS=recrea_pass123

# Base de datos de pruebas

DB_HOST_TEST=mysql_test

DB_PORT_TEST=3307

DB_NAME_TEST=test_bd_recrea_sys

DB_USER_TEST=recrea_user

DB_PASS_TEST=recrea_pass123

# Redis

REDIS_HOST=redis

REDIS_PORT=6379

REDIS_PASSWORD=

# Mail (MailHog)

MAIL_HOST=mailhog

MAIL_PORT=1025

MAIL_USERNAME=

MAIL_PASSWORD=

MAIL_ENCRYPTION=null

# XDebug

XDEBUG_MODE=debug,coverage

XDEBUG_CLIENT_HOST=host.docker.internal

XDEBUG_CLIENT_PORT=9003

# API

API_VERSION=v1

API_BASE_PATH=/api/public

# Rate Limiting

RATE_LIMIT_ENABLED=true

# JWT (si usas)

JWT_SECRET=tu_jwt_secret_aqui

JWT_TTL=3600

# Encriptación

ENCRYPT_METHOD=AES-256-CBC

SECRET_KEY=clave_super_segura_cambiar_en_produccion

SECRET_IV=vector_inicial_16

BREVO_API_KEY=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

EMAIL_FROM_EMAIL="jc9493479@gmail.com"

EMAIL_FROM_NAME="RecreaSys"

ABSTRACT_API_KEY="70101e1639c9437baf8e483b56805994"


en produccion:


DB_HOST=sql208.infinityfree.com

DB_USER=if0_41358584

DB_PASS=Ymt2ZVewnuOuPll

DB_NAME=if0_41358584_bd_recrea_sys

APP_ENV=production

BREVO_API_KEY=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

EMAIL_FROM_EMAIL="jc9493479@gmail.com"

EMAIL_FROM_NAME="RecreaSys"

ABSTRACT_API_KEY="70101e1639c9437baf8e483b56805994"
