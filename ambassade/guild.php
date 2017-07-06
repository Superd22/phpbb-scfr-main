<?php  namespace scfr\main\ambassade;
class Guild {
  protected $SSID;
  protected $topic;
  protected $first_reply;
  /** @param \phpbb\db\driver\driver_interface */
  protected $db;
  public $is_registerd;
  public $RSI;

  function __construct(\phpbb\db\driver\driver_interface &$db) {
    $this->db = $db;
    $is_registerd = false;
  }

  function __topic_init($_id) {
    $this->topic = (integer) $_id;
    $this->set_up();
  }

  function __ssid_init($_ssid) {
    $this->SSID = $_id;
    $this->set_up();
  }

  private function set_up() {
    if($this->check_guild_is_registered()) {
      $this->get_rsi_api_info();
    }
  }


  public function check_guild_is_registered() {
    $criteria = "topic";
    $var = $this->topic;
    if($this->SSID) {
      $criteria = "SSID";
      $var = $this->SSID;
    }

    $sql = "SELECT SSID,topic FROM star_ambassade
    WHERE {$criteria} = '{$var}' LIMIT 1";
    $result = $this->db->sql_query($sql);
    $ssid = $this->db->sql_fetchrow($result);

    if($ssid["SSID"]) {
      $this->SSID = $ssid["SSID"];
      $this->topic = $ssid["topic"];
      $this->is_registerd = true;
      return $ssid["SSID"];
    }
    else return false;
  }

  public function check_reg() {
    $add = request_var("scfrGuildAdd", false);
    $ssid = request_var("scfrOrgName", "");
    if($add && $ssid) {
      $ssid = strtoupper($ssid);
      $sql = "INSERT INTO star_ambassade (SSID, topic) VALUES ('$ssid', '') ";
    }
  }

  private function update_topic($_newtopic) {

  }

  private function update_ssid($_newSSID) {

  }

  private function register_topic() {

  }

  private function unregister_topic() {

  }

  public function get_rsi_api_info() {
    if($this->SSID) {
      if(!$this->RSI) {
        $api = json_decode(file_get_contents('https://starcitizen.fr/API/RSI/?api_source=cache&system=organizations&action=single_organization&target_id='.$this->SSID.'&format=json'));
        $this->RSI = $api->data;
      }
    }
    else throw new \Exception("no SSID declared");
  }

  public function get_logo() {
    return isset($this->RSI->logo) ? $this->RSI->logo : null;
  }

}
?>
