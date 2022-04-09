<?php
Require 'authorization_helper.php';

function getAuthUser($token)
{
    return authorization::validateToken($token);
}

function auth_user($rest_obj)
{
    $token = $rest_obj->_getHeaderAuthorization();
    try {
        $student = getAuthUser($token);

        if ($student) {
            $student = $student->data;
            return $student;
        }
        else{
            return false;
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}