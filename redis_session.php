<?php
/*
  Copyright 2011, Alca Società Cooperativa
  
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 
  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

// We use Rediska to wrap the connection to Redis. Change this in
// order to fit your installation
require('redis/Rediska.php');

define('REDIS_SERVER', 'localhost');
define('REDIS_PORT', 6379);


// This class implements the methods needed by
// session_set_save_handler()
class Session
{
    private $_redis = null;
    private $options = array('namespace' => 'SESSION_',
			     'servers' => array(array('host' => REDIS_SERVER,
						      'port' => REDIS_PORT,
						      'persistent' => true)));

    // open
    public function open() {
      // create the connection to Redis
      if ($this->_redis = new Rediska($this->options)) {
	return (bool)true;
      }

      return (bool)false;
    }

    // read
    public function read($id) {
      // return the (decoded) content of the given key
      return (string)base64_decode($this->_redis->get($id));
    }

    // write
    public function write($id, $data) {
      // get the default session max lifetime 
      $ttl = ini_get("session.gc_maxlifetime");
      
      // use Redis' setex command to set the key value and its expire
      // time
      $cmd = new Rediska_Key($id);
      
      // the session string is base64 encoded before being stored
      return (bool) $cmd->SetAndExpire(base64_encode($data), $ttl);
    }

    // delete
    public function destroy($id) {
      // delete the key from Redis
      return (bool) $this->_redis->delete($id);
    }


    // these methods are unuseful in this implementation
    public function close() {}
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
