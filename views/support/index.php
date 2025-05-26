<?php
require_once 'views/layouts/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Debug session structure
error_log("User session structure: " . print_r($_SESSION['user'], true));

$user_name = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['ten'] ?? 'User';
$user_id = $_SESSION['user']['ma_nguoi_dung'];
$user_email = $_SESSION['user']['email'];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/support.css?v=2">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="support-container">
    <div class="support-header">
        <div class="header-content">
            <h1><i class="fas fa-headset"></i> Hỗ trợ khách hàng</h1>
            <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7. Hãy mô tả vấn đề bạn đang gặp phải!</p>
        </div>
        <div class="support-status">
            <div class="status-indicator" id="connection-status">
                <i class="fas fa-circle"></i>
                <span>Đang kết nối...</span>
            </div>
        </div>
    </div>
    
    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-info">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                        <div class="user-email"><?= htmlspecialchars($user_email) ?></div>
                    </div>
                </div>
            </div>
            <div class="chat-actions">
                <button id="refresh-chat" class="btn-action" title="Làm mới">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button id="clear-chat" class="btn-action" title="Xóa lịch sử">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div class="welcome-message">
                <div class="welcome-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="welcome-content">
                    <h3>Xin chào <?= htmlspecialchars($user_name) ?>!</h3>
                    <p>Chào mừng bạn đến với hệ thống hỗ trợ của Venow. Chúng tôi sẽ hỗ trợ bạn giải quyết mọi vấn đề một cách nhanh chóng và hiệu quả.</p>
                    <div class="quick-actions">
                        <button class="quick-btn" onclick="sendQuickMessage('Tôi cần hỗ trợ đặt vé sự kiện')">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Hỗ trợ đặt vé</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('Tôi gặp vấn đề với thanh toán')">
                            <i class="fas fa-credit-card"></i>
                            <span>Vấn đề thanh toán</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('Tôi muốn hủy hoặc đổi vé')">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Hủy/đổi vé</span>
                        </button>
                        <button class="quick-btn" onclick="sendQuickMessage('Tôi cần hỗ trợ khác')">
                            <i class="fas fa-question-circle"></i>
                            <span>Hỗ trợ khác</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="typing-indicator" id="typing-indicator" style="display: none;">
            <div class="typing-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="typing-content">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="typing-text">Nhân viên đang nhập...</span>
            </div>
        </div>
        
        <div class="chat-input">
            <form id="chat-form" autocomplete="off">
                <div class="input-group">
                    <div class="input-wrapper">
                        <textarea 
                            id="user-input" 
                            placeholder="Nhập tin nhắn của bạn..." 
                            rows="1"
                            maxlength="1000"
                            required
                        ></textarea>
                        <div class="input-actions">
                            <button type="button" id="emoji-btn" class="btn-emoji" title="Emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                            <button type="submit" id="send-btn" class="btn-send" title="Gửi tin nhắn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="input-footer">
                    <div class="char-counter">
                        <span id="char-count">0</span>/1000
                    </div>
                    <div class="input-hint">
                        <i class="fas fa-info-circle"></i>
                        Nhấn Enter để gửi, Shift+Enter để xuống dòng
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let lastMessageCount = 0;
let currentSessionId = null;
let autoRefreshInterval;
let isConnected = false;

const userId = <?= $user_id ?>;
const userName = '<?= htmlspecialchars($user_name) ?>';
const userEmail = '<?= htmlspecialchars($user_email) ?>';

// Khởi tạo chat
function initializeChat() {
    loadMessages();
    startAutoRefresh();
    setupEventListeners();
    autoResizeTextarea();
}

// Load tin nhắn
function loadMessages() {
    fetch('<?= BASE_URL ?>/api/support/load_messages.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderMessages(data);
                updateConnectionStatus(data.session_status || 'waiting');
                
                if (data.session_id) {
                    currentSessionId = data.session_id;
                }
                
                // Kiểm tra tin nhắn mới
                if (data.messages && data.messages.length > lastMessageCount) {
                    if (lastMessageCount > 0) {
                        playNotificationSound();
                        showNewMessageNotification();
                    }
                    lastMessageCount = data.messages.length;
                }
                
                isConnected = true;
            } else {
                console.error('Lỗi load tin nhắn:', data.message);
                updateConnectionStatus('error');
                isConnected = false;
            }
        })
        .catch(err => {
            console.error('Lỗi kết nối:', err);
            updateConnectionStatus('error');
            isConnected = false;
        });
}

// Render tin nhắn
function renderMessages(data) {
    const container = document.getElementById('chat-messages');
    const welcome = container.querySelector('.welcome-message');
    
    // Giữ welcome message nếu chưa có tin nhắn
    if (!data.messages || data.messages.length === 0) {
        if (!welcome) {
            container.innerHTML = `
                <div class="no-messages">
                    <i class="fas fa-comment-slash"></i>
                    <h3>Chưa có tin nhắn nào</h3>
                    <p>Hãy bắt đầu cuộc trò chuyện bằng cách gửi tin nhắn đầu tiên</p>
                </div>
            `;
        }
        return;
    }
    
    // Xóa welcome message khi có tin nhắn
    container.innerHTML = '';
    
    data.messages.forEach(msg => {
        const messageEl = createMessageElement(msg);
        container.appendChild(messageEl);
    });
    
    scrollToBottom();
}

