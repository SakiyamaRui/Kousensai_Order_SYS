self.addEventListener('push', function(e) {
    let receiveText = e.data.text();

    let json = JSON.parse(receiveText);

    let options = {
        body: e.data.text(),
        tag: json['title'],
    }

    e.waitUntil(self.registration.showNotification(json['title'], options));
})

self.addEventListener('message', (e) => {
    self.registration.showNotification(e.data);
})