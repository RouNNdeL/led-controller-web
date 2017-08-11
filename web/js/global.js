/**
 * Created by Krzysiek on 11/08/2017.
 */
$(function()
{
    const form = $("#global-form");
    const save_btn = $("#btn-save");
    let changes = false;
    save_btn.click(function()
    {
        $.ajax("/api/save/global", {
            method: "POST",
            data: JSON.stringify(objectifyForm(form.serializeArray()))
        }).done(function(response)
        {
            showSnackbar(response.message, 2500);
            save_btn.prop("disabled", true);
            changes = false;
        }).fail(function(e)
        {
            showSnackbar(e.responseJSON.message);
            console.error(e);
        });
    });

    form.find("input,select").change(function()
    {
        save_btn.prop("disabled", false);
        changes = true;
    });

    $(window).on("beforeunload", function(e)
    {
        if(! changes)
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