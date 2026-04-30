FROM mysql:8.0

# Configurar charset
RUN { \
    echo '[mysqld]'; \
    echo 'character-set-server=utf8mb4'; \
    echo 'collation-server=utf8mb4_unicode_ci'; \
    echo 'default-authentication-plugin=mysql_native_password'; \
} > /etc/mysql/conf.d/charset.cnf

EXPOSE 3306

CMD ["mysqld"]