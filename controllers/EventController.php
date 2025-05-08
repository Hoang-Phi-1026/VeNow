<?php
require_once __DIR__ . '/../models/Event.php';

class EventController extends BaseController {
    private $eventModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
    }

    public function index() {
        $featuredEvents = $this->eventModel->getFeaturedEvents();
        $upcomingEvents = $this->eventModel->getUpcomingEvents();
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function show($id) {
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
            return;
        }
        require_once __DIR__ . '/../views/event/show.php';
    }

    public function getFeaturedEvents() {
        return $this->eventModel->getFeaturedEvents();
    }

    public function getUpcomingEvents() {
        return $this->eventModel->getUpcomingEvents();
    }
}
