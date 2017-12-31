/**
 * Created by Krzysiek on 11/08/2017.
 */
$(function()
{
    const form = $("#global-form");
    const save_btn = $("#btn-save");
    let changes = false;
    let save = () =>
    {
        let json = objectifyForm(form.serializeArray());
        json.enabled = $("input[name=enabled]")[0].checked;
        let data = JSON.stringify(json);

        $("ul.nav-pills > li[role=presentation].highlight").removeClass("highlight");
        $("ul.nav-pills > li[role=presentation]").eq(parseInt(json.current_profile) + 1).addClass("highlight");

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
    quick_save.change(save);

    $(window).on("beforeunload", function(e)
    {
        if(!changes)
            return undefined;
        const confirmationMessage = 'It looks like you have been editing something. '
            + 'If you leave before saving, your changes will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });

    function objectifyForm(formArray)
    {
        const returnArray = {};
        for(let i = 0; i < formArray.length; i++)
        {
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }

    function showSnackbar(text, duration = 2500)
    {
        const snackbar = $("#snackbar");
        snackbar.text(text);
        snackbar.addClass("show");
        setTimeout(() => snackbar.removeClass("show"), duration);
    }
});