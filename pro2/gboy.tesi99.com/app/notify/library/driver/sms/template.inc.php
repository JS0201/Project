<p class="notice">您正在编辑 <em id="content-label" class="blue">loading</em> 通知模板</span>
	<span class="pull-right">您的短信剩余条数：<em class="text-main">0</em></span>
</p>

<?php foreach($hooks as $tk=>$tv):?>
<div id='edit_<?php echo $tk?>' style="display: none;">
	<div class="form-group">

		<div class="con">
			<?php foreach($template[$tk] as $sk => $sv):?>


			<label style="padding-left: 30px;padding-top: 10px;">
				<?php
				$checked = $sv['id'] == $template['template'][$tk]['template_id'] ? 'checked=checked' : '' ;
				?>
				<input class="ace" type="radio"  value="<?php echo $sv['id']?>" name="<?php echo $tk?>[template_id]" <?php echo $checked?>> <span class="lbl">&nbsp;<?php echo $sv['message']?></span>
			</label><br>
			<?php endforeach;?>
		</div>
	</div>
</div>
<?php endforeach;?>