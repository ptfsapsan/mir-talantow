<?php

namespace Application\Controller;

use Application\Classes\Redis;
use Application\Classes\Vkontakte;
use Application\Form\Article;
use Application\Form\ArticleComment;
use Application\Form\Contacts;
use Application\Form\Filter\ArticleCommentFilter;
use Application\Form\Filter\ArticleFilter;
use Application\Form\Filter\ContactsFilter;
use Application\Form\Filter\ForgotFilter;
use Application\Form\Filter\LoginFilter;
use Application\Form\Filter\OrderFilter;
use Application\Form\Filter\RegistrationFilter;
use Application\Form\Forgot;
use Application\Form\Login;
use Application\Form\Order;
use Application\Form\Registration;
use Application\Model\Articles;
use Application\Model\Auth;
use Application\Model\Blanks;
use Application\Model\Certificates;
use Application\Model\Comments;
use Application\Model\Recaptcha;
use Application\Model\Themes;
use Application\Model\Mail;
use Application\Model\Nominations;
use Application\Model\Orders;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;

/**
 * Class IndexController
 *
 * @package Application\Controller
 */
class IndexController extends BasicController
{

    public function indexAction()
    {
        $sm = $this->_sm;
        //$cache = $sm->get('cache');
        $view = new ViewModel();
        $key = 'indexPage';
        //if($redis->hasItem($key)) return $redis->getItem($key);
        //$redis->setItem($key, $view);
        return $view;
    }

    public function kidAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $post = $this->_post;
        $session = new Container('indexKid');
        $nomination = $this->params()->fromRoute('nomination', '');
        $theme = $this->params()->fromRoute('theme', '');
        $model_nominations = new Nominations($sm);
        $model_themes = new Themes($sm);
        $nominations = $model_nominations->getAll();
        $themes = $model_themes->getAll();
        $nomination = $model_nominations->getByTrans($nomination);
        $theme = $model_themes->getByTrans($theme);
        if (empty($nomination) || empty($theme))
            return $this->redirect()->toRoute('kid', [
                'nomination' => current($nominations)['trans'],
                'theme' => current($themes)['trans']]);
        if (empty($nomination) || empty($theme) ||
            !in_array($nomination['id'], array_column($nominations, 'id')))
            return $this->error404();
        $form_order = new Order($sm);
        if (!empty($session->form_data))
            $form_order->populateValues($session->form_data);
        $form_order->prepareForIndex('kid', $nomination, $theme);

        $model_blanks = new Blanks($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_order':
                    $model_orders = new Orders($sm);
                    $form_order->setData($post);
                    $session->form_data = $post;
                    $order_filter = new OrderFilter($sm);
                    $form_order->setInputFilter($order_filter);
                    if ($form_order->isValid()) {
                        $model_recaptcha = new Recaptcha();
                        if (!empty($post['g-recaptcha-response']) &&
                            $model_recaptcha->verify($post['g-recaptcha-response'])) {
                            $data = array_merge($form_order->getData(),
                                ['link' => $post['link']]);
                            try {
                                $order_id =
                                    $model_orders->addOrder($data);
                                $fm->addSuccessMessage('Ваша заявка принята. ' .
                                    'На ваш адрес электронной почты отправлено письмо.'
                                );
                                if ($post['kind'] == 'monthly')
                                    return $this->redirect()->toRoute('home');
                                else return $this->redirect()->toRoute('pay',
                                    ['order_id' => $order_id, 'email' => $post['email']]
                                );
                            } catch (\Exception $e) {
                                $fm->addErrorMessage($e->getMessage());
                            }
                        } else $fm->addErrorMessage('Капча не прошла проверку');
                    } else {
                        $fm->addErrorMessage($form_order->getMessages());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }
        return [
            'nominations' => $nominations,
            'nomination' => $nomination,
            'themes' => $themes,
            'theme' => $theme,
            'form_order' => $form_order,
            'blanks' => $model_blanks->getAll(true),

        ];
    }

