<?php include '../../common/view/header.html.php';?>
<div class='panel'>
  <div class='panel-heading'>
    <strong><i class="icon-plus"></i> <?php echo $lang->customer->create;?></strong>
  </div>
  <div class='panel-body'>
    <form method='post' id='ajaxForm'>
      <table class='table table-form'>
        <tr>
          <th><?php echo $lang->customer->name;?></th>
          <td><?php echo html::input('name', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->type;?></th>
          <td><?php echo html::select("type", $lang->customer->typeList, '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->size;?></th>
          <td><?php echo html::select('size', $lang->customer->sizeList, '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->industry;?></th>
          <td><?php echo html::input('industry', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->area;?></th>
          <td><?php echo html::input('area', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->status;?></th>
          <td><?php echo html::select("status", $lang->customer->statusList, '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->level;?></th>
          <td><?php echo html::input('level', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->site;?></th>
          <td><?php echo html::input('site', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->weibo;?></th>
          <td><?php echo html::input('weibo', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->weixin;?></th>
          <td><?php echo html::input('weixin', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->desc;?></th>
          <td><?php echo html::textarea('desc', '', "rows='2' class='form-control'");?></td>
        </tr>
        <tr>
          <th><?php echo $lang->customer->referType;?></th>
          <td><?php echo html::input('referType', '', "class='form-control'");?></td>
        </tr>
        <tr>
          <th></th>
          <td><?php echo html::submitButton();?></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php include '../../common/view/footer.html.php';?>
