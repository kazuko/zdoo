<?php 
/**
 * The info file of customer module of RanZhi.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     customer 
 * @version     $Id$
 * @link        http://www.ranzhi.org
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include '../../common/view/kindeditor.html.php';?>
<ul id='menuTitle'>
  <li><?php commonModel::printLink('customer', 'browse', '', $lang->customer->list);?></li>
  <li class='divider angle'></li>
  <li class='title'>
    <?php echo $customer->name;?>
    <?php if($customer->public):?>
    <span class='label label-primary'><?php echo $lang->customer->public;?></span>
    <?php endif;?>
  </li>
</ul>
<div class='row-table'>
  <div class='col-main'>
    <div class='panel'>
      <div class='panel-heading'><strong><?php echo $lang->customer->desc;?></strong></div>
      <div class='panel-body'><?php echo $customer->desc;?></div>
    </div>

    <div class='panel'>
      <div class='panel-heading'><strong><?php echo $lang->customer->intension;?></strong></div>
      <div class='panel-body'><?php echo $customer->intension;?></div>
    </div>

    <?php if(!empty($returnList)):?>
    <div class='panel'>
      <div class='panel-heading'><strong><?php echo $lang->contract->returnRecords;?></strong></div>
      <table class='table'>
        <tr>
          <th><?php echo $lang->contract->common;?></th>
          <th class='w-100px'><?php echo $lang->contract->returnedDate;?></th>
          <th class='w-100px'><?php echo $lang->contract->returnedBy;?></th> 
          <th class='w-160px'><?php echo $lang->contract->amount;?></th> 
        </tr>
        <?php foreach($returnList as $return):?>
        <?php $contract = $contracts[$return->contract];?>
        <tr>
          <td><?php echo $contract->name;?></td>
          <td><?php echo $return->returnedDate;?></td>
          <td><?php echo zget($users, $return->returnedBy, $return->returnedBy);?></td>
          <td><?php echo zget($currencySign, $contract->currency, '') . formatMoney($return->amount);?></td>
        </tr>
        <?php endforeach;?>
      </table>
    </div>
    <?php endif;?>

    <?php echo $this->fetch('file', 'printFiles', array('files' => $files, 'fieldset' => 'true'))?>
    <?php echo $this->fetch('action', 'history', "objectType=customer&objectID={$customer->id}")?>
    <div class='page-actions'>
      <?php
      echo "<div class='btn-group'>";
      commonModel::printLink('crm.order', 'create', "customer=$customer->id", $lang->order->common, "class='btn'");
      commonModel::printLink('crm.contract', 'create', "customer=$customer->id", $lang->contract->common, "class='btn'");
      echo '</div>';
      echo "<div class='btn-group'>";
      commonModel::printLink('action', 'createRecord', "objectType=customer&objectID={$customer->id}&customer={$customer->id}&history=", $lang->customer->record, "class='btn' data-toggle='modal' data-width='860'");
      commonModel::printLink('customer', 'assign', "customerID=$customer->id", $lang->customer->assign, "class='btn' data-toggle='modal'");
      commonModel::printLink('customer', 'contact', "customerID=$customer->id", $lang->customer->contact,  "class='btn' data-toggle='modal'");
      commonModel::printLink('address',  'browse', "objectType=customer&objectID=$customer->id", $lang->customer->address, "class='btn' data-toggle='modal'");
      echo '</div>';
      echo "<div class='btn-group'>";
      commonModel::printLink('customer', 'edit', "customerID=$customer->id", $lang->edit, "class='btn'");
      commonModel::printLink('customer', 'delete', "customerID=$customer->id", $lang->delete, "class='deleter btn'");
      echo html::a('#commentBox', $this->lang->comment, "class='btn btn-default' onclick=setComment()");
      echo '</div>';

      $browseLink = $this->session->customerList ? $this->session->customerList : inlink('browse');
      commonModel::printRPN($browseLink, $preAndNext);
      ?>
    </div>
    <fieldset id='commentBox' class='hide'>
      <legend><?php echo $lang->comment;?></legend>
      <form id='ajaxForm' method='post' action='<?php echo inlink('edit', "customerID={$customer->id}&comment=true")?>'>
        <div class='form-group'><?php echo html::textarea('comment', '',"rows='5' class='w-p100'");?></div>
        <?php echo html::submitButton();?>
      </form>
    </fieldset>      
  </div>
  <div class='col-side'>  
    <div class='panel'>
      <div class='panel-heading'><strong><i class="icon-list-info"></i> <?php echo $lang->customer->basicInfo;?></strong></div>
      <div class='panel-body'>
        <table class='table table-info'>
          <tr>
            <th class='w-70px'><?php echo $lang->customer->source;?></th>
            <td><?php echo zget($lang->customer->sourceList, $customer->source);?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->sourceNote;?></th>
            <td><?php echo $customer->sourceNote;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->depositor;?></th>
            <td><?php echo $customer->depositor;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->level;?></th>
            <td><?php if($customer->level) echo $lang->customer->levelNameList[$customer->level];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->status;?></th>
            <td><?php if($customer->status) echo $lang->customer->statusList[$customer->status];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->assignedTo;?></th>
            <td><?php echo zget($users, $customer->assignedTo);?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->size;?></th>
            <td><?php echo $lang->customer->sizeNameList[$customer->size];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->type;?></th>
            <td><?php echo $lang->customer->typeList[$customer->type];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->industry;?></th>
            <td><?php if($customer->industry) echo $industryList[$customer->industry];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->area;?></th>
            <td><?php if($customer->area) echo $areaList[$customer->area];?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->weibo;?></th>
            <td><?php echo html::a("$customer->weibo", $customer->weibo, "target='_blank'");?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->weixin;?></th>
            <td><?php echo $customer->weixin;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->customer->site;?></th>
            <td><?php echo html::a("$customer->site", $customer->site, "target='_blank'");?></td>
          </tr>
          <?php if(formatTime($customer->nextDate)):?>
          <tr>
            <th><?php echo $lang->customer->nextDate;?></th>
            <td><?php echo $customer->nextDate;?></td>
          </tr>
          <?php endif;?>
        </table>
      </div>
    </div>
    <?php echo $this->fetch('contact', 'block', "customer={$customer->id}", 'crm')?>
    <div class='panel'>
      <div class='panel-heading'>
        <div class='row'>      
          <div class='col-sm-8'><strong><i class="icon-list-info"></i> <?php echo $lang->customer->contract;?></strong></div>
          <div class='col-sm-2'><strong><?php echo $lang->order->amount;?></strong></div> 
          <div class='col-sm-2'><strong><?php echo $lang->order->status;?></strong></div> 
        </div>
      </div>
      <table class='table table-data table-condensed'>
        <?php foreach($contracts as $contract):?>
        <tr data-url='<?php echo $this->createLink('crm.contract', 'view', "contractID=$contract->id"); ?>'>
          <td class='w-p70'><?php echo $contract->name;?></td>
          <td class='w-p15'><?php echo zget($currencySign, $contract->currency, '') . $contract->amount;?></td>
          <td class='w-p15 <?php echo "contract-{$contract->status}";?>'><?php echo $lang->contract->statusList[$contract->status];?></td>
        </tr>
        <?php endforeach;?>
      </table>
    </div>

    <div class='panel'>
      <div class='panel-heading'>
        <div class='row'>      
          <div class='col-sm-4'><strong><i class="icon-list-info"></i> <?php echo $lang->customer->order;?></strong></div>
          <div class='col-sm-3'><strong><?php echo $lang->order->plan;?></strong></div>
          <div class='col-sm-3'><strong><?php echo $lang->order->real;?></strong></div>
          <div class='col-sm-2'><strong><?php echo $lang->order->status;?></strong></div>
        </div>
      </div>
      <table class='table table-data table-condensed'>
        <?php foreach($orders as $order):?>
        <tr data-url='<?php echo $this->createLink('crm.order', 'view', "orderID=$order->id"); ?>'>
          <td class='w-p35'><?php foreach($order->products as $product) echo $product . ' ';?></td>
          <td class='w-p25'><?php echo $order->plan;?></td>
          <td class='w-p25'><?php echo zget($currencySign, $order->currency, '') . $order->real;?></td>
          <td class='w-p15 <?php echo "order-{$order->status}";?>'><?php echo $lang->order->statusList[$order->status];?></td>
        </tr>
        <?php endforeach;?>
      </table>
    </div>

    <?php if(!empty($productList)):?>
    <div class='panel'>
      <div class='panel-heading'>
        <div class='row'>      
          <div class='col w-p50'><strong><i class='icon-list-info'></i> <?php echo $lang->customer->purchasedProducts;?></strong></div>
          <div class='col w-p20'><strong><?php echo $lang->product->category;?></strong></div>
          <div class='col w-p15'><strong><?php echo $lang->product->type;?></strong></div>
          <div class='col w-p15'><strong><?php echo $lang->product->status;?></strong></div>
        </div>
      </div>
      <table class='table table-data table-condensed'>
        <?php foreach($productList as $product):?>
        <tr data-url='<?php echo $this->createLink('crm.product', 'view', "productID=$product->id"); ?>'>
          <td class='w-p50'><?php echo $product->name;?></td>
          <td class='w-p20'><?php echo zget($productCategories, $product->category);?></td>
          <td class='w-p15'><?php echo zget($lang->product->typeList, $product->type);?></td>
          <td class='w-p15'><?php echo zget($lang->product->statusList, $product->status);?></td>
        </tr>
        <?php endforeach;?>
      </table>
    </div>
    <?php endif;?>

    <div class='panel'>
      <div class='panel-heading'><strong><?php echo $lang->customer->address;?></strong></div>
      <table class='table table-data table-condensed'>
        <?php foreach($addresses as $address):?>
        <tr>
          <td><?php echo $address->title . $lang->colon . $address->fullLocation;?></td>
        </tr>
        <?php endforeach;?>
      </table>
    </div>
  </div>
</div>
<?php include $app->getAppRoot() . 'common/view/footer.html.php';?>
