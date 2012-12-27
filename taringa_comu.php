<?php

class taringa_comu
{

  private $data;
  public $nombre_corto;
  public $nombre;
  public $avatar;
  public $miembros;
  public $temas;
  public $seguidores;
  
  public function process($nombre_corto, $live = false)
  {
    $this->nombre_corto = strtolower($nombre_corto);
    if ($live) {
      if ($this->fetchData()) {
        $this->storeCache();
        return true;
      }
      return false;
    } else {
      if (!$this->readCache()) {
        if ($this->fetchData()) {
          $this->storeCache();
          return true;
        }
        return false;
      }
      return true;
    }
  }

  public function fetchData()
  {
    if (!isset($this->data)) {
      	$ch = curl_init("http://FDW:PASS@www.taringa.net/comunidades/{$this->nombre_corto}");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.3');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$cont = curl_exec($ch);
		curl_close($ch);
		$this->data = compress::html($cont);
    }
	//STORE LONG NAME ON VARIABLE
	if (preg_match('/big-group-data\" style=\"margin-top:5px\"><h1><a href=\"\/comunidades\/' . $this->nombre_corto . '\/\">(.*)<\/a><\/h1><\/div><\/div><\/div><div class=\"box\"/', $this->data, $cname)) {
		$continue = true;
	} else {
		$continue = false;
	}
	if ($continue) {
		preg_match('/<img class="big-avatar avatar-120" src="(.*)" alt="Ir a/', $this->data, $avatar);
		preg_match('/<li class="members-count"><span data-val="(.*)">/', $this->data, $miembros);
		$miemb = explode('"', $miembros[1]);
		preg_match('/<li class="temas-count"><span data-val="(.*)">/', $this->data, $temas);
		$tem = explode('"', $temas[1]);
		preg_match('/<span class="data-followers-count" data-val="(.*)">/', $this->data, $seguidores);
		$seg = explode('"', $seguidores[1]);
		$this->nombre = $cname[1];
		$this->avatar = $avatar[1];
		$this->miembros = $miemb[0];
		$this->temas = $tem[0];
		$this->seguidores = $seg[0];
		return true;
	}
	return false;
  }

  private function storeCache()
  {
    $values = array(
      array('nombre_corto', $this->nombre_corto),
      array('nombre', $this->nombre),
      array('avatar', $this->avatar),
      array('miembros', $this->miembros),
      array('temas', $this->temas),
      array('seguidores', $this->seguidores),
      array('last_update', time()),
    );
    db::insert('comu_data', $values, $values);
  }

  private function readCache()
  {
    $db = db::row('SELECT * FROM comu WHERE nombre_corto = :nombre_corto', array(array('nombre_corto', $this->nombre_corto)));
    if ($db) {
      if ((time() - $db->last_update) < 3600) {
      	$this->nombre_corto = $db->nombre_corto;
        $this->nombre = $db->nombre;
        $this->avatar = $db->avatar;
        $this->miembros = $db->miembros;
        $this->temas = $db->temas;
        $this->seguidores = $db->seguidores;
        return true;
      }
      return false;
    }
    return false;
  }

}

?>