/**
 * 管理後台 JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // 手動發放積分
        $('#manual-distribute-points').on('click', function() {
            if (!confirm('確定要手動發放本月積分嗎？此操作將立即執行。')) {
                return;
            }
            
            var button = $(this);
            var originalText = button.text();
            button.prop('disabled', true).text('處理中...');
            
            $.ajax({
                url: coshareAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'coshare_manual_distribute_points',
                    nonce: coshareAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message || '積分發放完成');
                        location.reload();
                    } else {
                        alert(response.data.message || '發放失敗');
                        button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('發放失敗，請稍後再試');
                    button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // 確認刪除操作
        $('.delete-action').on('click', function(e) {
            if (!confirm('確定要刪除嗎？此操作無法復原。')) {
                e.preventDefault();
            }
        });
        
        // 表單驗證
        $('form[data-validate="true"]').on('submit', function(e) {
            var form = $(this);
            var isValid = true;
            
            form.find('[required]').each(function() {
                var field = $(this);
                if (!field.val()) {
                    field.css('border-color', '#dc3232');
                    isValid = false;
                } else {
                    field.css('border-color', '');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('請填寫所有必填欄位');
            }
        });
        
        // 數字輸入限制
        $('input[type="number"][step="0.01"]').on('input', function() {
            var value = parseFloat($(this).val());
            var min = parseFloat($(this).attr('min'));
            var max = parseFloat($(this).attr('max'));
            
            if (value < min) {
                $(this).val(min);
            } else if (value > max) {
                $(this).val(max);
            }
        });
        
    });
    
})(jQuery);
