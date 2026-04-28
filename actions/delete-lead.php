<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$leadsFile = '../data/leads.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idsToDelete = $data['ids'] ?? [];

    if (empty($idsToDelete)) {
        echo json_encode(['success' => false, 'message' => 'No leads selected for deletion.']);
        exit;
    }

    if (file_exists($leadsFile)) {
        $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
        $originalCount = count($leads);
        
        $leads = array_filter($leads, function($lead) use ($idsToDelete) {
            return !in_array($lead['id'], $idsToDelete);
        });

        // Re-index array
        $leads = array_values($leads);
        
        if (file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT))) {
            echo json_encode([
                'success' => true, 
                'message' => ($originalCount - count($leads)) . ' leads deleted successfully.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update leads file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Leads file not found.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
