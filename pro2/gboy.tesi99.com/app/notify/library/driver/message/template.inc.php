<p class="notice">您正在编辑 <em id="content-label" class="text-main">loading</em> 通知模板</p>

	<?php foreach($hooks as $tk=>$tv):?>

        <div id='edit_<?php echo $tk?>' style="display:none;overflow:hidden;">

            <?php echo \form\Form::input('text', "{$tk}[title]", "{$template[template][$tk]['title']}", '短信标题：', '标题支持标签,请从编辑器中复制');?>


            <div class="col-sm-12">
                <?php echo \form\Form::editor("{$tk}[content]", "{$template[template][$tk]['content']}",'','','',"{$tk}",true); ?>
            </div>


        </div>


    <?php endforeach;?>