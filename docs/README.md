**Requerimientos**
*  PHP 5.4 o superior
*  Servidor web estándar (Apache2, hiawatha, lighttpd etc)
*  Url rewrite activadas, en el caso de hiawatha usar el ToolkitID de banshee, para  apache2 o lighttpd ya se suministra un .htaccess
*  Setee el directorio public de Guachi como el root de su servidor web o (sub)dominio.
*  Para usar el demo necesita tener instalado el modulo sqlite3 de php

**Instalación**

```bash
git clone https://gitlab.com/vitronic/Guachi_Framework.git
cd Guachi_Framework
cp guachi.sample.ini guachi.ini
cp modules.sample.ini modules.ini
sqlite3 db.db <extras/00-auth.sql
chown -R www-data.www-data $(pwd)
```

edite el fichero guachi.ini y edite el valor de `db_database` , ajustelo a su path 
de trabajo.

esto inicia una instalación preliminar con un demo de un CRUD completo y funcional.

suponiendo que configuro su instalación en el localhost ya puede acceder desde
http://localhost/ 
el usuario y la contraseña del demo es vitronic

**Creando un nuevo modulo**

```bash
./new_module private admin/mi_modulo
Creando directorio controllers/admin .
Creando directorio views/admin/demo/admin/mi_modulo .
Creando directorio public/js/admin/demo/admin/mi_modulo .
Creando directorio public/css/themes/admin/demo/admin/mi_modulo .
Creando el controlador, el modelo, la vista y los archivos asociados.
Configurando...
Listo!.
```

ya puede acceder desde http://localhost/admin/mi_modulo
los archivos involucrados para este modulo recién creado

* `controllers/admin/mi_modulo.php`
* `models/admin/mi_modulo.php`
* `views/admin/demo/admin/mi_modulo/main.html`
* `public/css/themes/admin/demo/admin/mi_modulo/main.css`
* `public/js/admin/demo/admin/mi_modulo/main.js`

Por favor lea el modulo `controllers/admin.php` y `models/admin.php` para entender la lógica MVC usada


**Servidor web integrado**

Se proporciona un servidor web integrado con propositos de desarrollo.

```bash
cp server-sample server
#si lo desea puede editar el archico server, por defecto usara el puerto 8080
sh -x server
+ PORT=8080
+ HOST=localhost
+ env php5 -S localhost -S localhost:8080 -t public
PHP 5.6.37 Development Server started at Fri Sep 21 03:51:44 2018
Listening on http://localhost:8080
Document root is /home/vitronic/Proyectos/Framework_Guachi/public
Press Ctrl-C to quit.

```

**Node.js**

Se proporciona un ambiente listo para iniciar a desarrollar con `node`

```bash
git clone https://gitlab.com/vitronic/Guachi_Framework.git
cd Guachi_Framework
cp guachi.sample.ini guachi.ini
cp modules.sample.ini modules.ini
sqlite3 db.db <extras/00-auth.sql
cp server-sample server
gem install sass
npm install
...

npm start

> Guachi@2.1.0 start /home/vitronic/Proyectos/Framework_Guachi
> grunt monitor

```

Con esto ya tiene un ambiente de trabajo tipo `node`, codifique su `javascript`
como si fuese `node` , `grunt` se encargara de hacer un bundle  en public/js/guachi.js
no se preocupe por incluirlo en la app, Guachi se encarga de eso, lo mismo aplica
para las hojas de estilo less y sass de la app, se compilaran en consecuencia.

En otra terminal ejecute

```bash
sh -x server
+ PORT=8080
+ HOST=localhost
+ env php5 -S localhost -S localhost:8080 -t public
PHP 5.6.37 Development Server started at Fri Sep 21 03:51:44 2018
Listening on http://localhost:8080
Document root is /home/vitronic/Proyectos/Framework_Guachi/public
Press Ctrl-C to quit.

```

Listo, ya tiene un ambiente de trabajo eficiente, cada edición de algún archivo
js o less/scss hara que el browser se recargue, esto es gracias a el livereload
que ofrece `grunt` .


**Bichos conocidos.**

* ~~`new_module` falla al crear un submodulo de segundo nivel `./new_module private admin/sub/subsub` fallara, de momento si lo requiero esto lo hago a mano~~
* Falla al hacer `npm start` esto no es un bicho, probablemente tenga que instalar sass `gem install sass`

**TODO**
* ~~Corregir y Mejorar el `new_module`~~
* Documentar todo
* Normalizar la documentación de las funciones en el código para generar documentación con apigen o phpDoc
