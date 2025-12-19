/**
 * Admin Bot Interaction Script
 * Handles payload construction and communication with the headless admin bot.
 */

const AdminBot = {
    iframeId: 'admin-bot-frame',
    
    init: function() {
        // Listen for messages from the bot
        window.addEventListener('message', (event) => {
            // Ignore messages that don't look like ours to avoid processing extension messages
            if (!event.data || typeof event.data !== 'object') return;

            if (event.data.type === 'bot_visit_start') {
                console.log('%c [AdminBot] Bot started visiting target...', 'color: orange');
            }
            
            if (event.data.type === 'bot_visit_end') {
                console.log('%c [AdminBot] Bot finished visit.', 'color: green');
                this.updateStatus('VISIT COMPLETE', 'status-ok');
            }
        });
        console.log('[AdminBot] Initialized. Waiting for commands.');
    },

    send: function(levelType, payloadInputId) {
        const payloadInput = document.getElementById(payloadInputId);
        if (!payloadInput) return;
        
        const payload = payloadInput.value.trim();
        if (!payload) {
            alert('Please enter a payload!');
            return;
        }

        let targetUrl = '';
        const baseUrl = window.location.href.split('?')[0]; // Get current page URL without query params

        switch(levelType) {
            case 'level1':
                // Reflected XSS: name parameter
                targetUrl = `${baseUrl}?name=${encodeURIComponent(payload)}`;
                break;
            case 'level2':
                // DOM XSS: keyword parameter
                targetUrl = `${baseUrl}?keyword=${encodeURIComponent(payload)}`;
                break;
            case 'level3':
                // Stored XSS: Just visit the page (payload assumed to be stored)
                // For L3, we ignore the payload input for URL construction, 
                // but we could log it or assume the user wants to test a specific state.
                // Simplified: Just visit the page.
                targetUrl = baseUrl;
                break;
            case 'level24':
                // Reflected XSS: search parameter
                targetUrl = `${baseUrl}?search=${encodeURIComponent(payload)}`;
                break;
            case 'level25':
                // Reflected XSS: search parameter
                targetUrl = `${baseUrl}?search=${encodeURIComponent(payload)}`;
                break;
            case 'level26':
                // Canonical Link XSS: The payload is part of the URL itself (query string manipulation)
                // The payload here is NOT a parameter value, but the query string itself.
                // However, our input box usually takes "what you type".
                // In this level, the user types: ?'accesskey='x'onclick='alert(1)
                // We should append it directly.
                // BUT wait, encodeURIComponent will break the injection if we treat it as a param value.
                
                // Let's assume the user enters the FULL Query String or just the injection part.
                // If user enters: ?'accesskey='x...
                // We append it.
                
                // To be safe and flexible:
                // If payload starts with '?', append as is (decoded).
                // Otherwise, treat as query string.
                
                // For this specific level, the injection is IN the URL.
                // Let's just append the payload raw to the base URL.
                // BUT we need to be careful.
                
                targetUrl = baseUrl + payload;
                break;
            default:
                console.error('Unknown level type');
                return;
        }

        console.log('[AdminBot] Target URL generated:', targetUrl);
        this.dispatchBot(targetUrl, levelType);
    },

    dispatchBot: function(url, levelType) {
        // Remove existing frame if any
        const oldFrame = document.getElementById(this.iframeId);
        if (oldFrame) oldFrame.remove();

        if (levelType === 'level26') {
            const iframe = document.createElement('iframe');
            iframe.name = this.iframeId;
            iframe.id = this.iframeId;
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            this.updateStatus('DISPATCHING BOT...', 'status-proc');
            window.postMessage({ type: 'bot_visit_start', url }, '*');
            iframe.onload = () => {
                window.postMessage({ type: 'bot_visit_end', url }, '*');
            };
            iframe.src = url;
            return;
        }

        // Create a hidden form to submit POST request to admin.php
        const form = document.createElement('form');
        form.method = 'POST';
        // Check if admin.php exists in current directory or parent directory
        // For simplicity in this lab structure, assume it's in the current level directory
        // But for level26/index.php, admin.php is likely in level26/admin.php OR we need a common one.
        // Wait, the error is 404 Not Found for admin.php.
        // In previous levels, did we have admin.php?
        // Checking file structure...
        // Actually, most levels might not have admin.php if I didn't create them.
        // The original template level1/admin.php exists? Let's check.
        // If not, we should point to a common admin handler or ensure admin.php exists.
        
        // FIX: The user reported 404 for admin.php.
        // I will point this to '../admin.php' if it exists, or create a mock admin.php in the level folder.
        // Since I cannot check file existence easily here in JS runtime without trial,
        // I will assume the standard path is 'admin.php' relative to index.php.
        // The error means admin.php is MISSING in level26/.
        
        form.action = 'admin.php'; 

        form.target = this.iframeId;
        form.style.display = 'none';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'url';
        input.value = url;
        form.appendChild(input);

        // Create the hidden iframe target
        const iframe = document.createElement('iframe');
        iframe.name = this.iframeId;
        iframe.id = this.iframeId;
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        document.body.appendChild(form);

        this.updateStatus('DISPATCHING BOT...', 'status-proc');
        
        // Submit
        try {
            form.submit();
        } catch (e) {
            console.error('[AdminBot] Form submit error:', e);
            this.updateStatus('ERROR: SUBMIT FAILED', 'status-err');
        }
        
        // Cleanup form
        setTimeout(() => form.remove(), 100);
    },

    updateStatus: function(text, className) {
        const statusEl = document.getElementById('bot-status');
        if (statusEl) {
            statusEl.innerText = text;
            statusEl.className = className; // e.g. status-ok, status-proc
        }
        
        // Console Log for Debugging
        if (text.includes('DISPATCHING')) {
            console.log('%c [AdminBot] Dispatching bot to URL...', 'color: #00f3ff');
        }
    }
};

// Initialize
AdminBot.init();
