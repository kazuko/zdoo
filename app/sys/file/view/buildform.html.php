<?php
/**
 * The buildform view file of file module of RanZhi.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     file 
 * @version     $Id: buildform.html.php 4029 2016-08-26 06:50:41Z liugang $
 * @link        http://www.ranzhi.org
 */
?>
<style>
.fileBox {margin-bottom: 10px; width: 100%}
table.fileBox td {padding: 0!important}
.fileBox .input-control > input[type='file'] {width: 100%; height: 100%; height: 26px; line-height: 26px; border: none; position: relative;}
.fileBox td .btn {border-radius: 0; border-left: none}
.file-wrapper.form-control {border-right: 0; border-radius: 0;}
.file-name .form-control{border-radius: 0;}
</style>
<?php if(commonModel::hasPriv('file', 'upload')):?>
<?php if(!$writeable):?>
<h5 class='text-danger text-left'> <?php echo $this->lang->file->errorUnwritable;?> </h5>
<?php else:?>
<div id='fileform'>
  <?php 
  /* Define the html code of a file row. */
  $fileRow = <<<EOT
  <table class='fileBox' id='fileBox\$i'>
    <tr>
      <td class='w-p45'><div class='form-control file-wrapper'><input type='file' name='files[]' class='fileControl'  tabindex='-1' onchange='checkSize(this)'/></div></td>
      <td class='file-name'><input type='text' name='labels[]' class='form-control' placeholder='{$lang->file->label}' tabindex='-1' /></td>
      <td class='w-30px'><a href='javascript:void(0);' onclick='addFile(this)' class='btn btn-block'><i class='icon-plus'></i></a></td>
      <td class='w-30px'><a href='javascript:void(0);' onclick='delFile(this)' class='btn btn-block'><i class='icon-remove'></i></a></td>
    </tr>
  </table>
EOT;
  for($i = 1; $i <= $fileCount; $i ++) echo str_replace('$i', $i, $fileRow);
  printf($lang->file->sizeLimit, $this->config->file->maxSize / 1024 / 1024);
?>
</div>
<?php endif;?>
<?php endif;?>

<script language='javascript'>
/**
 * Check file size.
 * 
 * @param  obj $obj 
 * @access public
 * @return void
 */
function checkSize(obj)
{
    if(typeof($(obj)[0].files) != 'undefined')
    {
        var maxUploadInfo = '<?php echo strtoupper(ini_get('upload_max_filesize'));?>';
        var sizeType = {'K': 1024, 'M': 1024 * 1024, 'G': 1024 * 1024 * 1024};
        var unit = maxUploadInfo.replace(/\d+/, '');
        var maxUploadSize = maxUploadInfo.replace(unit,'') * sizeType[unit];
        var fileSize = 0;
        $(obj).parents('#fileform').find(':file').each(function()
        {
            if($(this).val()) fileSize += $(this)[0].files[0].size;
        });
        if(fileSize > maxUploadSize)
        {
            alert('<?php echo $lang->file->errorFileSize?>');
            $(obj).parents('#fileform').find(':file').each(function()
            {
                if($(this).val()) $(this).val('');
            });
        }
    }
}

/**
 * Show the upload max filesize of config.  
 */
function maxFilesize(){return "(<?php printf($lang->file->maxUploadSize, $this->config->file->maxSize / 1024 /1024 . 'M');?>)";}

/**
 * Add a file input control.
 * 
 * @param  object $clickedButton 
 * @access public
 * @return void
 */
function addFile(clickedButton)
{
    fileRow = <?php echo json_encode($fileRow);?>;
    fileRow = fileRow.replace('$i', $('.fileID').size() + 1);
    $(clickedButton).closest('.fileBox').after(fileRow);

    updateID();
}

/**
 * Delete a file input control.
 * 
 * @param  object $clickedButton 
 * @access public
 * @return void
 */
function delFile(clickedButton)
{
    if($('.fileBox').size() == 1) return;
    $(clickedButton).closest('.fileBox').remove();
    updateID();
}

/**
 * Update the file id labels.
 * 
 * @access public
 * @return void
 */
function updateID()
{
    i = 1;
    $('.fileID').each(function(){$(this).html(i ++)});
}
</script>
