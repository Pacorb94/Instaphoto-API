# Objetivo
Aprender Laravel

## Descripción
Back-end en forma de API RESTful en la que cada usuario puede subir imágenes
y los otros usuarios comentarlas.
El usuario creador de la imagen puede editar y borra la imagen.
El usuario creador de la imagen puede borrar comentarios ofensivos.
El usuario creador del comentario puede borralo.
El usuario puede editar su perfil.
El usuario puede buscar imágenes.

## Requisitos
* Composer 2
* MySQL o MariaDB
* PHP 8 (puedes probar tu versión)

## Pasos a seguir
1. En un sistema gestor de bbdd importar database.sql de la carpeta database
2. `composer install`
3. Crea una carpeta llamada users en storage/app/ y dentro 2 más (images y profile-images)
4. Si no tienes XAMPP puedes usar `php artisan serve` sino despliega en htdocs

## Licencia
MIT

