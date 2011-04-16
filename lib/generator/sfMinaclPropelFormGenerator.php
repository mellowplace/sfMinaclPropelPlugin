<?php
/*
 * Minacl Project: An HTML forms library for PHP
 *          https://github.com/mellowplace/PHP-HTML-Driven-Forms/
 * Copyright (c) 2010, 2011 Rob Graham
 *
 * This file is part of Minacl.
 *
 * Minacl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Minacl is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with Minacl.  If not, see
 * <http://www.gnu.org/licenses/>.
 */

/**
 * Based on the sfPropelFormGenerator, this class helps with the generation
 * of forms based on the model schema
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package phform
 */
class sfMinaclPropelFormGenerator extends sfPropelFormGenerator
{
	public function initialize(sfGeneratorManager $generatorManager)
	{
		parent::initialize($generatorManager);

		$this->setGeneratorClass('sfMinaclPropelForm');
	}


	/**
	 * Generates classes and templates in cache.
	 *
	 * @param array $params The parameters
	 *
	 * @return string The data to put in configuration cache
	 */
	public function generate($params = array())
	{
		$this->params = $params;

		if (!isset($this->params['connection']))
		{
			throw new sfParseException('You must specify a "connection" parameter.');
		}

		if (!isset($this->params['model_dir_name']))
		{
			$this->params['model_dir_name'] = 'model';
		}

		if (!isset($this->params['form_dir_name']))
		{
			$this->params['form_dir_name'] = 'form';
		}

		$this->dbMap = Propel::getDatabaseMap($this->params['connection']);

		$this->loadBuilders();
		
		// create a form class for every Propel class
		foreach ($this->dbMap->getTables() as $tableName => $table)
		{
			$behaviors = $table->getBehaviors();
			if (isset($behaviors['symfony']['form']) && 'false' === $behaviors['symfony']['form'])
			{
				continue;
			}

			$this->table = $table;

			// find the package to store forms in the same directory as the model classes
			$packages = explode('.', constant(constant($table->getClassname().'::PEER').'::CLASS_DEFAULT'));
			array_pop($packages);
			if (false === $pos = array_search($this->params['model_dir_name'], $packages))
			{
				throw new InvalidArgumentException(sprintf('Unable to find the model dir name (%s) in the package %s.', $this->params['model_dir_name'], constant(constant($table->getClassname().'::PEER').'::CLASS_DEFAULT')));
			}
			$packages[$pos] = $this->params['form_dir_name'];
			$baseDir = sfConfig::get('sf_root_dir').'/'.implode(DIRECTORY_SEPARATOR, $packages).'/minacl';
			/*
			 * create the base directory for the form classes if it doesn't exist
			 * already
			 */
			if (!is_dir($baseDir.'/base'))
			{
				mkdir($baseDir.'/base', 0777, true);
			}
			/*
			 * create the directory for the form templates if it doesn't exist
			 * already
			 */
			if (!is_dir($baseDir.'/view'))
			{
				mkdir($baseDir.'/view', 0777, true);
			}
			/*
			 * write the BaseFormMinaclPropel class if it doesn't exist
			 */
			$baseForm = $baseDir . '/BaseFormMinaclPropel.class.php';
			if (!file_exists($baseForm))
			{
				file_put_contents($baseForm, $this->evalTemplate('sfMinaclPropelFormBase.php'));
			}
			/*
			 * write the Minacl form class ( Base[Model]Form.class.php ) 
			 */
			file_put_contents($baseDir.'/base/Base'.$table->getClassname().'Form.class.php', $this->evalTemplate('sfMinaclPropelFormGeneratedTemplate.php'));
			/*
			 * if none has been created already, write the extending class
			 * ( [Model]Form.class.php )
			 */
			if (!file_exists($classFile = $baseDir.'/'.$table->getClassname().'Form.class.php'))
			{
				file_put_contents($classFile, $this->evalTemplate('sfMinaclPropelFormTemplate.php'));
			}
			/*
			 * write the form template
			 */
			$formTemplate = $baseDir.'/view/'.strtolower($table->getClassname()).'.php';
			if (!file_exists($formTemplate))
			{
				file_put_contents($formTemplate, $this->evalTemplate('sfMinaclPropelFormViewTemplate.php'));
			}
		}
	}

