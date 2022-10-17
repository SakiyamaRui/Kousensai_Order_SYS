var type = '';
var order_id = '';

self.addEventListener('install', (e) => {
    e.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (e) => {
    e.waitUntil(self.clients.claim());
});

self.addEventListener('push', function(e) {
    let receiveText = decodeURIComponent(atob(e.data.text()));

    let json = JSON.parse(receiveText);

    let body = '';
    let title = '';

    // 処理を分ける
    if (json['type'] == 'costomer') {
        body = json['body'];
        title = json['title'];
        type = 'costomer';
        order_id = json['order_id'];
    }else if (json['type'] == 'shop') {
        // 店舗側の処理
        body = '新しい注文が入りました。注文番号(00000)';
        title = '新しい注文が入りました';
    }else{
        return false;
    }

    let options = {
        body: body,
        tag: title,
    };

    e.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (e) {
    e.notification.close();

    if (type == 'costomer') {
        e.waitUntil(
            // push通知がクリックされたら開くページ
            clients.openWindow(`/order/${order_id}/`)
        );
    }else if (type == 'shop') {
        e.waitUntil(
            // push通知がクリックされたら開くページ
            clients.openWindow(`/manage/orders/`)
        );
    }
});
