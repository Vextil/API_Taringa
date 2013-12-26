<?php

class taringa_user_api
{

	private $data;
	private $compressed_data;

	public $usuario;
	public $sexo;
	public $estado;
	public $karma;
	public $rango;
	public $rango_pre_karma;
	public $puntos;
	public $posts;
	public $temas;
	public $comentarios;
	public $seguidores;
	public $siguiendo;
	public $comunidades;
	public $medallas;
	public $pais;
	public $mensaje;
	public $avatar;
	public $pagina_web;
	public $facebook;
	public $twitter;
	
	public function process($nick)
	{
		$this->usuario = $nick;
		$this->fetchData();
		return $this->parseData();
	}

	private function fetchData()
	{
		$ch = curl_init();
		$a = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.3';
		curl_setopt($ch, CURLOPT_URL, "http://www.taringa.net/{$this->usuario}");
		curl_setopt($ch, CURLOPT_USERAGENT, $a);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$this->data = curl_exec($ch);
		$this->compressed_data = $this->compress($this->data);
		curl_close ($ch); 
	}

	private function parseData()
	{
		// Username capitalizado correctamente
		if (preg_match('/class="fn">@(.*)span>/', $this->data, $nick)) { 
			$user = str_replace('</', '', $nick[1]); 
			$continue = true;
		} else { 

			if (preg_match('/<span class="nickname">@(.*)<\/span> - <span class=\"adr\"/', $this->data, $nickk)) {
				$user = $nickk[1]; 
				$continue = true;
			} else {
				$continue = false;
			}
		}
		if ($continue) {
			// Posts
			preg_match_all('/<a href="\/'.$user.'\/posts">(.*)<\/a>/', $this->data, $posts);
			// Comentarios
			preg_match('/Posts<\/span><\/li><li><strong>(.*)<\/strong><span>Comentarios/', $this->compressed_data, $comments);
			// Avatar
			preg_match('/photo\" src=\"(.*)\" alt/', $this->data, $avatar);
			// Rango
			preg_match('/<span class="role"><strong>(.*)<\/strong><\/span>/', $this->data, $rank);
			// Puntos
			preg_match('/<li><strong>(.*)<\/strong><span>Puntos/', $this->compressed_data, $score);
			// Seguidores
			preg_match('/class="data-followers-count" href="(.*)seguidores">(.*)<\/a>/', $this->data, $followers);
			// Siguiendo
			preg_match('/class="data-following-count" href="(.*)siguiendo">(.*)<\/a>/', $this->data, $following);
			// Karma
			preg_match('/<strong>Nivel de Karma:<\/strong> (.*)<\/div>/', $this->data, $karma);
			// Temas
			preg_match_all('/<a href="\/(.*)\/temas">(.*)<\/a>/', $this->data, $topics);
			// Pagina web
			preg_match('/<span class=\"url\"><a href=\"(.*)\" target=\"_blank\" rel=\"nofollow\"/', $this->compressed_data, $website);
			$wbst = explode('"', $website[1]);
			// Comunidades
			preg_match_all('/\/comunidades">(.*)<\/a>/', $this->data, $comu);
			if ($comu[1] > "0") { 
				$communities = $comu[1][1]; 
			} else { 
				$communities = "0"; 
			}
			// Medallas
			preg_match('/medallas">(.*)<\/a>/', $this->data, $medals);
			if ($medals[1] > 0){ 
				$medal = $medals[1]; 
			} else { 
				$medal = "0"; 
			}
			// Estado
			preg_match('/-792px" title="(.*)"><\/span>/', $this->data, $status);
			if (isset($status[1]) && $status[1] == "Online"){ 
				$current = "Online"; 
			} else { 
				$current = "Offline"; 
			}
			// Mensaje
			if (preg_match('/<div><span class="bio">/', $this->compressed_data)) { 
				$msg = null; 
			} else { 
				preg_match('/<div>(.*)<\/div>/', $this->data, $message); $msg = $message[1]; 
			}
			// Facebook
			if (preg_match('/<a href="http:\/\/facebook.com\/(.*)" target/', $this->data, $FB)) { 
				$facebook = str_replace('profile.php?id=', '', $FB[1]); 
			} else { 
				$facebook = null; 
			}
			// Twitter
			if(preg_match('/<a href="http:\/\/twitter.com\/(.*)" target/', $this->data, $TW)){ 
				$twitter = $TW[1]; 
			} else { 
				$twitter = null; 
			}
			// Pais
			preg_match('/hspace="3" height="11" align="absmiddle" width="16" alt="(.*)" src=/', $this->data, $cntry);
			$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
			$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
			$country = str_replace($search, $replace, $cntry[1]);
			// Sexo
			if (preg_match('/icon hastipsy male/', $this->data)) { 
				$sex = "hombre"; 
			} elseif (preg_match('/icon hastipsy female/', $this->data)) { 
				$sex = "mujer"; 
			} else { 
				$sex = null; 
			}
			// Rango pre-karma
			if (preg_match('/medalla-new-full-user/', $this->data)) { 
				$rank_legacy = "New Full User"; 
			} elseif (preg_match('/medalla-full-user/', $this->data)) { 
				$rank_legacy = "Full User"; 
			} elseif (preg_match('/medalla-great-user/', $this->data)) { 
				$rank_legacy = "Great User"; 
			} else { 
				$rank_legacy = "Novato"; 
			}
		
			$this->usuario = $user;
			$this->sexo = $sex;
			$this->estado = $current;
			$this->karma = $karma[1];
			$this->rango = $rank[1];
			$this->rango_pre_karma = $rank_legacy;
			$this->puntos = str_replace( '.', '', $score[1]);
			$this->posts = str_replace( '.', '', $posts[1][1]);
			$this->temas = str_replace( '.', '', $topics[2][1]);
			$this->comentarios = str_replace( '.', '', $comments[1]);
			$this->seguidores = str_replace( '.', '', $followers[2]);
			$this->siguiendo = str_replace( '.', '', $following[2]);
			$this->comunidades = str_replace( '.', '', $communities);
			$this->medallas = str_replace( '.', '', $medal);
			$this->pais = $country;
			$this->mensaje = $message[1];
			$this->avatar = $avatar[1];
			$this->pagina_web = $wbst[0];
			$this->facebook = $facebook;
			$this->twitter = $twitter;

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
