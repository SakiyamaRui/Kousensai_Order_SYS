(function() {
    QRCodeReader = (video, canvas = document.createElement('canvas')) => {
        return new Promise((resolve, reject) => {
            let ctx = canvas.getContext('2d', {willReadFrequently: true});
            let img;
            
            // JSQRがあるか確認

            // すでにストリームされている場合は
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                loop();
            }else {
                // Webカメラの映像を取得
                const userMedia = {video: {facingMode: "environment"}};
                navigator.mediaDevices.getUserMedia(userMedia).then((stream) => {
                    video.srcObject = stream;
                    camera_stream = stream;
                    video.setAttribute("playsinline", true);
                    video.play();
                    loop();
                }).catch((err) => {
                    console.error(err);
                    reject(err);
                });
            }

            function loop() {
                // キャンバスに描画
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.height = video.clientHeight;
                    canvas.width = video.clientWidth;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // QRの識別
                    img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    let code = jsQR(img.data, img.width, img.height, {inversionAttempts: "dontInvert"});

                    if (code) {
                        // コードを読み取れた場合
                        console.log(`QR: ${code.location}`);

                        resolve(code.data, camera_stream);
                        return 0;
                    }
                }

                setTimeout(loop, 800);
            }
        });
    }
}());