<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
   exit; 
}

get_header();
?>
 <section class="cpm_investor_section">
   
<div class = "cpm_investor_div">
<h1 class="cpm_investor_heading">Investor Details</h1>
   <?php
   //loop to check if there is post or not
   // if(have_posts()): 
   //    while(have_posts()): the_posts();
   $investor_type = get_post_meta(get_the_ID(), 'cpm_investor_type', true); // This will be an array
      ?>

  <h2 class="cpminvestor_title"> <?php the_title(); ?></h2>
  <p class ="cpm_investors_content"> <?php the_content(); ?> <p>

  <p class="cpm_investor_foundeddate"><strong>Founded In: </strong><?php echo esc_html(get_post_meta(get_the_ID(), 'cpm_investor_founded', true)); ?></p>
  <?php if (!empty($investor_type)) : ?>
                    <p><strong>Investor Type:</strong> <?php echo implode(', ', array_map('esc_html', $investor_type)); ?></p>
                <?php endif; ?> 
  <?php
// endwhile;
// endif;
   ?>
 </div>
 <div class="investor_sidebar">
       <aside id="secondary" class="widget-area">
          <?php dynamic_sidebar('investor-sidebar'); ?>
       </aside>

   </div>
</section>
<?php
get_footer();
?>

