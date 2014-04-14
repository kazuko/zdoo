<?php
/**
 * The config file of doc module of RanZhi.
 *
 * @copyright   Copyright 2013-2014 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     LGPL
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     doc 
 * @version     $Id$
 * @link        http://www.ranzhi.org
 */
global $lang;

$config->doc = new stdclass();
$config->doc->require = new stdclass();
$config->doc->require->createLib = 'name';
$config->doc->require->editLib   = 'name';
$config->doc->require->create    = 'title';
$config->doc->require->edit      = 'title';

$config->doc->editor = new stdclass();
$config->doc->editor->create = array('id' => 'content', 'tools' => 'full');
$config->doc->editor->edit   = array('id' => 'content,digest,comment', 'tools' => 'full');
