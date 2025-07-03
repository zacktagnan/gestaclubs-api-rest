# GestaClubs API REST

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-green)
![Tests](https://img.shields.io/badge/Tests-PHPUnit-yellow)
![License](https://img.shields.io/badge/License-MIT-green)

**API RESTful** para la gestión de clubes deportivos, jugadores y entrenadores, desarrollada en **Laravel 12**, con autenticación segura mediante **Laravel Sanctum**, arquitectura modular y tests automatizados, entre otras cuestiones.

---

## 🚀 Características principales

- Gestión de clubes, jugadores y entrenadores.
- Estricto control del presupuesto para clubes y de la asignación de jugadores y entrenadores a los clubes.
- Lanzamiento de respuestas JSON y excepciones personalizadas.
- Autenticación y autorización con Laravel Sanctum.
- Arquitectura limpia: Actions, DTOs, Pipelines, Services.
- Empleo de Pipeline y Passable para ejecución de conjuntos de Actions.
- Factories y Seeders para datos de prueba realistas.
- Notificaciones desacopladas mediante interfaz, permitiendo añadir nuevos canales (ej: SMS) fácilmente.
- API versionada (v1).
- Tests unitarios (Unit) y de integración/funcionales (Feature) con PHPUnit; uso de DataProviders.

---

## 🔧 Tecnologías y herramientas

- Laravel 12 (sin Starter Kit).
- Laravel Sanctum (Autenticación con API Tokens).
- PHP 8.4 (mínimo 8.2 para Laravel 12).
- MySQL como base de datos.
- Docker Engine + Laravel Sail.
- Mailpit para pruebas de email.
- phpMyAdmin como gestor de bases de datos.
- VS Code.

---

## 📦 Instalación

> **Requisito(s):**
> Este proyecto está preparado para ejecutarse con [Laravel Sail](https://laravel.com/docs/sail), que requiere tener [Docker](https://www.docker.com/get-started/) instalado en el sistema.
>
> Si no fuera posible usar Docker, configurar el entorno manualmente, siguiendo la [documentación oficial de Laravel](https://laravel.com/docs/installation) según el sistema operativo empleado, demandando principalmente:
>
> - **PHP 8.2+** y [Composer](https://getcomposer.org/)

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/tu-usuario/gestaclubs-api-rest.git
   cd gestaclubs-api-rest
   ```

2. **Copiar y configurar los archivos de entorno**

   ```bash
   cp .env.example .env
   cp .env.testing.example .env.testing
   ```

   Ajustar las variables según tus necesidades (puertos, base de datos, etc).

3. **Levantar el entorno de desarrollo con Sail**

   ```bash
   ./vendor/bin/sail up -d
   ```

   > **Nota:**
   > Es posible [configurar un alias](<https://laravel.com/docs/12.x/sail#configuring-a-shell-alias>) para el comando `./vendor/bin/sail`.
   > Así, simplemente, usar `sail` en vez de toda la ruta completa cada vez.

4. **Instalar las dependencias**

   ```bash
   ./vendor/bin/sail composer install
   ```

5. **Generar la clave de la aplicación**

   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Ejecutar las migraciones y seeders**

   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

---

## 🧪 Ejecución de tests

```bash
./vendor/bin/sail test
```

- Los tests usan la configuración de `.env.testing`.
- Es posible filtrar por grupos o clases específicas, por ejemplo:

  ```bash
  ./vendor/bin/sail test --group api:v1:feat
  ```

  ```bash
  ./vendor/bin/sail test --filter HelpersTest
  ```

**Estructura de tests:**

- 📁 `tests/Unit`: Tests unitarios de helpers, actions, DTOs, traits, etc.
- 📁 `tests/Feature`: Tests de endpoints y flujos completos de la API.
- Archivo `.env.testing` para entorno de pruebas aislado.
- Uso de `DataProviders`, Traits de ayuda y clases base personalizadas.

---

## 📚 Documentación de la API

Endpoints principales:

- Autenticación:
  - `POST /api/v1/auth/register`
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
- Gestión de clubes:
  - `GET|POST|PUT|PATCH|DELETE /api/v1/management/clubs`
- Gestión de jugadores:
  - `GET|POST|PUT|DELETE /api/v1/management/players`
- Gestión de entrenadores:
  - `GET|POST|PUT|DELETE /api/v1/management/coaches`

Para más detalles, consultar la documentación de todos los endpoints disponible en dos formatos:

- **Postman**: [Carpeta de colecciones Postman](./__api-documentation/postman/)
- **Bruno**: [Carpeta de colecciones Bruno](./__api-documentation/bruno/)

Importar la colección en tu cliente favorito para explorar y probar la API.

---

## 🔜 Proyecto Frontend (Futuro)

Está previsto desarrollar un proyecto frontend independiente para consumir esta API REST, usando **Vue.js 3** con sintaxis **Script Setup** y consumo de API mediante **Axios** o **Fetch**.
Todo con el objetivo de proporcionar una experiencia de usuario completa para la gestión visual de clubes, jugadores y entrenadores.

---

## 🚧 Posibles Mejoras

- Añadir soporte para nuevos canales de notificación (SMS, push notifications, etc.).
- Documentación automática y más detallada (con OpenAPI/Swagger).
- Filtros avanzados en endpoints de gestión.
- Integración continua y despliegue automático (CI/CD).

---

## 🛠️ Estructura del proyecto

```
app/
├── Actions/
├── Console/
├── Contracts/
├── DTOs/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Libs/
├── Models/
│   └── Contracts/
├── Notifications/
│   ├── Channels/
│   └── Contracts/
├── Providers/
├── Services/
├── Traits/
bootstrap/
config/
database/
lang/
public/
resources/
routes/
├── api/
│   └── v1.php
├── api.php
storage/
stubs/
├── action.stub
├── dto.stub
tests/
├── DataProviders/
├── Feature/
├── Helpers/
└── Unit/
```

---

## 📝 Notas

- Uso de Sail para comandos de Artisan, Composer y PHPUnit.
- Cada test está agrupado y organizado para facilitar la ejecución selectiva.
- El sistema de notificaciones está preparado para soportar nuevos canales fácilmente gracias a su interfaz.

---

## 🤖 Asistencias técnicas

Durante el desarrollo, se ha hecho uso puntual de herramientas como GitHub Copilot y ChatGPT para refactorizar código, documentar más eficientemente y explorar buenas prácticas.

---

## 🔗 Referencias

- [Laravel](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel Sail](https://laravel.com/docs/sail)
- [PHPUnit](https://phpunit.de/documentation.html)

---

## 📄 Licencia

MIT © zacktagnan
