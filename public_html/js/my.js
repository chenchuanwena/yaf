//公共的验证方法
var report_message = "";
function hasContent(verification) {
    var has_value = false;
    switch (verification.control_type) {
        case 'html':
            has_value = $('#' + verification.control_id).html();
            break;
        case 'textbox':
            has_value = $('#' + verification.control_id).textbox('getValue');
            break;
        case 'src':
            has_value = $('#' + verification.control_id).attr('src');
            break;
        default:
            break;
    }
    if (has_value == false || has_value == null || has_value == '') {
        report_message = verification.message;
    }
}
//V2 字符串格式化函数
// string format js
String.prototype.format = function ()
{
    var args = arguments;
    return this.replace(/\{(\d+)\}/g,
            function (m, i) {
                return args[i];
            });
}
String.format = function () {
    if (arguments.length == 0)
        return null;
    var str = arguments[0];
    for (var i = 1; i < arguments.length; i++) {
        var re = new RegExp('\\{' + (i - 1) + '\\}', 'gm');
        str = str.replace(re, arguments[i]);
    }
    return str;
};
//trim去掉字符串两边的指定字符,默去空格
String.prototype.Trim = function (str) {
    if (!str) {
        str = '\\s';
    } else {
        if (str == '\\') {
            str = '\\\\';
        } else if (str == ',' || str == '|' || str == ';') {
            str = '\\' + str;
        } else {
            str = '\\s';
        }
    }
    eval('var reg=/(^' + str + '+)|(' + str + '+$)/g;');
    return this.replace(reg, '');
};

String.prototype.trim = function (str) {
    return this.Trim(str);
};
//判断一个字符串是否为NULL或者空字符串
String.prototype.isNull = function () {
    return this == null || this.trim().length == 0;
}
//判断两个字符串是否相等
String.prototype.equals = function (str) {
    return this == str;
}

//字符串截取后面加入...
String.prototype.interceptString = function (len) {
    if (this.length > len) {
        return this.substring(0, len - 1) + "...";
    }
    else {
        return this;
    }
}
//获得一个字符串的字节数

String.prototype.countLength = function () {
    var strLength = 0;
    for (var i = 0; i < this.length; i++) {
        if (this.charAt(i) > '~')
            strLength += 2;
        else
            strLength += 1;
    }
    return strLength;
};

//根据指定的字节数截取字符串

String.prototype.cutString = function (cutLength) {
    if (!cutLength) {
        cutLength = this.countLength();
    }
    var strLength = 0;
    var cutStr = "";
    if (cutLength > this.countLength()) {
        cutStr = this;
    } else {
        for (var i = 0; i < this.length; i++) {
            if (this.charAt(i) > '~') {
                strLength += 2;
            } else {
                strLength += 1;
            }
            if (strLength >= cutLength) {
                cutStr = this.substring(0, i + 1);
                break;
            }
        }
    }
    return cutStr;
};

///关于链接的操作命名空间,把一个字符串变成链接
var Link = {};
Link.Filter = function (str) {
    var urlReg = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=])?[^ <>\[\]*(){}\u4E00-\u9FA5]+/gi;   //lio 2012-4-25 eidt   //         /^[\u4e00-\u9fa5\w]+$/;\u4E00-\u9FA5
    return str.replace(urlReg, function (m) {
        return '<a target="_blank" href="' + m + '">' + m + '</a>';
    });
};

//验证一个字符串时候是email

RegExp.isEmail = function (str) {

    var emailReg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)*\.[\w-]+$/i;

    return emailReg.test(str);

};

//验证一个字符串是否是URL

RegExp.isUrl = function (str) {

    var patrn = /^http(s)?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\:+!]*([^<>])*$/;

    return patrn.exec(str);

};

//验证一个字符串是否是电话或传真

RegExp.isTel = function (str) {

    var pattern = /^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/;

    return pattern.exec(str);

};

//验证一个字符串是否是手机号码

RegExp.isMobile = function (str) {

    var patrn = /^((13[0-9])|(15[0-35-9])|(18[0,2,3,5-9]))\d{8}$/;

    return patrn.exec(str);

};

//验证一个字符串是否是汉字

RegExp.isZHCN = function (str) {

    var p = /^[\u4e00-\u9fa5\w]+$/;

    return p.exec(str);

};

//验证一个字符串是否是数字

RegExp.isNum = function (str) {

    var p = /^\d+$/;

    return p.exec(str);

};

//验证一个字符串是否是纯英文

