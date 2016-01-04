<?php
/**
 * The control file of leads module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     leads
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
class leads extends control
{
    /**
     * Construct method.
     * 
     * @param  string $moduleName 
     * @param  string $methodName 
     * @param  string $appName 
     * @access public
     * @return void
     */
    public function __construct($moduleName = '', $methodName = '', $appName = '')
    {
        parent::__construct($moduleName, $methodName, $appName);
        $this->loadModel('contact', 'crm');
    }

    /** 
     * The index page, locate to the browse page.
     * 
     * @access public
     * @return void
     */
    public function index()
    {
        $this->locate(inlink('browse'));
    }

    /**
     * Browse contact.
     * 
     * @param string $orderBy     the order by
     * @param int    $recTotal 
     * @param int    $recPerPage 
     * @param int    $pageID 
     * @access public
     * @return void
     */
    public function browse($mode = 'all', $status = 'wait', $origin = '',  $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {   
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $contacts = $this->contact->getList($customer = '', $relation = 'client', $mode, $status, $origin, $orderBy, $pager);
        $this->session->set('contactQueryCondition', $this->dao->get());
        $this->session->set('contactList', $this->app->getURI(true));
        $this->app->user->canEditContactIdList = ',' . implode(',', array_keys($contacts)) . ',';

        /* Build search form. */
        $this->loadModel('search', 'sys');
        $this->config->contact->search['actionURL'] = $this->createLink('contact', 'browse', 'mode=bysearch');
        $this->search->setSearchParams($this->config->contact->search);

        $this->view->title     = $this->lang->contact->list;
        $this->view->mode      = $mode;
        $this->view->origin    = $origin;
        $this->view->contacts  = $contacts;
        $this->view->pager     = $pager;
        $this->view->orderBy   = $orderBy;
        $this->display();
    }   

    /**
     * Edit a contact.
     * 
     * @param  int    $contactID 
     * @access public
     * @return void
     */
    public function edit($contactID)
    {
        $contact = $this->contact->getByID($contactID);

        if($_POST)
        {
            $return = $this->contact->update($contactID);
            $this->send($return);
        }

        $this->view->title      = $this->lang->contact->edit;
        $this->view->contact    = $contact;
        $this->view->modalWidth = 1000;

        $this->display();
    }

    /**
     * View contact. 
     * 
     * @param  int    $contactID 
     * @access public
     * @return void
     */
    public function view($contactID, $status = 'normal')
    {
        $actionList = $this->loadModel('action')->getList('contact', $contactID);
        $actionIDList = array_keys($actionList);
        $actionFiles = $this->loadModel('file')->getByObject('action', $actionIDList);
        $fileList = array();
        foreach($actionFiles as $files)
        {
            foreach($files as $file) $fileList[$file->id] = $file;
        }

        $this->view->title      = $this->lang->contact->view;
        $this->view->contact    = $this->contact->getByID($contactID, $status);
        $this->view->addresses  = $this->loadModel('address')->getList('contact', $contactID);
        $this->view->preAndNext = $this->loadModel('common', 'sys')->getPreAndNextObject('contact', $contactID); 
        $this->view->fileList   = $fileList;

        $this->display();
    }

    /**
     * Apply leads.
     * 
     * @access public
     * @return void
     */
    public function apply()
    {
        $remain = isset($this->config->leads->apply->remain) ? $this->config->leads->apply->remain : 10;
        $limit  = isset($this->config->leads->apply->limit) ? $this->config->leads->apply->limit : 50;

        $contactCount = $this->dao->select('count(*) as count')->from(TABLE_CONTACT)->where('assignedTo')->eq($this->app->user->account)->andWhere('status')->eq('wait')->fetch('count');
        if($contactCount >= $remain) $this->send(array('result' => 'fail', 'message' => $this->lang->leads->message->apply));

        $contacts = $this->dao->select('*')->from(TABLE_CONTACT)->where('status')->eq('wait')->andWhere('assignedTo')->eq('')->orderBy('id_desc')->limit($limit)->fetchAll();
        foreach($contacts as $contact)
        {
            $this->dao->update(TABLE_CONTACT)->set('assignedTo')->eq($this->app->user->account)->where('id')->eq($contact->id)->exec();
        }
        if(!dao::isError()) return $this->send(array('result' => 'success', 'locate' => inlink('browse', "mode=assignedTo")));
        $this->send(array('result' => 'fail', 'message' => dao::getError()));
    }

    /**
     * Assign a contact to a member.
     * 
     * @param  int    $contactID 
     * @param  string    $table 
     * @access public
     * @return void
     */
    public function assign($contactID, $table = null)
    {
        $this->loadModel('contact');
        if($_POST) 
        {
            $this->contact->assign($contactID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            if($this->post->assignedTo) 
            {
                $actionID = $this->loadModel('action')->create('contact', $contactID, 'Assigned', $this->post->comment, $this->post->assignedTo);
                $this->sendmail($contactID, $actionID);
            }
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $this->server->http_referer));
        }

        $this->view->title     = $this->lang->contact->assign;
        $this->view->users     = $this->loadModel('user')->getPairs('nodeleted, noclosed');
        $this->view->contactID = $contactID;
        $this->display();
    }

    /**
     * Transform contact.
     * 
     * @param  int     $contactID 
     * @access public
     * @return void
     */
    public function transform($contactID)
    {
        if($_POST)
        {
            $result = $this->contact->transform($contactID);
            if(is_array($result)) $this->send($result);

            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->importSuccess, 'locate' => $this->server->http_referer));
        }

        $this->view->title     = $this->lang->confirm . $this->lang->contact->common;
        $this->view->contact   = $this->contact->getByID($contactID, 'wait');
        $this->view->customers = $this->loadModel('customer')->getPairs('client');
        $this->display();
    }

    /**
     * Ignore contact in leads.
     * 
     * @param  int    $contactID 
     * @access public
     * @return void
     */
    public function ignore($contactID)
    {
        $this->contact->ignore($contactID);
        if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
        $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess));
    }

    /**
     * Send email.
     * 
     * @param  int    $contactID 
     * @param  int    $actionID 
     * @access public
     * @return void
     */
    public function sendmail($contactID, $actionID)
    {
        /* Reset $this->output. */
        $this->clear();

        /* Set toList and ccList. */
        $contact = $this->contact->getById($contactID);
        $users   = $this->loadModel('user')->getPairs('noletter');
        $toList  = $contact->assignedTo;

        /* send notice if user is online and return failed accounts. */
        $toList = $this->loadModel('action')->sendNotice($actionID, $toList);

        /* Get action info. */
        $action          = $this->loadModel('action')->getById($actionID);
        $history         = $this->action->getHistory($actionID);
        $action->history = isset($history[$actionID]) ? $history[$actionID] : array();

        /* Create the email content. */
        $this->view->contact = $contact;
        $this->view->action  = $action;
        $this->view->users   = $users;

        $mailContent = $this->parse($this->moduleName, 'sendmail');

        /* Send emails. */
        $this->loadModel('mail')->send($toList, 'CONTACT#' . $contact->id . $this->lang->colon . $contact->realname, $mailContent);
        if($this->mail->isError()) trigger_error(join("\n", $this->mail->getError()));
    }
}