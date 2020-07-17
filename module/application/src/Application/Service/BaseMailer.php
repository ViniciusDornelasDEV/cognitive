<?php

namespace Application\Service;

use Zend\Mail\Message;
use Zend\Mail\Address;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class BaseMailer {

    protected $user = false;

    public function __construct($from) {
        $this->message = new Message;
        $this->message->addFrom(new Address($from['email'], $from['name']));
        $this->message->setEncoding("UTF-8");
        $this->templatePath = __DIR__ . '/../../../view/email';
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    public function setContactDetails(array $contactDetails = array()) {
        $this->contactDetails = $contactDetails;
    }

    public function getContactDetails() {
        return $this->contactDetails;
    }

    public function setUrlHelper($urlHelper) {
        $this->urlHelper = $urlHelper;
    }

    public function getUrlHelper() {
        return $this->urlHelper;
    }

    //sends one email at time
    public function setRecipient($recipient) {
        $this->message->setTo($recipient);
    }

    //sends multiple emails in one go
    public function setRecipients($recipients) {
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }
        foreach ($recipients as $recipient) {
            $this->message->addTo($recipient);
        }
    }

    public function setSubject($subject) {
        $this->message->setSubject($subject);
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getGreeting() {
        return (isset($this->user['name'])) ? 'Dear ' . $this->user['name'] . ',' : false;
    }

    public function getTemplateContent($templateName, array $vars = array()) {
        $resolver = new Resolver\AggregateResolver;

        if ($this->templatePath) {
            $stack = new Resolver\TemplatePathStack(array(
                'script_paths' => array(
                    $this->templatePath
                )
            ));
            $resolver->attach($stack);
        }

        $renderer = new PhpRenderer;
        $renderer->setResolver($resolver);

        $vars['contactDetails'] = $this->getContactDetails();
        $view = new ViewModel($vars);
        $view->setTemplate($templateName);

        return $renderer->render($view);
    }

    public function send($html = false) {
        $this->setupBody($html);
        // Setup SMTP transport using LOGIN authentication
        $transport = new SmtpTransport();
        //@todo update with their icarus account -- using tech studio 
        $options = new SmtpOptions(array(
            'host' => 'mail.sabordovale.net',
            'port' => 587,
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => 'contato@sabordovale.net',
                'password' => 'FNQ^U-om0RYp',
            ),
        ));
        $transport->setOptions($options);

        return $transport->send($this->message);
    }

    private function setupBody($html) {

        if ($html) {
           $content = $this->getTemplateContent('default', array(
                'content' => $this->content,
                'greeting' => $this->getGreeting()
            ));
            // // For some reason you have to include this text part otherwise some
            // // mail clients treat the HTML content as an attachment
            $text = new MimePart('');
            $text->type = 'text/plain';
            $html = new MimePart($content);
            $html->type = "text/html";
            $body = new MimeMessage();
            $body->setParts(array($html));
            $this->message->setBody($body);
        } else {
            $this->message->setBody(strip_tags($this->content));
        }
    }

}
