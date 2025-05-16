<?php require_once 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/support.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="support-container">
    <div class="support-header">
        <h1><i class="fas fa-headset"></i> Hỗ trợ trực tuyến</h1>
        <p>Chào mừng đến với trung tâm hỗ trợ Venow. Hãy đặt câu hỏi của bạn, AI hỗ trợ của chúng tôi sẽ giúp bạn giải quyết vấn đề.</p>
    </div>

    <div class="chat-container">
        <div class="chat-messages" id="chat-messages">
            <div class="welcome-message">
                <i class="fas fa-robot"></i>
                <h2>Xin chào<?= isset($_SESSION['user_name']) ? ', ' . htmlspecialchars($_SESSION['user_name']) : '' ?>!</h2>
                <p>Tôi là trợ lý AI của Venow, sẵn sàng giúp bạn với mọi thắc mắc về đặt vé, sự kiện hoặc tài khoản của bạn. Hãy đặt câu hỏi hoặc chọn một trong các câu hỏi gợi ý bên dưới.</p>
            </div>
        </div>

        <div class="chat-input">
            <form id="chat-form">
                <input type="text" id="user-input" placeholder="Nhập câu hỏi của bạn..." autocomplete="off">
                <button type="submit" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>

        <div class="suggested-questions">
            <h3><i class="fas fa-lightbulb"></i> Câu hỏi thường gặp</h3>
            <div class="question-buttons">
                <button class="question-btn" data-question="Làm thế nào để đặt vé sự kiện?">Làm thế nào để đặt vé sự kiện?</button>
                <button class="question-btn" data-question="Tôi có thể hủy vé và được hoàn tiền không?">Hủy vé và hoàn tiền</button>
                <button class="question-btn" data-question="Làm sao để tạo tài khoản mới?">Tạo tài khoản mới</button>
                <button class="question-btn" data-question="Các phương thức thanh toán được chấp nhận?">Phương thức thanh toán</button>
                <button class="question-btn" data-question="Làm thế nào để tổ chức sự kiện trên Venow?">Tổ chức sự kiện</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const questionButtons = document.querySelectorAll('.question-btn');

    // Thêm tin nhắn vào khung chat
    function addMessage(content, isUser = false, isError = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = isUser ? 'message user' : 'message bot';
        if (isError) messageDiv.classList.add('error');

        const currentTime = new Date();
        const timeString = currentTime.getHours().toString().padStart(2, '0') + ':' + 
                          currentTime.getMinutes().toString().padStart(2, '0');

        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'message-avatar';
        avatarDiv.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        const paragraph = document.createElement('p');
        paragraph.textContent = content;
        contentDiv.appendChild(paragraph);
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = timeString;
        contentDiv.appendChild(timeDiv);

        if (isUser) {
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(avatarDiv);
        } else {
            messageDiv.appendChild(avatarDiv);
            messageDiv.appendChild(contentDiv);
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return messageDiv;
    }

    // Hiển thị trạng thái đang tải
    function addLoadingIndicator() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'message bot';
        loadingDiv.id = 'loading-message';

        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'message-avatar';
        avatarDiv.innerHTML = '<i class="fas fa-robot"></i>';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        
        const loadingDots = document.createElement('div');
        loadingDots.className = 'loading-dots';
        
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            loadingDots.appendChild(dot);
        }
        
        loadingIndicator.appendChild(loadingDots);
        contentDiv.appendChild(loadingIndicator);

        loadingDiv.appendChild(avatarDiv);
        loadingDiv.appendChild(contentDiv);

        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return loadingDiv;
    }

    // Xóa trạng thái đang tải
    function removeLoadingIndicator() {
        const loadingMessage = document.getElementById('loading-message');
        if (loadingMessage) {
            loadingMessage.remove();
        }
    }

    // Gửi tin nhắn đến API và nhận phản hồi
    async function sendMessage(message) {
        addMessage(message, true);
        const loadingIndicator = addLoadingIndicator();

        try {
            const response = await fetch('<?= BASE_URL ?>/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            if (!response.ok) {
                throw new Error('Không thể kết nối đến máy chủ.');
            }

            const data = await response.json();
            removeLoadingIndicator();

            if (data.error) {
                addMessage('Xin lỗi, đã xảy ra lỗi: ' + data.error, false, true);
            } else {
                addMessage(data.response, false);
            }
        } catch (error) {
            removeLoadingIndicator();
            addMessage('Xin lỗi, không thể kết nối đến dịch vụ AI. Vui lòng thử lại sau hoặc liên hệ với quản trị viên.', false, true);
            console.error('Error:', error);
        }
    }

    // Xử lý sự kiện submit form
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = userInput.value.trim();
        if (message) {
            sendMessage(message);
            userInput.value = '';
        }
    });

    // Xử lý sự kiện click vào câu hỏi gợi ý
    questionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            userInput.value = question;
            sendMessage(question);
        });
    });

    // Focus vào input khi trang được tải
    userInput.focus();
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
