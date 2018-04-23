<?

namespace Application\Model;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Mime;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\Validator\EmailAddress;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver;


/**
 * Class Mail
 *
 * @package Application\Model
 */
class Mail{
   /**
    * @var
    */
   private $_sm;

   /**
    * @var
    */
   private $_config;

   /**
    * Mail constructor.
    *
    * @param $sm
    */
   public function __construct(ServiceManager $sm){
      $this->_sm = $sm;
      $this->_config = $sm->get('config');
   }

   /**
    * отправка письма из шаблона
    *
    * @param $to
    * @param $subject
    * @param $template_name
    * @param $params
    *
    * @throws \Exception
    */
   public function sendView($to, $subject, $template_name, $params){
      $config = $this->_config;
      $to = $this->verTo($to);
      $body = self::getBodyFromTemplate($template_name, $params);

      // Собственно сообщение
      $message = new Message();
      $message
         ->setEncoding("UTF-8")
         ->addFrom($config['mail']['admin_email'], "Мир талантов")
         ->addTo($to)
         ->setBody($body)
         ->setSubject($subject);

      // Отправка
      $options = $config['mail']['smtp_options'];
      $smtpOptions = new SmtpOptions($options);

      $transport = new Smtp($smtpOptions);

      try{
         $transport->send($message);
      }
      catch(\Exception $e){
//         throw new \Exception($e->getMessage());
         throw new \Exception(
            'Сообщение не отправлено. Попробуйте еще раз '.
            'через некоторое время.'
         );
      }
   }

   /**
    * отправка письма с дипломом
    *
    * @param $order
    *
    * @throws \Exception
    */
   public function sendDiploma($order){
      $config = $this->_config;
      $to = $this->verTo($order['email']);
      $body = self::getBodyFromTemplate('diploma', $order);

      // прикрепляем диплом
      $image = new MimePart(file_get_contents(
         DOMAIN.'/diploma/'.$order['code']));
      $image->type = 'image/jpeg';
      $image->filename = 'diploma.jpg';
      $image->disposition = Mime::DISPOSITION_INLINE;
      $image->encoding = Mime::ENCODING_BASE64;
      $body->addPart($image);

      $subject = 'Всероссийский центр '.SITE_NAME;

      // Собственно сообщение
      $message = new Message();
      $message
         ->setEncoding("UTF-8")
         ->addFrom($config['mail']['admin_email'], trim(SITE_NAME, '"'))
         ->addTo($to)
         ->setBody($body)
         ->setSubject($subject);

      // Отправка
      $transport = new Smtp();
      $options = new SmtpOptions($config['mail']['smtp_options']);
      $transport->setOptions($options);

      try{
         $transport->send($message);
      }
      catch(\Exception $e){
         throw new \Exception('Сообщение не отправлено. Попробуйте еще раз '.
            'через некоторое время.'
         );
      }
   }

   /**
    * отправка письма с сертификатом
    *
    * @param $article
    * @param $code
    *
    * @throws \Exception
    */
   public function sendCertificate($article, $code){
      $config = $this->_config;
      $to = $this->verTo($article['email']);
      $article['code'] = $code;
      $body = self::getBodyFromTemplate('send_certificate', $article);

      // прикрепляем диплом
      $image = new MimePart(file_get_contents(
         DOMAIN.'/ajax/certificate/'.$code));
      $image->type = 'image/jpeg';
      $image->filename = 'certificate.jpg';
      $image->disposition = Mime::DISPOSITION_INLINE;
      $image->encoding = Mime::ENCODING_BASE64;
      $body->addPart($image);

      $subject = 'Сертификат о публикации статьи на сайте Мир Талантов';

      // Собственно сообщение
      $message = new Message();
      $message
         ->setEncoding("UTF-8")
         ->addFrom($config['mail']['admin_email'], trim(SITE_NAME, '"'))
         ->addTo($to)
         ->setBody($body)
         ->setSubject($subject);

      // Отправка
      $transport = new Smtp();
      $options = new SmtpOptions($config['mail']['smtp_options']);
      $transport->setOptions($options);

      try{
         $transport->send($message);
      }
      catch(\Exception $e){
         throw new \Exception('Сообщение не отправлено. Попробуйте еще раз '.
            'через некоторое время.'
         );
      }
   }

