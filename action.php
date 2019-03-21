<?php
/**
 *@author    Myron Turner <turnermm02@shaw.ca> 
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
class action_plugin_goto extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {   
	  $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_act',array('before'));   
    }
	
	function handle_act(Doku_Event $event, $param) {   	   
	   global $conf,$USERINFO,$INPUT;
		$act = act_clean($event->data);
		 if($act != 'login') {
				return;
		}
	    $user = $_SERVER['REMOTE_USER'];
		if(!$user) return;
		$auto_login = $this->getConf('auto_login');   
        $users_only = $this->getConf('user_only');
		$redirect_target = "";
		if($auto_login && ! $users_only) {			   
			$user_grps = $USERINFO['grps'];		
			$groups = $this->getConf('group');
			$groups = preg_replace("/\s+/","",$groups);
			$groups = explode(',',$groups);
		    $grp_opt = $this->getConf('group_options');
			foreach($groups as $grp) {
				if(in_array ($grp , $user_grps)) {
					$redirect_target = "$grp:";
					$redirect_target .= ($grp_opt == 'user_page' ? $user : $conf['start']);							
                    break;					
				}
			}	
			if($redirect_target) {
				setcookie("GOTO_LOGIN", $redirect_target, time()+120, DOKU_BASE);		
                return;				
			}			
		}			
		//msg($redirect_target);	
		$groups_only = $this->getConf('group_only');
        if($groups_only)  return;
		if($auto_login) {		       
		   $option  = $this->getConf('auto_options');
		   $common = $this->getConf('common_ns');
		   if($common) {
			   $common = rtrim($common,':');    
		   }               
		   $srch = array('common_ns','user_page','user_ns','start_page');               
		   $repl = array($common,$user,$user,$conf['start']);
		   $value = str_replace($srch,$repl,$option); 
		   setcookie("GOTO_LOGIN", $value, time()+120, DOKU_BASE);
		   return;
		}
		else {
			setcookie("DOKU_GOTO", $event->data['user'], time()+120, DOKU_BASE);
		}
		
    }
	function handle_login(Doku_Event $event, $param) {   
	    global $conf;
		$auto_login = $this->getConf('auto_login');
        if(!empty($event->data['user'])) {
			if($auto_login) {
		       $user = $event->data['user']; 
               $option  = $this->getConf('auto_options');
               $common = $this->getConf('common_ns');
               if($common) {
                   $common = rtrim($common,':');    
               }               
               $srch = array('common_ns','user_page','user_ns','start_page');               
               $repl = array($common,$user,$user,$conf['start']);
               $value = str_replace($srch,$repl,$option);             						
			   setcookie("GOTO_LOGIN", $value, time()+120, DOKU_BASE);
			   return;
			}
			else {
				setcookie("DOKU_GOTO", $event->data['user'], time()+120, DOKU_BASE);
			}
        }
	}
}