API_Taringa
===========

Hace mas de un año, los desarrolladores de Taringa.net tomaron la decision de borrar su API de datos oficial, con la promesa de traer una nueva y mucho mejor. 

Entonces esta todo bien no? A esta altura ya tendria que haber una buena API online y funcional. FALSO.

Despues de una larga espera sigue sin existir una forma facil de extraer datos de la web para usar en aplicaciones, es por eso que decidi crear el codigo necesario para extraer estos datos y compartirlo con todo aquel que lo necesite.

taringa_user
-------

Arreglando codigo.

taringa_post
-------
Esta clase es utilizada para extraer los datos de un post con una ID especifica, con la posibilidad de tambien conseguir los datos de posts cerrados solo para usuarios registrados.

### Uso

    $post = new taringa_post;
    $post->process(ID, 'usuario', 'contraseña');

Siendo ID la id del post a buscar, por ejemplo "14561171".

El usuario y contraseña son opcionales, no son necesarios para que el script funcione correctamente. Pero si son necesarios para poder conseguir los datos de posts cerrados solo para usuarios registrados.

En caso de no proveer estos datos y tratar de conseguir un post cerrado para usuarios registrados entonces $post->process devolvera false.

Si no se quiere poner el usuario y la contraseña entonces se usa de la siguiente manera:

    $post = new taringa_post;
    $post->process(ID);
    
De cualquier manera que decidas usarlo, los datos despues se pueden conseguir usando cualquiera de las siguientes variables:

    $post->id
    $post->titulo
    $post->creador
    $post->puntos
    $post->visitas
    $post->favoritos
    $post->seguidores
    $post->categoria
    $post->tiempo
    
En caso de querer verificar si se pudieron conseguir los datos o no antes de usarlos:
    $post = new taringa_post;
    if ($post->process(ID, 'username', 'password') {
        // Se pudieron conseguir los datos! Hacer algo.
    } else {
        // No se pudieron conseguir los datos, que hacemos? 
    }

taringa_comu
-------

Arreglando codigo.
