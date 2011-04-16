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
 * Symfony task that generates Minacl forms based on the model schema
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage lib.task
 */
class sfMinaclBuildFormsTask extends sfPropelBuildFormsTask
{

	/**
	 * @see sfTask
	 */
	protected function configure()
	{
		$this->addOptions(array(
		new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
		new sfCommandOption('model-dir-name', null, sfCommandOption::PARAMETER_REQUIRED, 'The model dir name', 'model'),
		new sfCommandOption('form-dir-name', null, sfCommandOption::PARAMETER_REQUIRED, 'The form dir name', 'form'),
		new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
		new sfCommandOption('generator-class', null, sfCommandOption::PARAMETER_REQUIRED, 'The generator class', 'sfMinaclPropelFormGenerator'),
		));

		$this->namespace = 'minacl';
		$this->name = 'build-forms';
		$this->briefDescription = 'Creates form classes for the current model';

		$this->detailedDescription = <<<EOF
The [minacl:build-forms|INFO] task creates form classes from the schema:

  [./symfony propel:build-forms|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

The task uses the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [--connection|COMMENT] option:

  [./symfony minacl:build-forms --connection="name"|INFO]

The model form classes files are created in [lib/form/minacl|COMMENT].

This task never overrides custom classes in [lib/form/minacl|COMMENT].
It only replaces base classes generated in [lib/form/minacl/base|COMMENT].
EOF;
	}

}