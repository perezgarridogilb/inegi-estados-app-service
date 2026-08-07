# inegi-estados-app-service

Servicio Laravel para consultar estados y municipios desde el API de INEGI, mostrarlos con DataTables.

## Stack

- Laravel 10
- MySQL (tablas `states` y `municipalities`)
- DataTables + Bootstrap 5
- Repositorios en `App\Repositories` (carpeta `app/Http/Repositories/`)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=MunicipalitySeeder
```

En `.env`:

```
DB_DATABASE=inegi_estados
DB_USERNAME=root
INEGI_STATES=https://gaia.inegi.org.mx/wscatgeo/v2
LOG_CHANNEL=daily
```

Los botones de sincronizar consultan el API de INEGI en vivo (`mgee/` para estados, `mgem/{cve_ent}` para municipios).

## Rutas

### API (`/api/v1`)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/states` | Lista de estados |
| POST | `/states/save` | Inserta/actualiza estados (payload `states[]`) |
| POST | `/states/sync` | Consulta estados a INEGI |
| POST | `/states/delete/{cve_ent?}` | Soft delete de un estado |
| GET | `/municipality/{cve_ent?}` | Lista de municipios (opcional filtrar por estado) |
| POST | `/municipality/save` | Inserta/actualiza municipios (payload `municipalities[]`) |
| POST | `/municipality/sync/{key_state?}` | Consulta municipios de un estado a INEGI |
| POST | `/municipality/delete/{cve_ent?}` | Soft delete de municipios de un estado |

### Web

| Ruta | Descripción |
|------|-------------|
| `/states` | Vista de estados (DataTables + sync + confirmación) |
| `/states/show/{id?}` | Vista de municipios por estado |
