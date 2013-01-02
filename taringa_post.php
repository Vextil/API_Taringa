<?php

class taringa_post
{

  	private $username;
	private $password;
	private $hash;
	private $data;

	public $id;
	public $titulo;
	public $creador;
	public $puntos;
	public $visitas;
	public $favoritos;
	public $seguidores;
	public $categoria;
	public $tiempo;
	
	public function process($id, $username = false, $password = false)
	{
		$this->id = $id;
		$this->username = $username;
		$this->password = $password;
		// Hash para usar en el nombre de la cookie
		$this->hash = sha1($this->rand . $this->username . $this->id);
		$this->fetchData();
		return $this->parseData();
	}

	private function fetchData()
	{

		// Iniciar cURL
		$ch = curl_init();
		// UserAgent de explorador web para poder acceder a Taringa.net
		$a = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.3';

		/* Si existe un usuario y contraseña, loguearse y guardar la cookie
		   Esto permite conseguir la información de posts que son solo para usuarios registrados */
		if ($this->username && $this->password) {
			curl_setopt($ch, CURLOPT_URL, 'https://www.taringa.net/registro/login-submit.php');
			curl_setopt($ch, CURLOPT_USERAGENT, $a);
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, 'nick=' . $this->username . '&pass=' . $this->password);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->hash);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$login = curl_exec ($ch);
		}

		// Conseguir el HTML de toda la pagina (y usar la cookie en caso de que exista)
		curl_setopt($ch, CURLOPT_URL, "http://www.taringa.net/posts/api/" . $this->id);
		if (isset($login)){
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->hash);
		}
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $a);
		$cont = curl_exec($ch);
		curl_close ($ch); 
		$this->data = $this->compress($cont);

		// Borrar la cookie para prevenir robo de sesiones
		if (is_file($this->hash)) {
			unlink($this->hash);
		}

	}

	private function parseData()
	{
		preg_match('/<title>(.*)<\/title>/', $this->data, $titulo);
		/* Si no se encuentra property="dc:creator" quiere decir que el post no existe o
		   no se puede acceder, por lo tanto no se buscan el resto de los datos y devuelve false */
		if (preg_match('/meta property="dc:creator" content/', $this->data)) {
			preg_match('/title=\"Post de (.*)\" href=\"\/rss\/(.*)\/posts\/\" \/>/', $this->data, $nick);
			$continue = true;
		} else {
			$continue = false;
		}
		if ($continue) {
			// Parsear el resto de los datos del HTML del post 
			preg_match('/<span id=\"postTotalScore\">(.*)<\/span> Puntos/', $this->data, $puntos);
			preg_match('/<i class="icon visits"><\/i><span>(.*)<\/span><\/div><strong>Visitas/', $this->data, $visitas);
			preg_match('/<span id="fav_counter" data-val="(.*)">(.*)<\/span><\/div>/', $this->data, $favs);
			preg_match('/<span class=\"data-followers-count\" data-val=\"(.*)\">(.*)<\/span><\/div>/', $this->data, $seguidores);
			preg_match('/Hace(.*)<\/a><\/div><ul class=\"post-share post-share-list\"/', $this->data, $bread);
			preg_match('#<div class=\"breadcrumb\"><a href=\"/posts/(.*)\">(.*)</a>(.*)<a title=#', $this->data, $category);
			$cat = explode('"', $category[1]);
			$seg = explode('"', $seguidores[1]);
			$fav = explode('"', $favs[1]);
			// Asignar los datos a sus respectivas variables
			$this->titulo = str_replace(' - Taringa!', '', $titulo[1]);
			$this->creador = $nick[1];
			$this->puntos = str_replace('.', '', $puntos[1]);
			$this->visitas = str_replace('.', '', $visitas[1]);
			$this->favoritos = str_replace('.', '', $fav[0]);
			$this->seguidores = str_replace('.', '', $seg[0]);
			$this->categoria = $cat[0];
			$this->tiempo = ($bread[1] == null ? 'ayer' : 'hace' . $bread[1]);
			return true;
		}
		return false;
	}

	/* Simple funcion para comprimir el HTML 
	   y hacer el parseo de datos mas simple */
	private function compress($html) 
	{
	    $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
	    $replace = array('>', '<', '\\1');
	    return preg_replace($search, $replace, $html);
	}

}
