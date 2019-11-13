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
// Object
function Student (name) {
    this.name = name;
}
Student.prototype.hello = function () {
    alert('Hello, ' + this.name + ' !');
}
var self = document.getElementById('to-be-removed');
var parent = self.parentElement;
var removed = parent.removeChild(self);
removed === self;
var parent = document.getElementById('parent');
var parent = document.getElementById('parent');
parent.removeChild(parent.children[0]);
parent.removeChild(parent.children[1]);
parent.removeChild(parent.children[2]);
parent.removeChild(parent.children[3]);
parent.removeChild(parent.children[4]);
parent.removeChild(parent.children[5]);
parent.removeChild(parent.children[6]);
parent.removeChild(parent.children[7]);
parent.removeChild(parent.children[8]);
parent.removeChild(parent.children[9]);
parent.removeChild(parent.children[10]);
parent.removeChild(parent.children[11]);
parent.removeChild(parent.children[12]);
parent.removeChild(parent.children[13]);
parent.removeChild(parent.children[14]);
parent.removeChild(parent.children[15]);
parent.removeChild(parent.children[16]);
parent.removeChild(parent.children[17]);
parent.removeChild(parent.children[18]);
parent.removeChild(parent.children[19]);
parent.removeChild(parent.children[20]);
parent.removeChild(parent.children[21]);
parent.removeChild(parent.children[22]);
parent.removeChild(parent.children[23]);
parent.removeChild(parent.children[24]);
parent.removeChild(parent.children[25]);
parent.removeChild(parent.children[26]);
parent.removeChild(parent.children[27]);
parent.removeChild(parent.children[28]);
parent.removeChild(parent.children[29]);
parent.removeChild(parent.children[30]);
parent.removeChild(parent.children[31]);
parent.removeChild(parent.children[32]);
parent.removeChild(parent.children[33]);
parent.removeChild(parent.children[34]);
parent.removeChild(parent.children[35]);
parent.removeChild(parent.children[36]);
parent.removeChild(parent.children[37]);
parent.removeChild(parent.children[38]);
parent.removeChild(parent.children[39]);
parent.removeChild(parent.children[40]);
parent.removeChild(parent.children[41]);
parent.removeChild(parent.children[42]);
parent.removeChild(parent.children[43]);
parent.removeChild(parent.children[44]);
parent.removeChild(parent.children[45]);
parent.removeChild(parent.children[46]);
parent.removeChild(parent.children[47]);
parent.removeChild(parent.children[48]);
parent.removeChild(parent.children[49]);
parent.removeChild(parent.children[50]);
parent.removeChild(parent.children[51]);
parent.removeChild(parent.children[52]);
parent.removeChild(parent.children[53]);
parent.removeChild(parent.children[54]);
parent.removeChild(parent.children[55]);
parent.removeChild(parent.children[56]);
parent.removeChild(parent.children[57]);
parent.removeChild(parent.children[58]);
parent.removeChild(parent.children[59]);
parent.removeChild(parent.children[60]);
parent.removeChild(parent.children[61]);
parent.removeChild(parent.children[62]);
parent.removeChild(parent.children[63]);
parent.removeChild(parent.children[64]);
parent.removeChild(parent.children[65]);
parent.removeChild(parent.children[66]);
parent.removeChild(parent.children[67]);
parent.removeChild(parent.children[68]);
parent.removeChild(parent.children[69]);
parent.removeChild(parent.children[70]);
parent.removeChild(parent.children[71]);
parent.removeChild(parent.children[72]);
parent.removeChild(parent.children[73]);
parent.removeChild(parent.children[74]);
parent.removeChild(parent.children[75]);
parent.removeChild(parent.children[76]);
parent.removeChild(parent.children[77]);
parent.removeChild(parent.children[78]);
parent.removeChild(parent.children[79]);
parent.removeChild(parent.children[80]);
parent.removeChild(parent.children[81]);