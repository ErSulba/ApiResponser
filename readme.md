
#### Repository
https://github.com/laravel/laravel

#### Que aprenderé ?


- Controlar el numero de peticiones por minutos a nuestra API en Laravel
- Editar el mensaje de error que viene por defecto con Laravel
- Crear un Trait para la mejor lectura y eficiencia de codigo en nuestra API 

#### Requerimientos

- Editor de codigo (en mi caso uso PHP Storm)
- Servidor Xampp/Lampp (Depende de tu sistema operativo)
- Consola de comando
- API tester, como POSTMAN

#### Dificultad:


- Intermedia

#### Contenido
Muchas veces nos encontramos, que cuando estamos creando una API en laravel, el mensaje que deja por defecto cuando un usuario pasa el limite de peticiones al servidor es algo soso y no deja mucha información, ademas si estamos creando una API lo que mas queremos es que la misma respuesta sea enviada en un formato ideal para una API (JSON en este caso) asi que con este tutorial se plantea una solución a este problema de forma que sea sencilla de leer para futuras consultas

comenzando con la instalación de nuestro proyecto fresco, asi que nos vamos a la consola y escribimos

```php
composer create-project --prefer-dist laravel/laravel nombre-proyecto
```
donde "nombre-proyecto" puede ser el que ustedes quieran, configuran todo su entorno en el archivo .env y estamos listos para arrancar

Para efectos de este tutorial nos vamos a dirigir a la carpeta "database" y ubicamos el archivo DatabaseSeeder.php
y dentro del metodo run colocamos lo siguiente
```php
public function run()  
{  
  factory(User::class, 15)->create();  
}
```

y luego nos vamos a la consola y escribimos

    php artisan migrate --seed

¿que quiere decir esto?
le estamos diciendo a laravel que nos cree la migraciones de las tablas que trajo por defecto y nos haga un seed con 15 usuarios en la tabla de usuarios.

Ahora que ya tenemos nuestros usuarios creados, queremos acceder a una lista de ellos en formato JSON, para ellos nos vamos a registrar las rutas en el archivo api.php en la carpeta Routes
````php
Route::resource('users', 'UsersController')->only(['show', 'index']);
````

y creamos nuestro controlador

    php artisan make:controller UsersController -r

 ¿que fue lo que logramos con todo esto?
Registramos nuestras rutas para que usaran el controlador "UsersController" y que solo usara los metodos "index" y "show" tal como muestra la imagen

