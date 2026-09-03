<?php
/**
 * StudyHub Point - Slide Editor & Panel (Layout 2: Large Right Image Panel)
 */

$jsonFile = __DIR__ . '/current_affairs_enriched.json';
if (!file_exists($jsonFile)) {
    $jsonFile = __DIR__ . '/current_affairs.json';
}

// Handle AJAX actions
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'load') {
        if (!file_exists($jsonFile)) {
            echo json_encode(['error' => 'JSON file not found: ' . basename($jsonFile)]);
            exit;
        }
        $raw = file_get_contents($jsonFile);
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'Invalid JSON in file: ' . json_last_error_msg()]);
            exit;
        }
        if (isset($data['mcqs'])) $data = $data['mcqs'];
        if (!is_array($data)) {
            echo json_encode(['error' => 'JSON data is not an array']);
            exit;
        }
        
        $getCachedUrl = function($url) {
            if (empty($url)) return null;
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($ext)) $ext = 'png';
            $cacheRelative = 'cache/' . md5($url) . '.' . $ext;
            if (file_exists(__DIR__ . '/' . $cacheRelative)) {
                return $cacheRelative;
            }
            return null;
        };
        
        foreach ($data as &$item) {
            if (!empty($item['logo_url_1'])) {
                $item['logo_url_1_cached'] = $getCachedUrl($item['logo_url_1']);
            }
            if (!empty($item['explanation_image_url'])) {
                $item['explanation_image_url_cached'] = $getCachedUrl($item['explanation_image_url']);
            }
            if (!empty($item['exam_focus_image_url'])) {
                $item['exam_focus_image_url_cached'] = $getCachedUrl($item['exam_focus_image_url']);
            }
            if (!empty($item['related_facts_image_url'])) {
                $item['related_facts_image_url_cached'] = $getCachedUrl($item['related_facts_image_url']);
            }
        }
        unset($item);
        
        echo json_encode($data);
        exit;
    }
    
    if ($_GET['action'] === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        
        $jsonStr = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $ok1 = file_put_contents(__DIR__ . '/current_affairs_enriched.json', $jsonStr);
        $ok2 = file_put_contents(__DIR__ . '/current_affairs.json', $jsonStr);
        $success = ($ok1 !== false || $ok2 !== false);
        
        echo json_encode(['success' => $success, 'error' => $success ? null : 'Failed to write to file']);
        exit;
    }
    
    if ($_GET['action'] === 'generate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        @set_time_limit(0);
        ob_start();
        include __DIR__ . '/generate_slides.php';
        $output = ob_get_clean();
        
        echo json_encode(['success' => true, 'output' => $output]);
        exit;
    }

    if ($_GET['action'] === 'generate_pdf' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        @set_time_limit(0);
        $cmd = 'python "' . __DIR__ . '/slidepdfgen.py" 2>&1';
        $output = shell_exec($cmd);
        echo json_encode(['success' => true, 'output' => $output]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyHub Point - Slide Editor (Large Image Layout)</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #090e1a;
            --bg-card: #121829;
            --bg-input: #1a233d;
            --text-light: #f5f6fa;
            --text-muted: #8b9bb4;
            --accent-primary: #f1b727;
            --accent-red: #ff4d4d;
            --accent-green: #2ecc71;
            --accent-blue: #3498db;
            --accent-purple: #9b59b6;
            --border-color: #243152;
            
            /* Slide specific colors */
            --slide-dark-blue: #0f1629;
            --slide-text-black: #0f1629;
            --slide-border-grey: #e0e0e0;

            --badge-green: #1a5c2d;
            --badge-blue: #12356a;
            --badge-purple: #641e78;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Editor Layout */
        .sidebar {
            width: 480px;
            background-color: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .sidebar-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--accent-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-header .badge-layout {
            background-color: rgba(241, 183, 39, 0.15);
            color: var(--accent-primary);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid rgba(241, 183, 39, 0.3);
        }

        .question-selector {
            padding: 12px 20px;
            background-color: var(--bg-input);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .question-selector label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .editor-form {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            padding: 10px 14px;
            border-radius: 8px;
            outline: none;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            border-color: var(--accent-primary);
        }

        .form-group textarea {
            height: 75px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .option-field {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .option-field .opt-label {
            font-weight: 700;
            width: 24px;
            color: var(--accent-primary);
            text-align: center;
        }

        .option-field input[type="text"] {
            flex: 1;
        }

        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            background-color: var(--bg-card);
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background-color: var(--accent-primary);
            color: #000;
        }

        .btn-secondary {
            background-color: var(--bg-input);
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .btn-success {
            background-color: #10b981;
            color: #fff;
        }

        /* Preview Area Layout */
        .preview-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--bg-dark);
            height: 100%;
            padding: 20px;
            align-items: center;
            justify-content: center;
        }

        .preview-header {
            margin-bottom: 12px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-header h1 {
            font-size: 1.35rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .layout-tag {
            font-size: 0.75rem;
            background-color: #10b981;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
        }

        /* 16:9 Aspect Ratio Slide Container */
        .slide-aspect-ratio-container {
            width: 100%;
            max-width: 100%;
            aspect-ratio: 1536 / 865;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
            border: 4px solid var(--border-color);
        }

        /* Slide Canvas (1536x865) */
        .slide-canvas {
            width: 1536px;
            height: 865px;
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: top left;
            transform: scale(var(--slide-scale, 0.625));
            background: #ffffff;
            font-family: 'Poppins', 'Noto Sans Devanagari', sans-serif;
            color: var(--slide-text-black);
            padding: 15px;
            box-sizing: border-box;
            font-weight: 700;
        }

        /* Watermark */
        .watermark-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.08;
            z-index: 5;
            pointer-events: none;
            mix-blend-mode: multiply;
        }

        .watermark-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Header Layout */
        .slide-header-container {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 15px;
            z-index: 10;
            position: relative;
        }

        .slide-q-badge {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background-color: var(--slide-dark-blue);
            border: 6px solid var(--accent-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 3.6rem;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }

        .slide-question-box {
            flex: 1;
            height: 185px;
            background-color: var(--slide-dark-blue);
            border: 4px solid var(--accent-primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 30px;
            text-align: center;
            color: #ffffff;
            font-size: calc(1.95rem * var(--slide-font-scale, 1));
            font-weight: 700;
            line-height: 1.45;
            width: 100%;
            box-sizing: border-box;
        }

        .slide-question-box div {
            width: 100%;
        }

        .slide-question-box span.highlight {
            color: var(--accent-primary);
            font-weight: 800;
        }

        .slide-calendar {
            width: 170px;
            height: 185px;
            background-color: #ffffff;
            border: 2px solid var(--slide-border-grey);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .slide-calendar-header {
            background-color: #b30000;
            height: 35px;
            position: relative;
        }

        .slide-calendar-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .slide-calendar-day {
            color: #b30000;
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1;
        }

        .slide-calendar-month-year {
            color: var(--slide-text-black);
            font-size: 1.4rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ============================================================
           NEW LAYOUT: 2-Column Split with Large Right Image Card
           ============================================================ */
        .slide-main-layout {
            display: flex;
            gap: 20px;
            height: 560px;
            z-index: 10;
            position: relative;
            width: 100%;
        }

        /* Left Section: 2x2 Grid (Options + Explanation on top, Exam + Related on bottom) */
        .slide-left-grid {
            flex: 1.85;
            display: flex;
            flex-direction: column;
            gap: 18px;
            height: 100%;
            min-width: 0;
        }

        .left-row {
            display: flex;
            gap: 18px;
            height: 271px;
            width: 100%;
        }

        .left-upper-row .options-card {
            flex: 1;
            min-width: 0;
        }

        .left-upper-row .explanation-card {
            flex: 1.4;
            min-width: 0;
        }

        .left-lower-row .exam-focus-card {
            flex: 1;
            min-width: 0;
        }

        .left-lower-row .related-facts-card {
            flex: 1;
            min-width: 0;
        }

        /* Right Section: Large Dedicated Graphic Card */
        .slide-right-image-panel {
            flex: 1.05;
            height: 100%;
            min-width: 0;
            display: flex;
        }

        .slide-card {
            background-color: #ffffff;
            border: 2px solid var(--slide-border-grey);
            border-radius: 20px;
            position: relative;
            padding: 22px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Card Section Themes */
        .slide-card.explanation-card {
            background-color: #f0f9f4;
            border: 2px solid var(--badge-green);
            color: var(--slide-text-black);
            padding: 22px 20px;
        }

        .slide-card.explanation-card span.highlight {
            color: #b30000;
            font-weight: 700;
        }

        .slide-card.exam-focus-card {
            background-color: #f2f7fb;
            border: 2px solid var(--badge-blue);
            color: var(--slide-text-black);
            padding: 22px 20px;
        }

        .slide-card.exam-focus-card span.highlight {
            color: #b30000;
            font-weight: 700;
        }

        .slide-card.related-facts-card {
            background-color: #faf5fb;
            border: 2px solid var(--badge-purple);
            color: var(--slide-text-black);
            padding: 22px 20px;
        }

        .slide-card.related-facts-card span.highlight {
            color: #b30000;
            font-weight: 700;
        }

        /* Card Badges */
        .slide-card-badge {
            position: absolute;
            top: -15px;
            left: 24px;
            height: 30px;
            border-radius: 6px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 15;
        }

        .badge-green { background-color: var(--badge-green); }
        .badge-blue { background-color: var(--badge-blue); }
        .badge-purple { background-color: var(--badge-purple); }

        /* Options card styles */
        .slide-card.options-card {
            padding: 8px;
        }

        .options-layout {
            display: flex;
            height: 100%;
            width: 100%;
        }

        .options-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            gap: 4px;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: calc(1.18rem * var(--slide-font-scale, 1));
            font-weight: 700;
            border: 2px solid var(--slide-dark-blue);
            border-radius: 10px;
            padding: 4px 8px;
            background-color: #f8fafc;
            box-sizing: border-box;
        }

        .option-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--slide-dark-blue);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .option-text {
            color: var(--slide-text-black);
            line-height: 1.2;
            font-size: calc(1.15rem * var(--slide-font-scale, 1));
            font-weight: 700;
        }

        /* Explanation card styles */
        .explanation-layout {
            display: flex;
            height: 100%;
        }

        .explanation-text-col {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .correct-opt-stmt {
            font-size: calc(1.2rem * var(--slide-font-scale, 1));
            font-weight: 700;
            margin-top: 12px;
            margin-bottom: 10px;
        }

        .correct-opt-stmt span {
            color: var(--accent-red);
            font-weight: 800;
        }

        .explanation-detail-box {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .checkmark-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: var(--badge-green);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .explanation-text-wrapped {
            font-size: calc(1.05rem * var(--slide-font-scale, 1));
            line-height: 1.45;
            color: var(--slide-text-black);
            font-weight: 700;
        }

        /* Bullets list styles */
        .bullets-image-layout {
            display: flex;
            height: 100%;
            margin-top: 4px;
        }

        .bullets-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }

        .bullet-item {
            display: flex;
            gap: 10px;
            font-size: calc(1.02rem * var(--slide-font-scale, 1));
            line-height: 1.4;
            align-items: flex-start;
        }

        .bullet-icon {
            font-size: 0.85rem;
            margin-top: 3px;
            flex-shrink: 0;
        }
        
        .bullet-icon-blue { color: var(--badge-blue); }
        .bullet-icon-purple { color: var(--badge-purple); }

        .bullet-item-text {
            color: var(--slide-text-black);
            font-size: calc(1.02rem * var(--slide-font-scale, 1));
            font-weight: 700;
        }

        /* Large Right Image Card */
        .slide-card.large-image-card {
            width: 100%;
            height: 100%;
            padding: 0;
            position: relative;
            border: 2px solid var(--slide-border-grey);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .slide-large-img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
            border-radius: 18px;
        }

        .image-banner {
            position: absolute;
            bottom: 15px;
            left: 15px;
            width: calc(100% - 30px);
            height: 38px;
            background-color: rgba(18, 53, 106, 0.92);
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            z-index: 10;
        }

        /* Footer Strip */
        .slide-footer-strip {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 55px;
            background: linear-gradient(90deg, #0a1128, #101f42);
            border-top: 3px solid var(--accent-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-sizing: border-box;
            color: #ffffff;
            z-index: 10;
        }

        .footer-logo-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-logo-brand span {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .footer-exam-tags {
            display: flex;
            gap: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        .footer-exam-tags span {
            background-color: rgba(255,255,255,0.05);
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .footer-pill-yellow {
            background-color: var(--accent-primary);
            color: #000;
            font-size: 0.9rem;
            font-weight: 800;
            padding: 6px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .source-overlay {
            position: absolute;
            bottom: 65px;
            left: 30px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--slide-text-black);
            background-color: rgba(255, 255, 255, 0.85);
            padding: 4px 10px;
            border-radius: 4px;
            z-index: 10;
        }

        .source-overlay span.url {
            color: #7f8c8d;
            font-weight: 700;
            margin-left: 8px;
        }

        /* Notification Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #1e293b;
            color: #fff;
            padding: 14px 22px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            border-left: 4px solid var(--accent-red);
        }

        .toast.toast-success {
            border-left: 4px solid var(--accent-green);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Left Sidebar: Question Editor -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>StudyHub Point <span class="badge-layout">Layout 2: Large Image</span></h2>
            <a href="index.php" style="color: var(--text-muted); font-size: 0.8rem; text-decoration: none;" title="Switch to 3-column Layout 1">Layout 1 ↗</a>
        </div>

        <!-- Question Navigation -->
        <div class="question-selector">
            <button class="btn btn-secondary" id="btnPrevSlide" style="flex: none; width: 42px; height: 42px; padding: 0;" title="Previous Slide">◀</button>
            <div style="flex: 1; text-align: center; font-weight: 700; font-size: 1rem; color: var(--accent-primary);" id="slideIndicator">
                Loading...
            </div>
            <button class="btn btn-secondary" id="btnNextSlide" style="flex: none; width: 42px; height: 42px; padding: 0;" title="Next Slide">▶</button>
            <div style="display: flex; gap: 4px;">
                <button class="btn btn-secondary" id="btnCopyCurrent" style="padding: 6px 12px; font-size: 0.8rem; height: 42px;" title="Copy current question JSON to clipboard">📋 JSON</button>
            </div>
        </div>

        <div class="editor-form">
            <!-- Question Font Scale -->
            <div class="form-group" style="background-color: var(--bg-card); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="margin-bottom: 0; color: var(--accent-primary); font-weight: 700;">🔤 Slide Font Size:</label>
                    <span id="fontScaleDisplay" style="font-size: 0.85rem; font-weight: 700; color: var(--text-light); background-color: var(--bg-input); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border-color);">100%</span>
                </div>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <button type="button" class="btn btn-secondary" id="btnFontDec" style="flex: none; width: 38px; height: 34px; padding: 0; font-weight: 800;">A-</button>
                    <input type="range" id="inpFontScale" min="70" max="130" value="100" step="1" style="flex: 1; accent-color: var(--accent-primary); cursor: pointer;">
                    <button type="button" class="btn btn-secondary" id="btnFontInc" style="flex: none; width: 38px; height: 34px; padding: 0; font-weight: 800;">A+</button>
                    <button type="button" class="btn btn-secondary" id="btnFontReset" style="flex: none; padding: 0 10px; height: 34px; font-size: 0.75rem; font-weight: 600;">Reset</button>
                </div>
            </div>

            <div class="form-group">
                <label>Question Text</label>
                <textarea id="inpQuestionText" style="height: 90px;"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="inpCategory">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="text" id="inpDate">
                </div>
            </div>

            <div class="form-group">
                <label>Options & Correct Answer</label>
                <div class="options-container">
                    <div class="option-field">
                        <input type="radio" name="correctOpt" value="0" title="Mark as correct">
                        <span class="opt-label">A</span>
                        <input type="text" id="inpOptA">
                    </div>
                    <div class="option-field">
                        <input type="radio" name="correctOpt" value="1" title="Mark as correct">
                        <span class="opt-label">B</span>
                        <input type="text" id="inpOptB">
                    </div>
                    <div class="option-field">
                        <input type="radio" name="correctOpt" value="2" title="Mark as correct">
                        <span class="opt-label">C</span>
                        <input type="text" id="inpOptC">
                    </div>
                    <div class="option-field">
                        <input type="radio" name="correctOpt" value="3" title="Mark as correct">
                        <span class="opt-label">D</span>
                        <input type="text" id="inpOptD">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Highlights (Comma separated keywords)</label>
                <textarea id="inpHighlights" style="height: 65px;"></textarea>
            </div>

            <div class="form-group">
                <label>Explanation (One point per line)</label>
                <textarea id="inpExplanation" style="height: 90px;"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Large Image URL / Path</label>
                    <input type="text" id="inpPortUrl" placeholder="images/1.png">
                </div>
                <div class="form-group">
                    <label>Image Caption Banner</label>
                    <input type="text" id="inpPortLabel" placeholder="e.g. Infographic">
                </div>
            </div>

            <div class="form-group">
                <label>Exam Focus Facts (One per line)</label>
                <textarea id="inpExamFacts" style="height: 90px;"></textarea>
            </div>

            <div class="form-group">
                <label>Related Facts (One per line)</label>
                <textarea id="inpRelatedFacts" style="height: 90px;"></textarea>
            </div>

            <div class="form-group">
                <label>Source / URL</label>
                <input type="text" id="inpSource" placeholder="e.g. PIB or http://...">
            </div>
        </div>

        <div class="sidebar-footer">
            <button class="btn btn-secondary" id="btnSave" style="flex: 1;">Save</button>
            <button class="btn btn-primary" id="btnGenerate" style="flex: 1;">PNGs</button>
            <button class="btn btn-primary" id="btnGeneratePdf" style="flex: 1.4; background-color: #10b981; border-color: #10b981; color: #ffffff;" title="Direct Vector PDF (Bypasses Image Rasterization)">📄 Vector PDF</button>
        </div>
    </div>

    <!-- Live Preview Area -->
    <div class="preview-area">
        <div class="preview-header">
            <div>
                <h1>Large Image Layout Live Preview <span class="layout-tag">550px Right Panel</span></h1>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Real-time HTML & Vector Slide Replication</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background-color: var(--bg-card); padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border-color);">
                <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">🔤 Font:</span>
                <button type="button" class="btn btn-secondary" id="btnPreviewFontDec" style="width: 32px; height: 32px; padding: 0; font-size: 0.95rem; font-weight: 800; border-radius: 6px;">A-</button>
                <button type="button" class="btn btn-secondary" id="btnPreviewFontInc" style="width: 32px; height: 32px; padding: 0; font-size: 0.95rem; font-weight: 800; border-radius: 6px;">A+</button>
                <button type="button" class="btn btn-secondary" id="btnPreviewFontReset" style="padding: 0 10px; height: 32px; font-size: 0.75rem; font-weight: 600; border-radius: 6px;">Reset</button>
            </div>
        </div>
        
        <div class="slide-aspect-ratio-container">
            <div class="slide-canvas" id="slideCanvas">
                <!-- Watermark Overlay -->
                <div class="watermark-container">
                    <img src="logo.png" alt="watermark">
                </div>
                
                <!-- Slide Header -->
                <div class="slide-header-container">
                    <div class="slide-q-badge" id="previewQNum">1</div>
                    <div class="slide-question-box">
                        <div id="previewQuestionText">
                            Question text will load here...
                        </div>
                    </div>
                    <div class="slide-calendar">
                        <div class="slide-calendar-header"></div>
                        <div class="slide-calendar-body">
                            <div class="slide-calendar-day" id="previewCalDay">18</div>
                            <div class="slide-calendar-month-year" id="previewCalMonthYear">AUGUST 2026</div>
                        </div>
                    </div>
                </div>

                <!-- NEW LAYOUT: Left 2x2 Grid + Right Large Full-Height Image Card -->
                <div class="slide-main-layout">
                    <!-- Left Section (2x2 Grid) -->
                    <div class="slide-left-grid">
                        <!-- Upper Row: Options + Explanation -->
                        <div class="left-row left-upper-row">
                            <!-- Options Card -->
                            <div class="slide-card options-card">
                                <div class="options-layout">
                                    <div class="options-list">
                                        <div class="option-item">
                                            <div class="option-badge">A</div>
                                            <div class="option-text" id="previewOptA">Option A</div>
                                        </div>
                                        <div class="option-item">
                                            <div class="option-badge">B</div>
                                            <div class="option-text" id="previewOptB">Option B</div>
                                        </div>
                                        <div class="option-item">
                                            <div class="option-badge">C</div>
                                            <div class="option-text" id="previewOptC">Option C</div>
                                        </div>
                                        <div class="option-item">
                                            <div class="option-badge">D</div>
                                            <div class="option-text" id="previewOptD">Option D</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Explanation Card -->
                            <div class="slide-card explanation-card">
                                <div class="slide-card-badge badge-green">व्याख्या (Explanation)</div>
                                <div class="explanation-layout">
                                    <div class="explanation-text-col">
                                        <div class="correct-opt-stmt">
                                            सही विकल्प (Correct Option): <span id="previewCorrectLetter">A</span>
                                        </div>
                                        <div class="explanation-detail-box">
                                            <div class="checkmark-icon">✓</div>
                                            <div class="explanation-text-wrapped" id="previewExplanation">
                                                Explanation text wraps here...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lower Row: Exam Focus + Related Facts -->
                        <div class="left-row left-lower-row">
                            <!-- Exam Focus Card -->
                            <div class="slide-card exam-focus-card">
                                <div class="slide-card-badge badge-blue">परीक्षा में उपयोगी तथ्य (Exam Focus)</div>
                                <div class="bullets-image-layout">
                                    <div class="bullets-list" id="previewExamBullets">
                                        <!-- Loaded dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Related Facts Card -->
                            <div class="slide-card related-facts-card">
                                <div class="slide-card-badge badge-purple">संबंधित तथ्य (Related Facts)</div>
                                <div class="bullets-image-layout">
                                    <div class="bullets-list" id="previewRelatedBullets">
                                        <!-- Loaded dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Large Full-Height Image Card -->
                    <div class="slide-right-image-panel">
                        <div class="slide-card large-image-card">
                            <img class="slide-large-img" id="previewMainImg" src="" alt="Main Graphic">
                            <div class="image-banner" id="previewImageBanner">
                                <span id="previewImageLabel">Label</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Strip -->
                <div class="slide-footer-strip">
                    <div class="footer-logo-brand">
                        <img src="logo.png" style="height: 38px; width: 38px; border-radius: 50%; object-fit: contain; background-color: #aceadf; padding: 4px; box-sizing: border-box;" alt="logo">
                        <span>StudyHub Point</span>
                    </div>
                    <div class="footer-exam-tags">
                        <span>UPTET</span>
                        <span>CTET</span>
                        <span>SUPER TET</span>
                        <span>SSC</span>
                        <span>UPSSSC</span>
                        <span>RAILWAY</span>
                        <span>UPSC</span>
                    </div>
                    <div class="footer-pill-yellow">
                        💡 Stay Updated Stay Ahead
                    </div>
                </div>
                
                <!-- Source Overlay -->
                <div class="source-overlay">
                    Source (स्रोत): <span id="previewSourceText">PIB</span>
                    <span class="url" id="previewSourceUrl">https://www.pib.gov.in</span>
                </div>

            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast">
        <span id="toastIcon">ℹ️</span>
        <span id="toastMessage">Notification details here.</span>
    </div>

    <script>
        // Application State
        let mcqs = [];
        let activeIdx = 0;

        const btnPrevSlide = document.getElementById('btnPrevSlide');
        const btnNextSlide = document.getElementById('btnNextSlide');
        const slideIndicator = document.getElementById('slideIndicator');
        const inpQuestionText = document.getElementById('inpQuestionText');
        const inpCategory = document.getElementById('inpCategory');
        const inpDate = document.getElementById('inpDate');
        const inpOptA = document.getElementById('inpOptA');
        const inpOptB = document.getElementById('inpOptB');
        const inpOptC = document.getElementById('inpOptC');
        const inpOptD = document.getElementById('inpOptD');
        const inpHighlights = document.getElementById('inpHighlights');
        const inpExplanation = document.getElementById('inpExplanation');
        const inpPortUrl = document.getElementById('inpPortUrl');
        const inpPortLabel = document.getElementById('inpPortLabel');
        const inpExamFacts = document.getElementById('inpExamFacts');
        const inpRelatedFacts = document.getElementById('inpRelatedFacts');
        const inpSource = document.getElementById('inpSource');
        const btnSave = document.getElementById('btnSave');
        const btnGenerate = document.getElementById('btnGenerate');
        const btnGeneratePdf = document.getElementById('btnGeneratePdf');
        const btnCopyCurrent = document.getElementById('btnCopyCurrent');
        const toast = document.getElementById('toast');

        // Font Size Controls
        const inpFontScale = document.getElementById('inpFontScale');
        const fontScaleDisplay = document.getElementById('fontScaleDisplay');
        const btnFontDec = document.getElementById('btnFontDec');
        const btnFontInc = document.getElementById('btnFontInc');
        const btnFontReset = document.getElementById('btnFontReset');
        const btnPreviewFontDec = document.getElementById('btnPreviewFontDec');
        const btnPreviewFontInc = document.getElementById('btnPreviewFontInc');
        const btnPreviewFontReset = document.getElementById('btnPreviewFontReset');

        // Scale responsive canvas to fit container
        function adjustSlideScale() {
            const container = document.querySelector('.slide-aspect-ratio-container');
            const canvas = document.getElementById('slideCanvas');
            if (!container || !canvas) return;
            const containerWidth = container.clientWidth;
            const scale = containerWidth / 1536;
            canvas.style.setProperty('--slide-scale', scale);
        }

        window.addEventListener('resize', adjustSlideScale);
        window.addEventListener('DOMContentLoaded', adjustSlideScale);

        function setFontScale(val, triggerUpdate = true) {
            const scale = Math.min(130, Math.max(70, parseInt(val, 10) || 100));
            if (inpFontScale) inpFontScale.value = scale;
            if (fontScaleDisplay) fontScaleDisplay.textContent = scale + '%';
            
            if (mcqs[activeIdx]) {
                mcqs[activeIdx].font_size_scale = scale / 100;
            }
            
            const slideCanvas = document.getElementById('slideCanvas');
            if (slideCanvas) {
                slideCanvas.style.setProperty('--slide-font-scale', scale / 100);
            }
        }

        if (inpFontScale) {
            inpFontScale.addEventListener('input', (e) => {
                setFontScale(e.target.value);
            });
        }

        const handleDecFont = () => {
            const cur = parseInt(inpFontScale ? inpFontScale.value : 100, 10);
            setFontScale(cur - 3);
        };
        const handleIncFont = () => {
            const cur = parseInt(inpFontScale ? inpFontScale.value : 100, 10);
            setFontScale(cur + 3);
        };
        const handleResetFont = () => {
            setFontScale(100);
        };

        if (btnFontDec) btnFontDec.addEventListener('click', handleDecFont);
        if (btnFontInc) btnFontInc.addEventListener('click', handleIncFont);
        if (btnFontReset) btnFontReset.addEventListener('click', handleResetFont);

        if (btnPreviewFontDec) btnPreviewFontDec.addEventListener('click', handleDecFont);
        if (btnPreviewFontInc) btnPreviewFontInc.addEventListener('click', handleIncFont);
        if (btnPreviewFontReset) btnPreviewFontReset.addEventListener('click', handleResetFont);

        // Form change triggers preview
        const inputs = [
            inpQuestionText, inpCategory, inpDate, inpOptA, inpOptB, inpOptC, inpOptD,
            inpHighlights, inpExplanation, inpPortUrl, inpPortLabel, 
            inpExamFacts, inpRelatedFacts, inpSource
        ];
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                updateStateFromForm();
                renderPreview(activeIdx);
            });
        });

        // Correct option radio change
        document.querySelectorAll('input[name="correctOpt"]').forEach(radio => {
            radio.addEventListener('change', () => {
                updateStateFromForm();
                renderPreview(activeIdx);
            });
        });

        // Load JSON Data
        async function loadData() {
            try {
                const res = await fetch('index2.php?action=load');
                if (!res.ok) {
                    throw new Error(`HTTP error ${res.status}: ${res.statusText}`);
                }
                mcqs = await res.json();
                if (!Array.isArray(mcqs)) {
                    if (mcqs && mcqs.error) {
                        throw new Error(mcqs.error);
                    }
                    throw new Error('Loaded JSON is not an array');
                }
                
                updateSlideIndicator();
                selectQuestion(0);
            } catch (err) {
                console.error('Error loading JSON:', err);
                showToast('Error loading JSON data: ' + err.message, false);
            }
        }

        // Show a Toast Alert
        function showToast(message, isSuccess = true) {
            toast.className = 'toast show' + (isSuccess ? ' toast-success' : '');
            document.getElementById('toastMessage').textContent = message;
            document.getElementById('toastIcon').textContent = isSuccess ? '✅' : '❌';
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        function updateSlideIndicator() {
            if (slideIndicator) {
                if (mcqs.length === 0) {
                    slideIndicator.textContent = 'No Slides';
                } else {
                    const currentQ = mcqs[activeIdx];
                    const qNum = currentQ ? (currentQ.question_number || `Q${activeIdx + 1}`) : `Q${activeIdx + 1}`;
                    slideIndicator.textContent = `${qNum} (${activeIdx + 1} / ${mcqs.length})`;
                }
            }
            if (btnPrevSlide) btnPrevSlide.disabled = (activeIdx === 0);
            if (btnNextSlide) btnNextSlide.disabled = (activeIdx === mcqs.length - 1);
        }

        // Navigation button listeners
        btnPrevSlide.addEventListener('click', () => {
            if (activeIdx > 0) {
                updateStateFromForm();
                activeIdx--;
                selectQuestion(activeIdx);
            }
        });

        btnNextSlide.addEventListener('click', () => {
            if (activeIdx < mcqs.length - 1) {
                updateStateFromForm();
                activeIdx++;
                selectQuestion(activeIdx);
            }
        });

        function selectQuestion(idx) {
            activeIdx = idx;
            updateSlideIndicator();
            const mcq = mcqs[idx];
            
            // Populate form fields
            inpQuestionText.value = (mcq.question_text || '').replace(/\*\*/g, '');
            inpCategory.value = mcq.category || '';
            inpDate.value = mcq.date || '';
            
            const opts = mcq.options || [];
            inpOptA.value = (opts[0] || '').replace(/\*\*/g, '');
            inpOptB.value = (opts[1] || '').replace(/\*\*/g, '');
            inpOptC.value = (opts[2] || '').replace(/\*\*/g, '');
            inpOptD.value = (opts[3] || '').replace(/\*\*/g, '');
            
            // Correct option radio check
            const correctVal = mcq.correct_option_id !== undefined ? mcq.correct_option_id : 0;
            const checkedRadio = document.querySelector(`input[name="correctOpt"][value="${correctVal}"]`);
            if (checkedRadio) checkedRadio.checked = true;
            
            inpHighlights.value = (mcq.highlights || []).join(', ');
            
            inpExplanation.value = (mcq.explanation || []).map(s => s.replace(/\*\*/g, '')).join('\n');
            inpPortUrl.value = mcq.explanation_image_url || '';
            inpPortLabel.value = mcq.explanation_image_label || '';
            
            inpExamFacts.value = (mcq.exam_focus_facts || []).map(s => s.replace(/\*\*/g, '')).join('\n');
            
            inpRelatedFacts.value = (mcq.related_facts || []).map(s => s.replace(/\*\*/g, '')).join('\n');
            
            inpSource.value = mcq.source || '';
            
            // Set font scale for active slide
            const slideFontScale = (mcq && mcq.font_size_scale !== undefined) ? Math.round(mcq.font_size_scale * 100) : 100;
            setFontScale(slideFontScale, false);

            renderPreview(idx);
        }

        // Update JavaScript state from editor inputs
        function updateStateFromForm() {
            if (!mcqs[activeIdx]) return;
            const mcq = mcqs[activeIdx];
            
            const curFontScaleVal = inpFontScale ? parseInt(inpFontScale.value, 10) : 100;
            mcq.font_size_scale = curFontScaleVal / 100;

            mcq.question_text = inpQuestionText.value;
            mcq.category = inpCategory.value;
            mcq.date = inpDate.value;
            mcq.options = [inpOptA.value, inpOptB.value, inpOptC.value, inpOptD.value];
            
            const selectedRadio = document.querySelector('input[name="correctOpt"]:checked');
            mcq.correct_option_id = selectedRadio ? parseInt(selectedRadio.value) : 0;
            
            mcq.highlights = inpHighlights.value.split(',').map(s => s.trim()).filter(s => s !== '');
            
            mcq.explanation = inpExplanation.value.split('\n').filter(s => s.trim() !== '');
            mcq.explanation_image_url = inpPortUrl.value;
            mcq.explanation_image_label = inpPortLabel.value;
            
            mcq.exam_focus_facts = inpExamFacts.value.split('\n').filter(s => s.trim() !== '');
            
            mcq.related_facts = inpRelatedFacts.value.split('\n').filter(s => s.trim() !== '');
            
            mcq.source = inpSource.value;
        }

        // Apply HTML Bold Highlights
        function highlightText(text, keywords) {
            if (!text) return '';
            let cleaned = text.replace(/\*\*/g, '');
            if (!keywords || keywords.length === 0) return cleaned;
            
            const sorted = [...keywords].sort((a, b) => b.length - a.length);
            let formatted = cleaned;
            
            sorted.forEach(kw => {
                if (!kw) return;
                const constEscaped = kw.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const regex = new RegExp('(?<![a-zA-Z0-9_\\u0900-\\u097F])' + constEscaped + '(?![a-zA-Z0-9_\\u0900-\\u097F])', 'g');
                formatted = formatted.replace(regex, `<span class="highlight">${kw}</span>`);
            });
            
            return formatted;
        }

        // Render HTML Slide Preview
        function renderPreview(idx) {
            const mcq = mcqs[idx];
            if (!mcq) return;
            
            const floatScale = mcq.font_size_scale !== undefined ? mcq.font_size_scale : 1.0;
            const slideCanvas = document.getElementById('slideCanvas');
            if (slideCanvas) {
                slideCanvas.style.setProperty('--slide-font-scale', floatScale);
            }

            const keywords = mcq.highlights || [];
            
            // Q number (Layout 2: clean number only)
            const qNum = mcq.question_number || `${idx + 1}`;
            const cleanNum = String(qNum).replace(/^[Qq]\.?\s*/, '');
            document.getElementById('previewQNum').textContent = cleanNum;
            
            // Date Parsing
            const dateStr = mcq.date || "18 August 2026";
            const dateParts = dateStr.split(' ');
            document.getElementById('previewCalDay').textContent = dateParts[0] || '18';
            document.getElementById('previewCalMonthYear').textContent = `${(dateParts[1] || 'AUGUST').toUpperCase()} ${dateParts[2] || '2026'}`;
            
            // Question Text
            document.getElementById('previewQuestionText').innerHTML = highlightText(mcq.question_text, keywords);
            
            // Options
            const opts = mcq.options || [];
            document.getElementById('previewOptA').innerHTML = opts[0] || '';
            document.getElementById('previewOptB').innerHTML = opts[1] || '';
            document.getElementById('previewOptC').innerHTML = opts[2] || '';
            document.getElementById('previewOptD').innerHTML = opts[3] || '';
            
            // Correct Letter Display
            const letters = ['A', 'B', 'C', 'D'];
            const correctOptVal = mcq.correct_option_id !== undefined ? mcq.correct_option_id : 0;
            document.getElementById('previewCorrectLetter').textContent = letters[correctOptVal] || 'A';
            
            // Explanation
            const explanationFacts = mcq.explanation || [];
            const expHtml = explanationFacts
                .map(fact => highlightText(fact, keywords))
                .filter(html => html.trim() !== '')
                .join('<div style="height: 6px;"></div>');
            document.getElementById('previewExplanation').innerHTML = expHtml;
            
            // Render Large Image Card (Full Height Right Panel)
            let qNo = idx + 1;
            if (mcq.question_number !== undefined && mcq.question_number !== null) {
                const qMatch = String(mcq.question_number).match(/\d+/);
                if (qMatch) qNo = qMatch[0];
            }
            const defaultImg = `images/${qNo}.png`;
            const mainImgUrl = mcq.explanation_image_url_cached || mcq.explanation_image_url || mcq.logo_url_1_cached || mcq.logo_url_1 || defaultImg;
            const mainImgLabel = mcq.explanation_image_label || mcq.logo_label_1 || '';
            
            const mainImgPanel = document.querySelector('.slide-right-image-panel');
            const mainImg = document.getElementById('previewMainImg');
            const mainImgBanner = document.getElementById('previewImageBanner');
            const mainImgLabelSpan = document.getElementById('previewImageLabel');
            
            if (mainImgUrl) {
                mainImgPanel.style.display = 'flex';
                mainImg.src = mainImgUrl;
                if (mainImgLabel) {
                    mainImgBanner.style.display = 'flex';
                    mainImgLabelSpan.textContent = mainImgLabel;
                } else {
                    mainImgBanner.style.display = 'none';
                }
            } else {
                mainImgPanel.style.display = 'none';
            }
            
            // Exam Focus Facts List
            const examBullets = document.getElementById('previewExamBullets');
            examBullets.innerHTML = '';
            const examFacts = mcq.exam_focus_facts || [];
            examFacts.slice(0, 4).forEach(fact => {
                const item = document.createElement('div');
                item.className = 'bullet-item';
                item.innerHTML = `
                    <div class="bullet-icon bullet-icon-blue">▶</div>
                    <div class="bullet-item-text">${highlightText(fact, keywords)}</div>
                `;
                examBullets.appendChild(item);
            });
            
            // Related Facts list
            const relatedBullets = document.getElementById('previewRelatedBullets');
            relatedBullets.innerHTML = '';
            const relatedFacts = mcq.related_facts || [];
            relatedFacts.slice(0, 4).forEach(fact => {
                const item = document.createElement('div');
                item.className = 'bullet-item';
                item.innerHTML = `
                    <div class="bullet-icon bullet-icon-purple">★</div>
                    <div class="bullet-item-text">${highlightText(fact, keywords)}</div>
                `;
                relatedBullets.appendChild(item);
            });
            
            // Source Footer
            const source = mcq.source || 'PIB';
            let sourceUrl = "https://www.pib.gov.in/PMContents";
            let displaySource = source;
            if (source.startsWith('http')) {
                sourceUrl = source;
                try {
                    displaySource = new URL(source).hostname;
                } catch(e) {
                    displaySource = 'Official Source';
                }
            }
            document.getElementById('previewSourceText').textContent = displaySource;
            document.getElementById('previewSourceUrl').textContent = sourceUrl;
        }

        // Helper to save current state to server JSON database
        async function saveDataToServer() {
            updateStateFromForm();
            try {
                const res = await fetch('index2.php?action=save', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: jsonStringifyWithHighlights(mcqs)
                });
                const result = await res.json();
                return result.success;
            } catch(e) {
                console.error(e);
                return false;
            }
        }

        // Save Button Handler
        btnSave.addEventListener('click', async () => {
            btnSave.disabled = true;
            btnSave.textContent = 'Saving...';
            const success = await saveDataToServer();
            if (success) {
                showToast('Changes saved to JSON successfully!');
            } else {
                showToast('Failed to save JSON changes', false);
            }
            btnSave.disabled = false;
            btnSave.textContent = 'Save';
        });

        // Helper to stringify JSON cleanly
        function jsonStringifyWithHighlights(data) {
            const isArray = Array.isArray(data);
            const clone = JSON.parse(JSON.stringify(data));
            const items = isArray ? clone : [clone];
            
            items.forEach(item => {
                delete item.logo_url_1_cached;
                delete item.explanation_image_url_cached;
                delete item.exam_focus_image_url_cached;
                delete item.related_facts_image_url_cached;
                
                const stripStars = (text) => {
                    if (!text) return text;
                    return text.replace(/\*\*/g, '');
                };
                
                if (item.question_text) item.question_text = stripStars(item.question_text);
                if (item.options) item.options = item.options.map(opt => stripStars(opt));
                if (item.explanation) item.explanation = item.explanation.map(exp => stripStars(exp));
                if (item.exam_focus_facts) item.exam_focus_facts = item.exam_focus_facts.map(fact => stripStars(fact));
                if (item.related_facts) item.related_facts = item.related_facts.map(fact => stripStars(fact));
            });
            
            return isArray ? JSON.stringify(clone, null, 2) : JSON.stringify(items[0], null, 2);
        }

        // Generate PNGs Button Handler
        btnGenerate.addEventListener('click', async () => {
            btnGenerate.disabled = true;
            btnGenerate.textContent = 'Generating...';
            
            const saveSuccess = await saveDataToServer();
            if (!saveSuccess) {
                showToast('Failed to save recent changes before generating. Proceeding anyway...', false);
            }
            
            try {
                const res = await fetch('index2.php?action=generate', {
                    method: 'POST'
                });
                const result = await res.json();
                if (result.success) {
                    showToast('PNG files generated successfully inside output/ directory!');
                    console.log(result.output);
                } else {
                    showToast('Failed to generate PNGs: ' + result.error, false);
                }
            } catch(e) {
                showToast('Network error generating PNGs', false);
            } finally {
                btnGenerate.disabled = false;
                btnGenerate.textContent = 'PNGs';
            }
        });

        // Generate Vector PDF Button Handler
        btnGeneratePdf.addEventListener('click', async () => {
            btnGeneratePdf.disabled = true;
            btnGeneratePdf.textContent = 'Generating PDF...';
            
            const saveSuccess = await saveDataToServer();
            if (!saveSuccess) {
                showToast('Failed to save recent changes before generating. Proceeding anyway...', false);
            }
            
            try {
                const res = await fetch('index2.php?action=generate_pdf', {
                    method: 'POST'
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Vector PDF generated successfully in output/ folder!');
                    console.log(result.output);
                } else {
                    showToast('Failed to generate PDF: ' + result.error, false);
                }
            } catch(e) {
                showToast('Network error generating Vector PDF', false);
            } finally {
                btnGeneratePdf.disabled = false;
                btnGeneratePdf.textContent = '📄 Vector PDF';
            }
        });

        // Copy to clipboard helper
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard successfully!');
            }).catch(err => {
                showToast('Failed to copy text: ' + err, false);
            });
        }

        // Copy Current Question JSON
        btnCopyCurrent.addEventListener('click', () => {
            updateStateFromForm();
            if (mcqs[activeIdx]) {
                copyToClipboard(jsonStringifyWithHighlights([mcqs[activeIdx]]));
            }
        });

        // Keyboard Shortcuts (Arrow Left/Right for Slide Navigation)
        window.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            if (e.key === 'ArrowLeft' && activeIdx > 0) {
                activeIdx--;
                selectQuestion(activeIdx);
            } else if (e.key === 'ArrowRight' && activeIdx < mcqs.length - 1) {
                activeIdx++;
                selectQuestion(activeIdx);
            }
        });

        // Initial Load
        loadData();
    </script>
</body>
</html>
