/**
 * Created by riki on 15/5/27.
 */



function setCss(obj, css) {
    for(key in css)
    {
        obj.style[key] = css[key];
    }
}

TraceWindow = function(){
    this.createFrame();
    this.setClient();
};

TraceWindow.prototype =
{
    frame: null,
    titleBar: null,
    title: null,
    body: null,
    list: null,
    detail: null,
    height:240,


    createFrame: function(){
        var _this = this;
        if (document.body == null) {
            document.write("<span></span>");
        }

        this.frame = document.createElement('div');
        document.body.appendChild(this.frame);
        //document.body.style.height = (document.body.offsetHeight + this.height) + "px";

        setCss(this.frame, {
            width: "100%",
            height: this.height + "px",
            fontSize: "10pt",
            backgroundColor: "#fffffa",
            fontFamily: "Georgia",
            borderTop: "1px solid #dddddd",
            position: "fixed",
            left: "0px",
            bottom: "0px"
        });

        var titleHeight = "20px";
        this.titleBar = document.createElement('div');
        this.frame.appendChild(this.titleBar);
        setCss(this.titleBar, {
            width: "100%",
            height: titleHeight,
            frontWeight: "bold",
            lineHeight: titleHeight,
            borderBottom: "2px solid #999999",
            backgroundColor: "#d0d0d0"
        });

        this.title = document.createElement('div');
        this.titleBar.appendChild(this.title);
        setCss(this.title, {
            float: "left",
            width: "70%",
            height: "100%",
            paddingLeft: "5px"
        });

        var closeButton = document.createElement('div');
        closeButton.innerHTML = "Close";
        closeButton.onclick = function(){
            document.body.removeChild(_this.frame);
            //document.body.style.height = (document.body.offsetHeight - _this.height) + "px";
        };
        this.titleBar.appendChild(closeButton);
        setCss(closeButton, {
            cursor: "pointer",
            height: "100%",
            width: "40px",
            float: "right",
            color: "red"
        });

        this.body = document.createElement('div');
        this.frame.appendChild(this.body);
        setCss(this.body, {
            width: "100%",
            height: this.height - 20 + "px"
        });

        this.list = document.createElement('div');
        this.body.appendChild(this.list);
        setCss(this.list, {
            height: "100%",
            paddingLeft: "5px",
            overflowY: "auto",
            width: "50%",
            borderRight: "1px solid #888888",
            float: "left"
        });


        this.detail = document.createElement('div');
        this.body.appendChild(this.detail);
        setCss(this.detail, {
            width: document.body.clientWidth - this.list.offsetWidth - 10 + "px",
            paddingLeft: "5px",
            height: "100%",
            overflowY: "scroll",
            float: "left"
        });


        // Resizable
        var __traceWindowTimer = 0;
        var __traceBeginY = 0;
        var __traceFrameHeight = this.height;
        var __resizable = false;
        var __move = 0;


        this.titleBar.onmousedown = function(e) {
            e.preventDefault();
            __traceBeginY = e.clientY;
            __resizable = true;
            document.body.style.cursor = "n-resize";
            __traceFrameHeight = _this.frame.offsetHeight;

        };

        document.onmousemove = function(e) {
            if (__resizable) {
                clearTimeout(__traceWindowTimer);
                __traceWindowTimer = setTimeout(function(){
                    __move = __traceBeginY - e.clientY;
                    _this.frame.style.height = __traceFrameHeight + __traceBeginY - e.clientY - 1;
                    _this.body.style.height = (_this.frame.clientHeight - 20) + "px";
                }, 5);
                //console.log(e.clientY);
            }
        }

        document.onmouseup = function (e) {
            //xx = e;
            document.body.style.cursor = "";
            clearInterval(__traceWindowTimer);
            //console.log("clear");
            if (__resizable) {

                _this.frame.style.height = __traceFrameHeight + __move - 1;
            }
            _this.body.style.height = (_this.frame.clientHeight - 20) + "px";
            __resizable = false;
            __move = 0;
        }
    },

    setClient: function() {
        var _this = this;
        this.detail.innerHTML = "";
        var data = __risen_trace_option.data;

        this.list.innerHTML = "";
        error_rows = new Array();
        sql_rows = new Array();
        item_title = document.createElement('div');
        setCss(item_title, {
            height: "20px",
            borderBottom: "1px solid #888888",
            padding: "0",
            fontWeight: "bold",
            marginTop: "5px"
        });


        error_title = item_title;
        sql_title = item_title.cloneNode();


        if (typeof(data['statics']) != "undefined")
        {
            this.title.innerHTML = "";
            this.title.innerHTML += "Risen PHP Framework Trace: " + "[Page cost: " + data['statics'].time
            + " Sec | Requests per second: " + Math.round(1 / data['statics'].time)
            + " | Memory usage: " + (data['statics'].mem / 1024 )+ "KB]";
        }

        list_row = document.createElement('div');
        setCss(list_row, {
            paddingLeft: "10px",
            width: this.list.clientWidth - 20 + "px",
            paddingTop: "3px",
            borderBottom: "1px solid #cccccc"
        });


        // PHP 全局变量
        var globals_row = list_row.cloneNode();
        this.list.appendChild(globals_row);
        if  (typeof(data['globals']) != "undefined")
        {
            var globals_cell = new Array();
            for (i in data['globals'])
            {
                globals_cell[i] = document.createElement('a');
                globals_cell[i].innerHTML = i;
                globals_cell[i].href = "javascript:void(0);";
                globals_row.appendChild(globals_cell[i]);
                globals_cell[i].info = data['globals'][i];
                globals_cell[i].onclick = function() {
                    _this.detail.innerHTML = "";
                    for(j in this.info)
                    {
                        _this.detail.innerHTML += j + "=" + this.info[j] + "<br/>";
                    }
                }
                setCss(globals_cell[i], {
                    display: "inline-block",
                    width: "100px"
                })
            }
        }

		// php dump
		if (typeof(data['info']) != 'undefined') {
			info_rows = new Array();
			this.detail.innerHTML="User Trace:<hr/>";

			for(i in data['info'])
			{
				info_rows[i] = list_row.cloneNode();
				info_rows[i].innerHTML = data['info'][i].replace(/\n/g, '<br/>');
				this.detail.appendChild(info_rows[i]);
			}
		}

        // 错误信息
        if (typeof(data['error']) != "undefined")
        {
            this.list.appendChild(error_title);
            error_title.innerHTML = "Error Info("+ data['error'].length +"):";
            var error_rows = new Array();
            for (i in data['error'])
            {
                error_rows[i] = list_row.cloneNode();
                error_rows[i].style.color="red";
                error_rows[i].innerHTML = data['error'][i].errstr;
                if (typeof(data['error'][i].backtrace) != 'undefined') {
                    error_rows[i].style.cursor = "pointer";
                    error_rows[i].backtrace = data['error'][i].backtrace;
                    var _this = this;
                    error_rows[i].onclick = function() {
                        _this.detail.innerHTML = "";
                        _this.showBacktrace(this.backtrace);
                    }
                }
                this.list.appendChild(error_rows[i]);
            }

        }

        // SQL 信息
        if (typeof(data['sql']) != 'undefined')
        {
            this.list.appendChild(sql_title);
            sql_title.innerHTML = "SQL Info:("+ data['sql'].length +")";
            var sql_rows = new Array();
            for (i in data['sql'])
            {
                sql_rows[i] = list_row.cloneNode();
                if (data['sql'][i].time > 0.1) {
                    sql_rows[j].style.color="red";
                }
                sql_rows[i].explain = data['sql'][i].explain;
                sql_rows[i].backtrace = data['sql'][i].backtrace;
                sql_rows[i].style.cursor = "pointer";
                //var str = data['sql'][i].sql + ";\nquery cost: "+data['sql'][i].time+" Sec";
                sql_rows[i].innerHTML = (data['sql'][i].sql + ";\nquery cost: "+data['sql'][i].time+" Sec").replace(/\n/g, "<br/>");
                this.list.appendChild(sql_rows[i]);


                // SQL Detail
                sql_rows[i].onclick = function() {

                    _this.detail.innerHTML = "";
                    //show_explain(info, this.index);
                    //show_detail(info, this.index);

                    var explain = this.explain;
                    if (explain != null) {
                        var explhtml = "SQL Explain: <table border=\"1\" cellspacing='0'>";
                        var thead = "<thead><tr/>";
                        for(i in explain[0])
                        {
                            thead += "<th>" + i + "</th>";
                        }
                        for (i in explain)
                        {
                            explhtml += "<tr>";

                            for (j in explain[i])
                            {
                                explhtml += "<td>"
                                explhtml += explain[i][j];
                                explhtml += "</td>";
                            }
                            explhtml += "</tr>";
                        }
                        explhtml += thead + "</tr>";
                        explhtml += "</table> ";
                    }
                    _this.detail.innerHTML = explhtml;

                    _this.detail.innerHTML += "BackTrace:<br/>";
                    for(i in this.backtrace)
                    {
                        if (typeof(this.backtrace[i].file) == 'undefined') {
                            continue;
                        }
                        if (this.backtrace[i].file.indexOf("risen") == 0) {
                            continue;
                        }
                        _this.detail.innerHTML +=  this.backtrace[i].file + "(" + this.backtrace[i].line + ")<br/>";
                    }
                };
            }
        }
    },

    showBacktrace: function (backtrace)
    {
        if (backtrace == null) {
            return;
        }
        this.detail.innerHTML += "BackTrace:<br/>";
        for(i in backtrace)
        {
            if (typeof(backtrace[i].file) == 'undefined') {
                continue;
            }
            if (backtrace[i].file.indexOf("risen") == 0) {
                continue;
            }
            this.detail.innerHTML +=  backtrace[i].file + "(" + backtrace[i].line + ")<br/>";
        }
    }
}


