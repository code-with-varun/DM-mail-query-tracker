<?php
/**
 * Home / Launchpad / cPanel Controller
 */

class Home extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $user = current_user();

        $this->render('home/index', [
            'title' => 'Home Hub & Control Panel',
            'user' => $user
        ]);
    }
}
