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
 * Base form class that all Symfony generated Minacl forms for Propel
 * extend
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 */
abstract class sfFormMinaclPropel extends sfFormMinacl
{
	public function __construct($name, $template, $object = null)
	{
		parent::__construct($name, $template, $object);
		
		$this->updateDefaultsFromObject();
	}
	
	/**
	 * Returns the default connection for the current model.
	 *
	 * @return Connection A database connection
	 */
	public function getConnection()
	{
		return Propel::getConnection(constant(sprintf('%s::DATABASE_NAME', get_class($this->object->getPeer()))));
	}
	
	/**
	 * Sets the form defaults from the object
	 */
	protected function updateDefaultsFromObject()
	{
		$defaults = $this->object->toArray(BasePeer::TYPE_FIELDNAME);
		$view = $this->getView();
		/*
		 * for each of the objects values, bind the value to the corresponding
		 * data type
		 */
		foreach($defaults as $key=>$value)
		{
			try
			{
				$view->getData($key)->bind($value);
			}
			catch(phFormException $e)
			{
				// will be thrown if the user has removed the data item from the form
			}
		}
	}
}