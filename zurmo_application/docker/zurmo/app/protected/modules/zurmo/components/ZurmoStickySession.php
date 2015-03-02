<?php

/**
 * Represents the default zurmo session handler which is sticky
 */
class ZurmoStickySession extends CHttpSession
{
    public static function createSessionToken($sessionId, $userPass)
    {
        $token = md5($sessionId . $userPass);
        return $token;
    }
}
