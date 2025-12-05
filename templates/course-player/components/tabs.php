<?php
/**
 * @var array $items
 * @var string $style
 * @var string $class
 * @var int $active_tab_index
 * @var boolean $dark_mode
 */

wp_enqueue_style( 'masterstudy-tabs' );
wp_enqueue_script( 'masterstudy-tabs' );

$id = $class ?? '';
$active = $items[ $active_tab_index ] ?? array();
?>

<div class="masterstudy-tabs masterstudy-tabs_<?php echo esc_attr( $style ); ?> <?php echo esc_attr( $class ?? '' ); ?> <?php echo esc_attr( $dark_mode ? 'masterstudy-tabs_dark-mode' : '' ); ?>"
	data-active-tab-id="<?php echo esc_attr( $active['id'] ); ?>">
	<div class="masterstudy-tabs__nav">
		<?php
		foreach ( $items as $tab ) {
			$item_class = 'masterstudy-tabs__nav-item' . ( $tab['id'] === $active['id'] ? ' masterstudy-tabs__nav-item_active' : '' );
			?>
			<a href="#" class="<?php echo esc_attr( $item_class ); ?>" data-tab-id="<?php echo esc_attr( $tab['id'] ); ?>">
				<?php echo esc_html( $tab['title'] ); ?>
			</a>
		<?php } ?>
	</div>
</div>
