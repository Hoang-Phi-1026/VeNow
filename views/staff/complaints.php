<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['vai_tro'] ?? $_SESSION['user']['ma_vai_tro'] ?? 0) != 3) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}
?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khiếu nại - Staff</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Dùng chung CSS chat support -->
    <link href="<?= BASE_URL ?>/public/css/support.css?v=1" rel="stylesheet">
    <style>
        /* Bổ sung layout ngang cho staff chat board */
        .staff-support-board {
            max-width: 1150px;
            margin: 40px auto 0;
            background: #fff;
            border-radius: 2.2rem;
            box-shadow: 0 8px 32px rgba(255,87,34,.09);
            padding: 0;
            min-height: 700px;
            display: flex;
            overflow: hidden;
        }
        .staff-sessions-list {
            width: 330px;
            background: var(--venow-orange-bg);
            border-right: 1.5px solid var(--venow-border);
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: 700px;
        }
        .staff-sessions-header {
            padding: 30px 24px 16px 24px;
            background: var(--venow-orange);
            color: #fff;
            font-weight: 700;
            font-size: 1.13rem;
            letter-spacing: 0.05em;
            border-bottom: 1.5px solid var(--venow-border);
        }
        .sessions-list-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 0 0 10px 0;
        }
        .session-item {
            padding: 18px 22px;
            border-bottom: 1px solid var(--venow-border);
            cursor: pointer;
            background: transparent;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 13px;
            position: relative;
        }
        .session-item:hover,
        .session-item.active {
            background: var(--venow-orange-bg2);
        }
        .session-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: var(--venow-orange);
            border-radius: 4px;
        }
        .session-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--venow-orange-light);
            color: #fff;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .session-info {
            flex: 1;
            min-width: 0;
        }
        .session-name {
            font-weight: 700;
            font-size: 1.07rem;
            color: var(--venow-orange);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .session-last {
            font-size: 0.98rem;
            color: var(--venow-gray);
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .session-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }
        .unread-badge {
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 12px;
            margin-left: 6px;
            font-weight: 600;
        }
        .status-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            background: var(--venow-orange-bg2);
            color: var(--venow-orange);
            font-weight: 600;
        }
        .status-active {background: #2ed573; color: #fff;}
        .status-pending {background: #ffc107; color: #fff;}
        .status-closed {background: #6c757d; color: #fff;}

        .staff-chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 700px;
            background: var(--venow-orange-bg);
        }
        .staff-chat-header {
            padding: 28px 30px 12px 30px;
            background: #fff;
            border-bottom: 1.5px solid var(--venow-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
        }
        .staff-chat-user {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .staff-chat-user .user-name {
            font-weight: 700;
            color: var(--venow-orange);
            font-size: 1.07rem;
        }
        .staff-chat-user .user-email {
            color: var(--venow-gray);
            font-size: 0.97rem;
        }
        .staff-chat-header-ops {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .staff-chat-status {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            background: var(--venow-orange-bg2);
            color: var(--venow-orange);
            font-weight: 600;
        }
        .staff-chat-close-btn {
            background: #fff;
            border: 1.5px solid #e5735b;
            color: var(--venow-orange);
            border-radius: 10px;
            padding: 4px 18px;
            font-size: 1.01rem;
            font-weight: 600;
            transition: background .16s, color .16s;
            cursor: pointer;
        }
        .staff-chat-close-btn:hover {
            background: var(--venow-orange);
            color: #fff;
        }

        .staff-messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px 32px;
            background: var(--venow-orange-bg);
            display: flex;
            flex-direction: column;
        }
        .staff-message {
            margin-bottom: 17px;
            display: flex;
            align-items: flex-end;
        }
        .staff-message.user {
            justify-content: flex-start;
        }
        .staff-message.staff {
            justify-content: flex-end;
        }
        .staff-message-content {
            max-width: 68%;
            padding: 14px 19px;
            border-radius: 15px;
            font-size: 1.02rem;
            box-shadow: 0 2px 12px rgba(255,87,34,0.07);
            font-weight: 500;
            line-height: 1.6;
        }
        .staff-message.user .staff-message-content {
            background: #fff;
            color: var(--venow-orange);
            border-bottom-left-radius: 7px;
            border: 1.5px solid var(--venow-border);
        }
        .staff-message.staff .staff-message-content {
            background: linear-gradient(100deg,var(--venow-orange),var(--venow-orange-light));
            color: #fff;
            border-bottom-right-radius: 7px;
        }
        .staff-message-meta {
            margin-top: 7px;
            font-size: 0.95rem;
            color: var(--venow-gray);
        }
        .staff-no-session {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c4a8a8;
            font-size: 1.13rem;
        }
        .staff-message-input {
            background: #fff;
            border-top: 1.5px solid var(--venow-border);
            padding: 18px 30px;
        }
        .staff-message-input form {
            display: flex;
            gap: 12px;
        }
        .staff-message-input input {
            flex: 1;
            border: 2px solid var(--venow-border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1.03rem;
            color: var(--venow-orange);
            background: var(--venow-orange-bg);
            transition: border-color 0.18s;
        }
        .staff-message-input input:focus {
            outline: none;
            border-color: var(--venow-orange);
        }
        .staff-message-input button {
            background: var(--venow-orange);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0 24px;
            font-size: 1.08rem;
            font-weight: 700;
            transition: background .16s, color .16s;
            cursor: pointer;
        }
        .staff-message-input button:hover {
            background: var(--venow-orange-light);
        }
        /* Responsive */
        @media (max-width: 1200px) {
            .staff-support-board { max-width: 100vw; }
        }
        @media (max-width: 990px) {
            .staff-support-board { flex-direction: column; min-height: 0;}
            .staff-sessions-list { width: 100%; min-height: 0; border-right: none; border-bottom: 1.5px solid var(--venow-border);}
        }
        @media (max-width: 700px) {
            .staff-support-board { max-width: 100vw; border-radius: 0;}
            .staff-sessions-list { width: 100vw; min-width: 0;}
            .staff-chat-header, .staff-messages-list, .staff-message-input { padding-left: 10px; padding-right: 10px;}
            .staff-messages-list { padding-left: 3px; padding-right: 3px;}
        }
    </style>
<body>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="staff-support-board">
    <!-- Session list sidebar -->
    <div class="staff-sessions-list">
        <div class="staff-sessions-header">
            <i class="fas fa-users"></i> Danh sách cuộc trò chuyện
        </div>
        <div class="sessions-list-scroll" id="sessions-list">
            <div id="sessions-loading" class="loading" style="padding: 22px; text-align:center;">
                <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
            <div id="sessions-error" class="error-message" style="display: none;"></div>
            <div id="sessions-container"></div>
        </div>
    </div>
    <!-- Chat area -->
    <div class="staff-chat-area">
        <div class="staff-chat-header" style="display: none;" id="chat-header">
            <div class="staff-chat-user">
                <span class="user-name" id="chat-user-name">Khách hàng</span>
                <span class="user-email" id="chat-user-email"></span>
            </div>
            <div class="staff-chat-header-ops">
                <span class="staff-chat-status" id="chat-status">Trạng thái</span>
                <button class="staff-chat-close-btn" id="close-session-btn">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
        <div class="staff-messages-list" id="messages-area" style="display: none;">
            <div id="messages-loading" class="loading">
                <i class="fas fa-spinner fa-spin"></i> Đang tải tin nhắn...
            </div>
            <div id="messages-error" class="error-message" style="display: none;"></div>
            <div id="messages-list"></div>
        </div>
        <div class="staff-message-input" id="chat-input-area" style="display: none;">
            <form id="message-form" autocomplete="off">
                <input type="text" id="message-input" placeholder="Nhập tin nhắn..." maxlength="1000" autocomplete="off">
                <button type="submit" id="send-btn"><i class="fas fa-paper-plane"></i> Gửi</button>
            </form>
        </div>
        <div class="staff-no-session" id="no-session">
            <div>
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>Chọn một cuộc trò chuyện để bắt đầu</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<script>
class StaffSupportBoard {
    constructor() {
        this.currentSessionId = null;
        this.polling = null;
        this.init();
    }
    init() {
        this.loadSessions();
        this.bindEvents();
    }
    bindEvents() {
        // Send message
        document.getElementById('message-form').addEventListener('submit', e => {
            e.preventDefault();
            this.sendMessage();
        });
        // Close session
        document.getElementById('close-session-btn').addEventListener('click', () => {
            this.closeSession();
        });
        // Auto-refresh
        setInterval(() => this.loadSessions(), 30000);
    }
    async loadSessions() {
        const loading = document.getElementById('sessions-loading');
        const error = document.getElementById('sessions-error');
        const container = document.getElementById('sessions-container');
        loading.style.display = 'block';
        error.style.display = 'none';
        container.innerHTML = '';
        try {
            const resp = await fetch(`<?= BASE_URL ?>/api/support/staff_load_sessions.php?status=all`);
            if (!resp.ok) throw new Error(resp.status);
            const data = await resp.json();
            if (!data.success) throw new Error(data.message || 'Không thể tải danh sách');
            loading.style.display = 'none';
            this.renderSessions(data.sessions || []);
        } catch (e) {
            loading.style.display = 'none';
            error.style.display = 'block';
            error.textContent = 'Lỗi: ' + e.message;
        }
    }
    renderSessions(sessions) {
        const container = document.getElementById('sessions-container');
        if (!sessions.length) {
            container.innerHTML = '<div class="text-center p-4 text-muted">Không có cuộc trò chuyện nào</div>';
            return;
        }
        container.innerHTML = sessions.map(session => `
            <div class="session-item ${session.session_id === this.currentSessionId ? 'active' : ''}" data-session-id="${session.session_id}">
                <span class="session-avatar"><i class="fas fa-user"></i></span>
                <div class="session-info">
                    <div class="session-name">${this.escapeHtml(session.user_name || session.user_email || 'Khách hàng')}</div>
                    <div class="session-last">${this.escapeHtml(session.last_message || 'Chưa có tin nhắn')}</div>
                    <div class="session-meta">
                        <small>${this.formatDateTime(session.last_message_time || session.created_at)}</small>
                        <span class="status-badge status-${session.status}">${this.getStatusText(session.status)}</span>
                        ${session.unread_count > 0 ? `<span class="unread-badge">${session.unread_count}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        // Bind click
        container.querySelectorAll('.session-item').forEach(item => {
            item.addEventListener('click', () => {
                if (item.classList.contains('active')) return;
                this.selectSession(item.dataset.sessionId);
            });
        });
    }
    async selectSession(sessionId) {
        this.currentSessionId = sessionId;
        // Mark active
        document.querySelectorAll('.session-item').forEach(item => item.classList.remove('active'));
        const current = document.querySelector(`.session-item[data-session-id="${sessionId}"]`);
        if (current) current.classList.add('active');
        // Show chat area
        document.getElementById('no-session').style.display = 'none';
        document.getElementById('chat-header').style.display = '';
        document.getElementById('messages-area').style.display = '';
        document.getElementById('chat-input-area').style.display = '';
        await this.loadMessages(sessionId);
        this.startPolling();
    }
    async loadMessages(sessionId) {
        document.getElementById('messages-loading').style.display = 'block';
        document.getElementById('messages-error').style.display = 'none';
        document.getElementById('messages-list').innerHTML = '';
        try {
            const resp = await fetch(`<?= BASE_URL ?>/api/support/staff_load_messages.php?session_id=${sessionId}`);
            if (!resp.ok) throw new Error(resp.status);
            const data = await resp.json();
            if (!data.success) throw new Error(data.message || 'Không thể tải tin nhắn');
            document.getElementById('messages-loading').style.display = 'none';
            this.renderMessages(data.messages || []);
            this.updateHeader(data.session, data.user_info);
        } catch (e) {
            document.getElementById('messages-loading').style.display = 'none';
            document.getElementById('messages-error').style.display = 'block';
            document.getElementById('messages-error').textContent = 'Lỗi: ' + e.message;
        }
    }
    renderMessages(messages) {
        const container = document.getElementById('messages-list');
        if (!messages.length) {
            container.innerHTML = '<div class="text-center text-muted">Chưa có tin nhắn nào</div>';
            return;
        }
        container.innerHTML = messages.map(msg => `
            <div class="staff-message ${msg.sender}">
                <div class="staff-message-content">
                    <div>${this.escapeHtml(msg.message)}</div>
                    <div class="staff-message-meta">
                        ${msg.sender === 'user' ? msg.user_name : msg.staff_name} •
                        ${this.formatDateTime(msg.sent_at)}
                    </div>
                </div>
            </div>
        `).join('');
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }
    updateHeader(session, userInfo) {
        document.getElementById('chat-user-name').textContent = userInfo?.ho_ten || userInfo?.email || 'Khách hàng';
        document.getElementById('chat-user-email').textContent = userInfo?.email || '';
        const statusEl = document.getElementById('chat-status');
        statusEl.textContent = this.getStatusText(session.status);
        statusEl.className = `staff-chat-status status-${session.status}`;
    }
    async sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message || !this.currentSessionId) return;
        const sendBtn = document.getElementById('send-btn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gửi';
        try {
            const resp = await fetch(`<?= BASE_URL ?>/api/support/staff_send_message.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ session_id: this.currentSessionId, message })
            });
            const data = await resp.json();
            if (data.success) {
                input.value = '';
                await this.loadMessages(this.currentSessionId);
                this.loadSessions();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch (e) {
            alert('Có lỗi xảy ra khi gửi tin nhắn');
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi';
        }
    }
    async closeSession() {
        if (!this.currentSessionId) return;
        if (!confirm('Bạn có chắc muốn đóng cuộc trò chuyện này?')) return;
        try {
            const resp = await fetch(`<?= BASE_URL ?>/api/support/staff_close_session.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ session_id: this.currentSessionId })
            });
            const data = await resp.json();
            if (data.success) {
                alert('Đã đóng cuộc trò chuyện');
                this.loadSessions();
                this.currentSessionId = null;
                document.getElementById('no-session').style.display = 'flex';
                document.getElementById('chat-header').style.display = 'none';
                document.getElementById('messages-area').style.display = 'none';
                document.getElementById('chat-input-area').style.display = 'none';
                this.stopPolling();
            } else {
                alert('Lỗi: ' + data.message);
            }
        } catch (e) {
            alert('Có lỗi xảy ra khi đóng cuộc trò chuyện');
        }
    }
    startPolling() {
        this.stopPolling();
        this.polling = setInterval(() => {
            if (this.currentSessionId) this.loadMessages(this.currentSessionId);
        }, 5000);
    }
    stopPolling() {
        if (this.polling) clearInterval(this.polling);
        this.polling = null;
    }
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    formatDateTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN');
    }
    getStatusText(status) {
        const statusMap = {
            'pending': 'Chờ xử lý',
            'active': 'Đang xử lý',
            'closed': 'Đã đóng'
        };
        return statusMap[status] || status;
    }
}
document.addEventListener('DOMContentLoaded', () => new StaffSupportBoard());
</script>
</body>
