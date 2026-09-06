/**
 * WebSocket Client for Real-time Communication
 * Handles real-time comments between Katuparan Center and LGU users
 */

class CommentWebSocketClient {
    constructor() {
        this.socket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3; // Reduce attempts
        this.reconnectDelay = 2000; // Increase delay to 2 seconds
        this.formId = null;
        this.userId = null;
        this.userRole = null;
    }

    connect(formId, userId, userRole) {
        this.formId = formId;
        this.userId = userId;
        this.userRole = userRole;


        try {
            // WebSocket connection
            this.socket = new WebSocket('ws://localhost:8081');

            this.socket.onopen = (event) => {
                this.isConnected = true;
                this.reconnectAttempts = 0;

                // Join the room for this form
                this.joinRoom();

                // Show connection status
                this.updateConnectionStatus('connected');
            };

            this.socket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            };

            this.socket.onclose = (event) => {
                this.isConnected = false;
                this.updateConnectionStatus('disconnected');

                // Only reconnect if it wasn't a clean close (code 1000)
                if (event.code !== 1000 && this.reconnectAttempts < this.maxReconnectAttempts) {
                    setTimeout(() => {
                        this.reconnectAttempts++;
                        this.connect(this.formId, this.userId, this.userRole);
                    }, this.reconnectDelay * this.reconnectAttempts);
                } else if (event.code === 1000) {
                } else {
                    this.updateConnectionStatus('error');
                }
            };

            this.socket.onerror = (error) => {
                this.updateConnectionStatus('error');
            };

        } catch (error) {
            this.fallbackToAjax();
        }
    }

    joinRoom() {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                action: 'join_room',
                form_id: this.formId,
                user_id: this.userId,
                user_role: this.userRole
            }));
        }
    }

    sendComment(formId, phaseId, activityId, commentText) {

        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            const message = {
                action: 'send_comment',
                form_id: formId,
                phase_id: phaseId,
                activity_id: activityId,
                comment_text: commentText,
                user_id: this.userId,
                user_role: this.userRole
            };

            this.socket.send(JSON.stringify(message));
        } else {
            this.fallbackToAjax();
        }
    }

    handleMessage(data) {
        switch (data.action) {
            case 'new_comment':
                this.displayNewComment(data);
                break;
            case 'user_joined':
                this.showUserJoinedNotification(data);
                break;
            case 'pong':
                // Heartbeat response
                break;
            case 'error':
                break;
        }
    }

    displayNewComment(data) {
        const isAdmin = data.user_role === 'admin';
        const alignmentClass = isAdmin ? '' : 'justify-content-end';
        const bgClass = isAdmin ? 'bg-light' : 'bg-primary text-white';
        const profilePicture = data.user_profile_picture || '../../assets/img/davsur.jpg';

        let newComment = `
            <div class="comment-card mb-3">
                <div class="d-flex gap-2 ${alignmentClass}">
                    ${isAdmin ? `
                    <div class="user-avatar">
                        <img src="${profilePicture}"
                             class="rounded-circle"
                             alt="Profile Picture"
                             width="40"
                             height="40"
                             onerror="this.src='../../assets/img/davsur.jpg'">
                    </div>
                    ` : ''}
                    <div class="flex-grow-0">
                        <div class="comment-content p-3 ${bgClass} rounded" style="max-width: 80%;">
                            <p class="mb-1">${data.comment_text}</p>
                            <small class="${isAdmin ? 'text-muted' : 'text-white-50'}">${data.created_at}</small>
                        </div>
                    </div>
                    ${!isAdmin ? `
                    <div class="user-avatar">
                        <img src="${profilePicture}"
                             class="rounded-circle"
                             alt="Profile Picture"
                             width="40"
                             height="40"
                             onerror="this.src='../../assets/img/davsur.jpg'">
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        $('#commentsList').prepend(newComment);

        // Scroll to top to show new comment
        $('#commentsList').scrollTop(0);

        // Show notification
        this.showNotification(`New comment from ${data.user_name}`, 'info');
    }

    showUserJoinedNotification(data) {
        if (data.user_id !== this.userId) {
            this.showNotification(`${data.user_name} joined the conversation`, 'success');
        }
    }

    showNotification(message, type = 'info') {
        // Create a simple notification
        const notification = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 3000);
    }

    updateConnectionStatus(status) {
        const statusElement = $('#websocket-status');
        if (statusElement.length === 0) {
            // Create status indicator if it doesn't exist
            $('body').prepend(`
                <div id="websocket-status" class="position-fixed"
                     style="top: 10px; left: 10px; z-index: 9999; padding: 5px 10px; border-radius: 5px; font-size: 12px;">
                </div>
            `);
        }

        switch (status) {
            case 'connected':
                statusElement.removeClass('bg-warning bg-danger').addClass('bg-success text-white')
                    .text('🟢 Connected');
                break;
            case 'disconnected':
                statusElement.removeClass('bg-success bg-danger').addClass('bg-warning text-dark')
                    .text('🟡 Reconnecting...');
                break;
            case 'error':
                statusElement.removeClass('bg-success bg-warning').addClass('bg-danger text-white')
                    .text('🔴 Connection Error');
                break;
        }
    }

    fallbackToAjax() {
        // This will be handled by the existing AJAX code
        // We'll modify the form submission to check WebSocket first
    }

    disconnect() {
        if (this.socket) {
            this.socket.close();
        }
    }

    // Heartbeat to keep connection alive
    startHeartbeat() {
        setInterval(() => {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify({ action: 'ping' }));
            }
        }, 30000); // Ping every 30 seconds
    }
}

// Global WebSocket client instance
window.commentWebSocket = new CommentWebSocketClient();
