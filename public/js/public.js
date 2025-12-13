/**
 * 前台 JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // 複製推薦連結
        $('.copy-button, .copy-referral-link').on('click', function() {
            var button = $(this);
            var target = button.data('target') || '.referral-input, .coshare-referral-input';
            var input = $(target);
            
            if (input.length) {
                input.select();
                document.execCommand('copy');
                
                var originalText = button.text();
                button.text('已複製！');
                
                setTimeout(function() {
                    button.text(originalText);
                }, 2000);
            }
        });
        
        // 載入收益數據
        function loadEarningsData() {
            $.ajax({
                url: cosharePublic.ajax_url,
                type: 'POST',
                data: {
                    action: 'coshare_get_earnings',
                    nonce: cosharePublic.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        updateEarningsDisplay(response.data);
                    }
                }
            });
        }
        
        // 更新收益顯示
        function updateEarningsDisplay(data) {
            // 更新各類收益數據
            if (data.by_type) {
                $.each(data.by_type, function(type, amount) {
                    $('.earning-' + type + ' .amount').text('NT$ ' + formatNumber(amount));
                });
            }
        }
        
        // 格式化數字
        function formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
        
        // 自動載入數據（如果需要）
        if ($('.coshare-auto-load').length) {
            loadEarningsData();
        }
        
    });
    
})(jQuery);
