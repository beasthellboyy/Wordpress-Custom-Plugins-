<?php
/**
 * Ultra-simple test template to verify override is working
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Template Override Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4); 
            color: white; 
            padding: 50px; 
            text-align: center; 
        }
        .success-box { 
            background: rgba(255,255,255,0.2); 
            padding: 30px; 
            border-radius: 10px; 
            backdrop-filter: blur(10px); 
        }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>🎉 SUCCESS! Template Override is Working!</h1>
        <p>Your MasterStudy Custom Layout plugin is successfully overriding templates.</p>
        <hr>
        <p><strong>Current Page:</strong> <?php echo get_the_title(); ?></p>
        <p><strong>Post Type:</strong> <?php echo get_post_type(); ?></p>
        <p><strong>Post ID:</strong> <?php echo get_the_ID(); ?></p>
        <p><strong>Template:</strong> <?php echo basename(__FILE__); ?></p>
        <hr>
        <p><em>Now you can switch back to your course-player template!</em></p>
    </div>
</body>
</html><?php
