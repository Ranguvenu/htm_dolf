<?php
namespace local_userapproval;

class userinfo {

    public static function get_query($rolename, $userid=false) {
        global $USER;

        $sql = "SELECT ra.userid, CONCAT(u.firstname,'',u.lastname) as username 
                  FROM {role_assignments} as  ra 
                  JOIN {role} as r ON ra.roleid = r.id  AND r.shortname = '$rolename'
                  JOIN {user} as u ON u.id= ra.userid
                  AND u.confirmed=1 AND u.deleted=0 AND u.suspended=0";
        return $sql;
    }

    public static function get_experts(){}

    public static function get_trainers(){}

    public static function get_organizationofficials(){}

    public static function get_examofficials(){
        global $DB;
        $sql = self::get_query('examofficial');

        return $DB->get_records_sql_menu($sql);
    }

}