    /**
     * @return \Application\Controller\ViewModel|array|\Zend\Http\Response|ViewModel
     */
    public function educatorAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $post = $this->_post;
        $session = new Container('indexEducator');
        $nomination = $this->params()->fromRoute('nomination');
        $model_nominations = new Nominations($sm);
        $nominations = $model_nominations->getAll('educator');

        if (empty($nomination))
            return $this->redirect()->toRoute('educator', [
                'nomination' => current($nominations)['trans']]);
        $nomination = $model_nominations->getByTrans($nomination);
        if (empty($nomination) ||
            !in_array($nomination['id'], array_column($nominations, 'id')))
            return $this->error404();
        $form_order = new Order($sm);
        if (empty($session->form_data)) $session->form_data = [];
        $form_order->prepareForIndex('educator', $nomination, null,
            $session->form_data);

        $model_blanks = new Blanks($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_order':
                    $model_orders = new Orders($sm);
                    $form_order->setData($post);
                    $session->form_data = $post;
                    $order_filter = new OrderFilter($sm);
                    $form_order->setInputFilter($order_filter);
                    if ($form_order->isValid()) {
                        $model_recaptcha = new Recaptcha();
                        if (!empty($post['g-recaptcha-response']) &&
                            $model_recaptcha->verify($post['g-recaptcha-response'])) {
                            $data = array_merge($form_order->getData(),
                                ['link' => $post['link']]);
                            try {
                                $order_id =
                                    $model_orders->addOrder($data);
                                $fm->addSuccessMessage('Ваша заявка принята. ' .
                                    'На ваш адрес электронной почты отправлено письмо.'
                                );
                                if ($post['kind'] == 'monthly')
                                    return $this->redirect()->toRoute('home');
                                else return $this->redirect()->toRoute('pay',
                                    ['order_id' => $order_id, 'email' => $post['email']]
                                );
                            } catch (\Exception $e) {
                                $fm->addErrorMessage($e->getMessage());
                            }
                        } else $fm->addErrorMessage('Капча не прошла проверку');
                    } else {
                        $fm->addErrorMessage($form_order->getMessages());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'nominations' => $nominations,
            'nomination' => $nomination,
            'form_order' => $form_order,
            'blanks' => $model_blanks->getAll(true),

        ];
    }

    /**
     * @return array|\Zend\Http\Response
     */
    public function loginAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $form_login = new Login();
        $form_forgot = new Forgot();
        $form_registration = new Registration();
        $post = $this->_post;
        $get = $this->_get;
        $session = new Container('indexLogin');
        $model_users = new Auth($sm);

