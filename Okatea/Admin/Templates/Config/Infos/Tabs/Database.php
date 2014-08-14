<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>

<h3><?php _e('c_a_infos_database_title') ?></h3>

<?php foreach ($aDbInfos['sm']->listTables() as $table) : ?>

	<h4><?php echo $table->getName() ?></h4>

		<h5>Columns</h5>

		<table class="common">
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Type</th>
					<th scope="col">Length</th>
					<th scope="col">Precision</th>
					<th scope="col">Scale</th>
					<th scope="col">Unsigned</th>
					<th scope="col">Fixed</th>
					<th scope="col">Not null</th>
					<th scope="col">Default</th>
					<th scope="col">Column Definition</th>
					<th scope="col">Autoincrement</th>
				</tr>
			</thead>
			<tbody>
			<?php  $iCountLine = 0;
			foreach ($table->getColumns() as $column) :
				$sTdClass = $iCountLine++ % 2 == 0 ? 'even' : 'odd'; ?>
				<tr>
					<th scope="row" class="<?php echo $sTdClass ?> fake-td"><?php echo $column->getName() ?></th>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getType() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getLength() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getPrecision() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getScale() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getUnsigned() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getFixed() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getNotnull() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getDefault() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getColumnDefinition() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $column->getAutoincrement() ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>

		<h5>Indexes</h5>

		<table class="common">
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Columns</th>
					<th scope="col">Unique</th>
					<th scope="col">Primary</th>
				</tr>
			</thead>
			<tbody>
			<?php  $iCountLine = 0;
			foreach ($table->getIndexes() as $index) :
				$sTdClass = $iCountLine++ % 2 == 0 ? 'even' : 'odd'; ?>
				<tr>
					<th scope="row" class="<?php echo $sTdClass ?> fake-td"><?php echo $index->getName() ?></th>
					<td class="<?php echo $sTdClass ?>"><?php echo implode(' - ', $index->getColumns()) ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $index->isUnique() ?></td>
					<td class="<?php echo $sTdClass ?>"><?php echo $index->isPrimary() ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>

		<h5>Foreign Keys</h5>

		<?php foreach ($table->getForeignKeys() as $foreignKey) : ?>
		<?php d($foreignKey) ?>
		<?php endforeach ?>

<?php endforeach ?>
