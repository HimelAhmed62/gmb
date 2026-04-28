<?php
require_once '../includes/config.php';

if (isset($_GET['action']) && isset($_GET['api'])) {
    $action = $_GET['action'];
    $api = $_GET['api'];
    
    if ($action === 'disconnect') {
        if ($api === 'gmail') $_SESSION['gmail_connected'] = false;
        if ($api === 'whatsapp') $_SESSION['whatsapp_connected'] = false;
        if ($api === 'gemini') {
            $_SESSION['gemini_connected'] = false;
            $_SESSION['demo_mode'] = false;
        }
        if ($api === 'chatgpt') $_SESSION['chatgpt_connected'] = false;
        
        set_flash_message(ucfirst($api) . " disconnected successfully", "danger");
    }
    
    if ($action === 'connect') {
        if ($api === 'gmail') $_SESSION['gmail_connected'] = true;
        if ($api === 'whatsapp') $_SESSION['whatsapp_connected'] = true;
        if ($api === 'gemini') $_SESSION['gemini_connected'] = true;
        if ($api === 'chatgpt') $_SESSION['chatgpt_connected'] = true;
        
        set_flash_message(ucfirst($api) . " connected successfully", "success");
    }

    if ($action === 'demo' && $api === 'gemini') {
        $_SESSION['gemini_connected'] = true;
        $_SESSION['demo_mode'] = true;
        $_SESSION['gemini_api_key'] = 'DEMO_KEY_LOCAL';
        set_flash_message("Gemini Demo Mode enabled! You can now test analysis features.", "info");
    }
    
    // Redirect back to the referring page or settings
    $referer = $_SERVER['HTTP_REFERER'] ?? '../settings.php';
    header("Location: " . $referer);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api = $_POST['api'] ?? '';
    $action = $_POST['action'] ?? 'connect';
    $apiKey = $_POST['api_key'] ?? '';
    
    if ($api === 'chatgpt' && $action === 'verify') {
        header('Content-Type: application/json');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'API Key is missing.']);
            exit;
        }

        $url = "https://api.openai.com/v1/models";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'message' => 'Network/Connection Error: ' . $curlError . ' (Code: ' . curl_errno($ch) . ')']);
            exit;
        }

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $models = [];
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $model) {
                    if (isset($model['id']) && strpos($model['id'], 'gpt-') === 0) {
                        $models[] = $model['id'];
                    }
                }
            }
            sort($models);
            if (empty($models)) $models = ['gpt-3.5-turbo', 'gpt-4', 'gpt-4o'];
            echo json_encode(['success' => true, 'message' => 'OpenAI API Key is valid.', 'models' => $models]);
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'OpenAI Response Error (HTTP ' . $httpCode . '): ' . $response;
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

    if ($api === 'chatgpt' && $action === 'chat') {
        header('Content-Type: application/json');
        $message = $_POST['message'] ?? 'Hi';
        $model = $_POST['model'] ?? 'gpt-3.5-turbo';

        $url = "https://api.openai.com/v1/chat/completions";
        $payload = json_encode([
            "model" => $model,
            "messages" => [["role" => "user", "content" => $message]],
            "max_tokens" => 100
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $reply = $data['choices'][0]['message']['content'] ?? 'No response content.';
            echo json_encode(['success' => true, 'response' => $reply]);
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Chat Error (HTTP ' . $httpCode . ')';
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

    if ($api === 'gemini' && $action === 'verify') {
        header('Content-Type: application/json');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'Gemini API Key is missing.']);
            exit;
        }

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
        $payload = json_encode(["contents" => [["parts" => [["text" => "hi"]]]]]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'message' => 'Gemini Network Error.']);
            exit;
        }

        if ($httpCode === 200) {
            echo json_encode(['success' => true, 'message' => 'Gemini API Key is valid!']);
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? $errorData[0]['error']['message'] ?? 'Gemini API Error (HTTP ' . $httpCode . '): ' . $response;
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

    if ($api) {
        if ($action === 'connect') {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            
            if ($api === 'gmail') {
                $_SESSION['gmail_connected'] = true;
                $stmt->execute(['gmail_connected', '1', '1']);
            }
            if ($api === 'whatsapp') {
                $_SESSION['whatsapp_connected'] = true;
                $stmt->execute(['whatsapp_connected', '1', '1']);
            }
            if ($api === 'gemini') {
                $_SESSION['gemini_connected'] = true;
                $_SESSION['gemini_api_key'] = $apiKey;
                $stmt->execute(['gemini_connected', '1', '1']);
                $stmt->execute(['gemini_api_key', $apiKey, $apiKey]);
            }
            if ($api === 'chatgpt') {
                $_SESSION['chatgpt_connected'] = true;
                $_SESSION['chatgpt_api_key'] = $apiKey;
                $model = $_POST['model'] ?? 'gpt-3.5-turbo';
                $_SESSION['chatgpt_model'] = $model;
                $stmt->execute(['chatgpt_connected', '1', '1']);
                $stmt->execute(['chatgpt_api_key', $apiKey, $apiKey]);
                $stmt->execute(['chatgpt_model', $model, $model]);
            }
            set_flash_message(ucfirst($api) . " connected successfully", "success");
        }
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '../settings.php';
    header("Location: " . $referer);
    exit();
}
?>
?>