        if (!empty($post['act'])) {
            $model_recaptcha = new Recaptcha();
            switch ($post['act']) {
                case 'login':
                    $form_login->setData($post);
                    $form_login->setInputFilter(new LoginFilter($sm));
                    if ($form_login->isValid()) {
                        try {
                            $model_users->login($form_login->getData());
                        } catch (\Exception $e) {
                            $fm->addErrorMessage($e->getMessage());
                        }

                        return $this->redirect()->toRoute('admin');
                    } else {
                        $fm->addErrorMessage(
                            current(current($form_login->getMessages()))
                        );
                    }
                    break;
                case 'forgot':
                    $form_forgot->setData($post);
                    $form_forgot->setInputFilter(new ForgotFilter($sm));
                    if ($form_forgot->isValid()) {
                        if (!empty($post['g-recaptcha-response']) &&
                            $model_recaptcha->verify($post['g-recaptcha-response'])) {
                            $data = $form_forgot->getData();
                            $model_users->forgot($data['email']);
                            $fm->addSuccessMessage('На ваш email выслано письмо с 
                           паролем');
                        } else $fm->addErrorMessage('Капча не прошла проверку');
                    } else {
                        $fm->addErrorMessage(
                            current(current($form_forgot->getMessages()))
                        );
                    }
                    break;
                case 'registration':
                    $form_registration->setData($post);
                    $form_registration->setInputFilter(new RegistrationFilter($sm));
                    if ($form_registration->isValid()) {
                        if (!empty($post['g-recaptcha-response']) &&
                            $model_recaptcha->verify($post['g-recaptcha-response'])) {
                            $model_users->registration($form_registration->getData());
                            $fm->addSuccessMessage('На электронную почту выслано
                        письмо с ссылкой для активации аккаунта');
                        } else $fm->addErrorMessage('Капча не прошла проверку');
                    } else {
                        $fm->addErrorMessage(
                            current(current($form_registration->getMessages()))
                        );
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_type':
                    if (in_array($get['type'], ['login', 'forgot', 'registration']))
                        $session->type = $get['type'];
                    break;
            }
            $this->redirect()->refresh();
        }

        if (empty($session->type)) $session->type = 'login';

        return [
            'form_login' => $form_login,
            'form_forgot' => $form_forgot,
            'form_registration' => $form_registration,
            'session' => $session,
        ];
    }


    public function payAction()
    {
        $sm = $this->_sm;
        $order_id = $this->params()->fromRoute('order_id', 0);
        $email = $this->params()->fromRoute('email', '');
        $model_orders = new Orders($sm);
        $order = $model_orders->getById($order_id);
        if (!empty($order) && $email != $order['email']) {
            return $this->redirect()->toRoute('pay');
        }

        return $this->_view_model->setVariables([
            'order' => $order,
        ]);
    }

    /**
     *
     */
    public function rulesAction()
    {
        $model = new Orders($this->_sm);

        return $this->_view_model->setVariables([
            'config' => $model->getConfig(),
        ]);
    }

    /**
     *
     */
    public function contactsAction()
    {
        $form_contacts = new Contacts();
        $post = $this->_post;
        $fm = $this->_fm;
        $sm = $this->_sm;

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'contacts':
                    $form_contacts->setData($post);
                    $form_contacts->setInputFilter(new ContactsFilter());
                    if ($form_contacts->isValid()) {
                        $model_recaptcha = new Recaptcha();
                        if (!empty($post['g-recaptcha-response']) &&
                            $model_recaptcha->verify($post['g-recaptcha-response'])) {
                            try {
                                $model_email = new Mail($sm);
                                $site_name = $this->layout()->config['site_name'];
                                $subject = 'Сообщение с сайта ' . $site_name;
                                $model_email->sendView(null, $subject,
                                    'from_footer', $form_contacts->getData()
                                );
                                $fm->addSuccessMessage('Сообщение отправлено');
                                return $this->redirect()->toRoute('home');
                            } catch (\Exception $e) {
                                $fm->addErrorMessage($e->getMessage());
                            }
                        } else $fm->addErrorMessage('Капча не прошла проверку');
                    } else {
                        $fm->addErrorMessage($form_contacts->getMessages());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return $this->_view_model->setVariables([
            'form_contacts' => $form_contacts,
        ]);
    }

    /**
     *
     * @return ViewModel
     */
    public function galleryAction()
    {
        $params = $this->params()->fromRoute();
        $type = $params['type'];
        $sm = $this->_sm;
        $model_themes = new Themes($sm);
        $model_nominations = new Nominations($sm);

        return $this->_view_model->setVariables([
            'params' => $params,
            'type' => $this->params()->fromRoute('type', 'kid'),
            'nominations' => $model_nominations->getAll($type),
            'themes' => $model_themes->getAll($type),
        ]);
    }

    /**
     *
     * @return ViewModel
     */
    public function resultsAction()
    {
        $params = $this->params()->fromRoute();
        $type = $params['type'];
        $sm = $this->_sm;
        $model_themes = new Themes($sm);
        $model_nominations = new Nominations($sm);

        return $this->_view_model->setVariables([
            'params' => $params,
            'nominations' => $model_nominations->getAll($type),
            'themes' => $model_themes->getAll($type),
        ]);
    }


    /**
     * @return array|Stream
     */
    public function diplomAction()
    {
        $sm = $this->_sm;
        $code = $this->params()->fromRoute('code');
        $model_orders = new Orders($sm);
        $order = $model_orders->getOrderByCode($code);
        if (empty($order)) {
            $this->error404();
        }
        $get = $this->_get;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'save':
                    $content = file_get_contents(DOMAIN . '/diploma/' . $code);
                    $link = ROOT_DIR . '/files/temp_files/diploma_' . $code . '.jpg';
                    file_put_contents($link, $content);
                    $response = new Stream();
                    $response->setStream(fopen($link, 'r'));
                    $response->setStatusCode(200);
                    $response->setStreamName('Диплом');

                    $headers = new Headers();
                    $headers->addHeaders([
                            'Content-Disposition' =>
                                'attachment; filename="diploma_' . $code . '.jpg"',
                            'Content-Type' => 'image/jpeg',
                            'Content-Length' => strlen($content),
                        ]
                    );
                    $response->setHeaders($headers);

                    return $response;

                    break;
            }
        }

        return [
            'code' => $code,
        ];
    }

    /**
     * @return array|Stream
     */
    public function certificateAction()
    {
        $sm = $this->_sm;
        $code = $this->params()->fromRoute('code');
        $model_articles = new Articles($sm);
        $article = $model_articles->getArticleByCode($code);
        if (empty($article)) {
            $this->error404();
        }
        $get = $this->_get;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'save':
                    $content = file_get_contents(DOMAIN . '/ajax/certificate/' . $code);
                    $link = ROOT_DIR . '/files/temp_files/certificate_' . $code . '.jpg';
                    file_put_contents($link, $content);
                    $response = new Stream();
                    $response->setStream(fopen($link, 'r'));
                    $response->setStatusCode(200);
                    $response->setStreamName('Сертификат');

                    $headers = new Headers();
                    $headers->addHeaders([
                            'Content-Disposition' =>
                                'attachment; filename="certificate_' . $code . '.jpg"',
                            'Content-Type' => 'image/jpeg',
                            'Content-Length' => strlen($content),
                        ]
                    );
                    $response->setHeaders($headers);

                    return $response;

                    break;
            }
        }

