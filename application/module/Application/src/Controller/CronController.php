<?php

namespace Application\Controller;

use Application\Model\Cron;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class CronController extends AbstractActionController
{

    protected $_sm;
    protected $_view_model;

    public function onDispatch(MvcEvent $e)
    {
        $this->_sm = $e->getApplication()->getServiceManager();

        $view_model = new ViewModel();
        $view_model->setTerminal(true)
            ->setTemplate('application/ajax/blank');
        $this->_view_model = $view_model;

        parent::onDispatch($e);
    }

    public function addResultsAction()
    {
        //$model_cron = new Cron($this->_sm);
        //$model_cron->addEmptyResults();
        return $this->_view_model;
    }

    public function sendOrderAction()
    {
        $model_cron = new Cron($this->_sm);
        // отправляем диплом по оплаченной и оцененной заявке
        $model_cron->sendOrder();

        return $this->_view_model;
    }

    public function everydayAction()
    {
        $model_cron = new Cron($this->_sm);

        // удаляем старые файлы
        try {
            $model_cron->deleteOldFiles();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        // удаляем временные файлы
        try {
            $model_cron->deleteTempFiles();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

//        // удаляем пустые каталоги
//        $model_cron->recRemoveEmptyDir();

        // неоплаченные заявки в архив
        try {
            $model_cron->oldOrdersInArchive();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        // архивные неоплаченные заявки в удаленные
        try {
            $model_cron->deleteOldOrders();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        return $this->_view_model;
    }

}