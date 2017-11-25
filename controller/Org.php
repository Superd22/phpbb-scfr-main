<?php namespace scfr\main\controller;

/**
* Controller class for api org thingies
*/
class Org {
    protected $api;
    
    protected $topic;
    protected $ssid;
    
    public function __construct(\scfr\phpbbJsonTemplate\helper\api $api) {
        $this->api = $api;
    }
    
    public function handle($mode) {
        $mode = request_var("mode", "register");
        $this->switch_mode($mode);
        return $this->api->render_message($this->payload);
    }
    
    protected function switch_mode($mode) {
        $this->getDatas();
        switch($mode) {
            case "register": $this->register(); break;
            case "delete": $this->delete(); break;
        }
    }
    
    /**
     * Checks for the required GET data
     */
    protected function getDatas() {
        $this->topic = (integer) request_var('topic', 0);
        $this->ssid = request_var('ssid', "");
    }
    
    /**
     * Check if the current user has the rights to do anything on that topic
     */
    protected function hasRights() {
        global $user, $auth, $db;

        if($user->data['user_id'] < 2) return false;
        $sql = "SELECT forum_id, topic_poster FROM testfo_topics WHERE topic_id={$this->topic} LIMIT 1";
        $result = $db->sql_query($sql);
        $test = $db->sql_fetchrow($result);
        
        if($user->data['user_id'] == $test['topic_poster']) return true;
        return $auth->acl_get('m_delete', (integer) $test['forum_id']);
    }

    /**
     * Delete the supplied association of ssid
     * @return void
     */
    protected function delete() {
        global $db;
        error_reporting(-1);
        // check we have a topic
        if(empty($this->topic)) {
            $this->payload = ["err" => true, "msg" => "need topic"];

            return;
        }

        // Check we have the rights
        if(!$this->hasRights()) {
            $this->payload = [
                "err" => true,
                "msg" => "You don't have the rights to do that.",
            ];

            return;
        }


        // Just delete the thingy.
        $sql = "DELETE from star_ambassade WHERE topic={$this->topic}";        
        $result = $db->sql_query($sql);
        $this->payload = [
            "err" => false,
            "msg" => "ok",
        ];

    }

    /**
     * Registers or updates an association for the supplied ssid & topic
     * @return void
     */
    protected function register() {
        global $db;
            
        if(empty($this->topic) || empty($this->ssid)) {
            $this->payload = ["err" => true, "msg" => "need ssid & topic"];
            return;
        }
            
        // Check for this ssid
        $sql = "SELECT * from star_ambassade WHERE SSID = '". $db->sql_escape($this->ssid) ."'";
        $result = $db->sql_query($sql);
        $test = $db->sql_fetchrow($result);

        // IF this ssid is already set
        if($test['SSID']) {
            $this->payload = ["err" => true, "msg" => $test['topic'] != $this->topic ? "ssid is already assigned to a topic" : "ssid already set to this topic"];
            return;
        }

        if(!$this->hasRights()) {
            $this->payload = [
                "err" => true,
                "msg" => "You don't have the rights to do that.",
            ];
            return;
        }

        // Otherwise we can register
        $sql = "INSERT INTO star_ambassade (ssid, topic) VALUES('".$db->sql_escape($this->ssid)."', {$this->topic}) ON DUPLICATE KEY UPDATE    
        ssid='".$db->sql_escape($this->ssid)."'";
        $result = $db->sql_query($sql);
        
        $this->payload = [
            "err" => false,
            "msg" => "ok",
        ];
    }
}