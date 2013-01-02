API_Taringa
===========

Hace mas de un año, los desarrolladores de Taringa.net tomaron la decision de borrar su API de datos oficial, con la promesa de traer una nueva y mucho mejor. 

Entonces esta todo bien no? A esta altura ya tendria que haber una buena API online y funcional. FALSO.

Despues de una larga espera sigue sin existir una forma facil de extraer datos de la web para usar en aplicaciones, es por eso que decidi crear el codigo necesario para extraer estos datos y compartirlo con todo aquel que lo necesite.

taringa_user
-------

Clase para conseguir los datos de un usuario especifico.

### Uso
    
    $user = new taringa_user;
    $user->process('username');
    
Luego de eso todos los datos quedan asignados en las siguientes variables:

    $user->usuario
    $user->sexo
    $user->estado
    $user->karma
    $user->rango
    $user->rango_pre_karma
    $user->puntos
    $user->posts
    $user->temas
    $user->comentarios
    $user->seguidores
    $user->siguiendo
    $user->comunidades
    $user->medallas
    $user->pais
    $user->mensaje
    $user->avatar
    $user->pagina_web
    $user->facebook
    $user->twitter
    
Tambien se puede verificar si se pudieron conseguir los datos o no, de la siguiente manera:

    $user = new taringa_user;
    if ($user->process('username')) {
        // Se pudieron conseguir los datos! Hacer algo.
    } else {
        // No se pudieron conseguir los datos, que hacemos? 
    }

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

Esta clase sirve para conseguir los datos de una comunidad especifica.

### Uso
    
    $comu = new taringa_comu;
    $comu->process('nombre_corto', 'usuario', 'contraseña');

Siendo 'nombre_corto' el nombre corto de la comunidad, el cual aparece en la URL.

Como en taringa_post, poner un usuario y contraseña no es necesario para que el script funcione, pero si lo es al momento de tratar de conseguir los datos de una comunidad cerrada solo para usuarios registrados.

En caso de no querer poner usuario y contraseña, usalo asi:

    $comu = new taringa_comu;
    $comu->parse('nombre_corto');
    
Al conseguir los datos, de cualquiera de las dos maneras, quedan en estas variables:

    $comu->nombre_corto
    $comu->nombre
    $comu->avatar
    $comu->miembros
    $comu->temas
    $comu->seguidores
    
Si se quiere verificar si se pudieron conseguir los datos antes de usarlos:

    $comu = new taringa_comu;
    if ($comu->process('nombre_corto', 'usuario', 'contraseña')) {
            // Se pudieron conseguir los datos! Hacer algo.
    } else {
        // No se pudieron conseguir los datos, que hacemos? 
    }
