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
 * A validator that checks a value or an array of values (if multi-choice) are
 * valid records from a Propel model
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage validator
 */
class sfMinaclPropelChoiceValidator extends phValidatorCommon
{
	/**
	 * If an invalid choice is made (i.e. the record does not exist) then
	 * this will be the error
	 * @var int
	 */
	const INVALID = 1;

	/**
	 * Are we validating multiple choices or just a single choice?
	 * @var boolean true if we are validating multiple choices
	 */
	protected $_multiple = false;

	/**
	 * The model class we are checking valid choices for
	 * @var string
	 */
	protected $_model = null;

	/**
	 * Optional named connection
	 * @var string
	 */
	protected $_connection = null;
	
	/**
	 * Arrays are ok in this validator
	 * @var boolean
	 */
	protected $_allowArrays = true;
	
	/**
	 * @param string $model The Propel model class we are checking valid choices on
	 * @param array $errors
	 */
	public function __construct($model, array $errors = array())
	{
		parent::__construct($errors);

		if(!class_exists($model) || !constant($model . '::PEER'))
		{
			throw new phValidatorException("The class '{$model}' does not exist or is not a valid Propel model class");
		}
		
		$this->_model = $model;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/form/validator/phValidator::doValidate()
	 */
	protected function doValidate($value, phValidatable $errors)
	{
		if($this->_multiple && !is_array($value))
		{
			throw new phValidatorException('Non-array value received where expecting array as multiple choice is true');
		}
		
		$col = $this->getPrimaryKeyColumn();
		$criteria = new Criteria();
		$criteria->add($col, $value, $this->_multiple ? Criteria::IN : Criteria::EQUAL);
		
		/*
		 * compare the count from the database and the passed value(s).  If they are
		 * different then there is an invalid choice.
		 */
		$count = is_array($value) ? sizeof($value) : 1;
		$dbCount = call_user_func(array(constant($this->_model.'::PEER'), 'doCount'), $criteria, false, $this->_connection);
		
		if($count != $dbCount)
		{
			$errors->addError($this->getError(self::INVALID));
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * If true then we expect and array of values if false then just a single value
	 * @param boolean $multiple
	 * @return sfMinaclPropelChoiceValidator
	 */
	public function setMultiple($multiple)
	{
		$this->_multiple = $multiple;
		return $this;
	}

	/**
	 * Optionally set the propel connection to use
	 * @param string $name
	 * @return sfMinaclPropelChoiceValidator
	 */
	public function setConnection($name)
	{
		$this->_connection = $name;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/form/validator/phValidatorCommon::getValidErrorCodes()
	 */
	protected function getValidErrorCodes()
	{
		return array(self::INVALID);
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/form/validator/phValidatorCommon::getDefaultErrorMessages()
	 */
	protected function getDefaultErrorMessages()
	{
		return array(self::INVALID => 'The choice is invalid');
	}

	/**
	 * Gets the primary key column for the model
	 * @return string the column to filter on
	 */
	protected function getPrimaryKeyColumn()
	{
		$map = call_user_func(array(constant($this->_model.'::PEER'), 'getTableMap'));
		foreach ($map->getColumns() as $column)
		{
			if ($column->isPrimaryKey())
			{
				$columnName = $column->getPhpName();
				break;
			}
		}
		$from = BasePeer::TYPE_PHPNAME;
		
		return call_user_func(array(constant($this->_model.'::PEER'), 'translateFieldName'), $columnName, $from, BasePeer::TYPE_COLNAME);
	}
}