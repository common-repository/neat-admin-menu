(function($) {
        $( "#sortable" ).sortable({
                revert: true
        });
        
        $('.save-wrapper input[type=submit]').on('click', function(e) {
                var orderedItems = new Array();
                $('#sortable li').each(function() {
                        var menuItem = {};
                        
                        menuItem['hidden'] = !$(this).find('input[type="checkbox"]').is(':checked');
                        menuItem['id'] = $(this).attr('id');
                        menuItem['name'] = $(this).attr('name');
                        
                        orderedItems.push(menuItem);
                });
                
                jQuery.ajax({
                        type:'POST',
                        data: {
                                action:'save_neat_admin_menu_settings',
                                orderedItems: orderedItems,
                                nonce: $('#nonce-neat-admin').html()
                        },
                        url: ajaxurl,
                        success: function() { 
                                location.reload();
                        }
                });
        });
})(jQuery);