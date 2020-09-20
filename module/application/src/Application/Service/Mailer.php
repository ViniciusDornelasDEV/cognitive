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

    public function emailAtivacao($link){
      return '<table style="background: #32363b; width: 100%; height: 100%" align="center" border="0" cellpadding="50" cellspacing="0" >
          <tbody>
          <tr>
            <td>
              <table align="center" border="0" cellpadding="0" cellspacing="0" style="width:538px; background-color:#464b51">
                <tbody>
                  <tr>
                      <td style="height: 65px; background-color: rgb(255, 255, 255); border-bottom: 1px solid rgb(77, 75, 72); text-align: center;"><img alt="" src="http://sistemacognitive.tk/images/logo.png" style="width: 110px; height: 81px; padding-top: 15px; padding-bottom: 15px;" /></td>
                  </tr>
                  <tr>
                      <td>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-left:15px; padding-right:15px; padding-bottom:10px" width="470">
                            <tbody>
                              <tr>
                                  <td style="padding-top:32px"><span style="font-family: Helvetica, Arial, sans-serif, serif, EmojiFont; font-size: 24px; color: rgb(255, 255, 255); font-weight: bold;">Seja bem vindo ao sistema cognitive</span></td>
                              </tr>
                              <tr>
                                  <td style="font-family:Helvetica,Arial,sans-serif; font-size:14px; color:#c6d4df; padding-top:16px">
                                      <h3 style="margin-bottom:0px; color:#c6d4df; font-size:15px">Para ativar sua conta favor acessar o link abaixo</h3>
                              </tr>
                              <tr>
                                  <td>
                                      <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-bottom:10px" width="470">
                                          <thead>
                                          </thead>
                                          <tbody>
                                              <tr>
                                                  <td colspan="3" style="padding-top:5px">&nbsp;</td>
                                              </tr>
                                              <tr style="background-color:#32363b">
                                                  <td colspan="3" style="font-family:Helvetica,Arial,sans-serif; font-size:12px; color:#61696d; width:250px; padding:10px">
                                                      <a href="'.$link.'" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: rgb(89, 168, 214);">'.$link.'</a>
                                                  </td>
                                              </tr>

                                              <tr>
                                              <tr>
                                                  <td colspan="3" style="padding-top: 10px;">&nbsp;</td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </td>
                              </tr>
                            </tbody>
                        </table>
                      </td>
                  </tr>
                    </tbody>
                </table>
            </td></tr>
        </tbody>';
    }

    public function emailInvoice($link){
      return '<table style="background: #32363b; width: 100%; height: 100%" align="center" border="0" cellpadding="50" cellspacing="0" >
          <tbody>
          <tr>
            <td>
              <table align="center" border="0" cellpadding="0" cellspacing="0" style="width:538px; background-color:#464b51">
                <tbody>
                  <tr>
                      <td style="height: 65px; background-color: rgb(255, 255, 255); border-bottom: 1px solid rgb(77, 75, 72); text-align: center;"><img alt="" src="http://sistemacognitive.tk/images/logo.png" style="width: 110px; height: 81px; padding-top: 15px; padding-bottom: 15px;" /></td>
                  </tr>
                  <tr>
                      <td>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-left:15px; padding-right:15px; padding-bottom:10px" width="470">
                            <tbody>
                              <tr>
                                  <td style="padding-top:32px"><span style="font-family: Helvetica, Arial, sans-serif, serif, EmojiFont; font-size: 24px; color: rgb(255, 255, 255); font-weight: bold;">Existe um novo invoice vinculado a sua empresa.</span></td>
                              </tr>
                              <tr>
                                  <td style="font-family:Helvetica,Arial,sans-serif; font-size:14px; color:#c6d4df; padding-top:16px">
                                      <h3 style="margin-bottom:0px; color:#c6d4df; font-size:15px">Clique no link para visualizar seu invoice.</h3>
                              </tr>
                              <tr>
                                  <td>
                                      <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-bottom:10px" width="470">
                                          <thead>
                                          </thead>
                                          <tbody>
                                              <tr>
                                                  <td colspan="3" style="padding-top:5px">&nbsp;</td>
                                              </tr>
                                              <tr style="background-color:#32363b">
                                                  <td colspan="3" style="font-family:Helvetica,Arial,sans-serif; font-size:12px; color:#61696d; width:250px; padding:10px">
                                                      <a href="#" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: rgb(89, 168, 214);">'.$link.'</a>
                                                  </td>
                                              </tr>

                                              <tr>
                                              <tr>
                                                  <td colspan="3" style="padding-top: 10px;">&nbsp;</td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </td>
                              </tr>
                            </tbody>
                        </table>
                      </td>
                  </tr>
                    </tbody>
                </table>
            </td></tr>
        </tbody>';
    }

    public function emailRecuperarSenha($link){
      return '<table style="background: #32363b; width: 100%; height: 100%" align="center" border="0" cellpadding="50" cellspacing="0" >
          <tbody>
          <tr>
            <td>
              <table align="center" border="0" cellpadding="0" cellspacing="0" style="width:538px; background-color:#464b51">
                <tbody>
                  <tr>
                      <td style="height: 65px; background-color: rgb(255, 255, 255); border-bottom: 1px solid rgb(77, 75, 72); text-align: center;"><img alt="" src="http://sistemacognitive.tk/images/logo.png" style="width: 110px; height: 81px; padding-top: 15px; padding-bottom: 15px;" /></td>
                  </tr>
                  <tr>
                      <td>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-left:15px; padding-right:15px; padding-bottom:10px" width="470">
                            <tbody>
                              <tr>
                                  <td style="padding-top:32px"><span style="font-family: Helvetica, Arial, sans-serif, serif, EmojiFont; font-size: 24px; color: rgb(255, 255, 255); font-weight: bold;">Você solicitou recuperação de senha.</span></td>
                              </tr>
                              <tr>
                                  <td style="font-family:Helvetica,Arial,sans-serif; font-size:14px; color:#c6d4df; padding-top:16px">
                                      <h3 style="margin-bottom:0px; color:#c6d4df; font-size:15px">Acesse o link para recuperar a senha, seu link tem validade de uma hora.</h3>
                              </tr>
                              <tr>
                                  <td>
                                      <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-bottom:10px" width="470">
                                          <thead>
                                          </thead>
                                          <tbody>
                                              <tr>
                                                  <td colspan="3" style="padding-top:5px">&nbsp;</td>
                                              </tr>
                                              <tr style="background-color:#32363b">
                                                  <td colspan="3" style="font-family:Helvetica,Arial,sans-serif; font-size:12px; color:#61696d; width:250px; padding:10px">
                                                      <a href="#" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: rgb(89, 168, 214);">'.$link.'</a>
                                                  </td>
                                              </tr>

                                              <tr>
                                              <tr>
                                                  <td colspan="3" style="padding-top: 10px;">&nbsp;</td>
                                              </tr>
                                          </tbody>
                                      </table>
                                  </td>
                              </tr>
                            </tbody>
                        </table>
                      </td>
                  </tr>
                    </tbody>
                </table>
            </td></tr>
        </tbody>';
    }
    
}