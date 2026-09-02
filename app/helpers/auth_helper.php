<?php
/**
 * Auth Helper Functions
 */

function is_logged_in(): bool {
    return Session::has('user_id');
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id' => Session::get('user_id'),
        'user_code' => Session::get('user_code'),
        'name' => Session::get('full_name'),
        'email' => Session::get('email'),
        'role_id' => Session::get('role_id'),
        'role_name' => Session::get('role_name'),
        'department' => Session::get('department')
    ];
}

function is_super_admin(): bool {
    return Session::get('role_id') == 1;
}

function is_admin(): bool {
    return Session::get('role_id') == 2 || Session::get('role_id') == 1;
}

function is_employee(): bool {
    return Session::get('role_id') == 3;
}
