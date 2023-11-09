function initEmailSettingsForm(frm, p=null) {
    frm.reset();
    if (p == null) {
        frm.r.value = "create";
        frm.id.value = "0";
    } else {
        frm.r.value =  "edit";
        frm.id.value = p.id;

    }
}

var serviceProviders={"gmail" :
        {
            "imap" : {
                "ssl" : {
                    "server": "imap.gmail.com",
                    "port" : 993
                },
                "tls" : {
                    "server": "imap.gmail.com",
                    "port" : 993
                },
            },
            "pop3" : {
                "ssl" : {
                    "server": "pop.gmail.com",
                    "port": 995
                },
                "tls" : {
                    "server": "pop.gmail.com",
                    "port": 995
                }
            },
            "smtp" : {
                "ssl" : {
                    "server": "smtp.gmail.com",
                    "port" : 465
                },
                "tls" : {
                    "server": "smtp.gmail.com",
                    "port" : 587
                }
            }
        },
    "yahoo" :
        {
            "imap" : {
                "ssl" : {
                    "server": "imap.mail.yahoo.com",
                    "port" : 993
                },
                "tls" : {
                    "server": "imap.mail.yahoo.com",
                    "port" : 993
                },
            },
            "pop3" : {
                "ssl" : {
                    "server": "pop.mail.yahoo.com",
                    "port": 995
                },
                "tls" : {
                    "server": "pop.mail.yahoo.com",
                    "port": 995
                }
            },
            "smtp" : {
                "ssl" : {
                    "server": "smtp.mail.yahoo.com",
                    "port" : 465
                },
                "tls" : {
                    "server": "smtp.mail.yahoo.com",
                    "port" : 587
                }
            }
        },
    "live" :
        {
            "imap" : {
                "ssl" : {
                    "server": "imap-mail.outlook.com",
                    "port" : 993
                },
                "tls" : {
                    "server": "imap-mail.outlook.com",
                    "port" : 993
                },
            },
            "pop3" : {
                "ssl" : {
                    "server": "pop-mail.outlook.com",
                    "port": 995
                },
                "tls" : {
                    "server": "pop-mail.outlook.com",
                    "port": 995
                }
            },
            "smtp" : {
                "ssl" : {
                    "server": "smtp-mail.outlook.com",
                    "port" : 465
                },
                "tls" : {
                    "server": "smtp-mail.outlook.com",
                    "port" : 587
                }
            }
        },
    "manual" :
        {
            "imap" : {
                "ssl" : {
                    "server": "",
                    "port" : 993
                },
                "tls" : {
                    "server": "",
                    "port" : 993
                },
            },
            "pop3" : {
                "ssl" : {
                    "server": "",
                    "port": 995
                },
                "tls" : {
                    "server": "",
                    "port": 995
                }
            },
            "smtp" : {
                "ssl" : {
                    "server": "",
                    "port" : 465
                },
                "tls" : {
                    "server": "",
                    "port" : 587
                }
            }
        },

    }

var frm = document.getElementById("emailSettingsForm");
function setServiceProvider(e) {
    var sp = $('#serviceProviderPicker').val();
    setIncoming();
    setOutgoing();
}

function setIncoming() {
    if ($("#incomingDeliveryPicker").length == 0) return;
    var sp = $('#serviceProviderPicker').val();
    var incomingDelivery = $("#incomingDeliveryPicker").val();
    var incomingSecurity = $("#incomingSecurityPicker").val();
    var _sp = serviceProviders[sp][incomingDelivery ?? 'imap'][incomingSecurity ?? 'ssl'];
    frm.incomingServer.value = _sp.server;
    frm.incomingPort.value = _sp.port;
}

function setOutgoing() {
    var sp = $('#serviceProviderPicker').val();
    var outgoingDelivery = $("#outgoingDeliveryPicker").val();
    var outgoingSecurity = $("#outgoingSecurityPicker").val();
    var _osp = serviceProviders[sp][outgoingDelivery][outgoingSecurity];
    frm.outgoingServer.value = _osp.server;
    frm.outgoingPort.value = _osp.port;
}

$('#serviceProviderPicker').on('select2:select', setServiceProvider);
$('#incomingDeliveryPicker, #incomingSecurityPicker').on('select2:select', setIncoming);
$('#outgoingDeliveryPicker, #outgoingSecurityPicker').on('select2:select', setOutgoing);

$('#emailSettingsForm').validate({
    rules: {
        serviceProviderPicker: {
            required: true,
        },
        outgoingServer: {
            required: true,
        },
        outgoingDeliveryPicker: {
            required: true,
        },
        outgoingSecurityPicker: {
            required: true,
        },
        outgoingPort: {
            required: true,
        },
        username: {
            required: true,
        },
        password: {
            required: true,
        },
    },
    messages: {


    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    },
    submitHandler: function (form) {

        AdminAuth((auth) => {
            form.flags.value = 0;
            form.auth.value = auth;

            var fData = $("#emailSettingsForm").serializeArray();

            $.post(emailSettingsApiUrl, fData, (response)=>{
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#emailSettingsForm').modal('hide');
                        MessageBox(m["message"], false);
                    } else {
                        MessageBox(m["message"], true);
                    }
                }catch(e) {
                    showStatusDialog(e.stack, response);
                }
            });
        });
        return false;
    }
});