        return [
            'code' => $code,
            'article' => $article,
        ];
    }


    /**
     * @return array
     */
    public function diplomsAction()
    {
        $model_blanks = new Blanks($this->_sm);
        return [
            'blanks' => $model_blanks->getAll(true),
        ];
    }

    /**
     * @return ViewModel
     */
    public function commentsAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $comments_model = new Comments($sm);
        $on_page = 20;
        $page = (int)$this->params()->fromRoute('page', 1);
        $post = $this->_post;

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_comment':
                    $model_recaptcha = new Recaptcha();
                    if (!empty($post['g-recaptcha-response']) &&
                        $model_recaptcha->verify($post['g-recaptcha-response'])) {
                        $comments_model->addComment($post['comment']);
                        $fm->addSuccessMessage('Спасибо за отзыв, после модерации 
                  он появится на этой странице');
                    } else $fm->addErrorMessage('Капча не прошла проверку');
                    break;
            }
            $this->redirect()->refresh();
        }
        $comments = $comments_model->getActive($page, $on_page);
        $comments['page'] = $page < 1 ? 1 :
            ($page > $comments['page'] ? $comments['page'] : $page);
        $this->layout()->setVariables([
            'page' => $comments['page'],
            'pages' => ceil($comments['count'] / $on_page),
        ]);

        return $this->_view_model->setVariables([
            'comments' => $comments,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function articlesAction()
    {
        $sm = $this->_sm;
        $view_model = new ViewModel();
        $model_articles = new Articles($sm);
        $themes = $model_articles->getAllThemes();
        $view_model->setVariable('themes', $themes);
        $article = $this->params()->fromRoute('id', null);
        $theme = $this->params()->fromRoute('theme', null);
        if ($theme) {
            if (!in_array($theme, array_column($themes, 'trans')))
                $this->redirect()->toRoute('articles');
            $theme = $model_articles->getThemeByTrans($theme);
        }

        return empty($article) ?
            // вывод списка статей по теме
            $this->articles($view_model, $model_articles, $theme) :
            // вывод статьи
            $this->article($view_model, $model_articles, $theme, $article);
    }

    /**
     * @param ViewModel $view_model
     * @param Articles $model_articles
     * @param           $theme
     *
     * @return ViewModel
     */
    private function articles(ViewModel $view_model, Articles $model_articles,
                              $theme)
    {
        $page = $this->params()->fromRoute('page', 1);
        $on_page = 20;
        $theme_id = empty($theme) ? null : $theme['id'];

        return $view_model->setTemplate('application/index/articles')
            ->setVariables([
                'theme' => $theme,
                'articles' => $model_articles->getArticlesByThemeId($theme_id,
                    $page, $on_page),
            ]);
    }

    /**
     * @param ViewModel $view_model
     * @param Articles $model_articles
     * @param           $theme
     * @param           $article
     *
     * @return ViewModel
     */
    private function article(ViewModel $view_model, Articles $model_articles,
                             $theme, $article)
    {
        $article = $model_articles->getArticleById($article);
        if (empty($article) || empty($theme) ||
            $theme['id'] != $article['article_theme_id'])
            $this->redirect()->toRoute('articles');
        $post = $this->_post;
        $fm = $this->_fm;
        $form_comment = new ArticleComment();

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_comment':
                    $model_recaptcha = new Recaptcha();
                    if (!empty($post['g-recaptcha-response']) &&
                        $model_recaptcha->verify($post['g-recaptcha-response'])) {
                        $form_comment->setData($post);
                        $form_comment->setInputFilter(new ArticleCommentFilter());
                        if ($form_comment->isValid()) {
                            $post['article_id'] = $article['id'];
                            $model_articles->addComment($post);
                        } else {
                            $fm->addErrorMessage($form_comment->getMessages());
                        }
                    } else {
                        $fm->addErrorMessage('Капча не прошла проверку');
                    }

                    break;
            }
            $this->redirect()->refresh();
        }

        return $view_model->setTemplate('application/index/article')
            ->setVariables([
                'article' => $article,
                'form_comment' => $form_comment,
                'comments' => $model_articles->getCommentById($article['id']),
            ]);
    }

    /**
     * @return array
     */
    public function addArticleAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $post = $this->_post;
        $form_article = new Article();
        $model_articles = new Articles($sm);
        $themes = $model_articles->getAllThemes();

        $form_article->prepareForm($themes);
        $model_certificates = new Certificates($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_article':
                    $model_recaptcha = new Recaptcha();
                    if (!empty($post['g-recaptcha-response']) &&
                        $model_recaptcha->verify($post['g-recaptcha-response'])) {
                        $form_article->setData($post);
                        $form_article->setInputFilter(new ArticleFilter());
                        if ($form_article->isValid()) {
                            $model_articles->addArticle($form_article->getData());
                            $fm->addSuccessMessage('Ваша статья принята на модерацию.' .
                                ' На ваш адрес электронной почты выслано письмо.');
                        } else {
                            $fm->addErrorMessage($form_article->getMessages());
                        }
                    } else $fm->addErrorMessage('Капча не прошла проверку');

                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'form_article' => $form_article,
            'certificates' => $model_certificates->getAll(true),
            'themes' => $themes,
        ];
    }

    /**
     * @return array|\Zend\Http\Response
     */
    public function workAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $order_code = $this->params()->fromRoute('order_code');
        $model_orders = new Orders($sm);
        $order = $model_orders->getOrderByCode($order_code);
        if (empty($order) || $order['result'] == 0) {
            $fm->addSuccessMessage('Такой работы нет в галерее');
            return $this->redirect()->toRoute('home');
        }

        return [
            'order' => $order,
        ];
    }

    /**
     * @return array
     */
    public function confAction()
    {

        return [];
    }


}
