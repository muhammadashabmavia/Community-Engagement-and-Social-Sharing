jQuery(document).ready(function($) {
    // Tab functionality
    $('.activity-tabs li a').on('click', function(e) {
        e.preventDefault();
        
        // Get the target tab
        var target = $(this).attr('href');
        
        // Update active tab
        $('.activity-tabs li').removeClass('active');
        $(this).parent().addClass('active');
        
        // Show target content
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Reward deletion confirmation
    $('button.delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
    
    // Challenge day completion
    $('.complete-day').on('click', function() {
        var day = $(this).closest('.day').data('day');
        var challengeId = $(this).closest('.challenge').data('challenge-id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'complete_challenge_day',
                challenge_id: challengeId,
                day: day,
                social_url: 'https://example.com/share', // In real implementation, get actual URL
                nonce: aiCommunity.nonce
            },
            beforeSend: function() {
                button.text('Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    button.closest('.day').addClass('completed');
                    button.remove();
                    location.reload(); // Refresh to update points
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Reward redemption
    $('.redeem-reward').on('click', function() {
        var rewardId = $(this).data('reward-id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'redeem_reward',
                reward_id: rewardId,
                nonce: aiCommunity.nonce
            },
            beforeSend: function() {
                button.text('Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('Reward redeemed successfully!');
                    location.reload();
                } else {
                    alert(response.data);
                    button.text('Redeem').prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.text('Redeem').prop('disabled', false);
            }
        });
    });
});