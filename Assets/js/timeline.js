var mTimelineColorsArray = {"length" : 0};
var mTimelineNumArray = [52,76,3,32,60,18,34,20,22,95,1,17,68,16,82,87,24,6,39,67,33,99,47,91,83,61,70,65,98,23,19,81,90,11,36,44,88,54,50,53,86,35,89,57,37,27,28,69,66,93,12,59,26,97,79,5,2,29,41,96,31,75,48,0,85,49,92,9,46,45,56,55,13,38,77,51,25,15,73,10,64,43,14,40,58,94,4,21,7,62,72,42,71,74,84,63,30,8,80,78];

(function($) {

    $.MtTimelinePlugin=function(element, options) {

        this.startPage = 0;
        this.itemsPerPage = 25;
        this.totalItems = 0;
        this.ele = $(element);
        this.options = options;
        this.filter = "";
        this.toolbar = null;
        this.currentTemplate = null;

        this.startTime = 800;
        this.endTime = 1700;
        this.twelveHour =  false;
        this.resourceNames = [];
        this.eventTimes= [];
        this.setResourceNames = function(e) {
            this.resourceNames = e;
        },

        this.setEventTimes = function(e) {
            this.eventTimes = e;
            var minTime = 2359;
            var maxTime = 0;
            try {
                for (var i = 0; i < e.length; i++) {

                    if (e[i].start > e[i].end) {
                        throw new Error("Start-time should precede end-time");
                    }

                    if (e[i].start > maxTime) {
                        maxTime = e[i].start;
                    } else if (e[i].start < minTime) {
                        minTime = e[i].start;
                    }

                    if (e[i].end > maxTime) {
                        maxTime = e[i].start;
                    }
                }
            }catch(e) {
                return false;
            }
        }

        this.init = function () {
            var tlday = this.ele.attr("data-timeline-day");
            var tlbar = this.ele.attr("data-timeline-toolbar");

            var tl = $(this.ele);
            tl = tl.html("<div class='d-none col-12 d-md-flex timeline-toolbar'></div>").find("div");


            tl.append("<div class='ml-2 d-inline timeline-refresh-btn'><a href='#'><i class='fas fa-sync'></i>&nbsp;&nbsp;&nbsp;Refresh</a></div>");

            this.ele.append("<div class='mt-timeline row col-12'>" +
                "<div class='resources col-'></div>" +
                "<div class='events-list col-'></div>" +
                "</div>");


            var timeline = this.ele.find(".timeline-header");
            var eventsList = this.ele.find(".mt-timeline .events-list");

            eventsList.html("");

            var el = eventsList.find(".timeline-event");

            tl.find('.timeline-refresh-btn').on("click", ()=>{
                this.fetchData(this.ele);
            });

            this.ele.addClass("timeline-init");

            if (false && typeof $.fn.overlayScrollbars !== 'undefined') {
                $(this.ele).overlayScrollbars({
                    className: "os-theme-dark",
                    sizeAutoCapable: true,
                    scrollbars: {
                        autoHide: "true",
                        clickScrolling: true
                    }
                });

                $(eventsList).overlayScrollbars({
                    className: "os-theme-dark",
                    sizeAutoCapable: true,
                    scrollbars: {
                        autoHide: "true",
                        clickScrolling: true
                    }
                });
            }

            this.fetchData(this.ele);

        }

        this.formatTime = function(t) {
            var t = ("000" + t.toString()).substr(-4);
            return t.substr(0,2) + ":" + t.substr(2);
        }

        this.timeToMinutes = function(t) {
            var a = (Math.floor(t / 100) * 60) + Math.floor((t % 100) % 60);
            console.log("conv :",t, "res",a );
            return a;
        }

        this.renderEvent = function(ev) {
            console.log(ev.start, ev.end)

            var evStartTime = this.timeToMinutes(ev.start);
            var evEndTime = this.timeToMinutes(ev.end);

            var s = (evStartTime >= this.startTimeMins) ? evStartTime - this.startTimeMins : 0;
            var e = (evEndTime >= this.startTimeMins) ? evEndTime - this.startTimeMins : 0;

            if (e<= 0) return;
            console.log(s,e);
            startPerc = (s * 100) / this.relativeTime;
            endPerc = (e*100) / this.relativeTime;

            console.log("startTime"+this.startTime, "endTime"+this.endTime, "evStartTime"+evStartTime,"ss", ev.start, "evEndTime"+evEndTime, "relativeTime"+this.relativeTime, "startPerc" + startPerc + "endPerc" + endPerc  );

            // $(".timeline-event .timeline-r"+resource).append("<div class='event-item' data-timeline-event-time='1000' style='left:calc(" + (evStartTime/60 * 7)+"em); width:"+((evEndTime - evStartTime) * 7 / 60)+"em'> &nbsp;aaa</div>");
            var width = (endPerc - startPerc);
            if (width <= 0) return;
            var t = this.formatTime(ev.start);
            var et = this.formatTime(ev.end);
            var item = document.createElement("div");
            item.className = "event-item";
            if (typeof ev.iid != "undefined") {
                if (typeof mTimelineColorsArray[ev.iid] == "undefined") {
                    console.log("TimelineColorsArray", mTimelineColorsArray.length);

                    var color = mTimelineColorsArray.length % 3;
                    var intensity = mTimelineNumArray[mTimelineColorsArray.length];  //100 - ((mTimelineColorsArray.length) % 100);

                    intensity = (0x020202 * (intensity));
                    color = intensity | (0xaa << (color * 8));

                    mTimelineColorsArray[ev.iid] = color;
                    mTimelineColorsArray.length++;
                }
                var c = "#" + mTimelineColorsArray[ev.iid].toString(16);
                item.style.backgroundColor = c;

            }



            item.style.minWidth = item.style.maxWidth =item.style.width = width+"%";
            item.style.left = startPerc + "%";
            item.innerText = ev.name+", [" + t + " - " + et + "]";
            $(".timeline-event #timeline-r"+ev.resource).append(item);
            $(item).popover({
                title: ev.name,
                content: ev.name,
                trigger: 'hover',
                placement: 'top',
                container: 'body'
            });


            var eele = this;

            if (typeof options.onDblClick == "function") {
                $(item).on("dblclick", function (el) {
                    // alert("DblClick" + el + "..." + JSON.stringify(ev));
                    options.onDblClick(eele, ev, null);

                });
            }

            if (typeof options.onClick == "function") {
                $(item).on("click", function (el) {
                    // alert("Click" + el + "..." + JSON.stringify(ev));
                });
            }

            // $(item).on("mouseover", function(){
            //    // alert("Hover"+$(item).text());
            // });

        }

        this.prepareTemplate = function (template) {
            return template;
        }

        this.processData=function(m) {
            this.totalItems = parseInt(m["total"]);
            this.ele.data.mdCurItems = m.rows;
            this.ele.data.options = options;
            var renderer = null;
            var curTemplate = null;

            var resourcesList = m.rows['resources'];

            var resources = this.ele.find(".mt-timeline .resources");
            resources.html("<div class='timeline-resource'><span>Resources</span></div>")

            var eventsList = this.ele.find(".mt-timeline .events-list");
            eventsList.html("");
            eventsList.append("<div class='timeline-header'></div>");

            if (typeof resourcesList != "undefined" ) {

                var startTime = 800;
                var endTime = 1700;

                for (var j=0; j < resourcesList.length;j++) {
                    resources.append("<div class='timeline-resource'><span>"+resourcesList[j].name+"</span></div>");
                    eventsList.append("<div class='timeline-event'><div class='item' id='timeline-r" +  resourcesList[j].id + "' data-timeline-resource='" + resourcesList[j].id + "'></div></div>");
                }

                var itemsList = m.rows['items'];

                if (typeof itemsList != "undefined" && itemsList.length > 0) {
                    startTime = 2359;
                    endTime = 0;
                    for(var j=0;j<itemsList.length;j++) {

                        if (typeof itemsList[j].start == "string") itemsList[j].start = this.convertStringTime(itemsList[j].start);
                        if (typeof itemsList[j].end == "string") itemsList[j].end = this.convertStringTime(itemsList[j].end);

                        if (itemsList[j].start < startTime) startTime = itemsList[j];
                        if (itemsList[j].end > endTime) endTime = itemsList[j];
                    }

                    if (startTime < endTime) {
                        this.startTime = startTime;
                        this.endTime = endTime;
                    }
                }

                this.startTimeMins = this.timeToMinutes(this.startTime);
                this.endTimeMins = this.timeToMinutes(this.endTime);
                this.relativeTime = this.endTimeMins-this.startTimeMins;

                if (typeof itemsList != "undefined" && itemsList.length > 0) {
                    for (var j = 0; j < itemsList.length; j++) {
                        this.renderEvent({id: itemsList[j].id, resource: itemsList[j].resource, name: itemsList[j].name, start: itemsList[j].start, end: itemsList[j].end, iid: itemsList[j].iid});
                    }
                }

                var hours = Math.floor((this.relativeTime + 59) / 60)
                $(".timeline-event").width( hours * 150 );
                var rt = this.relativeTime;
                var stt = this.startTime;
                var eele = this;

                if (typeof options.onDblClick == "function") {
                    console.log(options.onDblClick);
                    $(eventsList).find(".timeline-event > .item").dblclick(function (e, x, y) {
                        console.log("eeDblClick!");
                        var target = $(e.target);
                        if (target.hasClass("item")) {
                            var wx = window.event.offsetX;
                            wt = target.width();
                            var timePerc = wx * 100 / wt;
                            var time = (wx * rt / wt);
                            time = stt + (Math.floor(time / 60) * 100) + (Math.floor((time % 60) / 15) * 15);
                            options.onDblClick(eele, e.target.id.substr(10), time);

                        }
                    });
                }

                if (typeof options.onClick == "function") {
                    $(eventsList).find(".timeline-event > .item").on('click', function(e){
                        console.log("eeClick!");
                        var target = $(e.target);
                        if (target.hasClass("item")) {
                            var wx = window.event.offsetX;
                            wt = target.width();
                            var timePerc = wx * 100 / wt;
                            var time = (wx * rt / wt);
                            time = stt + (Math.floor(time / 60) * 100) + (Math.floor((time % 60) / 15) * 15);
                            options.onClick(eele, e.target.id.substr(10), time);
                        }
                    });
                }

                var c=0;
                var header = eventsList.find(".timeline-header");
                for (var j=this.startTime; j < this.endTime; j+=100) {
                    var t = this.formatTime(j);
                    header.append("<div class='timeline-time timeline-time"+t+"'>"+t+"</div>");
                }
            }
        };

        this.convertStringTime = function(s) {
            var i = s.indexOf(":");

            if (i == -1) return parseInt(s);
            return parseInt(s.substr(0,i) + s.substr(i+1));
        }

        this.fetchData = function (ele) {
            var source = ele.attr("data-timeline-source");
            $.post(source, {
                search: this.filter,
                offset: (this.startPage * this.itemsPerPage),
                limit: this.itemsPerPage
            }, (response) => {
                try {
                    var m = JSON.parse(response);
                    this.processData(m);
                } catch (e) {
                    alert(e + " >>! " + response);
                }
            });
        }

        $.MtTimelinePlugin.prototype.update = function(options) {
            $.extend(true, this, options);
        };

        $.MtTimelinePlugin.prototype.refresh = function() {
            alert("refresh");
        }

        this.init(this);
    }

    $.fn.timeline = function(options) {
        var defaults = {};
        var settings={};
        if (typeof options == "object")
            settings = $.extend({}, defaults, options);
        else
            settings = $(this).data.options;
        return this.each(function(){
            if (undefined == $(this).data('Timeline')) {
                $(this).data('Timeline', new $.MtTimelinePlugin(this, settings));
            } else {
                if (typeof options == "string") {
                    var data = $(this).data;
                    if (options == "refresh")
                        $(this).data('Timeline', new $.MtTimelinePlugin(this, data.options));
                } else {

                }
            }
        });
    }
})(jQuery);
