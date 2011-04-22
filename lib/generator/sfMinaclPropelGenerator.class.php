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
 * Generator class for generating propel modules that are compatible with
 * Minacl
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage lib.generator
 */
class sfMinaclPropelGenerator extends sfPropelGenerator
{
	public function initialize(sfGeneratorManager $generatorManager)
	{
		parent::initialize($generatorManager);
		
		$this->setGeneratorClass('sfMinaclPropelModule');
	}
	
	/**
	 * Gets the form template name to use with this module
	 */
	public function getFormTemplate()
	{
		return strtolower($this->getTableMap()->getClassname());
	}
	
	/**
	 * @return phForm
	 */
	public function getFormObject()
	{
		$formClass = $this->getTableMap()->getClassname();
		return new $formClass($this->getFormTemplate());
	}
	
	/**
	 * Overridden to alter the first character to lower case
	 * @see generator/sfModelGenerator::getSingularName()
	 */
	public function getSingularName()
	{
		$name = parent::getSingularName();
		$name{0} = strtolower($name{0});
		return $name;
	}
}