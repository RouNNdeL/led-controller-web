/**
 * Created by Krzysiek on 10/08/2017.
 */
const URL_REGEX = /profile\/(\d{1,2})/;

let profile_n;
let current_profile;

$(function()
{
    handleHash();
    $('[data-toggle="tooltip"]').tooltip();

    const devices = $("#device-navbar").find("li[role=presentation] a");
    const profile_text = $("#main-navbar").find("li.active a");
    const profile_name = $("#profile-name");
    profile_n = parseInt($("#profile_n").val());
    current_profile = parseInt($("#current_profile").val());
    const delete_profile = $("#btn-delete-profile").not(".disabled");
    const warning_leds = $("#profile-warning-led-disabled");
    const warning_profile = $("#profile-warning-diff-profile");
    let string_confirm_delete;

    $.ajax("/api/get_string.php?name=profile_delete_confirm").done(response =>
    {
        string_confirm_delete = response.string;
    });

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
        handleHash();
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

    delete_profile.click(function()
    {
        if(confirm(string_confirm_delete))
        {
            $.ajax("/api/remove/profile", {
                method: "POST",
                data: JSON.stringify({profile_n: profile_n})
            }).done(d => window.location.href = "/");
        }
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/events");
        source.addEventListener("globals", ({data}) =>
        {
            try
            {
                const globals = JSON.parse(data).data;
                $("ul.nav-pills > li[role=presentation].highlight").removeClass("highlight");
                if(profile_n !== globals.current_profile)
                {
                    $("ul.nav-pills > li[role=presentation]").eq(parseInt(globals.current_profile) + 1).addClass("highlight");
                }

                warning_profile.css("display", profile_n === globals.current_profile ? "none" : "");
                warning_profile.find("a#current_profile_url").attr("href", "/profile/"+(current_profile+1));
                warning_leds.css("display", globals.leds_enabled ? "none" : "");
                current_profile = globals.current_profile;
            }
            catch(e)
            {
                console.error(e, data);
            }
        })
    }
});

$(window).on("beforeunload", function(e)
{
    $.ajax("/api/explicit_save", {method: "POST", async: false});
});

function handleHash()
{
    if(window.location.hash === "#enable_leds")
    {
        removeHash();
        $.ajax("/api/enable_leds", {method: "POST"});
    }
    else if(window.location.hash === "#change_profile")
    {
        removeHash();
        console.log(profile_n);
        $.ajax("/api/change_profile", {method: "POST", data: profile_n.toString()});
    }
}

function removeHash()
{
    history.replaceState("", document.title, window.location.pathname
        + window.location.search);
}