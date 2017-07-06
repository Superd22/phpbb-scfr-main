<?php namespace scfr\main\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
  /** @var \phpbb\template\template */
  protected $template;
  /** @var \phpbb\user */
  protected $user;
  /** @param \phpbb\db\driver\driver_interface */
  protected $db;
  private $topic_set_up;
  /** @param \SCFR\main\controller\Topic */
  private $topic;

  /**
  * Constructor
  *
  * @param \phpbb\template\template             $template          Template object
  * @param \phpbb\user   $user             User object
  * @param \phpbb\db\driver\driver_interface   $db             Database object
  * @access public
  */
  public function __construct( \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db) {
    $this->template = $template;
    $this->user = $user;
    $this->db = $db;
    $this->topic = new \scfr\main\controller\Topic($db, $template);
  }

  static public function getSubscribedEvents()
  {
    return array(
      'core.page_header_after' => 'set_common',
      'core.viewforum_modify_topicrow' => 'ambassade_topics',
      'core.viewtopic_modify_post_row' => 'post_row',
      'core.obtain_users_online_string_modify' => 'view_online',
      'core.viewtopic_assign_template_vars_before' => 'meta_viewtopic',
      'core.viewforum_get_topic_data' => 'meta_viewforum',
    );
  }

  public function view_online($event) {
    $view = new \scfr\main\controller\Viewonline($this->db, $this->template);
    $view->obtain_users_online_string($event);
  }

  public function post_row($event) {
    $this->topic->ambassade_single_topic($event);
    $this->topic->set_message_right_hand_side_guild_info($event);
    $this->topic->custom_parse($event);
  }


  public function ambassade_topics($event) {
    // IF IS AMBASSADE
    if($event["row"]["forum_id"] == 29) {
      $this->topic->ambassade_add_logo_topic($event);
    }
  }

  public function meta_viewforum($event) {
    $desc = htmlspecialchars(addslashes(substr(strip_tags($event['forum_data']['forum_desc']),0,180)));
    $metas = array(
      "SCFR_CUSTOM_META" => "
      <meta name='og:type' content='article'>
      <meta name='og:title' content='{$event['forum_data']['forum_name']}'>
      <meta name='og:description' content='{$desc}'>      
      <meta name='description' content='{$desc}'>
      "
    );
    
    $this->template->assign_vars($metas);
  }

  public function meta_viewtopic($event) {

    $d = $this->db->sql_query("SELECT post_text FROM testfo_posts WHERE post_id='{$event['topic_data']['topic_first_post_id']}' LIMIT 1");
    $g = $this->db->sql_fetchrow($d);

    $desc = htmlspecialchars(addslashes(substr(strip_tags($g['post_text']),0,180)));
    $metas = array(
      "SCFR_CUSTOM_META" => "
      <meta name='og:type' content='article'>
      <meta name='og:title' content='{$event['topic_data']['topic_title']}'>
      <meta name='og:article:section' content='{$event['topic_data']['forum_name']}'>
      <meta name='og:article:published_time' content='{$event['topic_data']['topic_time']}'>
      <meta name='og:article:author' content='{$event['topic_data']['topic_first_poster_name']}'>
      <meta name='og:description' content='{$desc}'>      
      <meta name='description' content='{$desc}'>
      "
    );

    $this->template->assign_vars($metas);

  }

  public function set_common($event) {
    $this->get_customs();
    $this->set_customs();
  }

  private function get_customs() {
    $this->user->get_profile_fields($this->user->data["user_id"]);
  }

  private function set_customs() {
    $this->template->assign_vars(array(
      'PROFILE_CUSTOM_BG_VALUE'    => isset($this->user->profile_fields["pf_custom_bg"]) ? $this->user->profile_fields["pf_custom_bg"] : '0',
      'USER_NAME'                  => $this->user->data["username"],
      'CURRENT_USER_ID'            => $this->user->data['user_id'],
    ));
  }

}
?>