	/**
	 * Gets the PHP code needed to set the validators for the column
	 *
	 * @param ColumnMap $column
	 * @return array with up to 3 elements with the keys "typeValidator", "typeValidatorChain" (optional) & "requiredValidator" (optional)
	 */
	public function getValidatorClassesAndChains(ColumnMap $column)
	{
		$name = null;
		$optionChain = '';

		switch ($column->getType())
		{
			case PropelColumnTypes::BOOLEAN:
				$name = 'Boolean';
				break;
			case PropelColumnTypes::CLOB:
			case PropelColumnTypes::CHAR:
			case PropelColumnTypes::VARCHAR:
			case PropelColumnTypes::LONGVARCHAR:
				$name = 'StringLength';
				if ($column->getSize())
				{
					$optionChain = "->max({$column->getSize()})";
				}
				break;
			case PropelColumnTypes::DOUBLE:
			case PropelColumnTypes::FLOAT:
			case PropelColumnTypes::NUMERIC:
			case PropelColumnTypes::DECIMAL:
			case PropelColumnTypes::REAL:
				$name = 'Numeric';
				break;
			case PropelColumnTypes::TINYINT:
				$name = 'Numeric';
				$optionChain = "->min(-128)->max(127)";
				break;
			case PropelColumnTypes::SMALLINT:
				$name = 'Numeric';
				$optionChain = "->min(-32768)->max(32767)";
				break;
			case PropelColumnTypes::INTEGER:
				$name = 'Numeric';
				$optionChain = "->min(-2147483648)->max(2147483647)";
				break;
			case PropelColumnTypes::BIGINT:
				$name = 'Numeric';
				$optionChain = "->min(-9223372036854775808)->max(9223372036854775807)";
				break;
			case PropelColumnTypes::DATE:
				$name = 'Date';
				break;
			case PropelColumnTypes::TIME:
				$name = 'Time';
				break;
			case PropelColumnTypes::TIMESTAMP:
				$name = 'DateTime';
				break;
		}

		$validators = array();

		if($name)
		{
			$typeVal = array ('class' => "ph{$name}Validator");

			if($optionChain)
			{
				$typeVal['chain'] = $optionChain;
			}

			$validators[] = $typeVal;
		}

		/*
		 * if the col is not null then it's required
		 */
		if ($column->isNotNull() && !$column->isPrimaryKey())
		{
			$validators[] = array('class' => 'phRequiredValidator');
		}

		/*
		 * if the column is a foreign key we'll want to add
		 * the propel choice validator
		 */
		if ($column->isForeignKey())
		{
			$foreignTable = $this->getForeignTable($column);
			$validators[] = array(
				'class' => 'sfMinaclPropelChoiceValidator',
				'chain' => "->setMultiple(false)->setModel('{$foreignTable->getClassname()}')"
			);
		}

		/*
		 * if the column is unique we'll want to add
		 * the propel unique validator
		 */
		$uniques = $this->getUniqueColumnNames();
		foreach($uniques as $u)
		{
			if($u == $column->getName())
			{
				$validators[] = array(
					'class' => 'sfMinaclPropelUniqueValidator',
					'chain' => "->setModel('{$this->table->getClassname()}')"
				);
			}
		}

		return $validators;
	}
	
	/**
	 * Gets a friendly label name from a column
	 * @param ColumnMap $column
	 * @return string
	 */
	public function label(ColumnMap $column)
	{
		$name = $this->underscore($this->translateColumnName($column));
		$name = str_replace('_', ' ', $name);
		return ucfirst($name);
	}
	
	/**
	 * If the column needs to be a subform (useful for more complex things like lists and
	 * dates) this will return true
	 * 
	 * @param ColumnMap $column
	 * @return boolean
	 */
	public function isSubForm(ColumnMap $column)
	{
		/*
		 * either the column must be one of these types
		 * or a foreign key to be a sub form
		 */
		switch($column->getType())
		{
			case PropelColumnTypes::DATE:
			case PropelColumnTypes::TIME:
			case PropelColumnTypes::TIMESTAMP:
				return true;
		}
		
		return $column->isForeignKey();
	}
	
	/**
	 * subforms have different id's and may have more than one field, we can use dot names
	 * to refer to them from a parent form in Minacl
	 */
	public function getLabelId(ColumnMap $column)
	{
		$name = $this->translateColumnName($column);
		switch($column->getType())
		{
			case PropelColumnTypes::DATE:
			case PropelColumnTypes::TIMESTAMP:
				$name .= '.date';
				break;
			case PropelColumnTypes::TIME:
				$name .= '.time';
				break;
		}
		
		if($column->isForeignKey())
		{
			$name .= '.list';
		}
		
		return $name;
	}
}