RegExp.isEnglish = function (str) {

    var p = /^[a-zA-Z., ]+$/;

    return p.exec(str);

};
//easyui 分页公共脚本
function formatpagenation(this_id) {
    var p = $('#' + this_id).datagrid('getPager');
    $(p).pagination({
        pageSize: 10, //每页显示的记录条数，默认为10 
        pageList: [10, 15, 20], //可以设置每页记录条数的列表 
        beforePageText: '第', //页数文本框前显示的汉字 
        afterPageText: '页    共 {pages} 页',
        displayMsg: '当前显示 {from} - {to} 条记录   共 {total} 条记录',
        /*onBeforeRefresh:function(){
         $(this).pagination('loading');
         alert('before refresh');
         $(this).pagination('loaded');
         }*/
    });
}

// 判断是否为对象类型

RegExp.isObject = function (obj) {

    return (typeof obj == 'object') && obj.constructor == Object;

}
//验证字符串是否不包含特殊字符 返回bool
RegExp.isUnSymbols = function (str) {

    var p = /^[\u4e00-\u9fa5\w \.,()，ê?。¡ê（ê¡§）ê?]+$/;

    return p.exec(str);

}

//将一个字符串用给定的字符变成数组，

String.prototype.toArray = function (str) {

    if (this.indexOf(str) != -1) {

        return this.split(str);

    }

    else {

        if (this != '') {

            return [this.toString()];

        }

        else {

            return [];

        }

    }

};

//根据数据取得数组中的索引
Array.prototype.getIndex = function (obj) {
    for (var i = 0; i < this.length; i++) {
        if (obj == this[i] || obj.equals(this[i])) {
            return i;
        }
    }
    return -1;
};

//移除数组中的某元素

Array.prototype.remove = function (obj) {

    for (var i = 0; i < this.length; i++) {

        if (obj.equals(this[i])) {

            this.splice(i, 1);

            break;

        }

    }

    return this;

};
//判断元素是否在数组中
Array.prototype.contains = function (obj) {
    for (var i = 0; i < this.length; i++) {
        if (obj == this[i] || obj.equals(this[i])) {
            return true;
        }
    }
    return false;
};
//js时间相减函数
Date.prototype.diff = function (date) {
    var diff_type = arguments[1] ? arguments[1] : 'minutes';
    var res = '';
    switch (diff_type) {
        case 'seconds':
            res = (this.getTime() - date.getTime()) / 1000;
            break;
        case 'minutes':
            res = (this.getTime() - date.getTime()) / (60 * 1000);
            break;
        case 'hours':
            res = (this.getTime() - date.getTime()) / (60 * 60 * 1000);
            break;
        case 'days':
            res = (this.getTime() - date.getTime()) / (24 * 60 * 60 * 1000);
            break;
        default:
            res = (this.getTime() - date.getTime()) / (60 * 1000);
            break;
    }
    return res;
};

//时间格式化函数
Date.prototype.format = function (format) {
    var o = {
        "M+": this.getMonth() + 1, //month 
        "d+": this.getDate(), //day 
        "h+": this.getHours(), //hour 
        "m+": this.getMinutes(), //minute 
        "s+": this.getSeconds(), //second 
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter 
        "S": this.getMilliseconds() //millisecond 
    }

    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
}

function myformatter(date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
}

function myparser(s) {
    if (!s)
        return new Date();
    var ss = (s.split('-'));
    var y = parseInt(ss[0], 10);
    var m = parseInt(ss[1], 10);
    var d = parseInt(ss[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
        return new Date(y, m - 1, d);
    } else {
        return new Date();
    }
}
//点击的时候格式化时间
function mytimeformatter(date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    var h = date.getHours();
    var mm = date.getMinutes();
    var s = date.getSeconds();
    return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d) + ' ' + (h < 10 ? ('0' + h) : h) + ':' + (mm < 10 ? ('0' + mm) : mm) + ':' + (s < 10 ? ('0' + s) : s);
}
//时间格式化
function mytimeparser(s) {
    if (!s || s === '0000-00-00 00:00:00')
        return new Date();
    var all_s = s.split(' ');
    var ss = (all_s[0].split('-'));
    var y = parseInt(ss[0], 10);
    var m = parseInt(ss[1], 10);
    var d = parseInt(ss[2], 10);
    var hh = (all_s[1].split(':'));
    var h = parseInt(hh[0], 10);
    var mm = parseInt(hh[1], 10);
    var ss = parseInt(hh[2], 10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d) && !isNaN(h) && !isNaN(mm) && !isNaN(ss)) {
        return new Date(y, m - 1, d, h, mm, ss);
    } else {
        return new Date();
    }
}
