[?php
/**
 * <?php echo $this->table->getClassname() ?> form base class.
 *
 * @method <?php echo $this->table->getClassname() ?> getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 */
abstract class Base<?php echo $this->table->getClassname() ?>Form extends BaseFormMinaclPropel
{
	public function preInitialize()
	{
		parent::preInitialize();
<?php
/*
 * Find foreign keys and many2many tables.  We need to have subforms for these
 * that are choosers.
 */
foreach ($this->table->getColumns() as $column):
	if($column->isForeignKey()): 
		$formName = $this->translateColumnName($column);
		$foreignTable = $this->getForeignTable($column);
		$modelClass = $foreignTable->getClassname();
?>
		$<?php echo $formName ?> = new sfMinaclPropelChooser('<?php echo $formName ?>', 'propelSingleSelect', '<?php echo $modelClass ?>');
		$this->addForm($<?php echo $formName ?>);
<?php
	endif;
endforeach;
foreach ($this->getManyToManyTables() as $tables):
	$dataName = $this->getListName($tables);
	$modelClass = $tables['relatedTable']->getClassname();
?>
		$<?php echo $dataName ?> = new sfMinaclPropelChooser('<?php echo $dataName ?>', 'propelMultiSelect', '<?php echo $modelClass ?>');
		$this->addForm($<?php echo $dataName ?>);
<?php 
endforeach;
?>
	}
	
	public function postInitialize()
	{
		parent::postInitialize();
<?php 
foreach ($this->table->getColumns() as $column): 
	$validators = $this->getValidatorClassesAndChains($column);
?>
		/*
		 * Validators for the <?php echo $column->getName() ?> column
		 */
		if($this->getView()->hasData('<?php echo $this->translateColumnName($column) ?>'))
		{
<?php 
	foreach($validators as $num => $val):
		$valName = '$' . $this->translateColumnName($column) . ($num + 1);
?>
			<?php echo $valName ?> = new <?php echo $val['class'] ?>(<?php echo isset($val['arguments']) ? $val['arguments'] : '' ?>);
<?php
		/*
		 * if the validator has an option chain, output it here
		 */ 
		if(isset($val['chain'])): 
?>
			<?php echo $valName . $val['chain'] ?>;
<?php
		endif;
	endforeach;
	/*
	 * if there is more than 1 validator for the field, we'll have to combine them
	 * with validator logic
	 */
	if(sizeof($validators) > 1):
?>
			$this-><?php echo $this->translateColumnName($column) ?>->setValidator(new phValidatorLogic(<?php echo '$' . $this->translateColumnName($column) . '1' ?>))->
<?php 
		for($x=1; $x<sizeof($validators); $x++):
			$val = $validators[$x];
			$valName = '$' . $this->translateColumnName($column) . ($x +1);
?>
				and_(<?php echo $valName ?>)<?php echo ($x+1) < sizeof($validators) ? '->' : ';' ?>
		
<?php 
		endfor;
	else:
			$valName = '$' . $this->translateColumnName($column) . '1';
?>
			$this-><?php echo $this->translateColumnName($column) ?>->setValidator(<?php echo $valName ?>);
<?php
	endif;
?>
		}
<?php 
endforeach;
foreach ($this->getManyToManyTables() as $tables):
	$dataName = $this->underscore($tables['middleTable']->getClassname()) . '_list';
?>
		/*
		 * Validators for the <?php echo $tables['middleTable']->getClassname() ?> many 2 many table
		 */
		if($this->getView()->hasData('<?php echo $dataName ?>'))
		{
			$<?php echo $dataName ?> = new sfMinaclPropelChoiceValidator('<?php echo $tables['relatedTable']->getClassname() ?>');
			$<?php echo $dataName ?>->setMultiple(true);
			$this-><?php echo $dataName ?>->setValidator($<?php echo $dataName ?>);
		}
<?php 
endforeach;
?>
	}

	public function getModelName()
	{
		return '<?php echo $this->table->getClassname() ?>';
	}

<?php if ($this->isI18n()): ?>
  public function getI18nModelName()
  {
    return '<?php echo $this->getI18nModel() ?>';
  }

  public function getI18nFormClass()
  {
    return '<?php echo $this->getI18nModel() ?>Form';
  }
<?php endif; ?>

<?php if ($this->getManyToManyTables()): ?>
  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

<?php 
	foreach ($this->getManyToManyTables() as $tables):
		$dataName = $this->getListName($tables); 
?>
    if ($this->getView()->hasData('<?php echo $dataName; ?>'))
    {
      $values = array();
      foreach ($this->object->get<?php echo $tables['middleTable']->getPhpName() ?>s() as $obj)
      {
        $values[] = $obj->get<?php echo $tables['relatedColumn']->getPhpName() ?>();
      }

      $this-><?php echo $dataName ?>->bind($values);
    }

<?php endforeach; ?>
  }

  protected function saveObject($con)
  {
    $object = parent::saveObject($con);

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    $this->save<?php echo $tables['middleTable']->getPhpName() ?>List($con);
<?php endforeach; ?>
    
    return $object;
  }

<?php foreach ($this->getManyToManyTables() as $tables): ?>
  public function save<?php echo $tables['middleTable']->getPhpName() ?>List($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!$this->getView()->hasData('<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list'))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $c = new Criteria();
    $c->add(<?php echo constant($tables['middleTable']->getClassname().'::PEER') ?>::<?php echo strtoupper($tables['column']->getName()) ?>, $this->object->getPrimaryKey());
    <?php echo constant($tables['middleTable']->getClassname().'::PEER') ?>::doDelete($c, $con);

    $values = $this-><?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list->getValue();
    if (is_array($values))
    {
      foreach ($values as $value)
      {
        $obj = new <?php echo $tables['middleTable']->getClassname() ?>();
        $obj->set<?php echo $tables['column']->getPhpName() ?>($this->object->getPrimaryKey());
        $obj->set<?php echo $tables['relatedColumn']->getPhpName() ?>($value);
        $obj->save();
      }
    }
  }

<?php endforeach; ?>
<?php endif; ?>
}