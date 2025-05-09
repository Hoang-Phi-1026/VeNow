<?php

class HomeController {
    public function index() {
        $eventModel = new Event();
        $featuredEvents = $eventModel->getFeaturedEvents();
        $upcomingEvents = $eventModel->getUpcomingEvents();
        $categories = $eventModel->getEventCategories();
        
        require_once __DIR__ . '/../views/home/index.php';
    }
} 