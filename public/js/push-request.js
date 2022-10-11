function urlB64ToUint8Array (base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/');
  
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
  
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

/* Service Workerの登録 */
window.addEventListener('load', async () => {
    if ('serviceWorker' in navigator) {
        window.sw = await navigator.serviceWorker.register('/serviceWorker.js', {scope: '/'});
    }
});


async function noticeSettings() {
    return new Promise(async (resolve, reject) => {
        if (!('Notification' in window)) {
            reject('NotSupport');
        }

        const appServerKey = 'BGP8mhYoPuSudWqM5Xs1l191OYI2QN3IJLDg9Es4GM3D12AxsaeOi2l5aZfEH_oFVzky-jhwRdXyRX4sKfrjBPs';
        const applicationServerKey = urlB64ToUint8Array(appServerKey);

        (await navigator.serviceWorker.ready).pushManager.permissionState({
            userVisibleOnly: true
        }).then((state) => {
            switch (state) {
                // 未選択
                case 'prompt':
                    // 新しくサブスクリプション登録
                    window.sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey
                    }).then((subscription) => {
                        const key = subscription.getKey('p256dh');
                        const token = subscription.getKey('auth');
                        const request = {
                            endpoint: subscription.endpoint,
                            userPublicKey: btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
                            userAuthToken: btoa(String.fromCharCode.apply(null, new Uint8Array(token)))
                        };

                        resolve(request);
                    }).catch((err) => {
                        console.log(err);
                        reject();
                    });
                    break;
                // 拒否
                case 'denied':
                    reject('denied');
                    break;
                // 承諾済み
                case 'granted':
                    // すでに登録されているサブスクリプションを取得
                    window.sw.pushManager.getSubscription().then((subscription) => {
                        const key = subscription.getKey('p256dh');
                        const token = subscription.getKey('auth');
                        const request = {
                            endpoint: subscription.endpoint,
                            userPublicKey: btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
                            userAuthToken: btoa(String.fromCharCode.apply(null, new Uint8Array(token)))
                        };

                        console.log(request);
                        resolve(request);
                    });
                    break;
            }
        });
    });
}

/**
 * NoticeSubscription
 * サーバーへサブスクリプションの登録
 */
function noticeSubscription(request) {
    return new Promise(async (resolve, reject) => {
        let fingerPrint;
        try {
            // FingerPrintを取得
            let result = (await fpPromise).get();
        }catch (err) {
            // FingerPrint発行時にエラー
            console.log(err);
            reject('fingerPrint Error');
        }

        // サーバー側に送信するデータの作成
        let sendData = {
            'fingerPrint': fingerPrint,
            'pushRequest': request
        };

        // サーバーへ保存
        let xhr = new XMLHttpRequest();
        xhr.open(
            'POST',
            '/api/?mode=notice-subscription'
        );
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.send(JSON.stringify(sendData));

        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                const status = xhr.status;

                if (status === 0 || (status >= 200 && status < 400)) {
                    // リクエストが正常に完了
                    let responceText = JSON.stringify(xhr.responseText);
                    console.log(responceText);
                }else{
                    // リクエストでエラーが発生
                    reject('Network Error');
                }
            }
        }
    });
}