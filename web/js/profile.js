/**
 * Created by Krzysiek on 10/08/2017.
 */
$(function()
{
    const devices = $("#device-navbar").find("li[role=presentation]");
    devices.click(function()
    {
        if(!$(this).hasClass("active"))
        {
            devices.removeClass("active");
            $(this).addClass("active");
            $("#device-settings-iframe").attr("src", "/device_settings/" + $(this).attr("data-device-url"));
        }
    })
});