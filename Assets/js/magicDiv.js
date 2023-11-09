(function($) {

    $.MtMagicDivPlugin=function(element, options) {

        this.startPage = 0;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.ele = $(element);
        this.filter = "";
        this.dataName = null;
        this.toolbar = null;
        this.currentTemplate = null;
        this.init = function () {
            // alert(this.ele.className());
            var tlbar = this.ele.attr("data-magicDiv-toolbar");
            this.dataName = this.ele.attr("data-magicDiv-data-name") ?? "rows";
            this.filter = this.ele.attr("data-magicDiv-search-filter");

            try {
                this.itemsPerPage = parseInt(this.ele.attr("data-magicDiv-items-per-page") ?? 10);
            } catch(e) {
                this.itemsPerPage = 10;
            }
//            alert(dataName);

            if (typeof tlbar != "undefined") {
                var tl = $(tlbar);
                var mdt = tl.find(".magicDiv-toolbar");

                if (mdt.length == 0)
                    tl = tl.append("<div class='d-none d-md-flex align-items-center  magicDiv-toolbar col-'></div>").find("div.magicDiv-toolbar");
                else {
                    tl = mdt;
                }

                var searchControl = '<div class="magicDiv-search morphsearch-search">\n' +
                    '                            <div class="input-group d-flex align-items-center ">\n' +
                    '<span class="input-group-prepend magicDiv-search-close-btn">\n' +
                    '<i class="feather icon-x input-group-text"></i>\n' +
                    '</span>\n' +
                    '                                <input type="text" class="search-filter" placeholder="Search Query">\n' +
                    '                                <span class="input-group-append magicDiv-search-btn mr-3">\n' +
                    '<a href="#"><i class="fas fa-search"></i></a>\n' +
                    '</span>\n' +
                    '                            </div>\n' +
                    '                        </div>';
                var i = tl.html("<div class='ml-2 d-inline'>"+searchControl+"</div><div class='ml-2 d-inline'><select class='select2' id='magicDivSelPerPage'></select></div>").find("select.select2");
                i.html("<option value=''>Per Page</option><option value='10'>10</option>\n" +
                    "                    <option value='25'>25</option><option value='50'>50</option><option value='100'>100</option>\n" +
                    "                    <option value='500'>500</option><option value='1000'>1000</option><option value='all'>All</option>");
                i.select2({width:"15em", minimumResultsForSearch: -1, placeholder:'Items per page'});

                i.on("select2:select", (e)=>{
                    var itemsPerPage = parseInt($("#magicDivSelPerPage").val());
                    itemsPerPage = itemsPerPage > 0 ? itemsPerPage : this.itemsPerPage;
                    if (itemsPerPage != this.itemsPerPage) {

                        var offset = this.startPage * this.itemsPerPage;

                        this.startPage = Math.floor(offset / itemsPerPage);
                        this.itemsPerPage = itemsPerPage;
                        this.fetchData(this.ele);
                    }
                });

                i = tl.find(".magicDiv-search");
                var filter = i.find("input.search-filter");

                filter.on("keypress", (e)=>{
                    if (e.originalEvent.charCode == 13) {
                        this.filter = filter.val();
                        this.fetchData(this.ele);
                    }
                });

                i.find(".magicDiv-search-btn").on("click", ()=>{
                    if ($(".magicDiv-search").hasClass("open")) {
                        this.filter = filter.val();
                        this.startPage = 0;
                        this.fetchData(this.ele);
                    }   else {
                        $(".magicDiv-search").addClass('open');
                        $('.magicDiv-search  .search-filter').animate({'width': '200px',});
                    }
                });

                i.find(".magicDiv-search-close-btn").on('click', function () {
                    $('.magicDiv-search .search-filter').animate({'width': '0',});
                    setTimeout(function () {
                        $(".magicDiv-search").removeClass('open');
                    }, 300);
                });

                tl.append("<div class='ml-2 d-inline magicDiv-refresh-btn'><a href='#'><i class='fas fa-sync'></i>&nbsp;&nbsp;&nbsp;Refresh</a></div>");

                tl.find('.magicDiv-refresh-btn').on("click", ()=>{
                    this.fetchData(this.ele);
                });
            }

            this.ele.addClass("magicDiv-init");
            this.fetchData(this.ele);
            this.ele.dblclick(function(e,f,g) {
            });
        }

        this.prepareTemplate = function (template) {
            return template;
        }

        this.renderTemplate = function (item, id, template) {
            var formatter = this.ele.attr("data-magicDiv-formatter");
            const val = eval("if (typeof " + formatter +" == 'function') {" + formatter + "(item);}");
            if (item != null) {
                var el = $(template.supplant(item)).attr("data-magicDiv-id",id);
                var exEl = this.ele;

                var dblClickHandler = exEl.attr("data-double-click");
                if (typeof dblClickHandler != "undefined") {
                    el.dblclick(function (e) {
                        eval(dblClickHandler + "(el, item)");
                    });
                }
                return el;
            }
        };

        this.processData=function(m) {

            this.totalItems = parseInt(m["total"]);

            this.ele.data.mdCurItems = m.rows;
            this.ele.data.options = options;
            var renderer = null;
            var curTemplate = null;
            var tmp = this.ele.find(".magicDivTemplate");

            var containerType = this.ele.attr("data-magicDiv-type") ?? "div";

            if (tmp.length > 0) {
                if (this.curTemplate == null) {
                    if (containerType == "table") {
                    }
                    this.curTemplate = this.prepareTemplate(tmp.html());
                }
            } else {
                renderer = eval(this.ele.attr("data-magicDiv-renderer"));
            }


            if (typeof m.itemsKey != "undefined") {
                m.rows = m[m.itemsKey];
            } else if (this.dataName != "rows" && typeof m[this.dataName] != "undefined") {
                m.rows = m[this.dataName];
            }
            else if (typeof m.rows == "undefined") {
                for (const k in m) {
                    if (typeof m[k] == "object" && typeof m[k].length != "undefined") {
                        m.rows=m[k];
                        continue;
                    }
                }
            }

            if (this.curTemplate) {

                var el = this.ele.find(".magicDivContainer");
                if (el.length == 0) {
                    el = containerType == "table" ? this.ele.append("<table class='table magicDivContainer'></table>") : this.ele.append("<div class='row magicDivContainer'></div>");
                }
                el = (typeof this.ele.attr("data-magicDiv-parent") == "string") ?
                             $(this.ele.attr("data-magicDiv-parent")) : this.ele.find(".magicDivContainer");
                el.html("");

                if (typeof this.ele.attr("data-magicDiv-before") == "string") {
                    try {
                        let beforeHandler = eval(this.ele.attr("data-magicDiv-before"));
                        beforeHandler();
                    } catch(e) {

                    }
                }

                var before = this.ele.find(".magicDivBefore");

                if (before.length > 0)
                    el.append(before.html());

                for (var c = 0; c < m.rows.length; c++) {
                    m.rows[c].magicDivTargetElement8192809324257345 = el;
                    var element = this.renderTemplate(m.rows[c], c, this.curTemplate);
                    if (element != null) {
                        let newElement = m.rows[c].magicDivTargetElement8192809324257345;
                        var e = newElement.append(element);
                    }
                }

                if (m.rows.length == 0) {
                    let noShow = this.ele.find(".magicDivNoShow");
                    if (noShow.length > 0)
                        el.append(noShow.html());
                }

                var after = this.ele.find(".magicDivAfter");
                if (after.length > 0)
                    el.append(after.html());

                if (typeof this.ele.attr("data-magicDiv-after") == "string") {
                    try {
                        let after = eval(this.ele.attr("data-magicDiv-after"));
                        after();
                    } catch(e) {

                    }
                }

                var nItems = parseInt(m["total"]);
                var nPages = Math.floor((nItems + this.itemsPerPage - 1) / this.itemsPerPage);
                var startOffset = (this.startPage * this.itemsPerPage) + 1;
                var endOffset = startOffset + m.rows.length - 1;

                if (nPages > 0) {
                    var sPagi = '<nav><ul class="pagination pagination-sm">\n';

                    var paginationItems;
                    if (nPages > 6) {
                        paginationItems = ["First Page",2, ".."];

                        var strt = Math.max(this.startPage -1, 3)
                        for (var c=strt; c<strt+3;c++)
                            paginationItems.push(c);

                        paginationItems.push("...");
                        paginationItems.push(nPages - 1);
                        paginationItems.push("Last Page");
                    } else {
                        paginationItems = ["First Page"];
                        for (var c=0;c < nPages - 1;c++) {
                            paginationItems.push(c + 1);
                        }
                        paginationItems.push("Last Page");
                    }
                    var maxItems = paginationItems.length; //Math.min(paginationItems.length, nPages);
                    for (var c = 0; c < maxItems; c++) {
                        sPagi += '<li class="page-item"><a class="page-link" href="#">' + (paginationItems[c]) + '</a></li>\n';
                    }

                    sPagi += '</ul></nav>';

                    var sPagiE = document.createElement(containerType == "table" ? "tr" : "div");
                    sPagiE.className = 'magicDiv-pagination';
                    sPagiE.innerHTML = '<' + (containerType =="table" ? 'td colspan="1000"' : 'span') + '>' +
                        startOffset + ' to ' + endOffset + ' of ' + nItems + ' rows</span> &nbsp; &nbsp; &nbsp; &nbsp; ' + (nPages > 1 ? sPagi : "");
                    var i = el.append(sPagiE);

                    $(sPagiE).find("nav ul li").on("click", (e) => {
                        var pgTxt = e.currentTarget.innerText;
                        var newPage;
                        if (pgTxt == "First Page")
                            newPage = 1;
                        else if (pgTxt == "Last Page")
                            newPage = nPages;
                        else if (pgTxt == "&lt;")
                            newPage = Math.max(this.startPage - 10, 1);
                        else if (pgTxt == "&gt;")
                            newPage = Math.min(this.startPage + 10, nPages);
                        else {
                            newPage = parseInt(e.currentTarget.innerText);
                        }
                        if (newPage > 0) {
                            this.startPage = newPage - 1;
                            this.fetchData(this.ele);
                        }
                    });
                }
            } else if (typeof renderer == "function") {
                for (var c = 0; c < m.rows.length; c++) {
                    this.ele.append(renderer(m.rows[c]));
                }
            }
            else {
                MessageBox("Renderer and template are both missing", true);
                return;
            }

            this.ele.find(".magicDivContainer > div").addClass("magicDiv-item");
            if (typeof this.ele.attr("data-magicDiv-onDone") == "string") {
                try {
                    let onDone = eval(this.ele.attr("data-magicDiv-onDone"));
                    onDone();
                } catch(e) {

                }
            }

        };
var weekdays=["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
var months=["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

this.fetchDataSet=function(ele,dataset) {
    var m = {total:0, rows:[]};
    var endItem = dataset.indexOf(ele.attr("data-magicDiv-to"));
    var startItem = dataset.indexOf(ele.attr("data-magicDiv-from"));

    if (endItem == -1) endItem = dataset.length;
    if (startItem == -1) startItem = 0;

    var dataSetLength = dataset.length;
    var c = startItem, d = 0;
    while(d < dataset.length) {
        m.rows[d] = {"item" : dataset[c]};

        if (c == endItem) break;

        c = ((c+1) % dataSetLength);
        d++;
    }

    m.total = m.rows.length;
    this.processData(m);
}

        this.fetchData = function (ele) {
            var source = ele.attr("data-magicDiv-source");
            if (typeof source != "string" ) {
                if (typeof ele.attr("data-magicDiv-iteration") == "string" && ele.attr("data-magicDiv-iteration") == "true") {
                    var m = {total:0, rows:[]};
                    switch (ele.attr("data-magicDiv-dataset")) {
                        case "weekdays":
                            this.fetchDataSet(ele,weekdays)
                            break;
                        case "months":
                            this.fetchDataSet(ele,months)
                            break;
                        case "integer":
                            var endNumber = parseInt(ele.attr("data-magicDiv-to"));
                            var startNumber = parseInt(ele.attr("data-magicDiv-from"));

                            if (startNumber > endNumber) {
                                startNumber = endNumber;
                                endNumber = ele.attr("data-magicDiv-from");
                            }

                            for(var c=startNumber, d=0; c<endNumber; c++,d++) {
                                m.rows[d] = {id:d, number : c};
                            }
                            m.total = m.rows.length;
                            this.processData(m);
                            break;
                        default:
                            break;
                    }
                }
            } else {

                if (source != "") {
                    $.post(source, {
                        search: this.filter,
                        offset: (this.startPage * this.itemsPerPage),
                        limit: this.itemsPerPage
                    }, (response) => {
                        try {
                            var m = JSON.parse(response);

                            if (m["status"] == "OK")
                                this.processData(m);
                            else if (typeof m["message"] != "undefined")
                                MessageBox(m["message"], true);
                        } catch (e) {
                            alert(e.stack + "|||" + e + " >>! " + response);
                        }
                    });
                } else {
                    let el = this.ele.find(".magicDivContainer");
                    el.html("");
                }

            }
        }

        $.MtMagicDivPlugin.prototype.update = function(options) {
            $.extend(true, this, options);
        };

        $.MtMagicDivPlugin.prototype.refresh = function() {
            alert("refresh");
        }

        this.init(this);
    }

    $.fn.magicDiv = function(options) {
        var defaults = {};
        var settings={};
        if (typeof options == "object")
            settings = $.extend({}, defaults, options);
        else
            settings = $(this).data.options;
        return this.each(function(){
            if (undefined == $(this).data('Timeline')) {
                $(this).data('MagicDiv', new $.MtMagicDivPlugin(this, settings));
            } else {
                if (typeof options == "string") {
                    var data = $(this).data;
                    if (options == "refresh")
                        $(this).data('MagicDiv', new $.MtMagicDivPlugin(this, data.options));
                } else {

                }
            }
        });
    }
})(jQuery);
