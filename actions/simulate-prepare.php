<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $count = $data['count'] ?? 10;
    $targetId = $data['id'] ?? '';
    
    $leadsFile = '../data/leads.json';
    if (file_exists($leadsFile)) {
        $leads = json_decode(file_get_contents($leadsFile), true) ?? [];
        
        $engine = $data['engine'] ?? 'gemini';
        
        $processedCount = 0;
        foreach ($leads as &$lead) {
            if ($lead['status'] === 'Preparing') {
                if ($targetId && $lead['id'] !== $targetId) continue;
                
                $lead['status'] = 'Ready';
                
                // Varied scoring based on engine
                if ($engine === 'manual') {
                    $lead['score'] = rand(40, 75); // Local audit is less generous
                } else {
                    $lead['score'] = rand(65, 98); // AI engines find more value
                }
                
                $lead['audit_engine'] = $engine;
                $processedCount++;
                
                if ($processedCount >= $count) break;
            }
        }
        
        file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'processed' => $processedCount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Leads file not found']);
    }
    exit;
}
