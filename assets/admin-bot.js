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
            default:
                console.error('Unknown level type');
                return;
        }

        console.log('[AdminBot] Target URL generated:', targetUrl);
        this.dispatchBot(targetUrl);
    },

    dispatchBot: function(url) {
        // Remove existing frame if any
        const oldFrame = document.getElementById(this.iframeId);
        if (oldFrame) oldFrame.remove();

        // Create a hidden form to submit POST request to admin.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin.php'; // Assuming we are in levelX/ directory
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
