$("#add").click(function (e) 
{
    $("#items").append('<div class="form-group">\
                            <div id="items" class="form-group">\
                                <div class="form-row">\
                                    <div class="col-md-5">\
                                        <input type="text" name="ip_port[]" class="form-control" placeholder="Addres">\
                                    </div>\
                                    <div class="col-md-5">\
                                        <input type="text" name="descripcion_ip_port[]" class="form-control" placeholder="Description">\
                                    </div>\
                                </div>\
                            </div>\
                        </div>\
                        <input class="col-md-2 delete btn btn-danger" value="Delete">'); 
});
$("body").on("click", ".delete", function (e) 
{
    $(this).parent("div").remove();
});

function mostrar(id) 
{
    if (id == "host") 
    {
        $("#host").show();
        $("#network").hide();
        $("#port").hide();
        $("#url").hide();
        $("#url_ports").hide();
        $("#urltable").hide();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "network") 
    {
        $("#host").hide();
        $("#network").show();
        $("#port").hide();
        $("#url").hide();
        $("#url_ports").hide();
        $("#urltable").hide();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "port") 
    {
        $("#host").hide();
        $("#network").hide();
        $("#port").show();
        $("#url").hide();
        $("#url_ports").hide();
        $("#urltable").hide();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "url") 
    {
        $("#host").hide();
        $("#network").hide();
        $("#port").hide();
        $("#url").show();
        $("#url_ports").hide();
        $("#urltable").hide();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "url_ports") 
    {
        $("#host").hide();
        $("#network").hide();
        $("#port").hide();
        $("#url").hide();
        $("#url_ports").show();
        $("#urltable").hide();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "urltable") 
    {
        $("#host").hide();
        $("#network").hide();
        $("#port").hide();
        $("#url").hide();
        $("#url_ports").hide();
        $("#urltable").show();
        $("#urltable_ports").hide();
        $("#items").show();
    }
    if (id == "urltable_ports") 
    {
        $("#host").hide();
        $("#network").hide();
        $("#port").hide();
        $("#url").hide();
        $("#url_ports").hide();
        $("#urltable").hide();
        $("#urltable_ports").show();
        $("#items").show();
    }
}