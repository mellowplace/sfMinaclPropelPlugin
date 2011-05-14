[?php
/*
 * <?php echo $this->table->getClassname() ?> form template
 */
?]
<?php
$primaryKeyColumn = null;

foreach ($this->table->getColumns() as $column):
	/*
	 * if the pri key is an auto incrementing one it must be a hidden field we'll output 
	 * this outside of the dl otherwise it'll cause the xhtml to be invalid
	 */
	if($column->isPrimaryKey() && $this->table->isUseIdGenerator())
	{
		$primaryKeyColumn = $column;
		continue;
	}
	
	$name = $this->translateColumnName($column);
?>
<tr>
<?php 
	/*
	 * for dates and times the element will be a subform
	 * also for foreign key relations (it draws a list)
	 */
	if($this->isSubForm($column)):
		/*
		 * find out who we attach the label to
		 */
		$labelName = $this->getLabelId($column);
?>
	<th><label for="[?php echo $this->id('<?php echo $labelName ?>') ?]"><?php echo $this->label($column) ?></label></th>
	<td>
		[?php echo $this->form('<?php echo $name ?>') ?]
		[?php echo $this->errorList('<?php echo $name ?>'); ?]
	</td>
<?php 
	else:
?>
	<th><label for="[?php echo $this->id('<?php echo $name ?>') ?]"><?php echo $this->label($column) ?></label></th>
	<td>
<?php 
		switch($column->getType()):
			case PropelColumnTypes::BOOLEAN:
?>
		<input type="checkbox" 
			id="[?php echo $this->id('<?php echo $name ?>') ?]"
			name="[?php echo $this->name('<?php echo $name ?>') ?]" 
			value="1" 
		/>
<?php
				break;
			case PropelColumnTypes::CLOB:
			case PropelColumnTypes::LONGVARCHAR:
?>
		<textarea 
			id="[?php echo $this->id('<?php echo $name ?>') ?]"
			name="[?php echo $this->name('<?php echo $name ?>') ?]"></textarea>
<?php 
				break;
			default:
?>
		<input type="text" 
			id="[?php echo $this->id('<?php echo $name ?>') ?]"
			name="[?php echo $this->name('<?php echo $name ?>') ?]" 
			value=""
			maxlength="<?php echo $column->getSize() ?>"
		/>
<?php
		endswitch;
?>
		[?php echo $this->errorList('<?php echo $name ?>'); ?]
	</td>
<?php 
	endif;
?>
</tr>
<?php 
endforeach; // columns
/*
 * Many 2 many relationships (multi choice subforms)
 */
$tables = $this->getManyToManyTables();
foreach($tables as $table):
	$name = $this->underscore($table['middleTable']->getClassname()) . '_list';
?>
<tr>
	<th><label for="[?php echo $this->id('<?php echo $name ?>.list') ?]"><?php echo $this->label($table['relatedColumn']) ?>s</label></th>
	<td>[?php echo $this->form('<?php echo $name ?>') ?]</td>
</tr>
<?php 
endforeach; // many 2 many tables
?>
<?php 
if($primaryKeyColumn): 
	$name = $this->translateColumnName($primaryKeyColumn);
?>
<tr style="display: none;">
	<td colspan="2">
		<input 	type="hidden" 
			id="[?php echo $this->id('<?php echo $name ?>') ?]"
			name="[?php echo $this->name('<?php echo $name ?>') ?]" />
		[?php echo $this->errorList('<?php echo $name ?>'); ?]
	</td>
</tr>
<?php
endif;
?>