![route list.PNG](https://cdn.steemitimages.com/DQmVn3RfQdw5cTpP5YJvXohSaXgotniRpKWw5fjJRgrPy9o/route%20list.PNG)

ahora nos ubicamos sobre el controlador "UsersController.php" y vamos a escribir 
  en el metodo index
````php
public function index()  
{  
  $users = User::all();  
  return response()->json(['data' =>$users]);  
}
````
  
esto quiere decir que cada vez que hagamos un consulta a la ruta /api/users , nos retornara un objeto JSON que a su vez contiene un objeto "data" el cual tiene todos los usuarios como por ejemplo

![lista usuarios.PNG](https://cdn.steemitimages.com/DQmeKrNQJWndZTv1gMa3r6x8Yt7qQRqNZAZTU7JaiAMYZZ3/lista%20usuarios.PNG)

Ahora vamos con el metodo "show" para mostrar un solo registro en nuestra API, nos volvemos a ubicar en el controlador y colocamos lo siguiente:

````php
public function show($id)  
{  
  $user = User::find($id);  
  return response()->json(['data' => $user]);  
}
````

y si hacemos una consulta a la ruta /api/users/1, nuestra API nos arroja el siguiente resultado:
![showone.PNG](https://cdn.steemitimages.com/DQmPpQPpoJ44ewerVrVVScQt3962nHuuxHNsshahnmQUTpr/showone.PNG)

pero ahora ¿que pasaria si el registro no existe en nuestra base de datos?
laravel por defecto coloca el resultado como null tal como se expresa aca:
	
	![null.PNG](https://cdn.steemitimages.com/DQmahncPnaL6vg9Wu9xHQZDWHputXyWPBnxVEpPueLpgNgM/null.PNG)
	
Pero ¿como hacemos si queremos arrojar un mensaje de error diciendo que no se encontró ningún resultado?
Hay dos maneras de manejar esto
- la mas ideal es dejar que el programador que trabaje Front-end sea quien personalice el mensaje cuando el resultado sea null.
-  arrojar mediante nuestra API un mensaje personalizado diciendo que no se puede encontrar el registro.

vamos con la segunda opción:
Ahora modificando un poco la funcion "show" agregamos lo siguiente
`````php
public function show($id)  
{  
  $user = User::find($id);  
  
  if ($user == null)  
  {  
	 return response()->json(['error' => 'Usuario no encontrado', 'code' => 404], 404);  
  }  
	 return response()->json(['data' => $user]);  
}

`````

de esta manera cuando se haga una peticion a la API y esta no encuentre un resultado, arrojara lo siguiente:

![404.PNG](https://cdn.steemitimages.com/DQmWe6qQK71XwdJQW685Pn5zBhU8hrSsjxFaMS9JWujmGW7/404.PNG)

Pero ahora, si analizamos un poco el código, vemos que hay patron que se repite y hace que nuestro código sea un poco mas difícil de leer para otros, el cual es 

    return response()->json()
	
y tal vez si queremos agregar mas resultados esto nos va traer mas código asi, por eso en este tutorial aprenderemos a abstraer esta parte por algo mas legible, creando un controlador que extienda todos los métodos del Controlador principal de laravel y agregando nuestros métodos abstractos al mismo, haciendo uso de nuestro nuevos metodos en los controladores de nuestra API

comenzando creando un trait en la ruta **app/Traits** (si no existe la carpeta Traits, solo creala) llamado **ApiResponser.php** el cual llevara el siguiente contenido

````php
<?php  
  
namespace App\Traits;  
  
use Illuminate\Database\Eloquent\Model;  
use Illuminate\Support\Collection;  
  
trait ApiResponser  
{  
  private function succesResponse($data, $code)  
 {  
	 return response()->json($data, $code);  
 }  
  protected function errorResponse($message, $code)  
 {  
	 return response()->json(['error' => $message, 'code' => $code], $code);  
 }  
  protected function showAll(Collection $collection, $code = 200)  
 {  
	 return $this->succesResponse(['data' => $collection], $code);  
 }  
  protected function showOne(Model $instance, $code = 200)  
 {  
	 return $this->succesResponse(['data' => $instance], $code);  
 }
}
 
````

creamos nuesto nuevo controlador mediante la consola:

    php artisan make:controller ApiController

y ahora nos ubicamos sobre nuestro nuevo controlador (debe estar en la ruta app\Http\Controllers) el cual se encuentra vacio, el cual solo vamos agregar una sola linea de codigo:

````php
<?php  
  
namespace App\Http\Controllers;  
  
use App\Traits\ApiResponser;  
use Illuminate\Http\Request;  
  
class ApiController extends Controller  
{  
  use ApiResponser;  //esta es la linea que agregamos
}
```` 

y reescribimos nuestro **UsersController** de la siguiente manera

````php
<?php  
  
namespace App\Http\Controllers;  
  
use App\User;  
use Illuminate\Http\Request;  
  
class UsersController extends ApiController  //Ahora extiende de nuestro nuevo controlador y usa los nuevos metodos que escribimos en el Trait
{  
  /**  
 * Display a listing of the resource. 
 * *@return \Illuminate\Http\Response  
 */  
 public function index()  
 {  
	 $users = User::all();  
	 return $this->showAll($users);  //funciona igual que nuestro metodo anterior, pero ahora es mas legible su funcionalidad
 }  
  /**  
 * Display the specified resource. * * @param int  $id  
 * @return \Illuminate\Http\Response  
 */  
 public function show($id)  
 {  
	 $user = User::find($id);
	 if ($user == null)  
	 {  
	 return $this->errorResponse('Usuario no encontrado', 404);  //Al no encontrar un registro arrojara este error para que sea manejado por el front de manera adecuada
	 }  
	 return $this->showOne($user);  //Muestra el registro encontrado
 }  
}

````

Ya con estos metodos tenemos una manera facil de escribir codigo mas legible y tenemos mas control sobre las respuestas de nuestra API.

Ahora vamos agregar un poco de seguridad al **middleware "throttle"** de la API, dicho middleware lo encontraremos registrado en la ruta app\Http en el archivo Kernel.php bajo el objeto $routedMiddleware

````php
/**  
 * The application's route middleware groups. * * @var array  
 */
 protected $middlewareGroups = [  
  'web' => [  
  \App\Http\Middleware\EncryptCookies::class,  
  \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,  
  \Illuminate\Session\Middleware\StartSession::class,  
  // \Illuminate\Session\Middleware\AuthenticateSession::class,  
  \Illuminate\View\Middleware\ShareErrorsFromSession::class,  
  \App\Http\Middleware\VerifyCsrfToken::class,  
  \Illuminate\Routing\Middleware\SubstituteBindings::class,  
 ],  
  'api' => [  
  'throttle:60,1',  // Justo aqui esta registrado el middleware para la API para un limite de 60 peticiones por minuto
  'bindings',  
	],
 ];  
  
/**  
 * The application's route middleware. * * These middleware may be assigned to groups or used individually. 
 * 
 * @var array  
 */
 protected $routeMiddleware = [  
  'auth' => \Illuminate\Auth\Middleware\Authenticate::class,  
  'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,  
  'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,  
  'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,  
  'can' => \Illuminate\Auth\Middleware\Authorize::class,  
  'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,  
  'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,  
  'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,  //Registra el nombre de el middleware throttle
];
````

ahora **¿que pasa cuando un usuario rebasa el limite peticiones?**
laravel arroja el siguiente error formateado en HTML y dandole sus propios estilos:
![too many request.PNG](https://cdn.steemitimages.com/DQmW2CHJkBFU2yCRDdMQbKnZ5GYup5RMweUwHzrdAfrX6Vg/too%20many%20request.PNG)

este no es el formato ideal de respuesta para una API y laravel no provee una manera directa de modificar este comportamiento en su documentación, así que manos a la obra.

La manera mas rapida y directa es verificando cual es la instancia que se ejecuta cuando se llega al limite de peticiones establecido, en el caso de laravel 5.6 la instancia que se ejecuta es **ThrottleRequestsException** la cual no podemos modificar, mas si podemos aprovechar el uso del **método render** en el archivo **app/Exceptions/Handler.php**
de la siguiente manera:

````php
<?php  
  
namespace App\Exceptions;  
  
use App\Traits\ApiResponser;  
use Exception;  
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;  
use Illuminate\Http\Exceptions\ThrottleRequestsException;  
  
class Handler extends ExceptionHandler  
{  
  use ApiResponser;  //Hacemos uso de nuestro trait de respuestas
	....

  /**  
 * Render an exception into an HTTP response. 
 * 
 * @param \Illuminate\Http\Request  $request  
 * @param \Exception  $exception  
 * @return \Illuminate\Http\Response  
 */  public function render($request, Exception $exception)  
     {  
		 if ($exception instanceof ThrottleRequestsException) {  
		 return $this->errorResponse('limite de peticiones rebasado', 409); //Aqui estamos modificando el comportamiento por defecto de la aplicacion y enviamos el mensaje modificado para el uso apropiado en nuestra API  
		 }  
		 if (config('app.debug')) {  
		 return parent::render($request, $exception);
		 } 
	 }
}
````

ya con esto verificamos en nuestra API que se muestra el mensaje:
![limite rebasado.PNG](https://cdn.steemitimages.com/DQmevkHC4P2exLt81CoyanH9R6LQo96arEaJUzWTdz8WMoc/limite%20rebasado.PNG)

y ya con esto estamos listos para desarrollar una API con todas las respuestas en un formato deseado, espero les haya gustado y ayudado este tutorial y hasta la próxima
.
#### Curriculum


- [ Cómo configurar un verdadero entorno de desarrollo para programar con Laravel en Windows 7/10, sin morir de la desesperacion 1/3](https://steemit.com/utopian-io/@ersulba/como-configurar-un-verdadero-entorno-de-desarrollo-para-programar-con-laravel-en-windows-7-10-sin-morir-de-la-desesperacion-1-3)

#### Proof of Work Done
https://github.com/ErSulba/ApiResponser
