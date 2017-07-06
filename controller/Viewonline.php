<?php namespace scfr\main\controller;

class Viewonline {

  /** @param \phpbb\db\driver\driver_interface */
  protected $db;
  /** @param \phpbb\template\template */
  protected $template;

  function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template) {
    $this->db = $db;
    $this->template = $template;
  }

  private function user_get_avatar_by_id($user_id = 0) {
    global $phpbb_root_path, $phpEx;
    $sql = 'SELECT user_avatar, user_avatar_type, user_type
        FROM ' . USERS_TABLE . '
        WHERE user_id = ' . (int) $user_id;
    $result = $this->db->sql_query($sql);
    $row = $this->db->sql_fetchrow($result);
    $this->db->sql_freeresult($result);
    if ($row) {
        if($row['user_type'] === 2) {
          $output =  '<img src="https://starcitizen.fr/Forum/images/avatars/cylon.jpg" width="30" height="30" alt="Avatar de l’utilisateur">';
        }
        else if ($row['user_avatar_type'] == "avatar.driver.remote") {
            $output = '<img src="' . (str_replace(' ', '%20', $row['user_avatar'])) . '" width="30" height="30"  alt="" />';
        }
        else if ($row['user_avatar_type'] == "avatar.driver.upload") {
            $output = '<img src="' . $phpbb_root_path . "download/file.$phpEx?avatar=" . $row['user_avatar'] . '"  width="30" height="30"  alt="" />';
        }
        if (!empty($output)) {
            return $output;
        }
        else {
            $output =  '<img src="https://starcitizen.fr/Forum/images/avatars/normal.jpeg" width="30" height="30" alt="Avatar de l’utilisateur">';
        }
    }
    else {
        return false;
    }
}

  // Function obtain_users_online_string
  // used to display avatars instead of strings for online users.
  // (uses $event to change template)
  // @param event core.obtain_users_online_string_modify event.
  public function obtain_users_online_string($event) {
    $cache = $event["online_userlist"];
    $online_userlist = "";
    // No need for cache since we're supposed to iterate through everyone once.
    foreach($event["rowset"] as $row) {
      $cache = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
      if(function_exists('phpbb_get_user_avatar')) {$avatar = $this->user_get_avatar_by_id($row['user_id']);}
          // TO DO: better tooltips.
          $online_userlist .= "<div class='online_user'><a href='https://starcitizen.fr/Forum/memberlist.php?mode=viewprofile&u=".$row['user_id']."'>".$avatar."</a><span><b></b>".$cache."</span></div>";
        }

        $event["online_userlist"] = $online_userlist;
      }

    }

    ?>
