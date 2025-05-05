<div class="wrap">
    <h1><?php _e('Forum Settings', 'ai-community'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('ai_community_forum_settings'); ?>
        <?php do_settings_sections('ai_community_forum_settings'); ?>
        
        <h2><?php _e('Forum Categories', 'ai-community'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Enabled Categories', 'ai-community'); ?></th>
                <td>
                    <?php 
                    $categories = $this->get_categories();
                    $enabled_categories = get_option('ai_community_enabled_categories', array_keys($categories));
                    
                    foreach ($categories as $slug => $name): ?>
                        <label>
                            <input type="checkbox" name="ai_community_enabled_categories[]" 
                                   value="<?php echo esc_attr($slug); ?>" 
                                   <?php checked(in_array($slug, $enabled_categories)); ?>>
                            <?php echo esc_html($name); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Moderation', 'ai-community'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="ai_community_forum_moderation" 
                               value="1" <?php checked(get_option('ai_community_forum_moderation', 1)); ?>>
                        <?php _e('Require moderation for new topics and replies', 'ai-community'); ?>
                    </label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Affiliate Links', 'ai-community'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="ai_community_allow_affiliate_links" 
                               value="1" <?php checked(get_option('ai_community_allow_affiliate_links', 1)); ?>>
                        <?php _e('Allow affiliate links in forum posts', 'ai-community'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>