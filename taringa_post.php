<?php

class taringa_post
{

  
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
  
  public function process($id, $live = false)
  {
    $this->id = $id;
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
      $ch = curl_init();
      $a = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.3';
      curl_setopt($ch, CURLOPT_URL, 'https://www.taringa.net/registro/login-submit.php');
      curl_setopt($ch, CURLOPT_USERAGENT, $a);
      curl_setopt ($ch, CURLOPT_POST, 1);
      curl_setopt ($ch, CURLOPT_POSTFIELDS, 'nick=glizp&pass=abracadabra');
      curl_setopt ($ch, CURLOPT_COOKIEJAR, '/home/glizpcom/public_html/cache/' . md5('cookie'));
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
      $login = curl_exec ($ch);
      curl_setopt($ch, CURLOPT_URL, "http://www.taringa.net/posts/glizp/" . $this->id);
      curl_setopt($ch, CURLOPT_COOKIEFILE, '/home/glizpcom/public_html/cache/' . md5('cookie'));
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_USERAGENT, $a);
      $cont = curl_exec($ch);
      curl_close ($ch); 
      $this->data = compress::html($cont);
    }
    preg_match('/<title>(.*)<\/title>/', $this->data, $titulo);
    if (preg_match('/meta property="dc:creator" content/', $this->data)) {
      preg_match('/title=\"Post de (.*)\" href=\"\/rss\/(.*)\/posts\/\" \/>/', $this->data, $nick);
      $continue = true;
    } else {
      $continue = false;
    }
    if ($continue) {
      preg_match('/<span id=\"postTotalScore\">(.*)<\/span> Puntos/', $this->data, $puntos);
      preg_match('/<i class="icon visits"><\/i><span>(.*)<\/span><\/div><strong>Visitas/', $this->data, $visitas);
      preg_match('/<span id="fav_counter" data-val="(.*)">(.*)<\/span><\/div>/', $this->data, $favs);
      preg_match('/<span class=\"data-followers-count\" data-val=\"(.*)\">(.*)<\/span><\/div>/', $this->data, $seguidores);
      preg_match('/Hace(.*)<\/a><\/div><ul class=\"post-share post-share-list\"/', $this->data, $bread);
      preg_match('#<div class=\"breadcrumb\"><a href=\"/posts/(.*)\">(.*)</a>(.*)<a title=#', $this->data, $category);
      $cat = explode('"', $category[1]);
      $seg = explode('"', $seguidores[1]);
      $fav = explode('"', $favs[1]);
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

  private function storeCache()
  {
    $values = array(
      array('id', $this->id, PDO::PARAM_INT),
      array('creador', $this->creador),
      array('titulo', $this->titulo),
      array('puntos', $this->puntos),
      array('visitas', $this->visitas),
      array('favoritos', $this->favoritos),
      array('seguidores', $this->seguidores),
      array('categoria', $this->categoria),
      array('tiempo', $this->tiempo),
      array('last_update', time()),
    );
    db::insert('posts_data', $values, $values);
  }

  private function readCache()
  {
    $db = db::row('SELECT * FROM posts_data WHERE id = :id', array(array('id', $this->id)));
    if ($db) {
      if ((time() - $db->last_update) < 3600) {
        $this->creador = $db->creador;
        $this->titulo = $db->titulo;
        $this->puntos = $db->puntos;
        $this->visitas = $db->visitas;
        $this->favoritos = $db->favoritos;
        $this->seguidores = $db->seguidores;
        $this->categoria = $db->categoria;
        $this->tiempo = $db->tiempo;
        return true;
      }
      return false;
    }
    return false;
  }

}

?>