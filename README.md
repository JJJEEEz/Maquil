**Nombre del Proyecto**

Maquil

**Descripción**

Sistema de gestión interna (panel administrativo) para la aplicación Maquil. Incluye administración de usuarios, roles y permisos, gestión de órdenes y lotes, y panel de perfil de usuario.

**Requisitos**

- PHP 8.x
- Composer
- Node.js + npm
- Base de datos (MySQL, MariaDB u otra soportada por Laravel)

**Ejecución local**

1. Clonar el repositorio:

   git clone <URL_DEL_REPOSITORIO>
   cd Maquil

2. Instalar dependencias PHP:

   composer install

3. Instalar dependencias frontend (opcional si vas a compilar assets):

   npm install

4. Copiar y configurar el archivo de entorno:

   cp .env.example .env
   // Edita `.env` y configura la conexión a la base de datos y otros valores (APP_URL, MAIL, etc.)

5. Generar la clave de la aplicación:

   php artisan key:generate

6. Migrar la base de datos y ejecutar seeders:

   php artisan migrate --seed

7. Compilar assets frontend (desarrollo):

   npm run dev

8. Levantar el servidor localmente:

   php artisan serve

   Luego abre: http://127.0.0.1:8000

**Usuario admin de prueba**

El seeder de ejemplo crea un usuario administrador de prueba. Credenciales por defecto (si no fueron modificadas en los seeders):

- Email: `admin@example.com`
- Contraseña: `password`

Si las credenciales no funcionan revisa `database/seeders/RolesAndUsersSeeder.php` o ejecuta nuevamente los seeders.

**Notas**

- Si usas Valet, Homestead o Sail, adapta los comandos de servidor según corresponda.
- Para desarrollo frontend puedes usar `npm run dev` o `npm run watch` según la configuración del proyecto.