<?php namespace scfr\main\controller;

/**
* Controller class for user-side group partners
*/
class GroupPartner {
    /**
    * Triggered on viewforum after initial fetch and before retrieving topics
    *
    * @param [type] $event
    * @return void
    */
    public function viewforum_get_topic_data($event) {
        global $template;

        $misc = [];
        $partner = $this->get_partner_forum_info($event['forum_id']);

        if($partner) {
            if($partner['bg']) $misc['CUSTOM_BACKGROUND'] = $partner['bg'];

            if($partner['ban']) {
                $misc['BAN_HEIGHT'] = 350;
                $misc['CUSTOM_BANNER'] = $partner['ban'];
            }
        }

        $template->assign_vars(array_merge([
            'SCFR_PARTNER_FORUM' => $partner,
        ], $misc));
    }
    
    /**
    * Get info for a partner forum
    *
    * @param integer $forum_id
    * @param boolean $deep whether to deep search forum parents or not.
    * @return void
    */
    public function get_partner_forum_info($forum_id, $deep = true) {
        global $db;

        $forum_id = (integer) $forum_id;
        
        if(!$forum_id) return false;

        // Try and get results for this foorum
        $sql = "SELECT * FROM scfr_partners_group as s LEFT JOIN testfo_groups as g ON s.group_id = g.group_id WHERE s.forum_id = {$forum_id} LIMIT 1";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        // If we have no results and can deep search, fetch parent.
        if(!$row && $deep) {
            $sql = "SELECT parent_id FROM testfo_forums WHERE forum_id = {$forum_id} LIMIT 1";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            return $this->get_partner_forum_info($row['parent_id'], $deep);
        }

        return $row;
    }
}