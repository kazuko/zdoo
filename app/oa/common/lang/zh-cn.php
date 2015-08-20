<?php
/**
 * The zh-cn file of common module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv11.html)
 * @author      Chunsheng wang <chunsheng@cnezsoft.com>
 * @package     common 
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
$lang->app = new stdclass();
$lang->app->name = 'OA';

$lang->menu->oa = new stdclass();
$lang->menu->oa->dashboard = '我的地盘|dashboard|index|';
$lang->menu->oa->project   = '项目|project|index|';
$lang->menu->oa->announce  = '公告|announce|browse|';
$lang->menu->oa->doc       = '文档|doc|browse|';
$lang->menu->oa->attend    = '考勤|attend|personal|';
$lang->menu->oa->leave     = '请假|leave|browse|type=personal';

$lang->dashboard = new stdclass();

$lang->project   = new stdclass();
$lang->project->menu = new stdclass();
$lang->project->menu->involved = '我参与的|project|index|status=involved';
$lang->project->menu->doing    = '进行中|project|index|status=doing';
$lang->project->menu->finished = '已完成|project|index|ststus=finished';
$lang->project->menu->suspend  = '已挂起|project|index|ststus=suspend';

$lang->announce = new stdclass();
$lang->announce->menu = new stdclass();
$lang->announce->menu->browse   = array('link' => '公告列表|announce|browse|', 'alias' => 'view');
$lang->announce->menu->category = '类目管理|tree|browse|type=announce|';

$lang->doc = new stdclass();
$lang->doc->menu = new stdclass();
$lang->doc->menu->create = '添加文档库|doc|createlib|';

$lang->attend = new stdclass();
$lang->attend->menu = new stdclass();
$lang->attend->menu->personal   = '我的考勤|attend|personal|';
$lang->attend->menu->department = '部门考勤|attend|department|';
$lang->attend->menu->company    = '公司考勤|attend|department|date=&company=true';
$lang->attend->menu->review     = '补录审核|attend|review|';
$lang->attend->menu->holiday    = '节假日|holiday|browse|';
$lang->attend->menu->settings   = '设置|attend|settings|';

$lang->holiday = new stdclass();
$lang->holiday->menu = $lang->attend->menu;
$lang->menuGroups->holiday = 'attend';

$lang->leave = new stdclass();
$lang->leave->menu = new stdclass();
$lang->leave->menu->browsePersonal = '我的请假|leave|browse|type=personal';
$lang->leave->menu->browseDept     = '部门|leave|browse|type=department';
$lang->leave->menu->browseCompany  = '公司|leave|browse|type=company';
