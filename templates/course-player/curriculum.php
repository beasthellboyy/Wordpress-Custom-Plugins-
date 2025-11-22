<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var int $user_id
 * @var string $course_title
 * @var array $curriculum
 * @var array $user_course
 * @var int $trial_lessons
 * @var boolean $trial_access
 * @var boolean $is_enrolled
 * @var boolean $dark_mode
 */

wp_enqueue_style( 'masterstudy-course-player-curriculum' );
wp_enqueue_script( 'masterstudy-course-player-curriculum' );

// Find the section that contains the current lesson
$current_section_id = 0;
foreach ( $curriculum as $section ) {
    foreach ( $section['materials'] as $material ) {
        if ( intval( $material['id'] ) === intval( $item_id ) ) {
            $current_section_id = $section['id'];
            break 2;
        }
    }
}
?>

<div class="masterstudy-course-player-curriculum">
	<div class="masterstudy-course-player-curriculum__wrapper">
		<div class="masterstudy-course-player-curriculum__mobile-header">
			<h3 class="masterstudy-course-player-curriculum__mobile-title">
				<?php echo esc_html__( 'Curriculum', 'masterstudy-lms-learning-management-system' ); ?>
			</h3>
			<span class="masterstudy-course-player-curriculum__mobile-close"></span>
		</div>
		<div class="masterstudy-course-player-curriculum__tabs">
			<?php
			STM_LMS_Templates::show_lms_template(
				'components/tabs',
				array(
					'items'            => array(
						array(
							'id'    => 'lessons',
							'title' => __( 'Lessons', 'masterstudy-lms-learning-management-system' ),
						),
						array(
							'id'    => 'resources',
							'title' => __( 'Resources', 'masterstudy-lms-learning-management-system' ),
						),
						array(
							'id'    => 'overview',
							'title' => __( 'Overview', 'masterstudy-lms-learning-management-system' ),
						),
						array(
							'id'    => 'discussions',
							'title' => __( 'Discussions', 'masterstudy-lms-learning-management-system' ),
						),
					),
					'style'            => 'nav-sm',
					'active_tab_index' => 0,
					'dark_mode'        => $dark_mode,
					'class'            => 'masterstudy-course-player-curriculum-tabs',
				)
			);
			?>
		</div>
		<div class="masterstudy-course-player-curriculum__content">
			<div class="masterstudy-course-player-curriculum__title-wrapper">
				<h3 class="masterstudy-course-player-curriculum__title">
					<?php echo esc_html( $course_title ); ?>
				</h3>
				<?php
				if ( ! empty( $user_course['progress_percent'] ) ) {
					?>
					<div class="masterstudy-course-player-curriculum__progress">
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/progress',
							array(
								'title'     => __( 'Course progress', 'masterstudy-lms-learning-management-system' ),
								'progress'  => $user_course['progress_percent'],
								'dark_mode' => $dark_mode,
							)
						);
						?>
					</div>
				<?php } ?>
			</div>
			<?php
			if ( ! empty( $curriculum ) ) {
				STM_LMS_Templates::show_lms_template(
					'components/curriculum-accordion',
					array(
						'course_id'         => $post_id,
						'current_lesson_id' => $item_id,
						'user_id'           => $user_id,
						'curriculum'        => $curriculum,
						'trial_lessons'     => intval( $trial_lessons ),
						'trial_access'      => $trial_access,
						'is_enrolled'       => $is_enrolled,
						'dark_mode'         => $dark_mode,
                        // Pass the ID of the active section to the component
                        'opened_section'    => $current_section_id, 
					)
				);
			}
			?>
		</div>
	</div>
</div>
