<?php

namespace Application\Controller;

use Application\Form;
use Application\Form\Filter;
use Application\Model\Articles;
use Application\Model\Blanks;
use Application\Model\Certificates;
use Application\Model\Comments;
use Application\Model\Files;
use Application\Model\Mail;
use Application\Model\Nominations;
use Application\Model\Orders;
use Application\Model\Pays;
use Application\Model\Repository\RepositoryFactory;
use Application\Model\Thanks;
use Application\Model\Themes;
use Zend\Session\Container;
use Application\Model\Repository\Orders as OrdersRepository;

/**
 * Class AdminController
 * @package Application\Controller
 */
class AdminController extends BasicController
{

    public function ordersAction()
    {
        /** @var \Application\Model\Repository\Orders $orderModel */
        $orderModel = RepositoryFactory::factory('orders');
        /** @var \Application\Model\Repository\Nominations $nominationsModel */
        $nominationsModel = RepositoryFactory::factory('nominations');
        /** @var \Application\Model\Repository\Themes $themesModel */
        $themesModel = RepositoryFactory::factory('themes');

        $sm = $this->_sm;
        $model = new Orders($sm);
        $session = new Container('adminOrders');
        $get = $this->_get;
        $fm = $this->_fm;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_paid':
                    $model->changePaid($get['id']);
                    break;
                case 'ch_session':
                    $session->{$get['p']} = $get['v'];
                    $session->page = 1;
                    break;
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
                case 'delete':
                    $orderModel->deleteById($get['id']);
                    break;
                case 'ch_result':
                    $model->changeResult($get['id'], $get['result']);
                    break;
                case 'send':
                    try {
                        $model->sendDiploma($get['id']);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'ch_status':
                    $model->changeStatus($get);
                    break;
                case 'all_dip':
                    $model->allDiplomant($session);
                    break;
            }
            $this->redirect()->refresh();
        }

        if (empty($session->page)) $session->page = 1;
        $session->onPage = 50;
        if (empty($session->from))
            $session->from = date('d.m.Y',
                strtotime(date('Y') . '-' . date('m') . '-01'));
        if (empty($session->to))
            $session->to = date('d.m.Y', strtotime('+1 days'));
        if (!isset($session->search)) $session->search = '';