function traceConsole()
{
    if (typeof(console) == 'undefined') {
        return;
    }
    var data = __risen_trace_option.data;

    console.info("Risen PHP Framework Trace:");
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
                console.log("xxxx");
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

(function(){

    var SHOW_CONSOLE = 1;
    var SHOW_WINDOW = 2;

    if (typeof(jQuery) != 'undefined') {
        var userAjaxCompleteAlias = (typeof(jQuery.ajaxSettings.complete) != "undefined") ?
            jQuery.ajaxSettings.complete: null;

        jQuery.ajaxSetup({
            complete: function(xhr, ts){
                __risen_trace_option.data = eval("(" + xhr.getResponseHeader('__risen_trace_json') + ")");
                if (__risen_trace_option.showMask & SHOW_CONSOLE) {
                    traceConsole();
                }
                if (__risen_trace_option.showMask & SHOW_WINDOW) {
                    __risen_trace_option.handle.setClient();
                }
                if (userAjaxCompleteAlias != null) {
                    userAjaxCompleteAlias(xhr, ts);
                }
            }
        });
    }

    if (__risen_trace_option.showMask & SHOW_CONSOLE && navigator.userAgent.indexOf('MSIE') == -1) {
        traceConsole();
    }
    if (__risen_trace_option.showMask & SHOW_WINDOW) {
        __risen_trace_option.handle = new TraceWindow();
    }

})();
