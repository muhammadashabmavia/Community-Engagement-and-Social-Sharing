<div class="ai-social-challenges">
    <div class="challenges-header">
        <h2><?php _e('AI-Managed Social Challenges', 'ai-community'); ?></h2>
        <p><?php _e('Participate in challenges, earn badges, and generate income through affiliate marketing.', 'ai-community'); ?></p>
    </div>
    
    <div class="active-challenges">
        <h3><?php _e('Current Challenges', 'ai-community'); ?></h3>
        
        <?php 
        $challenges = $social_challenges->get_challenges();
        if ($challenges): 
            foreach ($challenges as $challenge): 
                $hashtags = explode(',', $challenge->hashtags);
                $products = unserialize($challenge->affiliate_products);
                $is_participating = false;
                $progress = 0;
                
                if (is_user_logged_in()) {
                    $participation = $social_challenges->get_user_participation(get_current_user_id(), $challenge->challenge_id);
                    $is_participating = !empty($participation);
                    
                    if ($is_participating) {
                        $completed_days = unserialize($participation->completed_days);
                        $progress = count($completed_days) / $challenge->duration * 100;
                    }
                }
                ?>
                <div class="challenge" data-challenge-id="<?php echo $challenge->challenge_id; ?>">
                    <h4><?php echo esc_html($challenge->title); ?></h4>
                    <div class="challenge-description"><?php echo wp_kses_post($challenge->description); ?></div>
                    
                    <div class="challenge-meta">
                        <div class="duration"><?php printf(_n('%d day', '%d days', $challenge->duration, 'ai-community'), $challenge->duration); ?></div>
                        
                        <?php if (!empty($hashtags)): ?>
                            <div class="hashtags">
                                <?php foreach ($hashtags as $tag): ?>
                                    <span class="hashtag"><?php echo esc_html(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_participating): ?>
                        <div class="challenge-progress">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                            <span><?php echo round($progress); ?>% <?php _e('complete', 'ai-community'); ?></span>
                        </div>
                        
                        <div class="challenge-days">
                            <?php for ($i = 1; $i <= $challenge->duration; $i++): ?>
                                <?php $completed = in_array($i, $completed_days); ?>
                                <div class="day <?php echo $completed ? 'completed' : ''; ?>" data-day="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                    <?php if (!$completed): ?>
                                        <button class="complete-day"><?php _e('Complete', 'ai-community'); ?></button>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php elseif (is_user_logged_in()): ?>
                        <button class="join-challenge"><?php _e('Join Challenge', 'ai-community'); ?></button>
                    <?php else: ?>
                        <p><?php _e('Please log in to join challenges.', 'ai-community'); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($products)): ?>
                        <div class="affiliate-products">
                            <h5><?php _e('Recommended Products', 'ai-community'); ?></h5>
                            <?php foreach ($products as $product_id): ?>
                                <?php $product = wc_get_product($product_id); ?>
                                <?php if ($product): ?>
                                    <div class="product">
                                        <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                            <?php echo $product->get_image(); ?>
                                            <h6><?php echo esc_html($product->get_name()); ?></h6>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; 
        else: ?>
            <p><?php _e('No active challenges at the moment. Check back soon!', 'ai-community'); ?></p>
        <?php endif; ?>
    </div>
</div>