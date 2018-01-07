/**
 * Created by Krzysiek on 11/08/2017.
 */
$(function()
{
    const form = $("#global-form");
    const save_btn = $("#btn-save");
    const slider = $("#brightness-slider").slider();
    let changes = false;

    let save = () =>
    {
        let json = objectifyForm(form.serializeArray());
        json.enabled = $("input[name=enabled]")[0].checked;
        json.fan_count = parseInt(json.fan_count);
        json.profile_index = parseInt(json.current_profile);
        json.brightness = parseInt(json.brightness);
        json.auto_increment = parseInt(json.auto_increment);
        let data = JSON.stringify(json);

        $.ajax("/api/save/global", {
            method: "POST",
            data: data,
            contentType: "application/json"
        }).done(function(response)
        {
            showSnackbar(response.message, 2500);
            save_btn.prop("disabled", true);
            $("#auto-increment").val(response.auto_increment_val);
            changes = false;
        }).fail(function(e)
        {
            showSnackbar(e.responseJSON.message);
            console.error(e);
        });
    };

    save_btn.click(save);

    let quick_save = form.find("select[name='fan_count'],select[name='current_profile'],input[name='enabled']");

    form.find("input,select").not(quick_save).change(() =>
    {
        changes = true;
        save_btn.prop("disabled", false);
    });
    quick_save.change(() => {
        changes = true;
        save();
    });

    $(window).on("beforeunload", function(e)
    {
        if(!changes)
            return undefined;
        const confirmationMessage = 'It looks like you have been editing something. '
            + 'If you leave before saving, your changes will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });

    $("#globals-profiles-active").sortable({
        connectWith: "#globals-profiles-inactive",
        receive: function(event, ui) {
            if ($(this).children().length > 8) {
                showSnackbar("You can only have 8 active profiles!");
                $(ui.sender).sortable('cancel');
            }
        },
        remove: function(event, ui) {
            if ($(this).children().length < 1) {
                showSnackbar("You need at least 1 active profile!");
                $(this).sortable('cancel');
            }
        },
        change: function(event, ui)
        {
            changes = true;
            save_btn.prop("disabled", false);
        }
    });

    $("#globals-profiles-inactive").sortable({
        connectWith: "#globals-profiles-active"
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/events");
        source.addEventListener("globals", ({data}) => {
            try
            {
                if(!changes)
                {
                    const globals = JSON.parse(data).data;
                    $("a.nav-link.highlight").removeClass("highlight");
                    $("a.nav-link").eq(parseInt(globals.highlight_index)).addClass("highlight");

                    $("li.list-group-item.highlight").removeClass("highlight");
                    $("li.list-group-item[data-index="+globals.highlight_profile_index+"]").addClass("highlight");

                    $("select[name=current_profile]").val(globals.highlight_profile_index);
                    $("input[name=enabled]")[0].checked = globals.leds_enabled;
                    slider.slider("setValue", globals.brightness);
                    $("input[name=auto_increment]").val(globals.auto_increment);
                    $("select[name=fan_count]").val(globals.fan_count);
                }
            }
            catch(e)
            {
                console.error(e, data);
            }
        });
        // noinspection EqualityComparisonWithCoercionJS
        source.addEventListener("tcp_status",
            ({data}) => $("#global-warning-tcp").toggleClass("hidden-xs-up", data === "1"));
    }
});

function showSnackbar(text, duration = 2500)
{
    const snackbar = $("#snackbar");
    snackbar.text(text);
    snackbar.addClass("show");
    setTimeout(() => snackbar.removeClass("show"), duration);
}

function objectifyForm(formArray)
{
    const returnArray = {};
    for(let i = 0; i < formArray.length; i++)
    {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}