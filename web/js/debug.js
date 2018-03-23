"use strict";

$(function()
{
    $(".debug-button").click(function(e)
    {
        const action = $(this).data("action");
        const value = $(this).data("value");

        if(action === "pause")
        {
            $(this).find(".oi").toggleClass("oi-media-pause oi-media-play");
            $(this).data("value", value ? 0 : 1);
        }

        $.ajax({
            url: "/api/debug",
            data: JSON.stringify({
                action: action,
                value: value
            }),
            method: "POST",
            dataType: "json",
            contentType: "application/json"
        });
    })
});