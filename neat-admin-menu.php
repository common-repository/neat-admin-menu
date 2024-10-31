<?php
/*
Plugin Name: Neat Admin Menu
Plugin URI: 
Description: Allows to hide and sort WP Admin menu items.
Version: 1.1
Author: Blas Asenjo
License: GNU
*/

class NeatAdminMenu {
        /**
         * @var string
         */
        protected $url;
        
        /**
         * @var string
         */
        protected $version = '1.1';
        
        /**
         * @var string
         */
        protected $userMetaKey = 'neat_wp_admin_settings';
        
        /**
         * @var string
         */
        protected $nonceName = 'neat_wp_admin_ajax_nonce';
        
        /**
         * Constructor
         */
        public function __construct() {    
                $this->url = plugins_url(plugin_basename(dirname(__FILE__)).'/');
                
                add_action('wp_ajax_save_neat_admin_menu_settings', array($this, 'saveSettings') );
                
                if (is_admin()) {
                        wp_enqueue_style('neat-admin-menu-style', $this->url . 'css/style.css', array(), $this->version);
                        
                        $this->buildMenus();
                        
                        add_filter( 'admin_menu', array($this, 'hideAdminMenuItems'), 999);
                        add_filter( 'menu_order', array($this, 'applyCustomOrderToAdminMenu'), 999);
                }
        }

        /**
         * Hides menu items
         * 
         * @global $menu
         */
        public function hideAdminMenuItems() {
                global $menu;

                $menuFromSettings = $this->buildMenuFromSettings();

                // Go through the items in the global menu, and add the "hidden" class
                // to those items marked as hidden in the user settings
                foreach ($menu as $k => $item) {
                        foreach ($menuFromSettings as $menuItem) {
                                if ($menuItem['id'] == $item[2]) {
                                        if ($menuItem['hidden'] == 'true') {
                                                $menu[$k][4] = $item[4] . ' neat-admin-menu-hidden';
                                        }
                                        
                                        break;
                                }
                        }
                }
        }
        
        /**
         * Applies custom order to menu items
         * 
         * @param int $menu_ord
         * 
         * @return boolean
         */
        public function applyCustomOrderToAdminMenu($menu_ord) {
                if ( !$menu_ord ) {
                        return true;
                }

                $menuFromSettings = $this->buildMenuFromSettings();
                
                // Return an array with the page ids in order
                return array_map(function($item) {
                        return $item['id'];
                }, $menuFromSettings);
        }
        
        /**
         * Adds link to admin page to the menu
         */
        public function buildMenus() {
                add_action('admin_menu', function() {
                        add_menu_page(
                                'Neat Admin Menu',
                                'Neat Admin Menu',
                                0,
                                basename(__FILE__),
                                array($this, 'buildOptionsPage'),
                                'dashicons-sort'
                        );      
                });  
        }
        
        /**
         * Builds options page
         */
        public function buildOptionsPage() {
                $menuFromSettings = $this->buildMenuFromSettings();
                
                wp_enqueue_script('neat-admin-menu-main-js', $this->url . 'js/main.js', array(), $this->version, true);
                wp_enqueue_style('jquery-ui-smoothness', $this->url . 'css/jquery-ui.css');

                include('neat-admin-menu-admin.php');
        }
        
        /**
         * Builds the WP Admin menu to display, based on the user settings, if any.
         * 
         * @return array
         */
        private function buildMenuFromSettings() {
                $userSettings = $this->getSettings();
                
                $standardMenuAsSimpleArray = $this->getStandardMenuAsSimpleArray();
                
                // go through menu items in user settings
                // if the menu exists in standard menu, add it to menuFromSettings.
                $menuFromSettings = array();
                foreach ($userSettings as $menuItem) {
                        // Only if menu item['id'] exists in standardMenu, add to menuFromSettings
                        foreach ($standardMenuAsSimpleArray as $k => $standardMenuItem) {
                                if ($standardMenuItem['id'] == $menuItem['id']) {
                                        $menuFromSettings[] = $menuItem;
                                        
                                        unset($standardMenuAsSimpleArray[$k]);
                                        
                                        break;
                                }
                        }
                }
                
                // Add menu items from the standard menu that were not present in userSettings.
                // In other words, add the standard menu items that were not unset in the foreach above
                foreach ($standardMenuAsSimpleArray as $standardMenuItem) {
                        $menuFromSettings[] = $standardMenuItem;
                }
                
                return $menuFromSettings;
        }
        
        /**
         * Gets the @global $menu as a simple array, better fit for the purposes of the plugin.
         * 
         * @global $menu
         * 
         * @return array
         */
        private function getStandardMenuAsSimpleArray() {
                global $menu;
                
                $standardMenu = array();
                foreach ($menu as $menuItem) {
                        if (strpos($menuItem[4], 'wp-menu-separator') !== false) {
                                continue;
                        }
                        
                        $standardMenu[] = array(
                            'id' => $menuItem[2],
                            'hidden' => false,
                            'name' => explode('<', $menuItem[0])[0] // Some menu items have names such as "Plugins <span>...". This just removes everything after "<".
                        );
                }
                
                return $standardMenu;
        }
        
        /**
         * Saves settings for the current user
         */
        public function saveSettings() {
                check_ajax_referer($this->nonceName, 'nonce');

                $orderedItems = $this->sanitizeOrderedItems($_POST['orderedItems']);
                $serializedItems = serialize($orderedItems);
                update_user_meta(get_current_user_id(), $this->userMetaKey, $serializedItems);

                echo 1;
                exit;
        }
        
        /**
         * Gets settings for the current user
         * 
         * @return array
         */
        private function getSettings() {
                if (!$settings = get_user_meta(get_current_user_id(), $this->userMetaKey, true)) {
                        return array();
                }
                
                return unserialize($settings);
        }
        
        /**
         * Sanitizes values of the ordered items
         * 
         * @param array $orderedItems
         * 
         * @return array
         */
        private function sanitizeOrderedItems($orderedItems) {
                $sanitizedArray = array();
                
                foreach ($orderedItems as $k => $menuItem) {
                        foreach ($menuItem as $j => $v) {
                                $sanitizedArray[$k][$j] = sanitize_text_field($v);
                        }
                }
                
                return $sanitizedArray;
        }
}

new NeatAdminMenu();