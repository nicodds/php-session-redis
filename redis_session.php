<?php
require('redis/Rediska.php');

define('REDIS_SERVER', 'localhost');
define('REDIS_PORT', 6379);


class Session
{
    private $_redis = null;
    private $options = array('namespace' => 'SESSION_',
			     'servers' => array(array('host' => REDIS_SERVER,
						      'port' => REDIS_PORT,
						      'persistent' => true)));


    public function open() {
      $this->_redis = new Rediska($this->options);

      if ($this->_redis) {
	return (bool)true;
      }

      return (bool)false;
    }

    public function read($id) {
      $str = base64_decode($this->_redis->get($id));

      return (string)$str;
    }

    public function write($id, $data) {
      $ttl = ini_get("session.gc_maxlifetime");


      $wr = new Rediska_Key($id);
      $wr->SetAndExpire(base64_encode($data), $ttl);

      return (bool)true;
    }

    public function destroy($id) {
      $this->_redis->delete($id);

      return (bool)true;
    }

    public function close() { }

    public function gc($max) {}

}

ini_set('session.save_handler', 'user');

$session = new Session();
session_set_save_handler(array($session, 'open'),
                         array($session, 'close'),
                         array($session, 'read'),
                         array($session, 'write'),
                         array($session, 'destroy'),
                         array($session, 'gc'));

?>