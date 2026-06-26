self.addEventListener('push', function (e) {
    var title = 'Kasir - Warung Angkringan';
    var body = 'Ada pemberitahuan baru!';
    
    if (e.data) {
        try {
            var msg = e.data.json();
            title = msg.title || title;
            body = msg.body || body;
        } catch (err) {
            body = e.data.text();
        }
    }

    e.waitUntil(self.registration.showNotification(title, {
        body: body
    }));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data) {
        event.waitUntil(
            clients.openWindow(event.notification.data)
        );
    }
});
