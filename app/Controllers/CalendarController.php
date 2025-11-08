<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Appointment;
use Models\Intervention;

class CalendarController extends Controller
{
    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
    }

    public function index()
    {
        View::render('calendar.index');
    }

    public function getEvents()
    {
        $user = Auth::user();
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $appointmentModel = new Appointment();
        $interventionModel = new Intervention();

        if ($user['role_name'] === 'technicien') {
            $events = $appointmentModel->getByTechnician($user['id'], $start, $end);
        } else {
            $events = $appointmentModel->getAll($start, $end);
        }

        View::json(['events' => $events]);
    }

    public function createAppointment()
    {
        if (!Auth::can('manage_appointments')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $data = $_POST;

        try {
            $appointmentModel = new Appointment();
            $appointmentId = $appointmentModel->create($data);

            View::json(['success' => true, 'id' => $appointmentId]);
        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateAppointment($id)
    {
        if (!Auth::can('manage_appointments')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        try {
            $appointmentModel = new Appointment();
            $appointmentModel->update($id, $_POST);

            View::json(['success' => true]);
        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }
}
