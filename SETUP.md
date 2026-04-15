# Galgospedia — Setup Local (Windows 11)

## 1. Instalar Laragon

Descarga desde https://laragon.org/download/ (versión Full con PHP 8.1 + MariaDB)

Laragon crea automáticamente el virtual host `galgospedia.test` apuntando a `Galgospedia/public/`

## 2. Verificar extensiones PHP (php.ini de Laragon)

Asegúrate de que estén habilitadas (quitar el `;` al inicio):
```
extension=gd
extension=fileinfo
extension=mbstring
extension=pdo_mysql
```

## 3. Crear base de datos

En HeidiSQL (incluido en Laragon) o phpMyAdmin:

```sql
CREATE DATABASE galgospedia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'galgo_user'@'localhost' IDENTIFIED BY 'dev_password_here';
GRANT ALL PRIVILEGES ON galgospedia.* TO 'galgo_user'@'localhost';
FLUSH PRIVILEGES;
```

Luego importar: `database/schema.sql`

## 4. Configurar .env

```bash
cp .env.example .env
# Editar .env con tus credenciales locales
```

Mínimo necesario:
```
DB_HOST=localhost
DB_NAME=galgospedia
DB_USER=galgo_user
DB_PASS=dev_password_here
APP_URL=http://galgospedia.test
APP_SECRET=pon_aqui_una_cadena_aleatoria_de_64_caracteres
```

## 5. Instalar dependencias PHP (PHPMailer)

```bash
composer install
```

## 6. Compilar CSS (Tailwind)

```bash
npm install
npm run dev    # modo watch (desarrollo)
npm run build  # producción (minificado)
```

## 7. Cargar datos de demo (opcional)

En phpMyAdmin/HeidiSQL, importar:
```
database/seeds/demo_dogs.sql
```

Usuarios de demo:
- admin@galgospedia.com / password  (rol: admin)
- galguero1@ejemplo.com / password  (rol: user)

## 8. Verificar

Abrir http://galgospedia.test en el navegador.

Rutas importantes para probar:
- `/` — Home
- `/perros` — Directorio
- `/sementales` — Sementales
- `/reproductoras` — Reproductoras
- `/arbol/hijo-del-viento` — Árbol genealógico D3 (requiere datos de demo)
- `/admin` — Panel admin (requiere login con rol admin)

## Despliegue a BanaHosting

1. Compilar CSS: `npm run build`
2. Instalar Composer sin dev: `composer install --no-dev`
3. Subir todo via FTP/cPanel **excepto** `.env`
4. En cPanel, apuntar el document root a `public/`
5. Crear DB en phpMyAdmin del hosting e importar `schema.sql`
6. Subir `.env` de producción con credenciales reales
7. Verificar permisos: `public/uploads/` debe ser 755
