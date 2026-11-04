# Bolsa de Empleo y Pasantías

**Caso 7** · Asignatura: **Desarrollo Web con Software Libre**
Universidad Católica de El Salvador (UNICAES)

## Integrantes

| Integrante | Usuario de GitHub |
|------------|-------------------|
| Gustavo    | [@Gu2tavo12](https://github.com/Gu2tavo12) |
| Jimmy      | @usuario-jimmy    |
| Andrés     | @usuario-andres   |
| Bryan      | @usuario-bryan    |
| Kevin      | @usuario-kevin    |

---

## Descripción

Sistema web para que las empresas publiquen **ofertas de empleo y pasantías** y para gestionar las
**postulaciones de estudiantes** a esas ofertas.

### Fase 1 (30 %)

- Base de datos completa creada con migraciones.
- Modelos Eloquent con sus relaciones.
- Datos de prueba (factories y seeders).
- Listado paginado de ofertas.
- Ficha (detalle) de cada oferta.

### Fase 2

- CRUD completo de ofertas, empresas y postulaciones.
- Registro de postulaciones de estudiantes.
- Validación de formularios.
- Carga de imágenes.
- Reglas de negocio del caso (ver más abajo).
- Panel y reporte.

---

## Tecnologías y requisitos

| Componente | Versión / detalle |
|------------|-------------------|
| PHP        | 8.3 o superior |
| Composer   | 2.x |
| Framework  | Laravel 13 |
| Base de datos | MySQL (XAMPP) |
| Extensiones PHP | `pdo_mysql`, `fileinfo`, `mbstring` |
| Interfaz   | Blade + Bootstrap 5.3 por CDN + CSS propio (`public/css/estilos.css`), **sin Vite ni npm** |
| Acceso a datos | Eloquent ORM |

---

## Instalación

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/Gu2tavo12/proyecto-final-laravel-equipo2.git
   ```

   ```bash
   cd proyecto-final-laravel-equipo2
   ```

2. Instalar las dependencias de PHP:

   ```bash
   composer install
   ```

3. Crear el archivo `.env` a partir del ejemplo.

   Windows:

   ```bash
   copy .env.example .env
   ```

   Linux / Mac:

   ```bash
   cp .env.example .env
   ```

4. Generar la clave de la aplicación:

   ```bash
   php artisan key:generate
   ```

5. Crear la base de datos vacía `bolsa_empleo` en MySQL (por ejemplo desde phpMyAdmin) con
   cotejamiento `utf8mb4_unicode_ci`:

   ```sql
   CREATE DATABASE bolsa_empleo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Revisar en `.env` que los datos de conexión coincidan con tu MySQL:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bolsa_empleo
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Crear las tablas y cargar los datos de prueba:

   ```bash
   php artisan migrate:fresh --seed
   ```

7. Crear el enlace simbólico para los archivos públicos:

   ```bash
   php artisan storage:link
   ```

8. Levantar el servidor de desarrollo:

   ```bash
   php artisan serve
   ```

   Abrir <http://127.0.0.1:8000> en el navegador.

---

## Modelo de datos

> Pendiente: diagrama ER (lo agrega Jimmy).

| Tabla | Rol |
|-------|-----|
| `empresas` | Catálogo |
| `ofertas` | Entidad principal |
| `postulaciones` | Entidad relacionada |

---

## Reglas de negocio (Fase 2)

- El salario mínimo debe ser menor o igual al salario máximo (ambos son opcionales en pasantías).
- La fecha de cierre debe ser posterior a la fecha de publicación.
- El número de vacantes debe ser mayor que 0.
- Un mismo correo no puede postularse dos veces a la misma oferta.
- No se aceptan postulaciones en ofertas cerradas.
- El enlace del CV debe tener formato de URL válida.

---

## Capturas de pantalla

> Pendiente.

---

## Distribución del trabajo

| Integrante | Tareas realizadas |
|------------|-------------------|
| Gustavo    |                   |
| Jimmy      |                   |
| Andrés     |                   |
| Bryan      |                   |
| Kevin      |                   |

---

## Flujo de trabajo en Git

- **Ramas:** `main` es la rama estable. Cada tarea se desarrolla en su propia rama `feature/<descripcion>`
  (por ejemplo `feature/migraciones`, `feature/listado-ofertas`).
- **Pull requests:** los cambios llegan a `main` solo mediante pull request revisado por otro integrante.
- **Commits:** se usa [Conventional Commits](https://www.conventionalcommits.org/es/) en español y sin tildes
  (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`...). Ejemplo: `feat(ofertas): agrega listado paginado`.
- **Etiquetas:** `v1.0` al cerrar la Fase 1 y `v2.0` al cerrar la Fase 2.