// Tạo element tin nhắn
function createMessageElement(msg) {
    const div = document.createElement('div');
    div.className = `message ${msg.sender === 'user' ? 'user-message' : 'staff-message'}`;
    
    const time = formatTime(msg.sent_at);
    
    if (msg.sender === 'user') {
        div.innerHTML = `
            <div class="message-content">
                <div class="message-text">${escapeHtml(msg.message)}</div>
                <div class="message-time">${time}</div>
            </div>
            <div class="message-avatar user-avatar">
                <i class="fas fa-user"></i>
            </div>
        `;
    } else {
        div.innerHTML = `
            <div class="message-avatar staff-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="message-content">
                <div class="staff-name">${escapeHtml(msg.staff_name || 'Nhân viên hỗ trợ')}</div>
                <div class="message-text">${escapeHtml(msg.message)}</div>
                <div class="message-time">${time}</div>
            </div>
        `;
    }
    
    return div;
}

// Gửi tin nhắn
function sendMessage(message) {
    if (!message.trim()) return;
    
    const input = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Disable input
    input.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('<?= BASE_URL ?>/api/support/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            updateCharCount();
            autoResizeTextarea();
            loadMessages();
            showSuccessMessage('Tin nhắn đã được gửi');
        } else {
            showErrorMessage(data.message || 'Gửi tin nhắn thất bại');
        }
    })
    .catch(err => {
        console.error('Lỗi gửi tin nhắn:', err);
        showErrorMessage('Không thể gửi tin nhắn. Vui lòng thử lại!');
    })
    .finally(() => {
        input.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        input.focus();
    });
}

// Gửi tin nhắn nhanh
function sendQuickMessage(message) {
    const input = document.getElementById('user-input');
    input.value = message;
    updateCharCount();
    sendMessage(message);
}

// Cập nhật trạng thái kết nối
function updateConnectionStatus(status) {
    const statusEl = document.getElementById('connection-status');
    const statusMap = {
        'waiting': { 
            text: 'Chờ nhân viên hỗ trợ', 
            class: 'waiting', 
            icon: 'clock' 
        },
        'active': { 
            text: 'Đã kết nối với nhân viên', 
            class: 'active', 
            icon: 'circle' 
        },
        'closed': { 
            text: 'Cuộc trò chuyện đã kết thúc', 
            class: 'closed', 
            icon: 'times-circle' 
        },
        'error': { 
            text: 'Lỗi kết nối', 
            class: 'error', 
            icon: 'exclamation-triangle' 
        }
    };
    
    const statusInfo = statusMap[status] || statusMap['waiting'];
    statusEl.innerHTML = `
        <i class="fas fa-${statusInfo.icon}"></i>
        <span>${statusInfo.text}</span>
    `;
    statusEl.className = `status-indicator ${statusInfo.class}`;
}

// Utility functions
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Vừa xong';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' phút trước';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' giờ trước';
    
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    container.scrollTop = container.scrollHeight;
}

function updateCharCount() {
    const input = document.getElementById('user-input');
    const counter = document.getElementById('char-count');
    const currentLength = input.value.length;
    
    counter.textContent = currentLength;
    
    if (currentLength > 900) {
        counter.style.color = '#e74c3c';
    } else if (currentLength > 800) {
        counter.style.color = '#f39c12';
    } else {
        counter.style.color = '#7f8c8d';
    }
}

function autoResizeTextarea() {
    const textarea = document.getElementById('user-input');
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Không thể phát âm thanh thông báo');
    }
}

function showNewMessageNotification() {
    if (document.hidden) {
        // Hiển thị notification nếu tab không active
        if (Notification.permission === 'granted') {
            new Notification('Tin nhắn mới từ Venow', {
                body: 'Bạn có tin nhắn mới từ nhân viên hỗ trợ',
                icon: '<?= BASE_URL ?>/public/images/logo.png'
            });
        }
    }
}

function showSuccessMessage(message) {
    showToast(message, 'success');
}

function showErrorMessage(message) {
    showToast(message, 'error');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        if (!document.hidden) {
            loadMessages();
        }
    }, 5000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

function setupEventListeners() {
    // Form submit
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('user-input');
        const message = input.value.trim();
        if (message) {
            sendMessage(message);
        }
    });
    
    // Input events
    const input = document.getElementById('user-input');
    input.addEventListener('input', function() {
        updateCharCount();
        autoResizeTextarea();
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('send-btn').click();
        }
    });
    
    // Refresh button
    document.getElementById('refresh-chat').addEventListener('click', function() {
        loadMessages();
        showSuccessMessage('Đã làm mới cuộc trò chuyện');
    });
    
    // Clear chat button
    document.getElementById('clear-chat').addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa toàn bộ lịch sử chat? Hành động này không thể hoàn tác.')) {
            fetch('<?= BASE_URL ?>/api/support/clear_chat.php', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showErrorMessage('Không thể xóa lịch sử chat');
                }
            })
            .catch(err => {
                showErrorMessage('Lỗi kết nối');
            });
        }
    });
    
    // Page visibility
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
            loadMessages();
        }
    });
    
    // Request notification permission
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

// Cleanup
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    initializeChat();
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>