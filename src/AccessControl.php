<?php namespace Maer\Oauth2Simple\Client;

class AccessControl
{
    /**
     * Validate an e-mail address against allow- and deny-lists
     * 
     * @param  array   $allow   List of e-mails and/or domains that are allowed
     * @param  array   $deny    List of e-mails and/or domains that should be blocked
     * @param  string  $email
     * @return boolean
     */
    public function isEmailAllowed(array $allow, array $deny, $email)
    {   
        if (!$allow && !$deny) {
            return true;
        }

        $email = strtolower($email);

        list($name, $domain) = explode("@", $email);
        $domain = "@{$domain}";

        $allowed       = true; // If allow is emtpy, treat it as "allow all"
        
        if (is_array($allow) && $allow) {
            // We have a white list. Now we need to check if this address is in it.
            $allow = array_map('strtolower', $allow); // Set all as lowercase
            $allowed   = in_array($email, $allow) || in_array($domain, $allow);
        }

        if ($allowed && is_array($allow) && $deny) {
            // If the white list was passed, test against the black list
            $deny = array_map('strtolower', $deny); // Set all as lowercase
            if (in_array($domain, $deny) || in_array($email, $deny)) {
                $allowed = false;
            }
        }

        return $allowed;
    }
}