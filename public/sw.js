self.addEventListener('push', function(event) {
    if (event.data) {
        const payload = event.data.json();
        const title = payload.title || 'การแจ้งเตือนใหม่';
        const options = {
            body: payload.body || '',
            icon: 'images/favicon.ico', // Adjust path if needed
            badge: 'images/favicon.ico',
            data: {
                url: payload.url || '/'
            }
        };

        event.waitUntil(
            self.registration.showNotification(title, options)
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
