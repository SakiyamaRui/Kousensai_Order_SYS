// ここから下 パスワードの表示・非表示の切り替えの処理

let ViewIcon = document.getElementById('view');

let InputType = document.getElementById('password');

ViewIcon.addEventListener('click', () => {
    if (InputType.type === 'password') {
        InputType.type = 'text';
        ViewIcon.innerHTML = '<svg width="25" height="25" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.8501 13.5681C14.2165 13.5681 15.3761 13.0918 16.3287 12.1391C17.2814 11.1865 17.7577 10.0269 17.7577 8.66051C17.7577 7.29407 17.2814 6.13453 16.3287 5.18187C15.3761 4.22921 14.2165 3.75289 12.8501 3.75289C11.4837 3.75289 10.3241 4.22921 9.37146 5.18187C8.4188 6.13453 7.94248 7.29407 7.94248 8.66051C7.94248 10.0269 8.4188 11.1865 9.37146 12.1391C10.3241 13.0918 11.4837 13.5681 12.8501 13.5681ZM12.8501 11.8938C11.9456 11.8938 11.1805 11.581 10.5551 10.9555C9.92958 10.3301 9.61684 9.56505 9.61684 8.66051C9.61684 7.75597 9.92958 6.99095 10.5551 6.36547C11.1805 5.73999 11.9456 5.42725 12.8501 5.42725C13.7546 5.42725 14.5197 5.73999 15.1451 6.36547C15.7706 6.99095 16.0834 7.75597 16.0834 8.66051C16.0834 9.56505 15.7706 10.3301 15.1451 10.9555C14.5197 11.581 13.7546 11.8938 12.8501 11.8938ZM12.8501 17.321C10.1942 17.321 7.77408 16.5993 5.58971 15.1559C3.40533 13.7125 1.71653 11.8072 0.523308 9.43995C0.465571 9.34372 0.422269 9.22825 0.3934 9.09353C0.364532 8.95881 0.350098 8.81447 0.350098 8.66051C0.350098 8.50654 0.364532 8.3622 0.3934 8.22748C0.422269 8.09276 0.465571 7.97729 0.523308 7.88106C1.71653 5.51386 3.40533 3.60854 5.58971 2.16513C7.77408 0.721709 10.1942 0 12.8501 0C15.506 0 17.9261 0.721709 20.1105 2.16513C22.2949 3.60854 23.9837 5.51386 25.1769 7.88106C25.2346 7.97729 25.2779 8.09276 25.3068 8.22748C25.3357 8.3622 25.3501 8.50654 25.3501 8.66051C25.3501 8.81447 25.3357 8.95881 25.3068 9.09353C25.2779 9.22825 25.2346 9.34372 25.1769 9.43995C23.9837 11.8072 22.2949 13.7125 20.1105 15.1559C17.9261 16.5993 15.506 17.321 12.8501 17.321ZM12.8501 15.5889C15.1788 15.5889 17.3199 14.9586 19.2733 13.698C21.2267 12.4375 22.7135 10.7583 23.7335 8.66051C22.7135 6.56274 21.2267 4.88356 19.2733 3.62298C17.3199 2.36239 15.1788 1.7321 12.8501 1.7321C10.5214 1.7321 8.38031 2.36239 6.42689 3.62298C4.47346 4.88356 2.97712 6.56274 1.93786 8.66051C2.97712 10.7583 4.47346 12.4375 6.42689 13.698C8.38031 14.9586 10.5214 15.5889 12.8501 15.5889Z" fill="black"/></svg>';
    } else {
        InputType.type = 'password';
        ViewIcon.innerHTML = '<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.43239 16.3639C3.83925 16.7707 4.49874 16.7706 4.90546 16.3637C5.31217 15.9568 5.31207 15.2973 4.90521 14.8905L2.51506 12.5011L6.12819 8.88774C8.30913 6.70669 11.4052 5.81802 14.3681 6.44233C14.931 6.56094 15.4834 6.20073 15.602 5.63779C15.7206 5.07485 15.3604 4.52236 14.7975 4.40376C11.1527 3.63579 7.33921 4.73032 4.65508 7.4146L0.305318 11.7646C-0.101496 12.1714 -0.101447 12.8311 0.305464 13.2378L3.43239 16.3639Z" fill="#311111"/><path d="M24.6949 11.7635L21.568 8.63751C21.1611 8.23077 20.5016 8.23087 20.0949 8.63775C19.6882 9.04464 19.6883 9.70416 20.0952 10.1109L22.4853 12.5004L18.8722 16.1137C16.6912 18.2948 13.5952 19.1834 10.6323 18.5591C10.0694 18.4405 9.51692 18.8007 9.39832 19.3636C9.27972 19.9266 9.63991 20.4791 10.2028 20.5977C13.8476 21.3656 17.6612 20.2711 20.3453 17.5868L24.6951 13.2368C25.1019 12.8299 25.1018 12.1703 24.6949 11.7635Z" fill="#311111"/><path d="M7.29224 12.5008C7.29224 13.0761 7.75857 13.5424 8.33383 13.5424C8.90909 13.5424 9.37542 13.0761 9.37542 12.5008C9.37542 10.7751 10.7746 9.37578 12.5003 9.37578C13.0755 9.37578 13.5418 8.90942 13.5418 8.33413C13.5418 7.75884 13.0755 7.29248 12.5003 7.29248C9.62409 7.29243 7.29224 9.62442 7.29224 12.5008Z" fill="#311111"/><path d="M15.6249 12.5009C15.6249 14.2267 14.2257 15.6259 12.5001 15.6259C11.9248 15.6259 11.4585 16.0923 11.4585 16.6676C11.4585 17.2429 11.9248 17.7092 12.5001 17.7092C15.3763 17.7092 17.7082 15.3772 17.7082 12.5009C17.7082 11.9256 17.2418 11.4592 16.6666 11.4592C16.0913 11.4592 15.6249 11.9256 15.6249 12.5009Z" fill="#311111"/><path d="M24.6935 0.305091C24.2868 -0.101697 23.6272 -0.101697 23.2205 0.305091L0.305074 23.2218C-0.101691 23.6286 -0.101691 24.2881 0.305074 24.6949C0.711838 25.1017 1.37137 25.1017 1.77814 24.6949L24.6935 1.77824C25.1003 1.37145 25.1003 0.711878 24.6935 0.305091Z" fill="#311111"/></svg>';
    }
});