   public function sendThank($thank){
      $config = $this->_config;
      $to = $this->verTo($thank['email']);
      $body = self::getBodyFromTemplate('send_thank', $thank);

      // прикрепляем диплом
      $image = new MimePart(file_get_contents(
         DOMAIN.'/thank/'.$thank['code']));
      $image->type = 'image/jpeg';
      $image->filename = 'thank.jpg';
      $image->disposition = Mime::DISPOSITION_INLINE;
      $image->encoding = Mime::ENCODING_BASE64;
      $body->addPart($image);

      $subject = 'Благодарность с сайта Мир Талантов';

      // Собственно сообщение
      $message = new Message();
      $message
         ->setEncoding("UTF-8")
         ->addFrom($config['mail']['admin_email'], trim(SITE_NAME, '"'))
         ->addTo($to)
         ->setBody($body)
         ->setSubject($subject);

      // Отправка
      $transport = new Smtp();
      $options = new SmtpOptions($config['mail']['smtp_options']);
      $transport->setOptions($options);

      try{
         $transport->send($message);
      }
      catch(\Exception $e){
         throw new \Exception('Сообщение не отправлено. Попробуйте еще раз '.
            'через некоторое время.'
         );
      }
   }


   /**
    * @param $email
    *
    * @return mixed
    * @throws \Exception
    */
   private function verTo($email){
      if(empty($email)){
         $config = $this->_config;
         $email = $config['mail']['admin_email'];
      }

      $v = new EmailAddress();
      if($v->isValid($email)){
         return $email;
      }
      throw new \Exception('Неверный адрес отправки письма');
   }

   /**
    * @param $template_name
    * @param $params
    *
    * @return MimeMessage
    * @throws \Exception
    */
   private function getBodyFromTemplate($template_name, $params){
      $config = $this->_config;
      $params['admin_email'] = $config['mail']['admin_email'];
      $view = new ViewModel(['data' => $params]);

      // Подготовка к рендерингу шаблонов письма
      $renderer = new PhpRenderer();
      $resolver = new Resolver\AggregateResolver();
      $renderer->setResolver($resolver);

      $templateMapResolver = new Resolver\TemplateMapResolver(
         $config['view_manager']['template_path_stack']
      );
      $templatePathStack = new Resolver\TemplatePathStack();
      $resolver->attach($templateMapResolver)
         ->attach($templatePathStack);

      // вариант письма html
      $view->setTemplate($template_name);
      $htmlContent = $renderer->render($view);
      $viewHtml = new ViewModel([
         'content' => $htmlContent,
         'skype' => $config['skype'],
         'email' => $config['email'],
      ]);
      $viewHtml->setTemplate('template');
      $htmlContent = $renderer->render($viewHtml);
      $messageHtmlContent = new MimePart($htmlContent);
      $messageHtmlContent->type = Mime::TYPE_HTML;
      $messageHtmlContent->setCharset('UTF-8');

      $messageBody = new MimeMessage();
      $messageBody->setParts([$messageHtmlContent]);

      return $messageBody;
   }

   public function sendFromPrivateSite($params, $user_id){
      $sm = $this->_sm;
      $config = $this->_config;
      $params['admin_email'] = $config['mail']['admin_email'];
      $view = new ViewModel(['data' => $params]);

      // Подготовка к рендерингу шаблонов письма
      $renderer = new PhpRenderer();
      $resolver = new Resolver\AggregateResolver();
      $renderer->setResolver($resolver);

      $templateMapResolver = new Resolver\TemplateMapResolver(
         $config['view_manager']['template_path_stack']
      );
      $templatePathStack = new Resolver\TemplatePathStack();
      $resolver->attach($templateMapResolver)
         ->attach($templatePathStack);

      // вариант письма html
      $view->setTemplate('site-contacts');
      $htmlContent = $renderer->render($view);
      $viewHtml = new ViewModel([
         'content' => $htmlContent,
      ]);
      $viewHtml->setTemplate('site-template');
      $htmlContent = $renderer->render($viewHtml);
      $messageHtmlContent = new MimePart($htmlContent);
      $messageHtmlContent->type = Mime::TYPE_HTML;
      $messageHtmlContent->setCharset('UTF-8');

      $messageBody = new MimeMessage();
      $messageBody->setParts([$messageHtmlContent]);

      $model_auth = new Auth($sm);
      $user = $model_auth->getUser($user_id);
      $to = $this->verTo($user['email']);
      $model_settings = new Settings($sm);
      $settings = $model_settings->getSettingsByUserId($user_id);

      // Собственно сообщение
      $message = new Message();
      $subject = 'Сообщение с вашего сайта http://mir-talantow.ru/'.
         $settings['site_code'];
      $message
         ->setEncoding("UTF-8")
         ->addFrom($config['mail']['admin_email'])
         ->addTo($to)
         ->setBody($messageBody)
         ->setSubject($subject);

      // Отправка
      $options = $config['mail']['smtp_options'];
      $smtpOptions = new SmtpOptions($options);

      $transport = new Smtp($smtpOptions);

      try{
         $transport->send($message);
      }
      catch(\Exception $e){
         //         throw new \Exception($e->getMessage());
         throw new \Exception(
            'Сообщение не отправлено. Попробуйте еще раз '.
            'через некоторое время.'
         );
      }

   }
   


}