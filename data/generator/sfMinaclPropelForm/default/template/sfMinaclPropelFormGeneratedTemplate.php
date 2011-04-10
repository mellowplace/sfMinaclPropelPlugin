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
	public function postInitialize()
	{
		parent::postInitialize();
<?php 
foreach ($this->table->getColumns() as $column): 
	$validators = $this->getValidatorClassesAndChains($column);
	
	
	foreach($validators as $num => $val):
		$valName = '$' . $this->translateColumnName($column) . ($num + 1);
?>
		<?php echo $valName ?> = new <?php echo $val['class'] ?>();
<?php
		/*
		 * if the validator has an option chain, output it here
		 */ 
		if(isset($val['chain'])): 
?>
		<?php echo $valName . $validator['chain'] ?>;
<?php
		endif;
	endforeach;
	/*
	 * if there is more than 1 validator for the field, we'll have to combine them
	 * with validator logic
	 */
	if(sizeof($validators) > 1):
?>
		$this-><?php echo $this->translateColumnName($column) ?>->setValidator(
			new phValidatorLogic(<?php echo $valName ?>)
		)->
<?php 
		foreach($validators as $num => $val):
			$valName = '$' . $this->translateColumnName($column) . ($num + 1);
?>
		and_(<?php echo $valName ?>)<?php $num < sizeof($validators) ? '->' : ';' ?>
<?php 
		endforeach;
	else:
		$valName = '$' . $this->translateColumnName($column) . '1';
?>
		$this-><?php echo $this->translateColumnName($column) ?>->setValidator(<?php echo $valName ?>);
<?php
	endif;
endforeach;
foreach ($this->getManyToManyTables() as $tables):
	$dataName = '$' . $this->underscore($tables['middleTable']->getClassname()) . 'List';
?>
		<?php echo $dataName ?>Validator = new sfMinaclPropelChoiceValidator();
		<?php echo $dataName ?>Validator->
			setModel(<?php echo $tables['relatedTable']->getClassname() ?>)->
			setMultiple(true);
		<?php echo $dataName ?>->setValidator(<?php echo $dataName ?>Validator);
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

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    if (isset($this->widgetSchema['<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list']))
    {
      $values = array();
      foreach ($this->object->get<?php echo $tables['middleTable']->getPhpName() ?>s() as $obj)
      {
        $values[] = $obj->get<?php echo $tables['relatedColumn']->getPhpName() ?>();
      }

      $this->setDefault('<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list', $values);
    }

<?php endforeach; ?>
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);

<?php foreach ($this->getManyToManyTables() as $tables): ?>
    $this->save<?php echo $tables['middleTable']->getPhpName() ?>List($con);
<?php endforeach; ?>
  }

<?php foreach ($this->getManyToManyTables() as $tables): ?>
  public function save<?php echo $tables['middleTable']->getPhpName() ?>List($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list']))
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

    $values = $this->getValue('<?php echo $this->underscore($tables['middleTable']->getClassname()) ?>_list');
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