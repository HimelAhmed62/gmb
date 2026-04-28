<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    $newStatus = $data['status'] ?? '';
    
    if (!$id || !$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Missing ID or status']);
        exit;
    }

    $leadsFile = '../data/leads.json';
    if (file_exists($leadsFile)) {
        $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
        $found = false;
        
        foreach ($leads as &$lead) {
            if ($lead['id'] === $id) {
                $lead['status'] = $newStatus;
                $found = true;
                break;
            }
        }
        
        if ($found) {
            file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lead not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Leads file not found']);
    }
    exit;
}
