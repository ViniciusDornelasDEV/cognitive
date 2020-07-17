<?php

namespace Application\Service;

class Mailer extends BaseMailer {
    
    /*
     * Sends a generic email to anyone
     */
    public function sendMail($recipient, $subject, $content) {
        
        $this->setRecipient($recipient);
        $this->setSubject($subject);
        $this->setContent($content);
        
        return $this->send(true);
    }
    
    /*
     * Send a generic email to site user
     */    
    public function mailUser($email, $subject, $content) {
        //$this->setRecipient('vinicius.s.dornelas@gmail.com');
        if($email){
            $this->setRecipient($email);
            $this->setSubject($subject);
            $this->setContent($content);

            return $this->send(true);
        }
        
    }
    
    public function sendResetPasswordLink(\ArrayObject $user) {
        
        
        $res = false;
        
        if (isset($user) && !isset($user->facebook_id)) {
                   
            //all is good, we have a user and they are not a facebook user
            $message = '<p>Dear ' . $user->name . '</p>';
            $message .= "<p>We've received your request to reset your password. Pelase use the link below to proceed:</p>";
            $message .= '<p><a href="'. SITE_URL . '/password/reset/'. $user->reset_token.'">';
            $message .= ''. SITE_URL . '/password/reset/'.$user->reset_token.'</a>';
            $message .= '<p>If you have not requested this please let us know.</p>';
                        
            $this->mailUser($user, 'Reset Password', $message);
            $res = true;
        } else if (isset($user) && strlen($user->facebook_id)) {
            throw new \Exception('You are registered using a Facebook account, it will not be possible to change your password here');
        } else {
            throw new \Exception('We were not able to locate your account.');
        }

        return $res;

        
    }
    
}