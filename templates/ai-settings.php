<div class="wrap">
    <h1><?php _e('AI Settings', 'ai-community'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('ai_community_ai_settings'); ?>
        <?php do_settings_sections('ai_community_ai_settings'); ?>
        
        <h2><?php _e('OpenAI Configuration', 'ai-community'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="ai_community_openai_key"><?php _e('API Key', 'ai-community'); ?></label></th>
                <td>
                    <input type="password" id="ai_community_openai_key" name="ai_community_openai_key" 
                           value="<?php echo esc_attr(get_option('ai_community_openai_key')); ?>" class="regular-text">
                    <p class="description"><?php _e('Enter your OpenAI API key for content moderation and hashtag generation.', 'ai-community'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><label for="ai_community_moderation_enabled"><?php _e('Content Moderation', 'ai-community'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="ai_community_moderation_enabled" name="ai_community_moderation_enabled" 
                               value="1" <?php checked(1, get_option('ai_community_moderation_enabled', 1)); ?>>
                        <?php _e('Enable AI content moderation for forum posts', 'ai-community'); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><label for="ai_community_auto_hashtags"><?php _e('Hashtag Generation', 'ai-community'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="ai_community_auto_hashtags" name="ai_community_auto_hashtags" 
                               value="1" <?php checked(1, get_option('ai_community_auto_hashtags', 1)); ?>>
                        <?php _e('Enable automatic hashtag generation for challenges', 'ai-community'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Affiliate Integration', 'ai-community'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="ai_community_affiliate_api_key"><?php _e('Affiliate API Key', 'ai-community'); ?></label></th>
                <td>
                    <input type="password" id="ai_community_affiliate_api_key" name="ai_community_affiliate_api_key" 
                           value="<?php echo esc_attr(get_option('ai_community_affiliate_api_key')); ?>" class="regular-text">
                    <p class="description"><?php _e('Enter your affiliate API key to track sales from challenge participants.', 'ai-community'); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><label for="ai_community_affiliate_rate"><?php _e('Commission Rate', 'ai-community'); ?></label></th>
                <td>
                    <input type="number" id="ai_community_affiliate_rate" name="ai_community_affiliate_rate" 
                           value="<?php echo esc_attr(get_option('ai_community_affiliate_rate', '20')); ?>" min="0" max="100" step="0.1">
                    <span>%</span>
                    <p class="description"><?php _e('Default commission rate for affiliate sales.', 'ai-community'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>