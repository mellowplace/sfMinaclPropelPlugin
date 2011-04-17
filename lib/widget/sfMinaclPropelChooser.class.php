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
 * A Minacl subform that provides a chooser for Propel models
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage lib.widget
 */
class sfMinaclPropelChooser extends phForm
{
	/**
	 * The propel model class we are dealing with
	 * @var string
	 */
	protected $_modelClass = null;
	
	public function __construct($name, $template, $modelClass)
	{
		$this->_modelClass = $modelClass;
		
		parent::__construct($name, $template);
	}
	
	public function preInitialize()
	{
		$peer = constant($this->_modelClass . '::PEER');
		if(!$peer)
		{
			throw new phFormException("The class '{$this->_modelClass}' does not exist or is not a valid Propel model");
		}
		
		$peerClass = new ReflectionClass($peer);
		$this->_view->list = $peerClass->getMethod('doSelect')->invoke(null, new Criteria());
	}
	
	/**
	 * @return array|int if multi-select an array of id's are returned, otherwise a single id is returned
	 */
	public function getValue()
	{
		return $this->list->getValue();
	}
	
	/**
	 * Gets the value of the primary key field for a Propel model object
	 * @param BaseObject $object
	 * @return mixed
	 */
	public function getPrimaryKeyValue(BaseObject $object)
	{
		$class = new ReflectionClass($object);
		$peerClass = $class->getConstant('PEER');
		
		$map = call_user_func(array($peerClass, 'getTableMap'));
		foreach ($map->getColumns() as $column)
		{
			if ($column->isPrimaryKey())
			{
				$columnName = $column->getPhpName();
				break;
			}
		}
		$from = BasePeer::TYPE_PHPNAME;
		
		return call_user_func(array($peerClass, 'translateFieldName'), $columnName, $from, BasePeer::TYPE_COLNAME);
	}
}