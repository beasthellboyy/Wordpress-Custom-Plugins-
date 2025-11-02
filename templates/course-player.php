<?php
/**
 * Custom MasterStudy Course Player Template - Silva Ultramind Style
 * Follows proper MasterStudy architecture with custom styling
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// This template expects $lesson_id and $lms_page_path from MasterStudy's routing
if ( ! isset( $lesson_id ) || ! isset( $lms_page_path ) ) {
    // If variables are missing, we're not in proper course player context
    // Redirect to course page or show error
    if ( is_singular( 'stm-courses' ) ) {
        // We're on a course page, show course overview with first lesson auto-start
        $course_id = get_the_ID();
        $curriculum_repo = new \MasterStudy\Lms\Repositories\CurriculumRepository();
        $curriculum = $curriculum_repo->get_curriculum( $course_id, true );
        
        if ( !empty( $curriculum ) && !empty( $curriculum[0]['materials'] ) ) {
            $first_lesson = $curriculum[0]['materials'][0];
            $first_lesson_url = STM_LMS_Lesson::get_lesson_url( $course_id, $first_lesson['post_id'] );
            
            // Auto-redirect to first lesson
            ?>
            <script>
                setTimeout(function() {
                    window.location.href = '<?php echo esc_js( $first_lesson_url ); ?>';
                }, 2000);
            </script>
            <?php
            
            get_header();
            ?>
            <div style="background: linear-gradient(135deg, #4c6ef5 0%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
                <div>
                    <h1>Starting Course...</h1>
                    <p>Redirecting to first lesson...</p>
                </div>
            </div>
            <?php
            get_footer();
            return;
        }
    }
    
    // Fallback: redirect to home
    wp_redirect( home_url() );
    exit;
}

// Now we're in proper course player context - use MasterStudy's system
use MasterStudy\Lms\Repositories\CoursePlayerRepository;

global $post;
$post = get_post( $lesson_id );

if ( $post instanceof \WP_Post ) {
	setup_postdata( $post );
}

$course_player = new CoursePlayerRepository();
$data = $course_player->get_main_data( $lms_page_path, (int) $lesson_id );
$quiz_data = 'quiz' === $data['lesson_type']
	? $course_player->get_quiz_data( $data['item_id'], $data['user_id'], $data['post_id'] )
	: array();

// Enqueue necessary assets
do_action( 'masterstudy_lms_course_player_register_assets' );
wp_enqueue_style( 'masterstudy-course-player-main' );
wp_enqueue_script( 'masterstudy-course-player-quiz-attempt' );

if ( empty( $data['theme_fonts'] ) ) {
	wp_enqueue_style( 'masterstudy-fonts' );
	wp_enqueue_style( 'masterstudy-course-player-fonts' );
	wp_enqueue_style( 'masterstudy-components-fonts' );
}

$data['dark_mode'] = true;

do_action( 'stm_lms_before_item_template_start', $data['post_id'], $data['item_id'], $data['material_ids'] );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_the_title( $data['item_id'] ) ); ?> - <?php echo esc_html( $data['course_title'] ); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for reliable icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php wp_head(); ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-blue: #4c6ef5;
            --primary-purple: #7c3aed;
            --bg-dark: #0a0a0a;
            --bg-darker: #000000;
            --bg-card: #1a1a1a;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --text-muted: #666666;
            --border-color: #2a2a2a;
            --success-green: #10b981;
            --gradient-main: linear-gradient(135deg, #4c6ef5 0%, #7c3aed 100%);
        }
        
        body {
            background: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
        }
        
        .course-player-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Header */
        .course-header {
            background: var(--bg-darker);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .back-btn {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        
        .back-btn:hover {
            background: var(--bg-card);
        }
        
        .course-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .bookmark-btn, .menu-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .bookmark-btn:hover, .menu-btn:hover {
            color: var(--text-primary);
            background: var(--bg-card);
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Video Player Area */
        .video-section {
            background: var(--gradient-main);
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            overflow: hidden;
        }
        
        /* Video styling for hero section */
        .video-section video,
        .video-section iframe,
        .video-section .masterstudy-single-course-video,
        .video-section .stm-lms-course-player-lesson-video {
            height: 100% !important;
            width: auto !important;
            max-width: 100% !important;
            border-radius: 0 !important;
            object-fit: cover !important;
        }
        
        .video-section .masterstudy-single-course-video__container,
        .video-section .masterstudy-single-course-video__wrapper {
            height: 100% !important;
            width: 100% !important;
            border-radius: 0 !important;
        }
        
        .video-content h1 {
            font-size: 3.5rem;
            font-weight: 300;
            line-height: 1.2;
            margin-bottom: 2rem;
            color: white;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }
        
        .video-content .subtitle {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.9);
            font-style: italic;
        }
        
        .video-controls {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            gap: 1rem;
        }
        
        .control-btn {
            background: rgba(0,0,0,0.3);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            background: rgba(0,0,0,0.5);
        }
        
        /* Course Info Section */
        .course-info {
            background: var(--bg-darker);
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .course-branding {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .course-logo {
            width: 60px;
            height: 60px;
            background: var(--gradient-main);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            color: white;
        }
        
        .course-details h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .course-author {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .course-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .continue-btn {
            background: white;
            color: var(--bg-dark);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,255,255,0.2);
        }
        
        .chat-btn {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chat-btn:hover {
            background: var(--bg-darker);
        }
        
        .progress-section {
            text-align: right;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Navigation Tabs */
        .nav-tabs {
            background: var(--bg-darker);
            padding: 0 2rem;
            display: flex;
            gap: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .nav-tab {
            background: none;
            border: none;
            color: var(--text-secondary);
            padding: 1rem 0;
            font-weight: 500;
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }
        
        .nav-tab.active {
            color: var(--text-primary);
        }
        
        .nav-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-blue);
        }
        
        .nav-tab:hover {
            color: var(--text-primary);
        }
        
        /* Tabbed Content */
        .tabbed-content {
            flex: 1;
            background: var(--bg-dark);
        }
        
        .tab-content {
            display: none;
            padding: 2rem;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Overview Section */
        .overview-section {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .course-overview h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .course-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .course-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .first-lesson-preview h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .lesson-preview-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid var(--border-color);
        }
        
        .lesson-preview-card:hover {
            background: #222;
            border-color: var(--primary-blue);
        }
        
        .lesson-preview-thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .lesson-preview-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .lesson-preview-thumbnail .thumbnail-placeholder {
            width: 100%;
            height: 100%;
            background: var(--gradient-main);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .lesson-preview-info h5 {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .lesson-preview-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Lessons Section */
        .lessons-section {
            padding: 0;
        }
        
        /* Resources Section */
        .resources-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }
        
        .resources-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        
        .resource-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border-color);
        }
        
        .resource-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        .resource-info h4 {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .resource-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .resource-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .resource-link:hover {
            text-decoration: underline;
        }
        
        /* Discussions Section */
        .discussions-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }
        
        .lesson-group {
            margin-bottom: 2rem;
        }
        
        .group-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
        }
        
        .group-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .lesson-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-card);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        
        .lesson-item:hover {
            background: #222;
            border-color: var(--border-color);
        }
        
        .lesson-thumbnail {
            width: 120px;
            height: 80px;
            background: var(--gradient-main);
            border-radius: 12px;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .lesson-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(76, 110, 245, 0.3);
        }
        
        .lesson-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .lesson-thumbnail .thumbnail-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            background: var(--gradient-main);
        }
        
        .lesson-thumbnail .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .lesson-thumbnail:hover .play-overlay {
            opacity: 1;
        }
        
        .lesson-thumbnail .play-overlay i {
            font-size: 12px;
            color: var(--primary-blue);
        }
        
        .lesson-thumbnail .play-overlay .emoji-fallback {
            font-size: 12px;
            color: var(--primary-blue);
            margin-left: 2px;
        }
        
        .lesson-info {
            flex: 1;
        }
        
        .lesson-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .lesson-meta {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .lesson-duration {
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        .lesson-status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--success-green);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }
        
        .lesson-status.incomplete {
            background: var(--border-color);
        }
        
        /* Icon System - Font Awesome with Emoji Fallbacks */
        .emoji-fallback {
            display: none;
        }
        
        /* If Font Awesome fails to load, show emoji fallbacks */
        .fa:not(.fa-loaded) + .emoji-fallback,
        .fas:not(.fa-loaded) + .emoji-fallback,
        .far:not(.fa-loaded) + .emoji-fallback {
            display: inline;
        }
        
        .fa:not(.fa-loaded),
        .fas:not(.fa-loaded),
        .far:not(.fa-loaded) {
            display: none;
        }
        
        /* Ensure icons are properly sized */
        .back-btn i,
        .bookmark-btn i,
        .menu-btn i,
        .control-btn i,
        .chat-btn i,
        .continue-btn i {
            margin-right: 0.5rem;
        }
        
        .section-toggle i {
            transition: transform 0.2s ease;
        }
        
        .section-toggle.expanded i {
            transform: rotate(180deg);
        }
        
        .resource-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .play-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lesson-status {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .course-header {
                padding: 1rem;
            }
            
            .video-content h1 {
                font-size: 2.5rem;
            }
            
            .course-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-tabs {
                padding: 0 1rem;
                gap: 1rem;
            }
            
            .lessons-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="course-player-container">
        <!-- Header -->
        <header class="course-header">
            <div class="header-left">
                <button class="back-btn" onclick="window.location.href='<?php echo esc_url( $data['course_url'] ); ?>'">
                    <i class="fas fa-arrow-left"></i>
                    <span class="emoji-fallback">←</span>
                </button>
                <span class="course-title"><?php echo esc_html( $data['course_title'] ); ?></span>
            </div>
            <div class="header-right">
                <button class="bookmark-btn">
                    <i class="fas fa-bookmark"></i>
                    <span class="emoji-fallback">🔖</span>
                </button>
                <button class="menu-btn">
                    <i class="fas fa-ellipsis-h"></i>
                    <span class="emoji-fallback">⋯</span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Video Section -->
            <section class="video-section">
                <div class="video-content">
                    <?php
                    // Strict access control - user must be logged in AND enrolled
                    $user_id = get_current_user_id();
                    $is_enrolled = false;
                    
                    if ( $user_id ) {
                        // Check if user is enrolled in the course
                        $is_enrolled = \STM_LMS_User::has_course_access( $data['post_id'], $data['item_id'], $user_id );
                        
                        // Alternative check using MasterStudy's enrollment system
                        if ( !$is_enrolled && function_exists( 'stm_lms_has_course_access' ) ) {
                            $is_enrolled = stm_lms_has_course_access( $data['post_id'], $user_id );
                        }
                    }
                    
                    // Only allow access if user is enrolled (ignore guest trial for security)
                    $has_access = $is_enrolled;

                    if ( $has_access || $data['has_preview'] ) {
                        if ( ! $data['lesson_lock_before_start'] && ! $data['lesson_locked_by_drip'] ) {
                            $item_content = apply_filters( 'stm_lms_show_item_content', true, $data['post_id'], $data['item_id'] );

                            if ( $item_content && ! empty( $data['item_id'] ) ) {
                                // Display the actual lesson content (video, text, etc.)
                                STM_LMS_Templates::show_lms_template(
                                    'course-player/content/' . $data['content_type'] . '/main',
                                    array(
                                        'post_id'               => $data['post_id'],
                                        'item_id'               => $data['item_id'],
                                        'user_id'               => $data['user_id'],
                                        'lesson_type'           => $data['lesson_type'],
                                        'lesson_completed'      => $data['lesson_completed'],
                                        'data'                  => 'quiz' === $data['content_type'] ? $quiz_data : array(),
                                        'last_lesson'           => $data['last_lesson'],
                                        'video_questions'       => $data['video_questions'] ?? array(),
                                        'video_questions_stats' => $data['video_questions_stats'] ?? array(),
                                        'dark_mode'             => $data['dark_mode'],
                                        'has_attempts'          => $quiz_data['has_attempts'] ?? false,
                                    )
                                );
                            } else {
                                // Fallback: show inspirational text
                                ?>
                                <h1>Access altered states of mind for<br>more intuition, clarity and healing</h1>
                                <p class="subtitle">Transform your learning experience</p>
                                <?php
                            }
                        } else {
                            ?>
                            <h1>Lesson Locked</h1>
                            <p class="subtitle"><?php echo esc_html( $data['lesson_lock_message'] ?? 'This lesson is not available yet.' ); ?></p>
                            <?php
                        }
                    } else {
                        ?>
                        <h1>Access Restricted</h1>
                        <p class="subtitle">Please enroll in this course to access the content</p>
                        <?php
                    }
                    ?>
                </div>
                <div class="video-controls">
                    <button class="control-btn">
                        <i class="fas fa-pause"></i>
                        <span class="emoji-fallback">⏸️</span>
                    </button>
                    <button class="control-btn">
                        <i class="fas fa-volume-up"></i>
                        <span class="emoji-fallback">🔊</span>
                    </button>
                    <button class="control-btn">
                        <i class="fas fa-expand"></i>
                        <span class="emoji-fallback">⛶</span>
                    </button>
                </div>
            </section>

            <!-- Course Info -->
            <section class="course-info">
                <div class="course-branding">
                    <div class="course-logo">
                        <?php 
                        $title_words = explode(' ', $data['course_title']);
                        echo esc_html(substr($title_words[0], 0, 1) . (isset($title_words[1]) ? substr($title_words[1], 0, 1) : ''));
                        ?>
                    </div>
                    <div class="course-details">
                        <h2><?php echo esc_html( $data['course_title'] ); ?></h2>
                        <p class="course-author">by <?php echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $data['post_id'] ) ) ); ?></p>
                    </div>
                </div>
                <div class="course-actions">
                    <?php if ( $has_access ) : ?>
                        <?php
                        // Get next lesson for continue button (enrolled users only)
                        $next_lesson_url = '';
                        $current_lesson_found = false;
                        
                        if ( !empty( $data['curriculum'] ) ) {
                            foreach ( $data['curriculum'] as $section ) {
                                if ( isset( $section['materials'] ) ) {
                                    foreach ( $section['materials'] as $material ) {
                                        if ( $current_lesson_found ) {
                                            $next_lesson_url = STM_LMS_Lesson::get_lesson_url( $data['post_id'], $material['post_id'] );
                                            break 2;
                                        }
                                        if ( $material['post_id'] == $data['item_id'] ) {
                                            $current_lesson_found = true;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ( $next_lesson_url ) : ?>
                            <a href="<?php echo esc_url( $next_lesson_url ); ?>" class="continue-btn">
                                Continue - Next Lesson
                            </a>
                        <?php else : ?>
                            <button class="continue-btn" disabled>Course Complete</button>
                        <?php endif; ?>
                        
                        <button class="chat-btn">
                            <i class="fas fa-comments"></i>
                            <span class="emoji-fallback">💬</span>
                            Chat with AI
                        </button>
                        <div class="progress-section">
                            <?php
                            $completed_count = 0;
                            $total_count = count( $data['material_ids'] );
                            ?>
                            <div><?php echo esc_html( $completed_count . ' / ' . $total_count . ' Completed' ); ?></div>
                        </div>
                    <?php else : ?>
                        <!-- Non-enrolled user actions -->
                        <?php
                        // Get course price information
                        $course_price = get_post_meta( $data['post_id'], 'price', true );
                        $course_sale_price = get_post_meta( $data['post_id'], 'sale_price', true );
                        $sale_price_active = STM_LMS_Helpers::is_sale_price_active( $data['post_id'] );
                        $is_free = empty( $course_price ) || $course_price == '0';
                        $guest_checkout = STM_LMS_Options::get_option( 'guest_checkout', false );
                        $is_logged = is_user_logged_in();
                        
                        // Determine button attributes based on MasterStudy's system
                        if ( $is_logged ) {
                            $button_attributes = 'data-purchased-course="' . intval( $data['post_id'] ) . '"';
                        } else {
                            if ( $guest_checkout ) {
                                $button_attributes = 'data-guest="' . intval( $data['post_id'] ) . '"';
                            } else {
                                $button_attributes = 'data-authorization-modal="login"';
                            }
                        }
                        
                        // Prepare button text and price display
                        $button_text = $is_free ? 'Enroll for Free' : 'Get Course';
                        $display_price = '';
                        
                        if ( !$is_free ) {
                            if ( !empty( $course_sale_price ) && $sale_price_active ) {
                                $display_price = STM_LMS_Helpers::display_price( $course_sale_price );
                            } elseif ( !empty( $course_price ) ) {
                                $display_price = STM_LMS_Helpers::display_price( $course_price );
                            }
                        }
                        ?>
                        
                        <!-- Enqueue MasterStudy's buy button assets -->
                        <?php
                        wp_enqueue_style( 'masterstudy-buy-button' );
                        wp_enqueue_script( 'masterstudy-buy-button' );
                        wp_localize_script(
                            'masterstudy-buy-button',
                            'masterstudy_buy_button_data',
                            array(
                                'ajax_url'        => admin_url( 'admin-ajax.php' ),
                                'get_nonce'       => wp_create_nonce( 'stm_lms_add_to_cart' ),
                                'get_guest_nonce' => wp_create_nonce( 'stm_lms_add_to_cart_guest' ),
                                'item_id'         => $data['post_id'],
                            )
                        );
                        ?>
                        
                        <a href="#" class="continue-btn masterstudy-buy-button__link" <?php echo $button_attributes; ?>>
                            <i class="fas fa-graduation-cap"></i>
                            <span class="emoji-fallback">🎓</span>
                            <?php echo esc_html( $button_text ); ?>
                            <?php if ( !empty( $display_price ) ) : ?>
                                <span style="margin-left: 0.5rem; opacity: 0.9;">
                                    <?php echo wp_kses_post( $display_price ); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        
                        <button class="chat-btn" onclick="alert('Please enroll to access course features')">
                            <i class="fas fa-eye"></i>
                            <span class="emoji-fallback">👁️</span>
                            Preview Course
                        </button>
                        <div class="progress-section">
                            <div>Course Preview Mode</div>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Navigation Tabs -->
            <nav class="nav-tabs">
                <button class="nav-tab active" data-tab="overview">Overview</button>
                <?php if ( $has_access ) : ?>
                    <button class="nav-tab" data-tab="lessons">Lessons</button>
                    <?php
                    // Check if course has resources
                    $has_resources = false;
                    $course_materials = get_post_meta( $data['post_id'], 'course_materials', true );
                    $course_files = get_post_meta( $data['post_id'], 'course_files', true );
                    if ( !empty( $course_materials ) || !empty( $course_files ) ) {
                        $has_resources = true;
                    }
                    
                    if ( $has_resources ) : ?>
                        <button class="nav-tab" data-tab="resources">Resources</button>
                    <?php endif; ?>
                    <button class="nav-tab" data-tab="discussions">Discussions</button>
                <?php endif; ?>
            </nav>

            <!-- Tabbed Content Section -->
            <section class="tabbed-content">
                
                <!-- Overview Tab (Always visible) -->
                <div class="tab-content active" id="overview-content">
                    <div class="overview-section">
                        <div class="course-overview">
                            <h3>About This Course</h3>
                            <div class="course-description">
                                <?php 
                                $course_excerpt = get_post_field( 'post_excerpt', $data['post_id'] );
                                $course_content = get_post_field( 'post_content', $data['post_id'] );
                                
                                if ( !empty( $course_excerpt ) ) {
                                    echo wp_kses_post( $course_excerpt );
                                } elseif ( !empty( $course_content ) ) {
                                    echo wp_kses_post( wp_trim_words( $course_content, 50 ) );
                                } else {
                                    echo '<p>Discover the transformative power of this comprehensive course designed to enhance your learning experience.</p>';
                                }
                                ?>
                            </div>
                            
                            <?php if ( !empty( $data['curriculum'] ) ) : ?>
                                <div class="course-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count( $data['material_ids'] ); ?></span>
                                        <span class="stat-label">Lessons</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count( $data['curriculum'] ); ?></span>
                                        <span class="stat-label">Sections</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">∞</span>
                                        <span class="stat-label">Access</span>
                                    </div>
                                </div>
                                
                                <?php if ( $has_access ) : ?>
                                    <!-- First lesson preview for enrolled users -->
                                    <?php 
                                    $first_lesson = null;
                                    if ( !empty( $data['curriculum'][0]['materials'][0] ) ) {
                                        $first_lesson = $data['curriculum'][0]['materials'][0];
                                    }
                                    ?>
                                    <?php if ( $first_lesson ) : ?>
                                        <div class="first-lesson-preview">
                                            <h4>Start Learning</h4>
                                            <div class="lesson-preview-card" onclick="window.location.href='<?php echo esc_js( STM_LMS_Lesson::get_lesson_url( $data['post_id'], $first_lesson['post_id'] ) ); ?>'">
                                                <div class="lesson-preview-thumbnail">
                                                    <?php
                                                    $first_lesson_thumb = get_the_post_thumbnail_url( $first_lesson['post_id'], 'medium' );
                                                    if ( !$first_lesson_thumb ) {
                                                        $first_lesson_thumb = get_the_post_thumbnail_url( $data['post_id'], 'medium' );
                                                    }
                                                    ?>
                                                    <?php if ( $first_lesson_thumb ) : ?>
                                                        <img src="<?php echo esc_url( $first_lesson_thumb ); ?>" alt="<?php echo esc_attr( $first_lesson['title'] ); ?>">
                                                    <?php else : ?>
                                                        <div class="thumbnail-placeholder">
                                                            <i class="fas fa-play"></i>
                                                            <span class="emoji-fallback">▶</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="lesson-preview-info">
                                                    <h5><?php echo esc_html( $first_lesson['title'] ); ?></h5>
                                                    <p>Begin your journey with this introductory lesson</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ( $has_access ) : ?>
                    <!-- Lessons Tab (Enrolled users only) -->
                    <div class="tab-content" id="lessons-content">
                        <div class="lessons-section">
                            <?php if ( !empty( $data['curriculum'] ) ) : ?>
                    <?php foreach ( $data['curriculum'] as $section_index => $section ) : ?>
                        <div class="lesson-group">
                            <div class="group-header">
                                <span class="section-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                    <span class="emoji-fallback">▼</span>
                                </span>
                                <h3 class="group-title"><?php echo esc_html( $section['title'] ); ?></h3>
                            </div>
                            
                            <?php if ( isset( $section['materials'] ) ) : ?>
                                <?php foreach ( $section['materials'] as $lesson_index => $lesson ) : ?>
                                    <?php 
                                    $lesson_url = STM_LMS_Lesson::get_lesson_url( $data['post_id'], $lesson['post_id'] );
                                    $is_completed = false; // You can add completion logic here
                                    $lesson_number = $lesson_index + 1;
                                    
                                    // Get lesson banner/thumbnail (multiple fallback methods with debugging)
                                    $thumbnail_url = '';
                                    $debug_info = array();
                                    
                                    // Method 1: Featured image
                                    $featured_img = get_the_post_thumbnail_url( $lesson['post_id'], 'medium' );
                                    if ( $featured_img ) {
                                        $thumbnail_url = $featured_img;
                                        $debug_info[] = 'Featured Image: ' . $featured_img;
                                    }
                                    
                                    // Method 2: Lesson banner meta (MasterStudy standard)
                                    if ( !$thumbnail_url ) {
                                        $lesson_banner = get_post_meta( $lesson['post_id'], 'lesson_banner', true );
                                        $debug_info[] = 'lesson_banner meta: ' . print_r( $lesson_banner, true );
                                        
                                        if ( !empty( $lesson_banner ) ) {
                                            if ( is_array( $lesson_banner ) && isset( $lesson_banner['url'] ) ) {
                                                $thumbnail_url = $lesson_banner['url'];
                                            } elseif ( is_string( $lesson_banner ) && filter_var( $lesson_banner, FILTER_VALIDATE_URL ) ) {
                                                $thumbnail_url = $lesson_banner;
                                            } elseif ( is_numeric( $lesson_banner ) ) {
                                                // Handle attachment ID
                                                $attachment_url = wp_get_attachment_image_url( $lesson_banner, 'medium' );
                                                if ( $attachment_url ) {
                                                    $thumbnail_url = $attachment_url;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Method 3: Try other common MasterStudy meta fields
                                    if ( !$thumbnail_url ) {
                                        $meta_fields = array( 'lesson_video_poster', 'video_poster', 'lesson_image', 'stm_lesson_banner', 'lesson_thumbnail' );
                                        foreach ( $meta_fields as $field ) {
                                            $meta_value = get_post_meta( $lesson['post_id'], $field, true );
                                            $debug_info[] = $field . ': ' . print_r( $meta_value, true );
                                            
                                            if ( !empty( $meta_value ) ) {
                                                if ( is_array( $meta_value ) && isset( $meta_value['url'] ) ) {
                                                    $thumbnail_url = $meta_value['url'];
                                                    break;
                                                } elseif ( is_string( $meta_value ) && filter_var( $meta_value, FILTER_VALIDATE_URL ) ) {
                                                    $thumbnail_url = $meta_value;
                                                    break;
                                                } elseif ( is_numeric( $meta_value ) ) {
                                                    $attachment_url = wp_get_attachment_image_url( $meta_value, 'medium' );
                                                    if ( $attachment_url ) {
                                                        $thumbnail_url = $attachment_url;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Method 4: Course featured image as fallback
                                    if ( !$thumbnail_url ) {
                                        $course_thumb = get_the_post_thumbnail_url( $data['post_id'], 'medium' );
                                        if ( $course_thumb ) {
                                            $thumbnail_url = $course_thumb;
                                            $debug_info[] = 'Using course thumbnail: ' . $course_thumb;
                                        }
                                    }
                                    
                                    // Debug output (remove this after fixing)
                                    if ( current_user_can( 'manage_options' ) ) {
                                        $debug_info[] = 'Final thumbnail_url: ' . $thumbnail_url;
                                    }
                                    
                                    // Get lesson duration
                                    $duration = get_post_meta( $lesson['post_id'], 'duration', true );
                                    if ( !$duration ) {
                                        $duration = rand( 8, 25 ) . ' mins';
                                    }
                                    
                                    // Get lesson type
                                    $lesson_type = get_post_type( $lesson['post_id'] );
                                    $type_label = '';
                                    switch ( $lesson_type ) {
                                        case 'stm-lessons':
                                            $type_label = 'LESSON';
                                            break;
                                        case 'stm-quizzes':
                                            $type_label = 'QUIZ';
                                            break;
                                        case 'stm-assignments':
                                            $type_label = 'ASSIGNMENT';
                                            break;
                                        default:
                                            $type_label = 'INTRO';
                                    }
                                    ?>
                                    
                                    <div class="lesson-item" onclick="window.location.href='<?php echo esc_js( $lesson_url ); ?>'" style="position: relative;">
                                        <div class="lesson-thumbnail">
                                            <?php if ( $thumbnail_url ) : ?>
                                                <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $lesson['title'] ); ?>" loading="lazy">
                                                <div class="play-overlay">
                                                    <i class="fas fa-play"></i>
                                                    <span class="emoji-fallback">▶</span>
                                                </div>
                                            <?php else : ?>
                                                <div class="thumbnail-placeholder">
                                                    <?php echo esc_html( $lesson_number ); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ( current_user_can( 'manage_options' ) && !$thumbnail_url ) : ?>
                                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(255,0,0,0.8); color: white; font-size: 10px; padding: 2px; text-align: center;">
                                                    No Banner Found
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ( current_user_can( 'manage_options' ) && !empty( $debug_info ) ) : ?>
                                            <div style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.9); color: #fff; font-size: 10px; padding: 5px; max-width: 200px; z-index: 1000; display: none;" class="debug-info">
                                                <strong>Debug Info for Lesson <?php echo $lesson['post_id']; ?>:</strong><br>
                                                <?php foreach ( $debug_info as $info ) : ?>
                                                    <?php echo esc_html( $info ); ?><br>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="lesson-info">
                                            <div class="lesson-title"><?php echo esc_html( $lesson['title'] ); ?></div>
                                            <div class="lesson-meta"><?php echo esc_html( $type_label . ' ' . $lesson_number ); ?></div>
                                            <div class="lesson-duration"><?php echo esc_html( $duration ); ?></div>
                                        </div>
                                        <div class="lesson-status <?php echo $is_completed ? '' : 'incomplete'; ?>">
                                            <?php if ( $is_completed ) : ?>
                                                <i class="fas fa-check"></i>
                                                <span class="emoji-fallback">✓</span>
                                            <?php else : ?>
                                                <?php echo $lesson_number; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <p>No lessons available yet. Check back soon!</p>
                    </div>
                <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Resources Tab (Enrolled users only, if resources exist) -->
                    <?php if ( $has_resources ) : ?>
                        <div class="tab-content" id="resources-content">
                            <div class="resources-section">
                                <h3>Course Resources</h3>
                                <div class="resources-grid">
                                    <?php
                                    // Display course materials
                                    if ( !empty( $course_materials ) ) {
                                        foreach ( $course_materials as $material ) {
                                            ?>
                                            <div class="resource-item">
                                                <div class="resource-icon">
                                                    <i class="fas fa-file-alt"></i>
                                                    <span class="emoji-fallback">📄</span>
                                                </div>
                                                <div class="resource-info">
                                                    <h4><?php echo esc_html( $material['title'] ?? 'Course Material' ); ?></h4>
                                                    <p><?php echo esc_html( $material['description'] ?? 'Additional course material' ); ?></p>
                                                    <?php if ( !empty( $material['url'] ) ) : ?>
                                                        <a href="<?php echo esc_url( $material['url'] ); ?>" class="resource-link" target="_blank">Download</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    
                                    // Display course files
                                    if ( !empty( $course_files ) ) {
                                        foreach ( $course_files as $file ) {
                                            $file_url = wp_get_attachment_url( $file );
                                            $file_name = get_the_title( $file );
                                            if ( $file_url ) {
                                                ?>
                                                <div class="resource-item">
                                                    <div class="resource-icon">
                                                        <i class="fas fa-paperclip"></i>
                                                        <span class="emoji-fallback">📎</span>
                                                    </div>
                                                    <div class="resource-info">
                                                        <h4><?php echo esc_html( $file_name ); ?></h4>
                                                        <p>Course attachment</p>
                                                        <a href="<?php echo esc_url( $file_url ); ?>" class="resource-link" target="_blank">Download</a>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Discussions Tab (Enrolled users only) -->
                    <div class="tab-content" id="discussions-content">
                        <div class="discussions-section">
                            <h3>Course Discussions</h3>
                            <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                                Course discussions and community features will be available here.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
            </section>
        </main>
    </div>

    <?php
    // Show discussions if enabled
    if ( $data['has_access'] ) {
        STM_LMS_Templates::show_lms_template(
            'course-player/discussions',
            array(
                'post_id'             => $data['post_id'],
                'item_id'             => $data['item_id'],
                'user_id'             => $data['user_id'],
                'lesson_type'         => $data['lesson_type'],
                'quiz_data'           => 'quiz' === $data['content_type'] ? $quiz_data : array(),
                'dark_mode'           => $data['dark_mode'],
                'discussions_sidebar' => $data['discussions_sidebar'],
                'settings'            => $data['settings'],
            )
        );
    }
    
    wp_footer();
    ?>
    
    <script>
        // Navigation tabs functionality
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show corresponding content
                const tabName = this.getAttribute('data-tab');
                const content = document.getElementById(tabName + '-content');
                if (content) {
                    content.classList.add('active');
                }
            });
        });
        
        // Debug functionality for lesson thumbnails
        document.querySelectorAll('.lesson-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                const debugInfo = this.querySelector('.debug-info');
                if (debugInfo) {
                    debugInfo.style.display = 'block';
                }
            });
            
            item.addEventListener('mouseleave', function() {
                const debugInfo = this.querySelector('.debug-info');
                if (debugInfo) {
                    debugInfo.style.display = 'none';
                }
            });
        });
        
        // Initialize first tab as active
        document.addEventListener('DOMContentLoaded', function() {
            const firstTab = document.querySelector('.nav-tab[data-tab="overview"]');
            const firstContent = document.getElementById('overview-content');
            
            if (firstTab && firstContent) {
                firstTab.classList.add('active');
                firstContent.classList.add('active');
            }
            
            // Check if Font Awesome loaded, if not show emoji fallbacks
            setTimeout(function() {
                const testIcon = document.createElement('i');
                testIcon.className = 'fas fa-home';
                testIcon.style.position = 'absolute';
                testIcon.style.left = '-9999px';
                document.body.appendChild(testIcon);
                
                const computedStyle = window.getComputedStyle(testIcon, ':before');
                const fontFamily = computedStyle.getPropertyValue('font-family');
                
                if (fontFamily.indexOf('Font Awesome') === -1) {
                    // Font Awesome didn't load, show emoji fallbacks
                    document.querySelectorAll('.fa, .fas, .far').forEach(function(icon) {
                        icon.style.display = 'none';
                        const fallback = icon.nextElementSibling;
                        if (fallback && fallback.classList.contains('emoji-fallback')) {
                            fallback.style.display = 'inline';
                        }
                    });
                } else {
                    // Font Awesome loaded successfully, mark icons as loaded
                    document.querySelectorAll('.fa, .fas, .far').forEach(function(icon) {
                        icon.classList.add('fa-loaded');
                    });
                }
                
                document.body.removeChild(testIcon);
            }, 100);
            
            // Section toggle functionality
            document.querySelectorAll('.group-header').forEach(function(header) {
                header.addEventListener('click', function() {
                    const toggle = this.querySelector('.section-toggle');
                    if (toggle) {
                        toggle.classList.toggle('expanded');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
do_action( 'stm_lms_template_main_after' );
?>
