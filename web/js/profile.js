/**
 * Created by Krzysiek on 10/08/2017.
 */
$(function()
{
    enableLeds();

    const devices = $("#device-navbar").find("li[role=presentation]");
    const profile_text = $("#main-navbar").find("li.active a");
    const profile_name = $("#profile-name");
    const profile_n = $("#profile_n").val();

    devices.click(function()
    {
        if(!$(this).hasClass("active"))
        {
            devices.removeClass("active");
            $(this).addClass("active");
            $("#device-settings-iframe").attr("src", "/device_settings/" + $(this).attr("data-device-url"));
        }
    });

    $(window).on('hashchange', function()
    {
        enableLeds();
    });

    profile_name.on("input", function()
    {
        let val = $(this).val();
        if(val.length > 30)
        {
            val = val.substring(0, 30);
            $(this).val(val);
        }
        if(val.length === 0)
            profile_text.text($(this).attr("placeholder"));
        else
            profile_text.text(val);
    });

    profile_name.change(function()
    {
        $.ajax("/api/save/profile", {
                method: "POST",
                data: JSON.stringify({
                    "profile_n": profile_n,
                    "name": $(this).val()
                })
            }
        ).fail(function(e)
        {
            console.error(e);
        });
    });
});

function enableLeds()
{
    if(window.location.hash === "#enable_leds")
    {
        removeHash();
        $.ajax("/api/enable_leds", {method: "POST"}).done(function()
        {
            window.location.reload(false);
        }).error(function(e)
        {
            console.error(e);
        });
    }
}
function removeHash()
{
    history.replaceState("", document.title, window.location.pathname
        + window.location.search);
}