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
 * A single choice view for the sfMinaclPropelChooser widget
 *
 * @author Rob Graham <htmlforms@mellowplace.com>
 * @package sfMinaclPropelPlugin
 * @subpackage widget.view
 */
?>
<select id="<?php echo $this->id('list') ?>" name="<?php echo $this->name('list') ?>">
<?php foreach($list as $l): ?>
	<option value="<?php echo $this->_form->getPrimaryKeyValue($l) ?>"><?php echo htmlentities($l, ENT_COMPAT, 'utf-8') ?></option>
<?php endforeach; ?>
</select>