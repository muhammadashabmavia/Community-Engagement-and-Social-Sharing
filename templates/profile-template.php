<div class="ai-community-profile">
    <div class="profile-header">
        <h2><?php _e('My Community Profile', 'ai-community'); ?></h2>
    </div>
    
    <div class="profile-stats">
        <div class="stat-points">
            <h3><?php _e('Your Points', 'ai-community'); ?></h3>
            <div class="points-value"><?php echo $reward_system->get_user_points(get_current_user_id()); ?></div>
        </div>
        
        <div class="stat-challenges">
            <h3><?php _e('Challenges Completed', 'ai-community'); ?></h3>
            <div class="challenges-value">
                <?php
                global $wpdb;
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}ai_community_challenge_participants 
                    WHERE user_id = %d AND is_completed = 1",
                    get_current_user_id()
                ));
                echo $count ?: 0;
                ?>
            </div>
        </div>
    </div>
    
    <div class="profile-rewards">
        <h3><?php _e('Your Rewards', 'ai-community'); ?></h3>
        <?php $rewards = $reward_system->get_user_rewards(get_current_user_id()); ?>
        <?php if ($rewards): ?>
            <div class="rewards-grid">
                <?php foreach ($rewards as $reward): ?>
                    <div class="reward-item">
                        <h4><?php echo esc_html($reward->name); ?></h4>
                        <p><?php echo esc_html($reward->description); ?></p>
                        <div class="reward-date"><?php echo date_i18n(get_option('date_format'), strtotime($reward->redeemed_date)); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('You haven\'t earned any rewards yet.', 'ai-community'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="profile-available-rewards">
        <h3><?php _e('Available Rewards', 'ai-community'); ?></h3>
        <?php $available = $reward_system->get_available_rewards(); ?>
        <?php if ($available): ?>
            <div class="rewards-grid">
                <?php foreach ($available as $reward): ?>
                    <div class="reward-item">
                        <h4><?php echo esc_html($reward->name); ?></h4>
                        <p><?php echo esc_html($reward->description); ?></p>
                        <div class="points-required"><?php printf(__('%d points', 'ai-community'), $reward->points_required); ?></div>
                        <button class="redeem-reward" data-reward-id="<?php echo $reward->reward_id; ?>">
                            <?php _e('Redeem', 'ai-community'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No rewards available at this time.', 'ai-community'); ?></p>
        <?php endif; ?>
    </div>
</div>