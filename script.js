// ===== GLOBAL UTILITIES =====

// Toggle mobile nav (if needed)
document.addEventListener('DOMContentLoaded', function () {

    // ===== ADMIN TABS =====
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const target = this.getAttribute('data-tab');
            const targetEl = document.getElementById(target);
            if (targetEl) targetEl.classList.add('active');
        });
    });

    // ===== PAYMENT METHOD SELECTOR =====
    const paymentOptions = document.querySelectorAll('.payment-method-option');
    paymentOptions.forEach(opt => {
        opt.addEventListener('click', function () {
            paymentOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const val = this.getAttribute('data-value');
            const hiddenInput = document.getElementById('payment_method_input');
            if (hiddenInput) hiddenInput.value = val;
        });
    });

    // ===== BOOKING PRICE CALCULATOR =====
    const travelersInput = document.getElementById('travelers');
    const packagePrice = document.getElementById('package_price');
    const totalDisplay = document.getElementById('total_price_display');

    if (travelersInput && packagePrice && totalDisplay) {
        travelersInput.addEventListener('input', function () {
            const count = parseInt(this.value) || 1;
            const price = parseFloat(packagePrice.value) || 0;
            const total = (count * price).toLocaleString('en-IN');
            totalDisplay.textContent = '₹' + total;
        });
    }

    // ===== AUTO DISMISS ALERTS =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // ===== FORM VALIDATION =====
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                e.preventDefault();
                showAlert('Passwords do not match!', 'danger');
            }
            if (pass.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters.', 'danger');
            }
        });
    }

    // ===== AI CHAT =====
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if (!msg) return;
            appendMessage(msg, 'user');
            chatInput.value = '';
            fetchBotReply(msg);
        });
    }

    // Quick chat buttons
    const quickBtns = document.querySelectorAll('.quick-btn');
    quickBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const query = this.textContent.trim();
            if (chatInput && chatMessages) {
                appendMessage(query, 'user');
                fetchBotReply(query);
            }
        });
    });

    function appendMessage(text, type) {
        if (!chatMessages) return;
        const div = document.createElement('div');
        div.classList.add('message', type);
        div.innerHTML = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function fetchBotReply(msg) {
        appendMessage('<i>Thinking...</i>', 'bot');
        fetch('ai_assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg) + '&ajax=1'
        })
        .then(res => res.json())
        .then(data => {
            // Remove "Thinking..." message
            const msgs = chatMessages.querySelectorAll('.message.bot');
            const last = msgs[msgs.length - 1];
            if (last && last.innerHTML.includes('Thinking')) last.remove();
            appendMessage(data.reply, 'bot');
        })
        .catch(() => {
            const msgs = chatMessages.querySelectorAll('.message.bot');
            const last = msgs[msgs.length - 1];
            if (last) last.remove();
            appendMessage('Sorry, something went wrong. Please try again.', 'bot');
        });
    }

    // ===== CONFIRM DELETE =====
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

});

// ===== HELPER: Show alert dynamically =====
function showAlert(message, type) {
    const existing = document.querySelector('.alert');
    if (existing) existing.remove();
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    const form = document.querySelector('form');
    if (form) form.insertBefore(alert, form.firstChild);
}
