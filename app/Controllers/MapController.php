<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Order;
use Models\Site;

class MapController extends Controller
{
    private $orderModel;
    private $siteModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->orderModel = new Order();
        $this->siteModel = new Site();
    }

    /**
     * Page de cartographie
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les sites avec coordonnées
        if ($user['role_name'] === 'client') {
            // Clients : seulement leur patrimoine
            $sites = $this->siteModel->query(
                "SELECT s.*, c.organization_name as client_name,
                       COUNT(DISTINCT o.id) as orders_count
                FROM sites s
                LEFT JOIN clients c ON s.client_id = c.id
                LEFT JOIN orders o ON o.site_id = s.id
                WHERE s.client_id = ? AND s.latitude IS NOT NULL AND s.longitude IS NOT NULL
                GROUP BY s.id",
                [$user['client_id']]
            );
        } else {
            // Admins, secrétaires, techniciens : tous les sites
            $sites = $this->siteModel->query(
                "SELECT s.*, c.organization_name as client_name,
                       COUNT(DISTINCT o.id) as orders_count
                FROM sites s
                LEFT JOIN clients c ON s.client_id = c.id
                LEFT JOIN orders o ON o.site_id = s.id
                WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL
                GROUP BY s.id"
            );
        }

        View::render('map.index', [
            'sites' => $sites,
            'user_role' => $user['role_name']
        ]);
    }

    /**
     * Récupère les commandes à proximité d'une adresse
     */
    public function getNearbyOrders()
    {
        $lat = $_GET['lat'] ?? null;
        $lng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 50; // km

        if (!$lat || !$lng) {
            View::json(['error' => 'Coordonnées manquantes'], 400);
        }

        $user = Auth::user();

        // Formule Haversine pour calculer la distance
        $sql = "SELECT o.*, s.name as site_name, s.address, s.latitude, s.longitude,
                       c.organization_name as client_name,
                       st.label as status_label, st.color as status_color,
                       (6371 * acos(cos(radians(?)) * cos(radians(s.latitude)) * 
                       cos(radians(s.longitude) - radians(?)) + 
                       sin(radians(?)) * sin(radians(s.latitude)))) AS distance
                FROM orders o
                LEFT JOIN sites s ON o.site_id = s.id
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses st ON o.status = st.code
                WHERE s.latitude IS NOT NULL AND s.longitude IS NOT NULL";

        $params = [$lat, $lng, $lat];

        // Filtrer par client si nécessaire
        if ($user['role_name'] === 'client') {
            $sql .= " AND o.client_id = ?";
            $params[] = $user['client_id'];
        }

        $sql .= " HAVING distance < ?
                 ORDER BY distance ASC
                 LIMIT 20";

        $params[] = $radius;

        $orders = $this->orderModel->query($sql, $params);

        View::json(['orders' => $orders]);
    }

    /**
     * Recherche d'adresse via API data.gouv
     */
    public function searchAddress()
    {
        $query = $_GET['q'] ?? '';

        if (strlen($query) < 3) {
            View::json(['features' => []]);
        }

        // Appel API data.gouv
        $url = 'https://api-adresse.data.gouv.fr/search/?q=' . urlencode($query) . '&limit=10';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            View::json(json_decode($response, true));
        } else {
            View::json(['features' => []]);
        }
    }
}
