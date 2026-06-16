@auth
<script>
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function subscribeUser() {
        navigator.serviceWorker.ready
            .then(function (registration) {
                const subscribeOptions = {
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('{{ config('webpush.vapid.public_key') }}')
                };

                return registration.pushManager.subscribe(subscribeOptions);
            })
            .then(function (pushSubscription) {
                storePushSubscription(pushSubscription);
            })
            .catch(function (err) {
                console.log('Failed to subscribe the user: ', err);
            });
    }

    function storePushSubscription(pushSubscription) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('/push-subscriptions', {
            method: 'POST',
            body: JSON.stringify(pushSubscription),
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            }
        })
        .then(function(res) {
            return res.json();
        })
        .then(function(res) {
            console.log('Push subscription success.');
        })
        .catch(function(err) {
            console.log('Push subscription failed.', err);
        });
    }

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js')
        .then(function (swReg) {
            console.log('Service Worker is registered', swReg);
            swReg.update(); // Force update sw.js
            
            // Request permission directly upon login
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        subscribeUser();
                    }
                });
            } else if (Notification.permission === 'granted') {
                subscribeUser(); // Re-subscribe to make sure we have token in DB
            }
        })
        .catch(function (error) {
            console.error('Service Worker Error', error);
        });
    } else {
        console.warn('Push messaging is not supported');
    }
</script>
@endauth
