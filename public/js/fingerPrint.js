(function () {
    fpPromise = import('https://openfpcdn.io/fingerprintjs/v3').then(FingerprintJS => FingerprintJS.load());

    fpPromise.then((fp) => fp.get()).then((result) => {
        window.visitorId = result.visitorId;
    });
}());