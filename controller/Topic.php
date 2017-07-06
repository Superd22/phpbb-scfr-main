<?php namespace scfr\main\controller;

class Topic {

  /** @param \phpbb\db\driver\driver_interface */
  protected $db;
  /** @param \phpbb\template\template */
  protected $template;
  protected $user_cache;
  public $topic_ambassade_set_up;

  function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template) {
      require_once(__DIR__."./../ambassade/guild.php");
    $this->db = $db;
    $this->template = $template;
  }

  // ambassade_single_topic
  // fetches and sets the guild info for a single ambassade topic
  // (uses $template to add to the block.)
  // @param $event PHPBB viewtopic_modify_post_row event
  public function ambassade_single_topic(&$event) {
  global $user;
    // IF IS AMBASSADE
    if($event["row"]["forum_id"] == 29 && !$this->topic_ambassade_set_up) {
      $guild = new \scfr\main\ambassade\Guild($this->db);
      $first_reply = $event["topic_data"]["topic_first_post_id"];
      $guild->__topic_init($event["row"]["topic_id"]);

      $templates["GUILD_JSON"] = json_encode(false);
      if($guild->is_registerd) {
        $templates["TOPIC_IS_REGISTERED_GUILD"] = true;
        foreach($guild->RSI as $name => $val)
        $templates["GUILD_".strtoupper($name)] = $val;

        $templates["GUILD_JSON"] = json_encode($guild->RSI);
      }

      $templates["TOPIC_IS_GUILD"] = true;
      $templates["GUILD_TOPIC_ID"] = $event["topic_data"]["topic_id"];

      $templates["S_REQUIRE_ANGULAR"] = true;
      $this->template->assign_vars($templates);
      $this->topic_set_up = true;
    }
  }

  // Set_message_right_hand_side_guild_info
  // fetches and set the guild logo on the right hand side of a message on the forum
  // (returns a modified $event post_row)
  // @param $event PHPBB viewtopic_modify_post_row event
  public function set_message_right_hand_side_guild_info(&$event) {
    $poster_id = $event["row"]["user_id"];
    if(!isset($this->user_cache[$poster_id]) || $this->user_cache[$poster_id]['GUILD']['done'] != true) {
      $guild = $this->db->sql_query("SELECT pf_handle,pf_handle_public,pf_guild_public,pf_guild FROM testfo_profile_fields_data WHERE user_id='".$poster_id."' ");
      $raw = $this->db->sql_fetchrow($guild);
      if($raw['pf_guild_public'] == 1)
        $this->user_cache[$poster_id]['GUILD']["G_SHOW_G"] = true;
      if($raw['pf_handle_public'] == 1)
        $this->user_cache[$poster_id]['GUILD']["G_SHOW_H"] = true;
      if($raw['pf_handle'] != '')
        $this->user_cache[$poster_id]['GUILD']["G_U_HANDLE"] = $raw['pf_handle'];

      if($raw['pf_guild'] != '') {
        $this->user_cache[$poster_id]['GUILD']["has_guild"] = true;
        $this->user_cache[$poster_id]['GUILD']["guild"] = $raw['pf_guild'];
        if(isset($this->user_cache[$poster_id]['GUILD']["G_SHOW_G"]) && $this->user_cache[$poster_id]['GUILD']["G_SHOW_G"]) {
          $logos = $this->db->sql_query("SELECT logo FROM api_organizations_rsi_info WHERE sid='".$this->user_cache[$poster_id]['GUILD']["guild"]."' ORDER BY scrape_date DESC");
          while($logo = $this->db->sql_fetchrow($logos)) {
            if($logo['logo'] != '') {
              $logo['logo'] = str_replace('http://','//', $logo['logo']);
              $this->user_cache[$poster_id]['GUILD']["G_SQ_LOGO"] = $logo['logo'];
              $this->user_cache[$poster_id]['GUILD']["G_HAS_GUILD"] = $logo['logo'];
              break;
            }
          }
          $logos = $this->db->sql_query("SELECT title FROM api_organizations_rsi_info WHERE sid='".$this->user_cache[$poster_id]['GUILD']["guild"]."' ORDER BY scrape_date DESC");
          while($logo = $this->db->sql_fetchrow($logos)) {
            if($logo['title'] != '') {
              $this->user_cache[$poster_id]['GUILD']["G_SQ_NAME"]  = $logo['title'];
              break;
            }
          }

          $t_raw = $this->db->sql_query("SELECT topic FROM star_ambassade WHERE SSID='{$this->user_cache[$poster_id]['GUILD']['guild']}' LIMIT 1");
          $topic = $this->db->sql_fetchrow($t_raw);

          if($topic['topic'] > 0) $topic = $topic['topic'];
            else $topic = false;

          if($topic) $this->user_cache[$poster_id]['GUILD']["G_U_SQ"]  = "https://starcitizen.fr/Forum/viewtopic.php?f=29&t=".$topic;
            else $this->user_cache[$poster_id]['GUILD']["G_U_SQ"]  = "https://starcitizen.fr/Forum/viewforum.php?f=29";
          }
        }
      $this->user_cache[$poster_id]['GUILD']['done'] = true;
    }

    $cache = $event["post_row"];
    $cache = array_merge($cache, $this->user_cache[$poster_id]['GUILD']);
    $event["post_row"] = $cache;
  }

  // ambassade_add_logo_topic
  // fetches and displays left-hand side logo for topic in the ambassade.
  // @param $event PHPBB viewforum_modify_topicrow event
  public function ambassade_add_logo_topic(&$event) {
    $guild = new \scfr\main\ambassade\Guild($this->db);
    $guild->__topic_init($event["topic_row"]["TOPIC_ID"]);
    if($guild->is_registerd) {
      $cache = $event["topic_row"];
      $cache["S_TOPIC_GUILD_LOGO"] = $guild->get_logo();
      $event["topic_row"] = $cache;
    }
  }

  // TO DO : re-do via bb-code. (spaces not working)
  // custom_parse
  // Does the custom parsing (eg : @{pseudo})
  // @param $event PHPBB viewtopic_modify_post_row event
  public function custom_parse(&$event) {
		// PSEUDO FORUM @
    $message = $event["post_row"];
		$message["MESSAGE"] = preg_replace('/(?!\b)(@\w+\b)/',"<a class='jquparseme scfr_pseudo_link'>$1</a>",$message["MESSAGE"]);


    $event["post_row"] = $message;
  }


}

?>
