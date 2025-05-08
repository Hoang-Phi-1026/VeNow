<?php

class View {
    public static function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once BASE_PATH . '/views/' . $view . '.php';
        
        // Get the contents of the buffer
        $content = ob_get_clean();
        
        // Include the layout
        require_once BASE_PATH . '/views/layouts/main.php';
    }
}
