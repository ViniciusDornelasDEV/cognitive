<?php 
  /**
   * API DO POWER BI
   */
  namespace Dashboard\Classes;


  class PowerBI
  {
    private $clientId; //ID do aplicativo (cliente) azure AD
    private $userName; //login conta azure AD
    private $password; //senha conta azure AD
    private $token = '';
    private $error = '';
    function __construct($cliente)
    {
      $this->clientId = $cliente['id_azure'];
      $this->userName = $cliente['usuario_azure'];
      $this->password = $cliente['senha_azure'];

      //RETIRAR EM PRODUÇÃO
      $curlPostToken = curl_init();
      curl_setopt($curlPostToken, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($curlPostToken, CURLOPT_SSL_VERIFYPEER, 0);

      curl_setopt_array($curlPostToken, array(
        CURLOPT_URL => "https://login.windows.net/common/oauth2/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array(
        'grant_type' => 'password',
        'scope' => 'openid',
        'resource' => 'https://analysis.windows.net/powerbi/api',
        'client_id' => $this->clientId, // Registered App ApplicationID
        'username' => $this->userName, // for example john.doe@yourdomain.com
        'password' => $this->password // Azure password for above user
        )
      ));
      $token = curl_exec($curlPostToken);
      curl_close($curlPostToken);
      $token = json_decode($token, true);
      
      if(isset($token['error'])){
        $this->error = $token['error'];
      }
      $this->token = $token['access_token'];
    }


    public function getUrl($workspaceId, $reportId){
      $curlGetUrl = curl_init();

      //RETIRAR EM PRODUÇÃO
      curl_setopt($curlGetUrl, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($curlGetUrl, CURLOPT_SSL_VERIFYPEER, 0);
      
      
      curl_setopt_array($curlGetUrl, array(
        CURLOPT_URL => 'https://api.powerbi.com/v1.0/myorg/groups/'.$workspaceId.'/reports/'.$reportId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        'Authorization: '."Bearer ".$this->token,
        "Cache-Control: no-cache",
      )));

      $embedResponse = curl_exec($curlGetUrl);
      $embedError = curl_error($curlGetUrl);
      curl_close($curlGetUrl);
      if($embedError) {
        $this->error = "cURL Error #:" . $embedError;
        return false;
      }else{
        $embed = json_decode($embedResponse, true);
        $embed['token'] = $this->token;
        return $embed;  
      }
    }

  }
?>