/**
 * Created by Krzysiek on 10/08/2017.
 */
$(function()
{
    enableLeds();

    const devices = $("#device-navbar").find("li[role=presentation]");
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
    history.replaceState ("", document.title, window.location.pathname
        + window.location.search);
}