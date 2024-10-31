<div class="neat-admin-menu-container">
        <h2>Neat Admin Menu</h2>
        
        <p id="fp-description">Drag and drop the menu items below in order to arrange their positions. To hide a menu item, just uncheck it.</p>
        
        <ul id="sortable">
        <?php foreach ($menuFromSettings as $key => $menuItem):?>
                    <li class="ui-state-default" id="<?php echo $menuItem['id']; ?>" name="<?php echo $menuItem['name']; ?>">
                            <input type="checkbox" 
                                    <?php echo ($menuItem['id'] == 'neat-admin-menu.php') ? 'disabled' : ''; ?> 
                                   value="<?php echo $menuItem['id']; ?>"
                                   <?php echo ($menuItem['hidden'] != 'true') ? 'checked' : ''; ?>/>
                            <?php echo esc_html($menuItem['name']); ?>
                    </li>
                <?php endforeach; ?>
        </ul>
        
        <div id="nonce-neat-admin" style="display: none;"><?php echo wp_create_nonce($this->nonceName); ?></div>
        
        <div class="save-container">
                <div class="save-wrapper">
                        <input type="submit" value="<?php _e('Save Changes') ?>" class="button button-primary"/>
                </div>
        </div>
</div>