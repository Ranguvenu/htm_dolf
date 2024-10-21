<?php
ini_set('memory_limit', -1);
require_once('config.php');

$users = $DB->get_fieldset_sql('SELECT id FROM staging.mdl_user where username not in("Admin","atheer.trainee3","sara.trainee1","reem.trainee1","nora.trainee","lama.trainee","atheer.trainer","atheer.org","atheer.org2","mona.org","atheer.to","atheer.exam","atheer.expert","atheer.event","atheer.com1","atheer.hall","atheer.cpd","atheer.fin","atheer.comp","2209085816","2209085824","s.org","s.exam","s.trainee","s.trainer","2495503084","1138984966","1138984966","1003327697","2495503074","2495503085","1975493097","1802900512","hadari","trainee","1082634237","ahmed2023","2314052800","1024005207","1002000000","sultan.aldossari","tuhamie.trainee","ibrahim..alharthi","fa.admin","ahmed.alrahmah","communication.official","fin_manager","hall_manager","examofficial","training.official","event_manager","expert","competencies_official","cpd","communication2.official2")');

foreach($users as $userid){
    echo (new \local_userapproval\action\manageuser)->delete_user($userid);
    echo "User deleted".$userid;
}