<?php

class taringa_user
{

	private $data;
	private $compressed_data;
	public $user;
	public $sex;
	public $status;
	public $karma;
	public $rank;
	public $rank_legacy;
	public $score;
	public $posts;
	public $topics;
	public $comments;
	public $followers;
	public $following;
	public $communities;
	public $medals;
	public $country;
	public $message;
	public $avatar;
	public $website;
	public $facebook;
	public $twitter;
	
	public function process($nick, $live = false)
	{
		$this->user = $nick;
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
			curl_setopt($ch, CURLOPT_URL, "http://www.taringa.net/{$this->user}");
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$this->data = curl_exec($ch);
			$this->compressed_data = compress::html($this->data);
			curl_close ($ch); 
		}
		# GET USERNAME
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
			# POSTS
			preg_match_all('/<a href="\/'.$user.'\/posts">(.*)<\/a>/', $this->data, $posts);
			# COMMENTS
			preg_match('/Posts<\/span><\/li><li><strong>(.*)<\/strong><span>Comentarios/', $this->compressed_data, $comments);
			# AVATAR
			preg_match('/photo\" src=\"(.*)\" alt/', $this->data, $avatar);
			# RANK
			preg_match('/<span class="role"><strong>(.*)<\/strong><\/span>/', $this->data, $rank);
			# SCORE
			preg_match('/<li><strong>(.*)<\/strong><span>Puntos/', $this->compressed_data, $score);
			# FOLLOWERS
			preg_match('/class="data-followers-count" href="(.*)seguidores">(.*)<\/a>/', $this->data, $followers);
			# FOLLOWING
			preg_match('/class="data-following-count" href="(.*)siguiendo">(.*)<\/a>/', $this->data, $following);
			# KARMA
			preg_match('/<strong>Nivel de Karma:<\/strong> (.*)<\/div>/', $this->data, $karma);
			# TOPICS
			preg_match_all('/<a href="\/(.*)\/temas">(.*)<\/a>/', $this->data, $topics);
			# WEBSITE
			preg_match('/<span class=\"url\"><a href=\"(.*)\" target=\"_blank\" rel=\"nofollow\"/', $this->compressed_data, $website);
			# COMMUNITIES
			preg_match_all('/\/comunidades">(.*)<\/a>/', $this->data, $comu);
			if ($comu[1] > "0") { 
				$communities = $comu[1][1]; 
			} else { 
				$communities = "0"; 
			}
			# MEDALS
			preg_match('/medallas">(.*)<\/a>/', $this->data, $medals);
			if ($medals[1] > 0){ 
				$medal = $medals[1]; 
			} else { 
				$medal = "0"; 
			}
			# ONLINE STATUS
			preg_match('/-792px" title="(.*)"><\/span>/', $this->data, $status);
			if ($status[1] == "Online"){ 
				$current = "Online"; 
			} else { 
				$current = "Offline"; 
			}
			# PERSONAL MESSAGE
			if (preg_match('/<div><span class="bio">/', $this->compressed_data)) { 
				$msg = null; 
			} else { 
				preg_match('/<div>(.*)<\/div>/', $this->data, $message); $msg = $message[1]; 
			}
			# FACEBOOK
			if (preg_match('/<a href="http:\/\/facebook.com\/(.*)" target/', $this->data, $FB)) { 
				$facebook = str_replace('profile.php?id=', '', $FB[1]); 
			} else { 
				$facebook = null; 
			}
			# TWITTER
			if(preg_match('/<a href="http:\/\/twitter.com\/(.*)" target/', $this->data, $TW)){ 
				$twitter = $TW[1]; 
			} else { 
				$twitter = null; 
			}
			# COUNTRY
			preg_match('/hspace="3" height="11" align="absmiddle" width="16" alt="(.*)" src=/', $this->data, $cntry);
			$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
			$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
			$country = str_replace($search, $replace, $cntry[1]);
			# SEX
			if (preg_match('/icon hastipsy male/', $this->data)) { 
				$sex = "hombre"; 
			} elseif (preg_match('/icon hastipsy female/', $this->data)) { 
				$sex = "mujer"; 
			} else { 
				$sex = null; 
			}
			# RANK-LEGACY
			if (preg_match('/medalla-new-full-user/', $this->data)) { 
				$rank_legacy = "New Full User"; 
			} elseif (preg_match('/medalla-full-user/', $this->data)) { 
				$rank_legacy = "Full User"; 
			} elseif (preg_match('/medalla-great-user/', $this->data)) { 
				$rank_legacy = "Great User"; 
			} else { 
				$rank_legacy = "Novato"; 
			}
		
			$this->user = $user;
			$this->sex = $sex;
			$this->status = $current;
			$this->karma = $karma[1];
			$this->rank = $rank[1];
			$this->rank_legacy = $rank_legacy;
			$this->score = str_replace( '.', '', $score[1]);
			$this->posts = str_replace( '.', '', $posts[1][1]);
			$this->topics = str_replace( '.', '', $topics[2][1]);
			$this->comments = str_replace( '.', '', $comments[1]);
			$this->followers = str_replace( '.', '', $followers[2]);
			$this->following = str_replace( '.', '', $following[2]);
			$this->communities = str_replace( '.', '', $communities);
			$this->medals = str_replace( '.', '', $medal);
			$this->country = $country;
			$this->message = $message[1];
			$this->avatar = $avatar[1];
			$this->website = $website[1];
			$this->facebook = $facebook;
			$this->twitter = $twitter;

			return true;
		}
		return false;
	}

	private function storeCache()
	{
		$values = array(
			array('nick', $this->user),
			array('sex', $this->sex),
			array('status', $this->status),
			array('karma', $this->karma),
			array('rank', $this->rank),
			array('rank_legacy', $this->rank_legacy),
			array('score', $this->score),
			array('posts', $this->posts),
			array('topics', $this->topics),
			array('comments', $this->comments),
			array('followers', $this->followers),
			array('following', $this->following),
			array('communities', $this->communities),
			array('medals', $this->medals),
			array('country', $this->country),
			array('message', htmlspecialchars($this->message)),
			array('avatar', $this->avatar),
			array('website', $this->website),
			array('facebook', $this->facebook),
			array('twitter', $this->twitter),
			array('last_update', time()),
		);
		db::insert('users_data', $values, $values);
	}

	private function readCache()
	{
		$db = db::row('SELECT * FROM users_data WHERE nick = :nick', array(array('nick', $this->user)));
		if ($db) {
			if ((time() - $db->last_update) < 3600) {
				$this->user = $db->nick;
				$this->sex = $db->sex;
				$this->status = $db->status;
				$this->karma = $db->karma;
				$this->rank = $db->rank;
				$this->rank_legacy = $db->rank_legacy;
				$this->score = $db->score;
				$this->posts = $db->posts;
				$this->topics = $db->topics;
				$this->comments = $db->comments;
				$this->followers = $db->followers;
				$this->following = $db->following;
				$this->communities = $db->communities;
				$this->medals = $db->medals;
				$this->country = $db->country;
				$this->message = $db->message;
				$this->avatar = $db->avatar;
				$this->website = $db->website;
				$this->facebook = $db->facebook;
				$this->twitter = $db->twitter;
				return true;
			}
			return false;
		}
		return false;
	}

}

?>