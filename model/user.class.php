<?php

!defined('IN_APP') && exit('Access Denied');

class usermodel extends modelbase{

	function __construct($base) {
        parent::__construct($base);
		$this->convert=true;;
        $this->idname = 'uid';
		$this->sortfields = array('uid'=>1);
    }
    
	function convert($item){
		if(isset($item['format_regtime'])) $item['format_regtime'] = tdate($item['regtime']);
		if(isset($item['format_birthday'])) $item['format_birthday'] = tdate($item['birthday'],2);
		if(isset($item['format_gender'])) $item['format_gender'] = (1==$item['gender'])?"男":"女";
		
		if (!isset($item['avatar'])) $item['avatar'] = '0/avatar_0.jpg';
		if (!isset($item['truename'])) $item['truename'] = $item['username'];
		return $item;
	}

    function refresh($user) {
        @$sid = tcookie('sid');
        $this->base->user = $user;
        //$this->db->query("UPDATE " . DB_TABLEPRE . "user SET `lastlogin`={$this->base->time}  WHERE `uid`=$uid"); //更新最后登录时间
        //$this->db->query("REPLACE INTO " . DB_TABLEPRE . "session (sid,uid,islogin,`time`) VALUES ('$sid',$uid,$islogin,{$this->base->time})");
        $uid = $user['uid'];
        $password = $user['password'];
        $auth = strcode("$uid\t$password", $this->base->setting['auth_key'], 'ENCODE');
		tcookie('auth', $auth, 864300*30);
    }

    function logout() {
        $sid = $this->base->user['sid'];
        tcookie('sid', '', 0);
        tcookie('auth', '', 0);
        tcookie('loginuser', '', 0);
        if ($sid) {
            //$this->db->query('DELETE FROM ' . DB_TABLEPRE . 'session WHERE sid=\'' . $sid . '\'');
        }
    }

  /*检测用户名合法性*/
    function check_usernamecensor($username) {
        $censorusername = $this->base->setting['censor_username'];
        $censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censorusername = trim($censorusername)), '/')).')$/i';
        if($censorusername && preg_match($censorexp, $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*检测邮件地址合法性*/
    function check_emailaccess($email) {
        $setting = $this->base->setting;
        $accessemail = $setting['access_email'];
        $censoremail = $setting['censor_email'];
        $accessexp = '/('.str_replace("\r\n", '|', preg_quote(trim($accessemail), '/')).')$/i';
        $censorexp = '/('.str_replace("\r\n", '|', preg_quote(trim($censoremail), '/')).')$/i';
        if($accessemail || $censoremail) {
            if(($accessemail && !preg_match($accessexp, $email)) || ($censoremail && preg_match($censorexp, $email))) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    /*注册安全频率检查,超过频率，返回false */
	function check_num($num,$minute) {
	     $ip=$this->base->ip;
         $endtime=$this->base->time;
         $begintime=$endtime-$minute*60;
	     $criteria=array('regtime'=>array('$gt'=>$begintime,'$lt'=>$endtime),'regip'=>$ip);
         return ($num > $this->count($criteria));
	 }



}
