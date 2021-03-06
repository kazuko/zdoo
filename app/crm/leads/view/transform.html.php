<?php
/**
 * The transform file of leads module of RanZhi.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     leads
 * @version     $Id$
 * @link        http://www.ranzhi.org
 */
?>
<?php include '../../../sys/common/view/header.modal.html.php';?>
<?php include '../../../sys/common/view/chosen.html.php';?>
<form method='post' id='contactForm' action='<?php echo inlink('transform', "contactID={$contact->id}&status=normal")?>'>
  <table class='table table-form'>
    <tr>
      <th class='w-80px'><?php echo $lang->contact->customer;?></th>
      <td>
        <div class='input-group'>
          <?php echo html::input('name', $contact->company ? $contact->company : '', "class='form-control'");?>
          <?php echo html::select('customer', $customers, $customerID, "class='form-control chosen' style='display:none' data-no_results_text='" . $lang->searchMore . "'");?>
          <span class='input-group-addon'>
            <label class='checkbox-inline'>
              <input type='checkbox' name='selectCustomer' id='selectCustomer' <?php if($customerID) echo 'checked';?> value='1'/><?php echo $lang->contact->selectCustomer;?>
            </label>
          </span>
        </div>
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        <?php echo html::submitButton();?>
        <div id='duplicateError'></div>
      </td>
    </tr>
  </table>
</form>
<div class='errorMessage hide'>
  <div class='alert alert-danger alert-dismissable'>
    <button aria-hidden='true' data-dismiss='alert' class='close' type='button'>×</button>
    <button type='submit' class='btn btn-default' id='continueSubmit'><?php echo $lang->continueSave;?></button>
  </div>
</div>
<?php include '../../../sys/common/view/footer.modal.html.php';?>
