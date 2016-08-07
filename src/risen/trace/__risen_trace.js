/**
 * Created by riki on 15/5/27.
 */

/**
 * 在javascript控制台输出Trace信息的函数
 */
function __risen_trace(data)
{
    if (window.console == undefined ||
        console.info ==undefined ||
        console.log == undefined ||
        console.group == undefined ||
        console.groupEnd == undefined ||
        console.dir == undefined ||
        console.table == undefined ||
        console.groupCollapsed == undefined ||
        console.warn == undefined) {
        return;
    }

    console.info("Risen PHP Framework Trace:");
    console.log("REQUEST: " + data['statics'].method + ": " + data['statics'].uri);
    console.log("[Page cost:" + data['statics'].time
        + " Sec, Requests per second: "
        + Math.round(1 / data['statics'].time)
        + ", Memory usage: " + data['statics'].mem / 1024 + "KB]");

    if (typeof(data['info']) != 'undefined') {
        console.group("User Trace");
        for (i in data['info'])
        {
            console.log(data['info'][i]);
        }
        console.groupEnd();
    }

    if (typeof(data['error']) != 'undefined') {

        console.group("Error Info(" + data['error'].length + ")");

        for (i in data['error']) {
            console.warn(data['error'][i].errstr);
            if (typeof(data['error'][i].backtrace) != 'undefined') {
                console.groupCollapsed("Backtrace");
                for(j in data['error'][i].backtrace)
                {
                    console.log(data['error'][i].backtrace[j].file + "(" + data['error'][i].backtrace[j].line + ")");
                }
                console.groupEnd();
            }
        }
        console.groupEnd();
    }

    if (typeof(data['sql']) != "undefined") {
        console.group("Sql Info(" + data['sql'].length + ")");
        for (j in data['sql']) {
            console.group("SQL[" + j + "]\tQuery Cost: " + data['sql'][j].time + " Sec");
            console.debug(data['sql'][j].sql);
            console.groupCollapsed("Execute information");
            if (data['sql'][j].explain != null) {
                console.log("Explain:");
                console.table(data['sql'][j].explain);
            }

            console.log("Backtrace:");

            var bt = data['sql'][j].backtrace;
            for (i in bt) {
                console.log(bt[i].file + " on line " + bt[i].line + "\n");
            }
            console.groupEnd();
            console.groupEnd();
        }
        console.groupEnd();
    }

    if (typeof(data['globals']) != "undefined")
    {
        console.groupCollapsed("Global variables");
        for (i in data['globals'])
        {
            console.groupCollapsed(i);
            console.dir(data['globals'][i]);
            console.groupEnd();
        }
        console.groupEnd();
    }
}

// 设置支持 jQuery or Zepto Ajax
(function($){

    if ($ == undefined) {
        return;
    }

    var userAjaxComplete = (
        $.ajaxSettings.complete != undefined &&
        $.ajaxSettings.complete instanceof Function
    ) ? $.ajaxSettings.complete: new Function;

    $.ajaxSettings.complete = function (xhr, ts) {
        var data = eval("(" + xhr.getResponseHeader('__risen_trace_json') + ")");
        __risen_trace(data);
        userAjaxComplete(xhr, ts);
    };
})(window.jQuery || window.Zepto);
