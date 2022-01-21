## Objetivo
Aprender Laravel

## Descripción
API RESTful en la que cada usuario puede subir imágenes
y los otros usuarios comentarlas.
El usuario creador de la imagen puede editar y borra la imagen.
El usuario creador de la imagen puede borrar comentarios ofensivos.
El usuario creador del comentario puede borralo.
El usuario puede editar su perfil.
El usuario puede buscar imágenes.


## Despliegue en producción
0. Si no tienes Docker Compose instálalo.
1. Crea los contenedores `docker-compose up -d --build`
2. Ejecuta las migraciones `docker-compose exec php php artisan migrate`
3. En el navegador pon `http://localhost:9080`

## Licencia
MIT