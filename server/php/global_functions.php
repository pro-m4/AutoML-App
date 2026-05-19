<?php
function getdomain(){
    
    return "https://kclusterhub.iee.ihu.gr/automl"; 
    
}

function verif_key_exists($verif_key){
    global $mysqli;
    $query = 'SELECT count(*) as c FROM verify_account WHERE verif_key=?';
    $st = $mysqli->prepare($query);
    $st->bind_param('s', $verif_key);
    $st->execute();
    $res = $st->get_result();
    return $res->fetch_assoc()['c'] > 0;
}

function verif_key_expired($verif_key){
    global $mysqli;
    
    $query = 'SELECT count(*) as c FROM verify_account WHERE verif_key=? AND creation_time < (NOW() - INTERVAL 15 MINUTE)';
    $st = $mysqli->prepare($query);
    $st->bind_param('s', $verif_key);
    $st->execute();
    if($st->get_result()->fetch_assoc()['c'] > 0) {
        return true;
    }
    return false;
}
?>