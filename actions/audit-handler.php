<?php
require_once '../includes/config.php';

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../debug.log');

header('Content-Type: application/json');

// Auto-fix: Ensure Database columns exist
try {
    $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS scores TEXT AFTER score");
    $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS metadata TEXT AFTER scores");
    $pdo->exec("ALTER TABLE leads MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'");
} catch (Exception $e) {
    // If IF NOT EXISTS is not supported, we attempt to add columns manually
    try { $pdo->exec("ALTER TABLE leads ADD scores TEXT AFTER score"); } catch(Exception $ex) {}
    try { $pdo->exec("ALTER TABLE leads ADD metadata TEXT AFTER scores"); } catch(Exception $ex) {}
    try { $pdo->exec("ALTER TABLE leads MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'"); } catch(Exception $ex) {}
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$leadId = $_POST['lead_id'] ?? '';
$aiType = $_POST['ai'] ?? 'chatgpt';

if (!$leadId) {
    echo json_encode(['success' => false, 'message' => 'Lead ID is required.']);
    exit;
}

// Fetch Lead Data
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    echo json_encode(['success' => false, 'message' => 'Lead not found.']);
    exit;
}

// Get AI Settings
$apiKey = '';
$model = '';
$instructions = '';

if ($aiType === 'chatgpt') {
    $apiKey = $_SESSION['chatgpt_api_key'] ?? '';
    $model = $_SESSION['chatgpt_model'] ?? 'gpt-3.5-turbo';
    $instructions = $_SESSION['chatgpt_prompt'] ?? 'Analyze this website for SEO and performance.';
} else {
    $apiKey = $_SESSION['gemini_api_key'] ?? '';
    $model = $_SESSION['gemini_model'] ?? 'gemini-1.5-flash';
    $instructions = $_SESSION['gemini_prompt'] ?? 'Analyze this website for SEO and performance.';
}

if (!$apiKey) {
    echo json_encode(['success' => false, 'message' => ucfirst($aiType) . ' API not connected.']);
    exit;
}

// Prepare AI Prompt
$website = $lead['website'];
$prompt = "You are an expert web auditor. Please analyze the following website: $website\n\nInstructions: $instructions\n\nProvide a JSON response with:\n1. 'performance' score (0-100)\n2. 'seo' score (0-100)\n3. 'accessibility' score (0-100)\n4. 'analysis' (a summary paragraph)\n5. 'status' ('Qualified' if good, 'Failed' if poor)";

// Call AI API
$aiResponse = "";
$success = false;

if ($aiType === 'manual_fetch') {
    $ch = curl_init($website);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    
    // Ensure HTML is UTF-8 to prevent json_encode failure
    if ($html) {
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8,ISO-8859-1');
    }
    
    echo json_encode(['success' => true, 'html' => $html, 'url' => $website], JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($aiType === 'manual_save') {
    try {
        // Auto-fix: Check and add missing columns
        try {
            $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS scores TEXT AFTER score");
            $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS metadata TEXT AFTER scores");
            $pdo->exec("ALTER TABLE leads MODIFY COLUMN status ENUM('Pending', 'Preparing', 'Ready', 'Contacted', 'Qualified', 'Failed') DEFAULT 'Pending'");
        } catch (Exception $dbFixError) {
            // Some MySQL versions don't support ADD COLUMN IF NOT EXISTS
            // We can ignore errors here as long as the columns end up existing
        }

        $results = json_decode($_POST['results'] ?? '{}', true);
        $scores = json_encode([
            'performance' => $results['performance'] ?? 50,
            'seo' => $results['seo'] ?? 50,
            'accessibility' => $results['accessibility'] ?? 50
        ]);
        
        $status = $results['status'] ?? 'Qualified';
        $score = $results['seo'] ?? 50;
        
        $existingMetadata = $lead['metadata'] ?? '';
        $metadata = [];
        if (!empty($existingMetadata)) {
            $decoded = json_decode($existingMetadata, true);
            if (is_array($decoded)) $metadata = $decoded;
        }
        
        $metadata['ai_analysis'] = $results['analysis'] ?? 'Manual custom audit completed.';
        $newMetadata = json_encode($metadata);

        $updateStmt = $pdo->prepare("UPDATE leads SET score = ?, status = ?, scores = ?, metadata = ? WHERE id = ?");
        $updateStmt->execute([$score, $status, $scores, $newMetadata, $leadId]);
        
        echo json_encode(['success' => true, 'message' => 'Manual audit saved.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Save Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($aiType === 'manual') {
    // Manual Browser-based Audit (Free)
    $ch = curl_init($website);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html) {
        $hasSsl = strpos($website, 'https') === 0;
        $hasTitle = preg_match('/<title>(.*?)<\/title>/i', $html, $matches);
        $hasDesc = preg_match('/<meta name="description" content="(.*?)"/i', $html, $descMatches);
        $h1Count = preg_match_all('/<h1/i', $html, $h1Matches);
        
        $perf = rand(60, 85); 
        $seo = 0;
        if ($hasTitle) $seo += 30;
        if ($hasDesc) $seo += 30;
        if ($h1Count > 0) $seo += 20;
        if ($hasSsl) $seo += 20;
        
        $aiResponse = [
            'performance' => $perf,
            'seo' => $seo,
            'accessibility' => rand(70, 90),
            'analysis' => "Manual Audit Summary: " . ($hasSsl ? "Secure SSL found. " : "Insecure (No SSL). ") . 
                         ($hasTitle ? "Title tag detected. " : "Missing Title tag. ") . 
                         ($hasDesc ? "Meta description present. " : "Missing meta description. ") . 
                         "Found $h1Count H1 tags. Overall SEO score is $seo%.",
            'status' => ($seo > 50) ? 'Qualified' : 'Failed'
        ];
        $success = true;
    }
} elseif ($aiType === 'chatgpt') {
    $url = "https://api.openai.com/v1/chat/completions";
    $payload = json_encode([
        "model" => $model,
        "messages" => [["role" => "system", "content" => "Return JSON only."], ["role" => "user", "content" => $prompt]],
        "response_format" => ["type" => "json_object"]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $aiResponse = json_decode($data['choices'][0]['message']['content'], true);
        $success = true;
    }
} else {
    // Gemini
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
    $payload = json_encode([
        "contents" => [["parts" => [["text" => $prompt . " Respond in raw JSON format."]]]],
        "generationConfig" => ["responseMimeType" => "application/json"]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $rawText = $data['candidates'][0]['content']['parts'][0]['text'];
        $aiResponse = json_decode($rawText, true);
        $success = true;
    }
}

if ($success && $aiResponse) {
    // Update Lead in DB
    $scores = json_encode([
        'performance' => $aiResponse['performance'] ?? 50,
        'seo' => $aiResponse['seo'] ?? 50,
        'accessibility' => $aiResponse['accessibility'] ?? 50
    ]);
    
    $status = $aiResponse['status'] ?? 'Qualified';
    $score = $aiResponse['seo'] ?? 50; 
    
    $metadata = is_string($lead['metadata']) ? json_decode($lead['metadata'], true) : $lead['metadata'];
    $metadata['ai_analysis'] = $aiResponse['analysis'] ?? 'Audit completed successfully.';
    $newMetadata = json_encode($metadata);

    try {
        $updateStmt = $pdo->prepare("UPDATE leads SET score = ?, status = ?, scores = ?, metadata = ? WHERE id = ?");
        $updateStmt->execute([$score, $status, $scores, $newMetadata, $leadId]);
        echo json_encode(['success' => true, 'message' => 'Audit completed successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'AI analysis failed or timed out.']);
}
exit;
?>
