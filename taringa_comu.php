<?php

class taringa_comu
{

	private $data;
	private $username;
	private $password;

	public $nombre_corto;
	public $nombre;
	public $avatar;
	public $miembros;
	public $temas;
	public $seguidores;

	public function process($nombre_corto, $username = false, $password = false)  
	{
			$this->nombre_corto = strtolower($nombre_corto);
			$this->username = $username;
			$this->password = $password;
			$this->hash = sha1($this->rand . $this->username . $this->nombre_corto);
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
		curl_setopt($ch, CURLOPT_URL, "http://www.taringa.net/comunidades/{$this->nombre_corto}");
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
		// Guardar nombre largo, verifica si se pueden conseguir los datos o no
		
		if (preg_match('/big-group-data\" style=\"margin-top:5px\"><h1><a href=\"\/comunidades\/' . $this->nombre_corto . '\/\">(.*)<\/a><\/h1><\/div><\/div><\/div><div class=\"box\"/', $this->data, $cname)) {
			$continue = true;
		} else {
			$continue = false;
		}

		
		if ($continue) {
			// Avatar
			preg_match('/<img class="big-avatar avatar-120" src="(.*)" alt="Ir a/', $this->data, $avatar);
			// Miembros
			preg_match('/<li class="members-count"><span data-val="(.*)">/', $this->data, $miembros);
			$miemb = explode('"', $miembros[1]);
			// Temas
			preg_match('/<li class="temas-count"><span data-val="(.*)">/', $this->data, $temas);
			$tem = explode('"', $temas[1]);
			// Seguidores
			preg_match('/<span class="data-followers-count" data-val="(.*)">/', $this->data, $seguidores);
			$seg = explode('"', $seguidores[1]);
			// Asignar datos a variables
			$this->nombre = $cname[1];
			$this->avatar = $avatar[1];
			$this->miembros = $miemb[0];
			$this->temas = $tem[0];
			$this->seguidores = $seg[0];
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

?>
