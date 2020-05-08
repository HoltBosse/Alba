<?php


class s
{
    /**
     * The number of seconds of inactivity before a session expires.
     */
    protected static $SESSION_AGE = 1800;
    

    public static function write($key, $value){
        self::_init();
        $_SESSION[$key] = $value;
        self::_age();
        return $value;
    }
    

    public static function set($key, $value){
        return self::write($key, $value);
    }
    

    public static function read($key, $child = false){
		self::_init();
		self::_age(); // moved age to prevent key issue after session 'timeout'
        if (isset($_SESSION[$key]))
        {
            //self::_age();
            if (false == $child)
            {
				
                return $_SESSION[$key];
            }
            else
            {
                if (isset($_SESSION[$key][$child]))
                {
                    return $_SESSION[$key][$child];
                }
            }
        }
        return false;
    }
    
    public static function get($key, $child = false)
    {
        return self::read($key, $child);
    }
    

    public static function delete($key)
    {
        self::_init();
        unset($_SESSION[$key]);
        self::_age();
    }
    

    public static function d($key)
    {
        self::delete($key);
    }
    

    public static function start()
    {
        // this function is extraneous
        return self::_init();
    }
    

    private static function _age()
    {
        $last = isset($_SESSION['LAST_ACTIVE']) ? $_SESSION['LAST_ACTIVE'] : false ;
        
        if (false !== $last && (time() - $last > self::$SESSION_AGE))
        {
            self::destroy();
        }
        $_SESSION['LAST_ACTIVE'] = time();
    }

    public static function params()
    {
        $r = array();
        if ( '' !== session_id() )
        {
            $r = session_get_cookie_params();
        }
        return $r;
    }
    

    public static function close()
    {
        if ( '' !== session_id() )
        {
            return session_write_close();
        }
        return true;
    }
    

    public static function commit()
    {
        return self::close();
    }
    

    public static function destroy()
    {
        if ( '' !== session_id() )
        {
            $_SESSION = array();
            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
    }
    

    private static function _init()
    {
        if ( '' === session_id() )
        {
            $params = session_get_cookie_params();
            session_set_cookie_params($params['lifetime'],
                $params['path'], $params['domain']);
            return session_start();
        }
        return session_regenerate_id(true);
    }
}