// QRコードリーダー関連
let qr_proccessing = false;
let display = document.getElementById('qrcode-display');

const user = document.querySelector('.input__class[name="username"]');
const pass = document.getElementById('password');
const submit_trg = document.getElementById('login-button');

// ここから下　QRコード読み取り画面の表示・非表示の切り替え処理

const btn__QR = document.getElementById('btn__QR');
const QR__btn_back = document.getElementById('QR__btn-back');
const body = document.querySelector('body');
const QR__display = document.getElementById('QR__display');
const mask = document.querySelector('.mask');
// QRコード読み取り画面を表示する関数
const View_QR = () => {
    body.style.position = 'fixed';
    body.style.width = '100%';
    body.style.height = '100%';
    QR__display.style.visibility = 'visible';
    QR__display.style.top = '55%';
    mask.style.pointerEvents = 'auto';
    mask.style.opacity = '0.2';

    // QRコードリーダーの起動
    QRCodeReader(display).then((data) => {
        proccesing = false;

        try {
            let value = JSON.parse(data);

            let username = value.user;
            let password = value.password;

            user.value = username;
            pass.value = password;

        }catch(e) {
            console.log(e);

            alert("このQRコードはログインに使用できません");
            Close_QR();
            return false;
        }

        Close_QR();

        // ログイン処理をする
        login_proccess();
    }).catch((err) => {
        alert('エラーが発生しました');
        Close_QR();
    });
}

// QRコード読み取り画面を閉じる関数
const Close_QR = () => {
    body.removeAttribute('style');
    QR__display.removeAttribute('style');
    mask.removeAttribute('style');

}

// それぞれのボタンがクリックされたらする動作
btn__QR.addEventListener('click', View_QR);
mask.addEventListener('click', () => {
    Close_QR();
});

QR__btn_back.addEventListener('click', () => {
    Close_QR();
});

let login_proccess = () => {
    //ボタンを押せないようにする
    submit_trg.style.pointerEvents = 'none';

    if (user.value == '' || pass.value == '') {
        alert('入力してください');
        submit_trg.style.pointerEvents = 'auto';
        return false;
    }

    // JSONデータの作成
    let submit_data = {
        'user': user.value,
        'password': pass.value
    };

    // ローディングの表示
    document.getElementById('loading').classList.remove('hidden');

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/store.php?mode=login-request');
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(submit_data));

    xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {

            if (xhr.status >= 200 && xhr.status < 400) {
                // リクエストが正常に完了
                try {
                    var responceText = JSON.parse(xhr.responseText);

                    if (responceText == false) {
                        alert("ユーザーが見つからないもしくは、\nパスワードが違います。");
                    }else{
                        if ('redirect' in responceText) {
                            window.location.href = responceText['redirect'];
                        }else{
                            alert("ログインに失敗しました。\n担当者へ連絡してください。");
                        }
                    }
                } catch (e) {
                    console.log(e);
                }

                submit_trg.style.pointerEvents = 'auto';
                document.getElementById('loading').classList.add('hidden');
            }else{
                // リクエストでエラーが発生
                document.getElementById('loading').classList.add('hidden');
                alert('リクエストが正常に処理できませんでした。\nもう一度お試しください。');
                location.reload();
            }
        }
    }
}

submit_trg.addEventListener('click', login_proccess);

window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('loading').classList.add('hidden');
});