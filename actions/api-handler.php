<?php
require_once '../includes/config.php';

if (isset($_GET['action']) && isset($_GET['api'])) {
    $action = $_GET['action'];
    $api = $_GET['api'];
    
    if ($action === 'disconnect') {
        $stmt = $pdo->prepare("DELETE FROM settings WHERE setting_key IN (?, ?, ?)");
        
        if ($api === 'gmail') {
            $_SESSION['gmail_connected'] = false;
            $stmt->execute(['gmail_connected', 'gmail_client_id', 'gmail_client_secret']);
        }
        if ($api === 'whatsapp') {
            $_SESSION['whatsapp_connected'] = false;
            $stmt->execute(['whatsapp_connected', 'whatsapp_access_token', 'whatsapp_phone_id']);
        }
        if ($api === 'gemini') {
            $_SESSION['gemini_connected'] = false;
            $_SESSION['demo_mode'] = false;
            $stmt->execute(['gemini_connected', 'gemini_api_key', 'gemini_demo_mode']);
        }
        if ($api === 'chatgpt') {
            $_SESSION['chatgpt_connected'] = false;
            $stmt->execute(['chatgpt_connected', 'chatgpt_api_key', 'chatgpt_model']);
        }
        
        set_flash_message(ucfirst($api) . " disconnected and credentials removed.", "danger");
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
    
    // If the API key is the placeholder, use the one from the session/database
    $placeholders = [
        'sk-proj-xxxxxxxxxxxxxxxxxxxxxxx',
        'AIzaSyB-xxxxxxxxxxxxxxxxxxxxxxx',
        'EAABxxxxxxxxxxxxxxxxxxxxxxx',
        'GOCSPX-xxxxxxxxxxxx',
        '123456-abcde.apps.googleusercontent.com'
    ];
    if (in_array($apiKey, $placeholders)) {
        if ($api === 'chatgpt') {
            $apiKey = $_SESSION['chatgpt_api_key'] ?? '';
            // If model is missing in POST, use session
            if (empty($_POST['model'])) $_POST['model'] = $_SESSION['chatgpt_model'] ?? 'gpt-3.5-turbo';
        }
        if ($api === 'gemini') {
            $apiKey = $_SESSION['gemini_api_key'] ?? '';
            if (empty($_POST['model'])) $_POST['model'] = $_SESSION['gemini_model'] ?? 'gemini-1.5-flash';
        }
        if ($api === 'whatsapp') $apiKey = $_SESSION['whatsapp_access_token'] ?? '';
    }
    
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

        // Use v1beta to fetch models
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $models = [];
            if (isset($data['models']) && is_array($data['models'])) {
                foreach ($data['models'] as $model) {
                    // Extract ID from 'models/gemini-...'
                    $id = str_replace('models/', '', $model['name']);
                    if (strpos($id, 'gemini-') === 0 && in_array('generateContent', $model['supportedGenerationMethods'])) {
                        $models[] = $id;
                    }
                }
            }
            sort($models);
            if (empty($models)) $models = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-1.0-pro'];
            echo json_encode(['success' => true, 'message' => 'Gemini API Key is valid!', 'models' => $models]);
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Gemini API Error (HTTP ' . $httpCode . '): ' . $response;
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }

    if ($api === 'gemini' && $action === 'chat') {
        header('Content-Type: application/json');
        $message = $_POST['message'] ?? 'Hi';
        $model = $_POST['model'] ?? 'gemini-1.5-flash';

        // Always use v1beta for test chat with 1.5 models
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
        $payload = json_encode([
            "contents" => [["parts" => [["text" => $message]]]]
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response content.';
            echo json_encode(['success' => true, 'response' => $reply]);
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Gemini Chat Error (HTTP ' . $httpCode . ')';
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
                $model = $_POST['model'] ?? 'gemini-1.5-flash';
                $prompt = $_POST['research_instructions'] ?? '';
                $_SESSION['gemini_model'] = $model;
                $_SESSION['gemini_prompt'] = $prompt;
                $stmt->execute(['gemini_connected', '1', '1']);
                $stmt->execute(['gemini_api_key', $apiKey, $apiKey]);
                $stmt->execute(['gemini_model', $model, $model]);
                $stmt->execute(['gemini_prompt', $prompt, $prompt]);
            }
            if ($api === 'chatgpt') {
                $_SESSION['chatgpt_connected'] = true;
                $_SESSION['chatgpt_api_key'] = $apiKey;
                $model = $_POST['model'] ?? 'gpt-3.5-turbo';
                $prompt = $_POST['research_instructions'] ?? '';
                $_SESSION['chatgpt_model'] = $model;
                $_SESSION['chatgpt_prompt'] = $prompt;
                $stmt->execute(['chatgpt_connected', '1', '1']);
                $stmt->execute(['chatgpt_api_key', $apiKey, $apiKey]);
                $stmt->execute(['chatgpt_model', $model, $model]);
                $stmt->execute(['chatgpt_prompt', $prompt, $prompt]);
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
