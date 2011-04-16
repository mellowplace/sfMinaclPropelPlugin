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
 * Generates a module for a model class
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage lib.task
 */
class sfMinaclGenerateModuleTask extends sfPropelGenerateModuleTask
{
	/**
	 * @see sfTask
	 */
	protected function configure()
	{
		parent::configure();

		$this->namespace = 'minacl';
		$this->name = 'generate-module';
		$this->briefDescription = 'Generates a Propel module (that is compatible with Minacl)';

		$this->detailedDescription = <<<EOF
The [minacl:generate-module|INFO] task generates a Propel module:

  [./symfony minacl:generate-module frontend article Article|INFO]

The task creates a [%module%|COMMENT] module in the [%application%|COMMENT] application
for the model class [%model%|COMMENT].

You can also create an empty module that inherits its actions and templates from
a runtime generated module in [%sf_app_cache_dir%/modules/auto%module%|COMMENT] by
using the [--generate-in-cache|COMMENT] option:

  [./symfony minacl:generate-module --generate-in-cache frontend article Article|INFO]

The generator can use a customized theme by using the [--theme|COMMENT] option:

  [./symfony minacl:generate-module --theme="custom" frontend article Article|INFO]

This way, you can create your very own module generator with your own conventions.

You can also change the default actions base class (default to sfActions) of
the generated modules:

  [./symfony minacl:generate-module --actions-base-class="ProjectActions" frontend article Article|INFO]
EOF;
	}

	protected function executeGenerate($arguments = array(), $options = array())
	{
		// generate module
		$tmpDir = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.md5(uniqid(rand(), true));
		$generatorManager = new sfGeneratorManager($this->configuration, $tmpDir);
		$generatorManager->generate('sfMinaclPropelGenerator', array(
      'model_class'           => $arguments['model'],
      'moduleName'            => $arguments['module'],
      'theme'                 => $options['theme'],
      'non_verbose_templates' => $options['non-verbose-templates'],
      'with_show'             => $options['with-show'],
      'singular'              => $options['singular'],
      'plural'                => $options['plural'],
      'route_prefix'          => $options['route-prefix'],
      'with_propel_route'     => $options['with-propel-route'],
      'actions_base_class'    => $options['actions-base-class'],
		));

		$moduleDir = sfConfig::get('sf_app_module_dir').'/'.$arguments['module'];

		// copy our generated module
		$this->getFilesystem()->mirror($tmpDir.DIRECTORY_SEPARATOR.'auto'.ucfirst($arguments['module']), $moduleDir, sfFinder::type('any'));

		if (!$options['with-show'])
		{
			$this->getFilesystem()->remove($moduleDir.'/templates/showSuccess.php');
		}

		// change module name
		$finder = sfFinder::type('file')->name('*.php');
		$this->getFilesystem()->replaceTokens($finder->in($moduleDir), '', '', array('auto'.ucfirst($arguments['module']) => $arguments['module']));

		// customize php and yml files
		$finder = sfFinder::type('file')->name('*.php', '*.yml');
		$this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $this->constants);

		// create basic test
		$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.'skeleton'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'actionsTest.php', sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php');

		// customize test file
		$this->getFilesystem()->replaceTokens(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php', '##', '##', $this->constants);

		// delete temp files
		$this->getFilesystem()->remove(sfFinder::type('any')->in($tmpDir));
	}

	protected function executeInit($arguments = array(), $options = array())
	{
		$moduleDir = sfConfig::get('sf_app_module_dir').'/'.$arguments['module'];

		// create basic application structure
		$finder = sfFinder::type('any')->discard('.sf');
		$dirs = $this->configuration->getGeneratorSkeletonDirs('sfMinaclPropelModule', $options['theme']);

		foreach ($dirs as $dir)
		{
			if (is_dir($dir))
			{
				$this->getFilesystem()->mirror($dir, $moduleDir, $finder);
				break;
			}
		}

		// move configuration file
		if (file_exists($config = $moduleDir.'/lib/configuration.php'))
		{
			if (file_exists($target = $moduleDir.'/lib/'.$arguments['module'].'GeneratorConfiguration.class.php'))
			{
				$this->getFilesystem()->remove($config);
			}
			else
			{
				$this->getFilesystem()->rename($config, $target);
			}
		}

		// move helper file
		if (file_exists($config = $moduleDir.'/lib/helper.php'))
		{
			if (file_exists($target = $moduleDir.'/lib/'.$arguments['module'].'GeneratorHelper.class.php'))
			{
				$this->getFilesystem()->remove($config);
			}
			else
			{
				$this->getFilesystem()->rename($config, $target);
			}
		}

		// create basic test
		$this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.'skeleton'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'actionsTest.php', sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php');

		// customize test file
		$this->getFilesystem()->replaceTokens(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.$arguments['application'].DIRECTORY_SEPARATOR.$arguments['module'].'ActionsTest.php', '##', '##', $this->constants);

		// customize php and yml files
		$finder = sfFinder::type('file')->name('*.php', '*.yml');
		$this->constants['CONFIG'] = sprintf(<<<EOF
    model_class:           %s
    theme:                 %s
    non_verbose_templates: %s
    with_show:             %s
    singular:              %s
    plural:                %s
    route_prefix:          %s
    with_propel_route:     %s
    actions_base_class:    %s
EOF
		,
		$arguments['model'],
		$options['theme'],
		$options['non-verbose-templates'] ? 'true' : 'false',
		$options['with-show'] ? 'true' : 'false',
		$options['singular'] ? $options['singular'] : '~',
		$options['plural'] ? $options['plural'] : '~',
		$options['route-prefix'] ? $options['route-prefix'] : '~',
		$options['with-propel-route'] ? $options['with-propel-route'] : 'false',
		$options['actions-base-class']
		);
		$this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $this->constants);
	}
}