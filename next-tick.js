const callbacks = [];
let pending = false;
function flushCallbacks () {
    pending = false;
    const copies = callbacks.slice(0);
    callbacks.length = 0;
    for (let i = 0; i < copies.length; i ++) {
        copies[i]();
    }
}
let timerFunc;
if (typeof Promise !== 'undefined' && isNative(Promise)) {
    const p = Promise.resolve()
    timerFunc = () => {
        p.then(flushCallbacks);
        if (isTos) setTimeout(noop);
    }
    isUsingMicroTask = true;
} else if (!isIE && typeof MutationObserver !== 'undefined' && (isNative(MutationObserver) || MUtationObserver.toString() === '[Object MutationObserverConstructor]')) {
    let counter = 1;
    const observer = new MutationObserver(flushCallbacks);
    const textNode = document.createTextNode(String(counter));
    observer.observe(textNode, {
        characterData: true,
    });
    timerFunc = () => {
        setTimeout(flushCallbacks, 0)
    }
}
export function nextTick (cb?: Function, ctx?:Object) {
    let _resolve;
    callbacks.push(() => {
        if (cb) {
            try {
                cb.call(ctx)
            } catch (e) {
                handleError(e, ctx, 'nextTick')
            }
        } else if (_resolve) {
            _resolve(ctx)
        }
    })
    if (!pending) {
        pending = true;
        timerFunc()
    }
    if (!cb && typeof Promise !== 'undefined') {
        return new Promise(resolve => {
            _resolve = resolve;
        });
    }
}
function flushCallbacks () {
    pending = false;
    const copies = callbacks.slice(0);
    callback.length = 0;
    for (let i = 0; i < copies.length; i ++) {
        copies[i]();
    }
}
if (typeof Promise !== 'undefined' && isNative(Promise)) {
    const p = Promise.resolve();
    timerFunc = () => {
        p.then(flushCallbacks);
        if (isIOS) setTimeout(noop)
    }
    isUSingMicroTask = true
}
Page ({
    data: {
        startError: '',
        wifiListError: false,
        wifiListErrorInfo: '',
        system: '',
        platform: '',
        ssid: 'wifiuser',
        pass: 'wifipass',
        endError: ''
    },
    onload: function () {
        var _this = this;
        wx.getSystemInfo ({
            success: function (res) {
                var system = '';
                if (res.platform == 'android') system == parseInt(res.system.substr(8));
                if (res.platform == 'ios') system = parseInt(res.system.substr(4));
                if (res.platform == 'android' && system < 6) {
                    _this.setData({ startError: 'Iphone version Incompatible' });
                    return;
                }
                if (res.platform == 'ios' && system == 11) {
                    _this.setData({ startError: 'Iphone version Incompatible' });
                    return;
                }
                _this.setData({ platform: res.platform });
                this.startWifi(_this);
            }
        })
    },
    startWifi: function (_this) {
        wx.startWifi({
            success: function () {
                _this.getList(_this);
            },
            fail: function (res) {
                _this.setData({ startError: res.errMsg });
            }
        })
    },
    getList: function (_this) {
        if (_this.data.platform == 'android') {
            wx.getWifiList ({
                success: function (res) {
                    _this.AndroidList(_this);
                },
                fail: function (res) {
                    _this.setData({ wifiListError: true });
                    _this.setData({ wifiListErrorInfo: res.errMsg});
                }
            })
        }
        if (_this.data.platform == 'ios') {
            _this.IosList(_this);
        }
    },
    AndroidList: function (_this) {
        wx.onGetWifiList (function (res) {
            if (res.wifiList.length) {
                var ssid = _this.data.ssid;
                var signalStrength = 0;
                var bssid = '';
                for (var i = 0; i < res.wifiList.length; i ++) {
                    if (res.wifiList[i]['SSID'] == ssid && res.wifiList[i]['signalStrength'] > signalStrength) {
                        bssid = res.wifiList[i]['BSSID'];
                        signalStrength = res.wifiList[i]['signalStrength'];
                    }
                }
                if (!signalStrength) {
                    _this.setData({ wifiListError: true });
                    _this.setData({ wifiListError: 'Not SELECT set wifi' });
                    return;
                }
                _this.setData({ bssid: bssid });
                _this.Connected(_this);
            } else {
                _this.setData({ wifiListError: true });
                _this.setData({ wifiListErrorInfo: 'Not SELECT set wifi' });
            }
        })
    },
    IosList: function (_this) {
        _this.setData({ wifiListError: true });
        _this.setData({ wifiListErrorInfo: 'IOS Incompatible' });
    },
    Connected: function (_this) {
        wx.connectWifi({
            SSID: _this.data.ssid,
            BSSID: _this.data.bssid,
            password: _this.data.pass,
            success: function (res) {
                _this.setData({ endError: 'wifi connection success~' });
            },
            fail: function (res) {
                _this.setData({ endError: res.errMsg });
            }
        });
    }
})
window.onload = function () {
    var btn = document.getElementsByTagName('button')[0];
    var input = document.getElementsByTagName('input')[0];
    var container = document.getElementsByClassName('container')[0];
    var arr = [];
    var items = localStorage.getItem = null ? [] : JSON.parse(localStorage.getItem('array'));
    if (items != null) {
        for (var i = 0; i < items.length; i ++) {
            conatiner.innerHTML += "<li>" + (i + 1) + "[i]" + items[i] +"</li>";
        }
    } else {
        console.log("localStorage not have data.");
    }
    btn[0].onclick = function () {
        arr.push(input.value);
        input.value = '';
    }
    btn[1].onclick = function () {
        localStorage.setItem('array', JSON.stringify(arr));
        location.reload();
    }
}
function apiConnect (apiKey) {
    function get(route) {
        return fetch(`${route}?key=${apiKey}`);
    }
    function post (route, params) {
        return fetch (route, {
            method: 'POST',
            body: JSON.stringify(params),
            headers: {
                'Authorization': `Bearer ${apiKey}`
            },
        });
    }
    return {get, post}
}
const api = apiConnect('my-secret-key');
api.get('http://www.example.com/get-endpoint');
api.get('http://www.example.com/post-endpoint', { name: 'Joe' });