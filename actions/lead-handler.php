<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$leadsFile = '../data/leads.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['leads_file'])) {
        $file = $_FILES['leads_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File upload error.']);
            exit;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'csv') {
            echo json_encode(['success' => false, 'message' => 'Please upload a valid CSV file.']);
            exit;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to open the file.']);
            exit;
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            echo json_encode(['success' => false, 'message' => 'CSV file is empty.']);
            exit;
        }

        // Handle BOM (Byte Order Mark) for the first header
        if (substr($headers[0], 0, 3) == "\xEF\xBB\xBF") {
            $headers[0] = substr($headers[0], 3);
        }

        // Normalize headers to lowercase and underscore
        $headers = array_map(function($h) {
            return strtolower(str_replace([' ', '-'], '_', trim($h)));
        }, $headers);

        $newLeads = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Basic check to see if row has content
            if (empty(array_filter($row))) continue;
            
            // If row has fewer columns than headers, pad it
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            } else if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }
            
            $leadData = array_combine($headers, $row);
            
            // Flexible matching for company and website
            $companyName = '';
            foreach (['company_name', 'company', 'name', 'business_name', 'business'] as $key) {
                if (!empty($leadData[$key])) {
                    $companyName = $leadData[$key];
                    break;
                }
            }

            $website = '';
            foreach (['website', 'url', 'site', 'link', 'web'] as $key) {
                if (!empty($leadData[$key])) {
                    $website = $leadData[$key];
                    break;
                }
            }
            
            if (empty($companyName)) continue;

            $newLeads[] = [
                'id' => uniqid(),
                'company_name' => $companyName,
                'website' => $website,
                'email' => $leadData['email'] ?? $leadData['e-mail'] ?? '',
                'phone' => $leadData['phone'] ?? $leadData['telephone'] ?? $leadData['mobile'] ?? '',
                'contact_name' => $leadData['contact_name'] ?? $leadData['contact'] ?? $leadData['person'] ?? '',
                'status' => 'Pending',
                'score' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'last_outreach' => 'Never',
                'metadata' => $leadData // Store all original data
            ];
        }
        fclose($handle);

        if (empty($newLeads)) {
            echo json_encode(['success' => false, 'message' => 'No valid leads found in the CSV.']);
            exit;
        }

        // Load existing leads
        $existingLeads = [];
        if (file_exists($leadsFile)) {
            $existingLeads = json_decode(file_get_contents($leadsFile), true) ?? [];
        }

        // Merge and save
        $allLeads = array_merge($existingLeads, $newLeads);
        file_put_contents($leadsFile, json_encode($allLeads, JSON_PRETTY_PRINT));

        echo json_encode([
            'success' => true, 
            'message' => count($newLeads) . ' leads uploaded successfully!',
            'count' => count($newLeads)
        ]);
        exit;
    }

    // Manual entry
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data && isset($data['company_name'])) {
        $existingLeads = [];
        if (file_exists($leadsFile)) {
            $existingLeads = json_decode(file_get_contents($leadsFile), true) ?? [];
        }

        $newLead = [
            'id' => uniqid(),
            'company_name' => $data['company_name'],
            'website' => $data['website'] ?? '',
            'email' => '',
            'phone' => '',
            'contact_name' => '',
            'status' => 'Pending',
            'score' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'last_outreach' => 'Never'
        ];

        $existingLeads[] = $newLead;
        file_put_contents($leadsFile, json_encode($existingLeads, JSON_PRETTY_PRINT));

        echo json_encode(['success' => true, 'message' => 'Lead added successfully!']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