        return [
            'orders' => $orderModel->getAll($session),
            'session' => $session,
            'kid_nominations' => $nominationsModel->getAll($orderModel::TYPE_KID),
            'educator_nominations' => $nominationsModel->getAll($orderModel::TYPE_EDUCATOR),
            'kid_themes' => $themesModel->getAll($orderModel::TYPE_KID),
        ];
    }

    public function orderDetailAction()
    {
        /** @var \Application\Model\Repository\Orders $orderModel */
        $orderModel = RepositoryFactory::factory('orders');
        /** @var \Application\Model\Repository\Nominations $nominationsModel */
        $nominationsModel = RepositoryFactory::factory('nominations');
        /** @var \Application\Model\Repository\Themes $themesModel */
        $themesModel = RepositoryFactory::factory('themes');

        $id = $this->params()->fromRoute('id', 0);
        $sm = $this->_sm;
        $fm = $this->_fm;
        $form = new Form\Order($sm);
        $post = $this->_post;
        $order = $orderModel->getRowById($id)->toArray();
        $nomination = $nominationsModel->getRowById($order['nomination_id'])->toArray();
        $theme = null;
        if (!empty($order['theme_id'])) {
            $theme = $themesModel->getRowById($order['theme_id'])->toArray();
        }
        $form->prepareForIndex($order['type'], $nomination, $theme);
        $form->prepareForEdit($order['type']);


        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'edit_order':
                    $form->setData($post);
                    $form->setInputFilter(new Filter\OrderFilter($sm));
                    if ($form->isValid()) {
                        try {
                            $orderModel->editOrder($form->getData(), $id);
                        } catch (\Exception $e) {
                            $fm->addErrorMessage($e->getMessage());
                        }
                    } else {
                        var_dump($form->getMessages());
                    }

                    break;
            }
            $this->redirect()->refresh();
        }

        $order = $orderModel->getRowById($id)->toArray();
        $form->populateValues($order);
        return [
            'order_id' => $id,
            'order' => $order,
            'form' => $form,
        ];
    }

    public function themesAction()
    {
        /** @var \Application\Model\Repository\Themes $themesModel */
        $themesModel = RepositoryFactory::factory('themes');
        $fm = $this->_fm;

        $get = $this->_get;
        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'del':
                    $themesModel->deleteById($get['id']);
                    break;
                case 'change':
                    try {
                        $themesModel->changeTitle($get, OrdersRepository::TYPE_KID);
                        $fm->addSuccessMessage('Изменения сохранены');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'add':
                    try {
                        $themesModel->add($get);
                        $fm->addSuccessMessage('Тема добавлена');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'types' => $themesModel->getAll(),
        ];
    }

    public function kidNominationsAction()
    {
        /** @var \Application\Model\Repository\Nominations $nominationsModel */
        $nominationsModel = RepositoryFactory::factory('nominations');
        $fm = $this->_fm;

        $get = $this->_get;
        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'del':
                    $nominationsModel->deleteById($get['id']);
                    break;
                case 'change':
                    try {
                        $nominationsModel->changeTitle($get, OrdersRepository::TYPE_KID);
                        $fm->addSuccessMessage('Изменения сохранены');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'add':
                    try {
                        $nominationsModel->add($get, OrdersRepository::TYPE_KID);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'nominations' => $nominationsModel
                ->getByWhere(['type' => OrdersRepository::TYPE_KID]),
        ];
    }

    public function educatorNominationsAction()
    {
        /** @var \Application\Model\Repository\Nominations $nominationsModel */
        $nominationsModel = RepositoryFactory::factory('nominations');
        $fm = $this->_fm;

        $get = $this->_get;
        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'del':
                    $nominationsModel->deleteById($get['id']);
                    break;
                case 'change':
                    try {
                        $nominationsModel->changeTitle($get, OrdersRepository::TYPE_EDUCATOR);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'add':
                    try {
                        $nominationsModel->add($get, OrdersRepository::TYPE_EDUCATOR);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'nominations' => $nominationsModel
                ->getByWhere(['type' => OrdersRepository::TYPE_EDUCATOR]),
        ];
    }

    public function blanksAction()
    {
        $post = $this->_post;
        $get = $this->_get;
        $sm = $this->_sm;
        $fm = $this->_fm;
        $blanks_model = new Blanks($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_blank':
                    try {
                        $blanks_model->uploadBlank($_FILES['file']);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_active':
                    $blanks_model->changeActive($get['id']);
                    break;
                case 'del':
                    $blanks_model->deleteBlank($get['id']);
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'blanks' => $blanks_model->getAllFull(),
        ];
    }

    public function galleryAction()
    {
        $get = $this->_get;
        $sm = $this->_sm;
        $session = new Container('adminGallery');
        $model_files = new Files($sm);
        $types = Orders::getTypes();
        if (empty($session->page)) $session->page = 1;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'delete':
                    $model_files->deleteFromGallery($get['id']);
                    break;
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
                case 'ch_type':
                    $session->type = $get['type'];
                    $session->page = 1;
                    break;
            }
            $this->redirect()->refresh();
        }

        $session->type = empty($session->type) ?
            key($types) : $session->type;
        $params = [
            'page' => $session->page,
            'on_page' => 30,
            'type' => $session->type,
        ];

        return [
            'files' => $model_files->getFilesForGallery($params),
            'types' => $types,
            'type' => $session->type,
        ];
    }

    public function paysAction()
    {
        $get = $this->_get;
        $sm = $this->_sm;
        $fm = $this->_fm;
        $model_pays = new Pays($sm);
        $model_orders = new Orders($sm);
        $session = new Container('adminPays');

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
                case 'delete':
                    $model_pays->deletePay($get['id']);
                    break;
                case 'add':
                    try {
                        $model_pays->addPay($get['order_id'], $get['pay_way'],
                            $get['sum']);
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        if (empty($session->page)) $session->page = 1;
        $session->on_page = 50;

        return [
            'pays' => $model_pays->getAll($session),
            'order_ids' => $model_orders->getNoPaidOrderIds(),
        ];
    }

    public function clientsAction()
    {
        $get = $this->_get;
        $sm = $this->_sm;
        $model_orders = new Orders($sm);
        $session = new Container('adminClients');

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
                case 'search_email':
                    $session->email = $get['email'];
                    $session->page = 1;
                    break;
            }
            $this->redirect()->refresh();
        }

        if (empty($session->page)) $session->page = 1;
        $session->on_page = 50;

        return [
            'clients' => $model_orders->getClientsForAdmin($session),
            'session' => $session,
        ];
    }

    public function clientDetailAction()
    {
        $sm = $this->_sm;
        $post = $this->_post;
        $get = $this->_get;
        $email = $this->params()->fromRoute('email', '');
        $model_orders = new Orders($sm);
        $session = new Container('adminClientDetail');

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'send_message':
                    $model_mail = new Mail($sm);
                    $subject = 'Всероссийский центр ' .
                        $this->layout()->config['site_name'];
                    $model_mail->sendView($email, $subject, 'from_admin',
                        ['message' => $post['message']]);
                    break;
            }
            $this->redirect()->refresh();
        }

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
            }
            $this->redirect()->refresh();
        }
        if (empty($session->page)) $session->page = 1;
        $session->on_page = 20;

        return [
            'orders' => $model_orders->getOrdersByEmail($email, $session),
        ];
    }

    public function resultsAction()
    {
        $sm = $this->_sm;
        $get = $this->_get;
        $session = new Container('adminResults');
        $model_nominations = new Nominations($sm);
        $model_themes = new Themes($sm);
        $model_orders = new Orders($sm);

        if (empty($session->type)) $session->type = key(Orders::getTypes());

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_session':
                    $session->{$get['k']} = $get['v'];
                    break;
                case 'add_result':
                    $model_orders->addResultFromAdmin($session->type, $get);
                    break;
            }
            $this->redirect()->refresh();
        }

        $session->page = 1;
        $session->on_page = 50;


        return [
            'session' => $session,
            'nominations' => $model_nominations->getAll($session->type),
            'themes' => $model_themes->getAll($session->type),
            'results' => $model_orders->getForResult($session),
        ];
    }

    public function commentsAction()
    {
        $sm = $this->_sm;
        $get = $this->_get;
        $session = new Container('adminComments');
        $comments_model = new Comments($sm);
        $on_page = 10;
        if (empty($session->page)) $session->page = 1;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_active':
                    $comments_model->chActive($get['id']);
                    break;
                case 'del':
                    $comments_model->delComment($get['id']);
                    break;
                case 'ch_text':
                    $comments_model->chText($get['id'], $get['text']);
                    break;
                case 'ch_page':
                    $session->page = $get['page'];
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'comments' => $comments_model->getAll($session->page, $on_page),
        ];
    }

    public function articleThemesAction()
    {
        $get = $this->_get;
        $sm = $this->_sm;
        $fm = $this->_fm;
        $model_articles = new Articles($sm);

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'del':
                    $model_articles->delTheme($get['id']);
                    break;
                case 'change':
                    try {
                        $model_articles->changeThemeTitle($get);
                        $fm->addSuccessMessage('Изменения сохранены');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
                case 'add':
                    try {
                        $model_articles->addTheme($get);
                        $fm->addSuccessMessage('Тема добавлена');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'themes' => $model_articles->getAllThemes(),
        ];
    }

    public function articlesAction()
    {
        $sm = $this->_sm;
        $model_articles = new Articles($sm);
        $get = $this->_get;

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'delete':
                    $model_articles->delArticle($get['id']);
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'articles' => $model_articles->getAllArticles(),
        ];
    }

    public function addArticleAction()
    {
        $sm = $this->_sm;
        $model_articles = new Articles($sm);
        $fm = $this->_fm;
        $post = $this->_post;
        $form_article = new Form\Article($sm);
        $themes = $model_articles->getAllThemes();
        $form_article->prepareForm($themes);
        $model_certificates = new Certificates($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_article':
                    $form_article->setData($post);
                    $form_article->setInputFilter(new Filter\ArticleFilter());
                    if ($form_article->isValid()) {
                        $id = $model_articles->adminAddArticle($post);
                        $fm->addSuccessMessage('Статья добавлена');
                        return $this->redirect()
                            ->toRoute('admin-article', ['id' => $id]);
                    } else {
                        $fm->addErrorMessage($form_article->getMessages());
                    }

                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'form_article' => $form_article,
            'certificates' => $model_certificates->getAll(true),
        ];
    }

    public function articleAction()
    {
        $sm = $this->_sm;
        $model_articles = new Articles($sm);
        $id = $this->params()->fromRoute('id');
        $article = $model_articles->getAdminArticleById($id);
        if (empty($article)) $this->redirect()->toRoute('articles');

        $fm = $this->_fm;
        $post = $this->_post;
        $get = $this->_get;
        $form_article = new Form\Article($sm);
        $themes = $model_articles->getAllThemes();
        $form_article->prepareForm($themes);
        $form_article->prepareForEdit($article);
        $model_certificates = new Certificates($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'edit_article':
                    $form_article->setData($post);
                    $form_article->setInputFilter(new Filter\ArticleFilter());
                    if ($form_article->isValid()) {
                        $model_articles->editArticle($post, $id);
                        $fm->addSuccessMessage('Изменения сохранены');
                    } else {
                        $fm->addErrorMessage($form_article->getMessages());
                    }

                    break;
            }
            $this->redirect()->refresh();
        }

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'send':
                    try {
                        $model_certificates->sendCertificate($id);
                        $fm->addSuccessMessage('Сертификат отправлен');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'form_article' => $form_article,
            'certificates' => $model_certificates->getAll(true),
            'article' => $article,
        ];
    }

    public function articleCommentsAction()
    {
        $sm = $this->_sm;
        $model_articles = new Articles($sm);

        return [
            'comments' => $model_articles->getAllComments(),
        ];
    }

    public function articleCommentDetailAction()
    {
        $sm = $this->_sm;
        $fm = $this->_fm;
        $post = $this->_post;
        $model_articles = new Articles($sm);
        $id = $this->params()->fromRoute('id');
        $comment = $model_articles->getArticleCommentById($id);
        if (empty($comment))
            $this->redirect()->toRoute('admin', ['action' => 'article-comments']);
        $form_comment = new Form\ArticleComment();
        $form_comment->prepareForEdit($comment);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'edit_comment':
                    $form_comment->setData($post);
                    $form_comment->setInputFilter(new Filter\ArticleCommentFilter());
                    if ($form_comment->isValid()) {
                        $model_articles->editComment($post, $id);
                    } else {
                        $fm->addErrorMessage($form_comment->getMessages());
                    }

                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'form_comment' => $form_comment,
            'comment' => $comment,
        ];
    }

    public function certificateAction()
    {
        $post = $this->_post;
        $get = $this->_get;
        $sm = $this->_sm;
        $fm = $this->_fm;
        $model_certificates = new Certificates($sm);

        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'add_certificate':
                    try {
                        $model_certificates->uploadCertificate($_FILES['file']);
                        $fm->addSuccessMessage('Бланк сертификата добавлен');
                    } catch (\Exception $e) {
                        $fm->addErrorMessage($e->getMessage());
                    }
                    break;
            }
            $this->redirect()->refresh();
        }

        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_active':
                    $model_certificates->changeActive($get['id']);
                    break;
                case 'del':
                    $model_certificates->deleteCertificate($get['id']);
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'certificates' => $model_certificates->getAllFull(),
        ];

    }

    public function thanksAction()
    {
        /** @var \Application\Model\Repository\Thanks $thanksModel */
        $thanksModel = RepositoryFactory::factory('thanks');
        $session = new Container('adminThanks');

        $get = $this->_get;
        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'ch_page':
                    $session->page = (int) $get['page'];
                    break;
                case 'delete':
                    $thanksModel->deleteById($get['id']);
                    $session->page = 1;
                    break;
            }
            $this->redirect()->refresh();
        }

        if (empty($session->page)) $session->page = 1;

        $paginator = $thanksModel->getAllPagination();
        $paginator->setCurrentPageNumber($session->page);
        $paginator->setItemCountPerPage(20);

        return [
            'thanks' => $paginator,
        ];
    }

    public function thankDetailAction()
    {
        $id = $this->params()->fromRoute('id');
        /** @var \Application\Model\Repository\Thanks $thanksModel */
        $thanksModel = RepositoryFactory::factory('thanks');
        $row = $thanksModel->getRowById($id);
        $fm = $this->_fm;
        if (empty($row)) {
            $fm->addErrorMessage('Такой благодарности нет');
            $this->redirect()->toRoute('admin', ['action' => 'thanks']);
        }
        $thank = $row->toArray();

        $sm = $this->_sm;
        $form_thank = new Form\Thank();
        $model_thanks = new Thanks($sm);
        $form_thank->populateValues($thank);
        $form_thank->get('act')->setValue('save');
        $form_thank->get('submit')->setValue('Сохранить');

        $post = $this->_post;
        if (!empty($post['act'])) {
            switch ($post['act']) {
                case 'save':
                    $thanksModel->saveChanges($post, $id);
                    break;
            }
            $this->redirect()->refresh();
        }

        $get = $this->_get;
        if (!empty($get['act'])) {
            switch ($get['act']) {
                case 'send':
                    $model_thanks->sendThank($id);
                    $model_mail = new Mail($sm);
                    $model_mail->sendThank($thank);
                    break;
            }
            $this->redirect()->refresh();
        }

        return [
            'thankForm' => $form_thank,
            'thank' => $thank,
        ];
    }


}
