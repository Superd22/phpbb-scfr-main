<?php namespace scfr\main\controller\adm;


class GroupPartner {
    
    /**
    * Triggered on group update/insert
    *
    * @param [type] $event
    * @return void
    */
    public function acp_manage_group_initialise_data($event) {
        
        
        if($event['action'] === "edit") {
            $is_partner_group = request_var("scfr_group_partner", false);
            if($is_partner_group) $this->set_group_is_partner($event['group_id']);
            else $this->set_group_non_parter($event['group_id']);
            }
        
    }
    
    /**
    * Triggered on forum update/insert
    *
    * @param [type] $event
    * @return void
    */
    public function acp_manage_forums_update_data_after($event) {
        $this->set_forum_partner_status($event['forum_data']['forum_id'], request_var("scfr_forum_partner", -1));
    }
    
    /**
    * Triggerd on forum view
    *
    * @param [type] $event
    * @return void
    */
    public function acp_manage_forums_initialise_data($event) {
        global $template;
        
        foreach($this->get_partners() as $partner) {
            $template->assign_block_vars('scfr_partners', array_merge($partner, ['selected' => ($event['forum_id'] == $partner['forum_id'])]));
        }
        
    }
    
    
    /**
    * Triggered on group fetch
    *
    * @param [type] $event
    * @return void
    */
    public function acp_manage_group_display_form($event) {
        global $template;
        
        $group_id = (integer) $event['group_id'];
        
        $template->assign_vars([
        'SCFR_GROUP_PARTNER' => $this->group_is_partner($event['group_id'])
        ]);
    }
    
    /**
    * Checks if a group is a partner group
    *
    * @param integer $group_id
    * @return boolean
    */
    public function group_is_partner($group_id) {
        global $db;
        
        $group_id = (integer) $group_id;
        
        $sql = "SELECT id FROM scfr_partners_group WHERE group_id = {$group_id} LIMIT 1";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        
        if($row['id']) return true;
        return false;
    }
    
    /**
    * Flags a group as a parnter group
    *
    * @param integer $group_id the phpbb id of the group we want to flag
    * @return void
    */
    public function set_group_is_partner($group_id) {
        global $db;
        
        $group_id = (integer) $group_id;
        $db-> sql_return_on_error(true);
        // Will insert on new group, and do nothing if group_id is already there.
        $sql = "INSERT INTO scfr_partners_group (group_id) VALUES ({$group_id})";
        $db->sql_query($sql);
    }
    
    /**
    * Flags a group as non-partner group.
    *
    * @param integer $group_id the phpbb id of the group we want to flag
    * @return void
    */
    public function set_group_non_parter($group_id) {
        global $db;
        $group_id = (integer) $group_id;
        
        $db->sql_return_on_error(true);
        $sql = "DELETE FROM scfr_partners_group WHERE group_id = {$group_id} LIMIT 1";
        $db->sql_query($sql);
    }
    
    /**
    * Flags a forum as being a partner forum or not.
    * any group_partner_value > 0 will set this forum as a partner forum,
    * neg will set it as a vanilla forum
    *
    * @param integer $forum_id the target forum
    * @param integer $group_partner_id the group id to link to this forum, <= 0 for normal forum
    * @return void
    */
    public function set_forum_partner_status($forum_id, $group_partner_id = -1) {
        global $db;
        $forum_id = (integer) $forum_id;
        $group_partner_id = (integer) $group_partner_id;
        
        // Set forum_id as null if we have no partner
        $forum_id_value = "NULL";
        if($group_partner_id > 0) $forum_id_value = $forum_id;
        
        // And update
        $sql = "UPDATE scfr_partners_group set forum_id = {$forum_id_value} WHERE group_id = {$group_partner_id} LIMIT 1";

        $db->sql_return_on_error(true);
        $db->sql_query($sql);
    }
    
    
    /**
    * Fetches all the current partners
    *
    * @return scfr_partner[]
    */
    public function get_partners() {
        global $db;
        
        $partners = [];
        
        $sql = "SELECT * FROM scfr_partners_group as s LEFT JOIN testfo_groups as g ON s.group_id = g.group_id";
        $result = $db->sql_query($sql);
        while($row = $db->sql_fetchrow($result)) {
            $partners[] = $row;
        }
        
        return $partners;
    }
}