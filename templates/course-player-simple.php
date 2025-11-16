<?php
/**
 * Simple test template for MasterStudy Course Player
 * Use this to test if template override is working before using the complex version
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header();
?>

<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid #007cba;">
    <h1>🎉 Custom Template Override Working!</h1>
    <p><strong>This is your custom MasterStudy template.</strong></p>
    
    <div style="background: #fff; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba;">
        <h3>Debug Information:</h3>
        <ul>
            <li><strong>Post ID:</strong> <?php echo get_the_ID(); ?></li>
            <li><strong>Post Type:</strong> <?php echo get_post_type(); ?></li>
            <li><strong>Post Title:</strong> <?php echo get_the_title(); ?></li>
            <li><strong>Template File:</strong> <?php echo basename( __FILE__ ); ?></li>
            <li><strong>Current User:</strong> <?php echo wp_get_current_user()->display_name ?: 'Not logged in'; ?></li>
        </ul>
        
        <h4>Query Variables:</h4>
        <pre style="background: #f9f9f9; padding: 10px; overflow: auto;">
<?php 
$query_vars = array();
foreach ( array( 'course_id', 'lesson_id', 'lms_page_path' ) as $var ) {
    $value = get_query_var( $var );
    if ( $value ) {
        $query_vars[$var] = $value;
    }
}
print_r( $query_vars );
?>
        </pre>
    </div>
    
    <div style="background: #fff; padding: 15px; margin: 10px 0;">
        <h3>Original Content:</h3>
        <?php 
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                the_content();
            }
        }
        ?>
    </div>
    
    <p><em>Once you confirm this is working, you can switch back to the full course-player.php template.</em></p>
</div>

<?php
get_footer();
?>
