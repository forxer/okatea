<?php
/**
 * @ingroup okt_module_catalog
 * @brief Liste des produits
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_CATALOG_MODULE')) die;

if ($list->isEmpty()) : ?>
<p>Il n’y a aucun produit.</p>

<?php else : ?>

	<p><?php if ($num_filtered_posts > 1) : ?>
	Il y a <?php echo $num_filtered_posts ?> produits<?php if ($num_pages > 1) : ?> affichés sur <?php echo $num_pages ?> pages<?php endif; ?>.
	<?php else : ?>
	Il y a un produit.
	<?php endif; ?></p>


<?php # affichage mosaique
if ($display_style == 'mosaic') : ?>
<ul class="smartColumns">
	<?php # boucle sur la liste des produits
	while ($list->fetch()) : ?>
	<li class="column"><div class="block ui-widget ui-widget-content ui-corner-all">

		<h3 class="ui-widget-header ui-corner-all"><a href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php echo $list->id ?>"><?php
			echo html::escapeHTML($list->title) ?></a></h3>

		<?php # image
		if ($okt->catalog->config->images['enable']) :
		$image = $list->getFirstImageInfo(); ?>

			<?php if (!empty($image) && isset($image['min_url'])) : ?>

			<p class="modal-box"><a href="<?php echo $image['img_url']?>" title="<?php echo html::escapeHTML($list->title) ?>" class="modal">
			<img src="<?php echo $image['min_url']?>" <?php echo $image['min_attr']?> alt="" /></a></p>

			<?php endif; ?>
		<?php endif; ?>

		<?php # catégorie
		if ($okt->catalog->config->categories_enable) : ?>
		<p><?php echo html::escapeHTML($list->category_name) ?></p>
		<?php endif; ?>

		<p class="clearer">Ajouté le <?php echo dt::dt2str(__('%Y-%m-%d %H:%M'),$list->created_at) ?>.
			<?php if ($list->updated_at > $list->created_at) : ?>
			<span class="note">Modifié le <?php echo dt::dt2str(__('%Y-%m-%d %H:%M'),$list->updated_at) ?>.</span>
			<?php endif; ?>
		</p>

		<ul class="actions">
			<li>
			<a href="module.php?m=catalog&amp;action=index&amp;switch_status=<?php echo $list->id ?>"
				title="Basculer la visibilité du produit <?php echo html::escapeHTML($list->title) ?>"
				<?php if ($list->visibility == 0) : ?>
				class="icon cross">masqué
				<?php else : ?>
				class="icon tick">visible
				<?php endif; ?></a>
			</li>

			<li>
			<a href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php echo $list->id ?>"
			title="Modifier l'actualité <?php echo html::escapeHTML($list->title) ?>"
			class="icon pencil">Modifier</a>
			</li>

			<?php if ($list->isDeletable()) : ?>
			<li>
			<a href="module.php?m=catalog&amp;action=delete&amp;product_id=<?php echo $list->id ?>"
			onclick="return window.confirm('<?php echo html::escapeJS('Etes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.') ?>')"
			title="Supprimer le produit <?php echo html::escapeHTML($list->title) ?>"
			class="icon delete">Supprimer</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	</li>
	<?php endwhile; ?>
</ul>
<div class="clearer"></div>


<?php # affichage liste
elseif ($display_style == 'list') : ?>

<table class="common">
	<caption>Liste des produits</caption>
	<thead><tr>
		<th scope="col"<?php if ($okt->catalog->config->images['enable']) echo ' colspan="2"' ?>>Titre</th>
		<?php if ($okt->catalog->config->categories_enable) : ?>
		<th scope="col">Catégorie</th>
		<?php endif; ?>
		<th scope="col">Prix</th>
		<th scope="col">Dates</th>
		<th scope="col">Actions</th>
	</tr></thead>
	<tbody>
	<?php # boucle sur la liste des produits
	$count_line = 0;
	while ($list->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';

		$aCurrentInfos = array();

		if ($okt->catalog->config->fields['promo'] && $list->is_promo) {
			$aCurrentInfos[] = __('m_catalog_action_promo');
		}
		if ($okt->catalog->config->fields['nouvo'] && $list->is_nouvo) {
			$aCurrentInfos[] = __('m_catalog_action_nouvo');
		}
		if ($okt->catalog->config->fields['favo'] && $list->is_favo) {
			$aCurrentInfos[] = __('m_catalog_action_favo');
		}
	?>
	<tr>
		<th class="<?php echo $td_class ?> fake-td"><a
		href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php echo $list->id ?>"><?php
		echo html::escapeHTML($list->title) ?></a>
			<?php if (!empty($aCurrentInfos)) {
				echo '<ul class="note"><li>'.implode('</li><li>',$aCurrentInfos).'</li></ul>';
			} ?>
		</th>

		<?php # image
		if ($okt->catalog->config->images['enable']) :
		$image = $list->getFirstImageInfo(); ?>
		<td class="<?php echo $td_class ?> modal-box">
			<?php if (!empty($image) && isset($image['square_url'])) : ?>
			<a href="<?php echo $image['img_url']?>" title="<?php echo html::escapeHTML($list->title) ?>" class="modal">
			<img src="<?php echo $image['square_url']?>" <?php echo $image['square_attr']?> alt="" /></a>
			<?php endif; ?>
		</td>
		<?php endif; ?>

		<?php # catégorie
		if ($okt->catalog->config->categories_enable) : ?>
		<td class="<?php echo $td_class ?>"><?php echo html::escapeHTML($list->category_name) ?></td>
		<?php endif; ?>

		<td class="<?php echo $td_class ?>">
			<?php if ($okt->catalog->config->fields['promo'] && $list->is_promo && $list->price_promo > 0) : ?>
				<del><?php echo util::formatNumber($list->price) ?>&nbsp;€</del><br />
				<?php echo util::formatNumber($list->price_promo) ?>&nbsp;€
			<?php elseif ($list->price > 0)  : ?>
				<?php echo $list->price ? util::formatNumber($list->price).'&nbsp;€' : ''; ?>
			<?php endif; ?>
		</td>

		<td class="<?php echo $td_class ?>">
			<p>Ajouté le <?php echo dt::dt2str(__('%Y-%m-%d'),$list->created_at) ?>.
				<?php if ($list->updated_at > $list->created_at) : ?>
				<span class="note">Modifié le <?php echo dt::dt2str(__('%Y-%m-%d'),$list->updated_at) ?>.</span>
				<?php endif; ?>
			</p>
		</td>

		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
				<li>
				<a href="module.php?m=catalog&amp;action=index&amp;switch_status=<?php echo $list->id ?>"
				title="Basculer la visibilité du produit <?php echo html::escapeHTML($list->title) ?>"
					<?php if ($list->visibility == 0) : ?>
					class="icon cross">masqué
					<?php else : ?>
					class="icon tick">visible
					<?php endif; ?></a>
				</li>

				<li>
				<a href="module.php?m=catalog&amp;action=edit&amp;product_id=<?php echo $list->id ?>"
				title="Modifier l'actualité <?php echo html::escapeHTML($list->title) ?>"
				class="icon pencil">Modifier</a>
				</li>

				<?php if ($list->isDeletable()) : ?>
				<li>
				<a href="module.php?m=catalog&amp;action=delete&amp;product_id=<?php echo $list->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS('Etes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.') ?>')"
				title="Supprimer le produit <?php echo html::escapeHTML($list->title) ?>"
				class="icon delete">Supprimer</a>
				</li>
				<?php endif; ?>
			</ul>
		</td>
	</tr>
	<?php $count_line++;
	endwhile; ?>
	</tbody>
</table>

<?php endif; ?>

	<?php if ($num_pages > 1) : ?>
	<ul class="pagination"><?php echo $pager->getLinks(); ?></ul>
	<?php endif; ?>

<?php endif; ?>