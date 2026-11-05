# inegi-estados-app-service

Laravel 10 para consultar estados y municipios del API de INEGI (DataTables + Bootstrap 5).

## Stack

- Laravel 10 / PHP 8.2 / MySQL (`states`, `municipalities`)
- RabbitMQ con `php-amqplib` (listener push tipo `@RabbitListener`)

## Setup

```bash
composer install
cp .env.example .env        # si no existe, crea .env con las variables de abajo
php artisan key:generate
php artisan migrate
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=MunicipalitySeeder
```

Variables mínimas en `.env`:

```
DB_DATABASE=inegi_estados
DB_USERNAME=root
DB_PASSWORD=
INEGI_STATES=https://gaia.inegi.org.mx/wscatgeo/v2
QUEUE_CONNECTION=rabbitmq
```

## Rutas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/states` | Lista de estados |
| POST | `/states/save` | Inserta/actualiza estados |
| POST | `/states/sync` | Sincroniza desde INEGI |
| POST | `/states/delete/{cve_ent?}` | Soft delete |
| GET | `/municipality/{cve_ent?}` | Lista de municipios |
| POST | `/municipality/save` | Inserta/actualiza municipios |
| POST | `/municipality/sync/{key_state?}` | Sincroniza municipios |
| POST | `/municipality/delete/{cve_ent?}` | Soft delete |

Vistas web: `/states` y `/states/show/{id?}`.

## Docker (local)

```bash
docker compose up -d --build app     # http://localhost:8879
docker compose logs -f app
```

Usa el MySQL de Homebrew vía `host.docker.internal:3306`. El servicio `listener` está comentado en `docker-compose.yml`.

## Build y push a ECR

Variables (una sola vez):

```bash
cp vars.example vars    # ajustar y luego:
source vars
```

```bash
aws ecr get-login-password --region $REGION | docker login --username AWS --password-stdin $ACCOUNT_ID.dkr.ecr.$REGION.amazonaws.com

docker buildx build --platform "$PLATFORM" \
  -t "$ACCOUNT_ID.dkr.ecr.$REGION.amazonaws.com/$REPO:$IMAGE_TAG" \
  -t "$ACCOUNT_ID.dkr.ecr.$REGION.amazonaws.com/$REPO:latest" \
  --push .
```

Desplegar en ECS (opcional):

```bash
aws ecs update-service --cluster "$ECS_CLUSTER" --service "$ECS_SERVICE" \
  --force-new-deployment --region "$REGION"
```

`vars` está en `.gitignore`. El `Dockerfile` actual compila xdebug e instala dev-dependencies; para producción pendiente: `composer install --no-dev` y xdebug fuera de la imagen.
