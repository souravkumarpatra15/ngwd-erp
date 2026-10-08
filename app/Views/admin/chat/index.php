<?php
// WhatsApp-like Chat Section - Admin Dashboard
// This provides a full-screen WhatsApp-style messaging interface

// Active conversation user data
$userId = $currentUser['id'] ?? session()->get('user_id');
$selectedUser = null;
$messages = [];

// If we have a selected user via AJAX or direct access
if (isset($conversationUserId)) {
    $userId = $conversationUserId;
}

// Load messages for selected user
if (isset($loadedMessages)) {
    $messages = $loadedMessages;
}

// Get user name for selected conversation
if (isset($selectedUserName)) {
    $selectedUser = [
        'id' => $userId,
        'name' => $selectedUserName,
        'avatar' => ''
    ];
}
?>
<!-- Chat Container -->
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="flex items-center px-4 h-16">
            <!-- Back button -->
            <button id="backToConversations" class="flex items-center gap-2 text-sm text-gray-500 hover:text-primary flex-1">
                <i class="bi bi-arrow-left"></i> Back to Conversations
            </button>
            
            <!-- User avatar and name -->
            <div class="relative w-10 h-10 shrink-0">
                <?php if($selectedUser && !empty($selectedUser['avatar'])): ?>
                    <img src="<?= esc(base_url('uploads/avatars/' . $selectedUser['avatar'])) ?>" 
                         alt="<?= esc($selectedUser['name']) ?>" 
                         class="w-full h-full rounded-full object-cover border-2 border-white">
                <?php else: ?>
                    <div class="w-full h-full rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                        <?= esc(substr($selectedUser['name'] ?? 'User', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ml-3 flex-1">
                <div class="font-medium truncate"><?= esc($selectedUser['name'] ?? 'Loading...') ?></div>
                <div class="text-xs text-gray-400"><?= esc($selectedUser['name'] ?? 'Client') ?></div>
            </div>
            
            <!-- Online indicator -->
            <div class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-green-500">
                <i class="bi bi-dot"></i> Online
            </div>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="flex flex-col flex-1 h-[calc(100vh-200px)]">
        <!-- Messages List -->
        <div class="flex-1 overflow-y-auto px-4 py-2" id="messagesList">
            <!-- Messages will be loaded here via AJAX -->
            <div id="noMessages" class="hidden h-64 flex items-center justify-center text-center text-gray-400">
                <i class="bi bi-chat-text-bottom fs-4 mb-3 opacity-25"></i>
                <span>Select a conversation to start messaging</span>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="bg-white border-t border-gray-200 p-4">
            <div class="flex gap-2">
                <!-- Image upload button -->
                <button id="uploadBtn" class="flex-1 px-3 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded flex items-center gap-2">
                    <i class="bi bi-image"></i> Image
                </button>
                
                <!-- Message input -->
                <textarea 
                    id="messageInput" 
                    rows="1" 
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Type a message..."
                    onkeydown="if(event.key=== 'Enter' && !event.shiftKey) { sendMessage(); return false; }"
                ></textarea>
                
                <!-- Send button -->
                <button id="sendBtn" class="px-4 py-2 text-sm bg-primary text-white rounded hover:bg-primary-dark transition-colors">
                    <i class="bi bi-send me-1"></i> Send
                </button>
            </div>
        </div>
    </div>
    
    <!-- Conversations Sidebar (collapsible on mobile) -->
    <div class="hidden md:block bg-white border-l border-gray-200 h-screen">
        <div class="p-4 border-b border-gray-200">
            <h4 class="font-medium text-sm">Conversations</h4>
            <button class="text-xs text-gray-400 float-right" onclick="toggleSidebar()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="h-[calc(100vh-80px)] overflow-y-auto">
            <div class="space-y-1">
                <?php foreach($conversations as $conv): ?>
                <div class="p-3 rounded cursor-pointer hover:bg-gray-50 select-item" 
                     data-user-id="<?= esc($conv['user_id']) ?>"
                     data-user-name="<?= esc($conv['user_name']) ?>">
                    <div class="d-flex w-100 align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-full bg-gray-200 w-6 h-6 text-center text-xs font-medium gray-text"><?= esc(substr($conv['user_name'] ?? 'U', 0, 1)) ?></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <small class="font-medium truncate text-gray-800"><?= esc($conv['user_name'] ?? 'Unknown') ?></small>
                            <div class="text-xs text-gray-400">
                                <?= esc($conv['last_message']['message'] ?? 'No messages yet') ?>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <?php if($conv['unread_count'] > 0): ?>
                                <span class="badge bg-primary rounded-pill text-white ms-1"><?= esc($conv['unread_count']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div id="emptyConversations" class="p-4 text-center text-gray-400 hidden">
                    <i class="bi bi-chat-text-bottom fs-4 mb-2 opacity-25"></i>
                    <p>No conversations yet</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedUserId = null;
    
    // Load conversations on sidebar
    loadConversations();
    
    // Load messages when a conversation is selected
    function loadConversations() {
        fetch('<?= base_url('admin/chat') ?>')
            .then(response => response.json())
            .then(data => {
                const sidebar = document.querySelector('.md\\:block');
                if (!sidebar) return;
                
                let html = '';
                const conversations = data.data.conversations || [];
                
                if (conversations.length === 0) {
                    document.getElementById('emptyConversations').classList.remove('hidden');
                } else {
                    document.getElementById('emptyConversations').classList.add('hidden');
                    conversations.forEach(conv => {
                        html += `
                            <div class="p-3 rounded cursor-pointer select-item" 
                                data-user-id="${conv.user_id}"
                                data-user-name="${conv.user_name}">
                                <div class="d-flex w-100 align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-full bg-gray-200 w-6 h-6 text-center text-xs font-medium gray-text">${conv.user_name ? conv.user_name.charAt(0) : 'U'}</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <small class="font-medium truncate text-gray-800">${conv.user_name}</small>
                                        <div class="text-xs text-gray-400">${conv.last_message ? conv.last_message.message : 'No messages yet'}</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        ${conv.unread_count > 0 ? `<span class="badge bg-primary rounded-pill text-white ms-1">${conv.unread_count}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    sidebar.innerHTML = html;
                }
            });
    }
    
    // Select a conversation
    function selectConversation(userId, userName) {
        selectedUserId = userId;
        
        // Update sidebar selection
        document.querySelectorAll('.select-item').forEach(el => el.classList.remove('selected'));
        event.target?.classList.add('selected');
        
        // Load messages
        loadMessages(userId, userName);
        
        // Update header
        updateHeader(userId, userName);
        
        // Scroll to bottom
        scrollToBottom();
    }
    
    // Load messages for a user
    function loadMessages(userId, userName) {
        selectedUserId = userId;
        
        fetch('<?= base_url('admin/chat/messages') }?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                const messagesList = document.getElementById('messagesList');
                const noMessages = document.getElementById('noMessages');
                
                if (data.data && data.data.length > 0) {
                    noMessages.classList.add('hidden');
                    messagesList.innerHTML = data.data.map(msg => `
                        <div class="mb-3 ${msg.is_me ? 'ms-auto' : 'mr-auto'} max-w-fit">
                            <div class="p-3 rounded ${msg.is_me ? 'bg-primary text-white' : 'bg-gray-200 text-gray-800'}">
                                <div class="small text-muted">${msg.time || msg.created_at || ''}</div>
                                <p class="mb-1 break-word">${msg.message || ''}</p>
                                ${msg.image_url ? `<img src="${msg.image_url}" class="mt-2 rounded w-full max-w-80" style="max-height: 200px;">` : ''}
                            </div>
                        </div>
                    `).join('');
                    scrollToBottom();
                } else {
                    noMessages.classList.remove('hidden');
                    messagesList.innerHTML = '';
                }
            })
            .catch(error => console.error('Error loading messages:', error));
    }
    
    // Update chat header
    function updateHeader(userId, userName) {
        // Update the header user info
        const userAvatar = document.querySelector('.relative.w-10.h-10');
        const userNameEl = document.querySelector('.font-medium.truncate');
        
        if (userAvatar && userNameEl) {
            userAvatar.innerHTML = `<div class="w-full h-full rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">${userName ? userName.charAt(0) : 'U'}</div>`;
            userNameEl.textContent = userName || 'Loading...';
        }
        
        // Send button should be enabled
        document.getElementById('sendBtn').disabled = false;
    }
    
    // Send message
    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message || !selectedUserId) return;
        
        // Show sending state
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="bi bi-loader bi-spin me-1"></i> Sending';
        
        fetch('<?= base_url('admin/chat/send') }', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                user_id: selectedUserId
            })
        })
        .then(response => response.json())
        .then(data => {
            // Clear input
            input.value = '';
            
            // Add message to UI
            addMessageToUI(data.data, true);
            
            // Load latest messages
            loadMessages(selectedUserId, <?= json_encode($selectedUser['name'] ?? 'User') ?>);
            
            // Reset button
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send me-1"></i> Send';
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send me-1"></i> Send';
        });
    }
    
    // Add message to UI
    function addMessageToUI(messageData, isMe) {
        const messagesList = document.getElementById('messagesList');
        
        const msgDiv = document.createElement('div');
        msgDiv.className = `mb-3 ${isMe ? 'ms-auto' : 'mr-auto'} max-w-fit`;
        
        const isImage = messageData.message_type === 'image';
        
        msgDiv.innerHTML = `
            <div class="p-3 rounded ${isMe ? 'bg-primary text-white' : 'bg-gray-200 text-gray-800'}">
                ${isImage ? `<img src="${messageData.image_url}" class="mt-2 rounded w-full max-w-80" style="max-height: 200px;">` : ''}
                <p class="mb-1 break-word">${messageData.message || ''}</p>
                <div class="small text-muted">${messageData.time || ''}</div>
            </div>
        `;
        
        messagesList.appendChild(msgDiv);
        messagesList.scrollTop = messagesList.scrollHeight;
    }
    
    // Scroll to bottom
    function scrollToBottom() {
        const messagesList = document.getElementById('messagesList');
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
    }
    
    // Image upload
    document.getElementById('uploadBtn').addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.onchange = function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('user_id', selectedUserId);
            formData.append('caption', document.getElementById('messageInput').value);
            
            fetch('<?= base_url('admin/chat/uploadImage') }', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                addMessageToUI(data.data, true);
                document.getElementById('messageInput').value = '';
            })
            .catch(error => console.error('Error uploading image:', error));
        };
        
        input.click();
    });
    
    // Handle conversation selection from sidebar
    document.querySelectorAll('.select-item').forEach(el => {
        el.addEventListener('click', function() {
            const userId = parseInt(this.getAttribute('data-user-id'));
            const userName = this.getAttribute('data-user-name');
            selectConversation(userId, userName);
        });
    });
    
    // Toggle sidebar on mobile
    function toggleSidebar() {
        const sidebar = document.querySelector('.md\\:block');
        if (sidebar) {
            sidebar.classList.toggle('hidden');
        }
    }
    
    // Close sidebar on link click
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.md\\:block')?.classList.add('hidden');
        });
    });
});
</script>