<?php 

class DBAdditional extends OAuth2\Storage\Pdo {

	public function __construct($connection, $config = array()) {
		parent::__construct($connection, $config);
		$this->config = array_merge(array(
            'auth_session_table' => 'oauth2_authentication_session',
            'auth_session_request_table' => 'oauth2_authentication_request'
        ), $this->config);
	}

	/**
   * @param string $client_id
   * @param null|string $client_secret
   * @return bool
   */
    public function checkSessionId($user_session_id)
    {
        $stmt = $this->db->prepare(sprintf("SELECT * from %s where user_session_id = :user_session_id and is_active='Y' ", $this->config['auth_session_table']));
        $stmt->execute(compact('user_session_id'));
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        // make this extensible
        return $stmt->rowCount() == 1 ?  true : false ;
    }

    /**
     * [setSessionsInactive description]
     * @param [type] $session_id [description]
     * @return none
     */
    public function setSessionsInactive($user_session_id, $end_type = 'forced') {
        //set the session related data to the given session id to is_active = 'N'
		$stmt = $this->db->prepare($sql = sprintf("UPDATE %s SET is_active='N', end_type = :end_type where user_session_id=:user_session_id", $this->config['auth_session_table']));
        return $stmt->execute(compact('user_session_id', 'end_type'));
    }

    /**
     * [createNewSession description]
     * @param  [type] $user_session_id [description]
     * @param  [type] $user_name       [description]
     * @param  [type] $client_id       [description]
     * @param  [type] $state_val       [description]
     * @return [type]                  [description]
     */
    public function createNewSession($user_session_id, $user_name, $client_id, $state_val) {
        $stmt = $this->db->prepare($sql = sprintf("INSERT INTO %s (`user_session_id`, `user_name`, `client_id`, `state_val`)
            VALUES (:user_session_id,:user_name,:client_id, :state_val)", $this->config['auth_session_table']));
        return $stmt->execute(compact('user_session_id', 'user_name', 'client_id', 'state_val'));
        // return true;
    }

    public function getSessionData($user_session_id) {
        $stmt = $this->db->prepare(sprintf("SELECT * from %s where user_session_id = :user_session_id and is_active='Y' ", $this->config['auth_session_table']));
        $stmt->execute(compact('user_session_id'));
        return  $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}