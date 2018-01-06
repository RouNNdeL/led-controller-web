/**
 * Created by Krzysiek on 10/08/2017.
 */
const REGEX_COLOR = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
const REGEX_DEVICE = /(pc|gpu|fan-(\d))/;
const COLOR_TEMPLATE = "<div class=\"color-container row mb-1\">\n            <div class=\"col-auto ml-3\">\n                <button class=\"btn btn-danger color-delete-btn\" type=\"button\" role=\"button\"><span class=\"oi oi-trash\"></span></button>\n            </div>\n            <div class=\"col pl-1\">\n                <div class=\"input-group colorpicker-component\" title=\"Using input value\">\n                    <input type=\"text\" class=\"form-control color-input\" value=\"$color\" autocomplete=\"off\" \n                    aria-autocomplete=\"none\" spellcheck=\"false\"/>\n                    <span class=\"input-group-addon\"><i></i></span>\n                </div>\n            </div>\n        </div>";

const SELECTOR_RADIOS = "input[type=radio][name=color]";

let profile_n;
let profile_index;
let previous_hash = window.location.hash;

$(function()
{
    handleHash();
    $('[data-toggle="tooltip"]').tooltip();

    const devices = [];
    const profile_text = $("#main-navbar").find("li.nav-item a.nav-link.active");
    const profile_name = $("#profile-name");
    profile_n = parseInt($("#profile_n").val());
    profile_index = parseInt($("#current_profile").val());
    const delete_profile = $("#btn-delete-profile").not(".disabled");
    const warning_leds = $("#profile-warning-led-disabled");
    const warning_profile = $("#profile-warning-diff-profile");
    let string_confirm_delete;

    refreshColorPickers();

    $.ajax("/api/get_string.php?name=profile_delete_confirm").done(response =>
    {
        string_confirm_delete = response.string;
    });

    $(window).on("hashchange", function()
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
        $.ajax("/api/save/profile/name", {
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
                $("a.nav-link.highlight").removeClass("highlight");
                if(profile_n !== globals.current_profile)
                {
                    $("a.nav-link").eq(parseInt(globals.highlight_index)).addClass("highlight");
                }

                warning_profile.toggleClass("hidden-xs-up", profile_n === globals.highlight_profile_index);
                warning_profile.find("a#current_profile_url").attr("href", "/profile/" + globals.highlight_profile_index);
                warning_leds.toggleClass("hidden-xs-up", globals.leds_enabled);
                profile_index = globals.highlight_profile_index;
            }
            catch(e)
            {
                console.error(e, data);
            }
        })
    }

    $(".device-settings-container").each(function(i)
    {
        devices.push(new DeviceSetting($(this)));
    });

    $(window).keydown(function(event)
    {
        if(event.keyCode === 13 && !profile_name.is(":focus"))
        {
            event.preventDefault();
            return false;
        }
    });

    $("#device-settings-submit").click(event =>
    {
        const forms = {
            profile_n: profile_n,
            devices: []
        };
        console.log(devices);
        for(let i = 0; i < devices.length; i++)
        {
            forms["devices"][i] = devices[i].formToJson();
        }
        $.ajax("/api/save/profile", {
            method: "POST",
            data: JSON.stringify(forms),
            contentType: "application/json"
        }).done(response =>
        {
            showSnackbar(response.message)
        }).fail(err => console.error);
    });
});

$(window).on("beforeunload", function(e)
{
    $.ajax("/api/explicit_save", {method: "POST", async: false});
});

function showSnackbar(text, duration = 2500)
{
    const snackbar = $("#snackbar");
    snackbar.text(text);
    snackbar.addClass("show");
    setTimeout(() => snackbar.removeClass("show"), duration);
}

function handleHash()
{
    let match;
    if(window.location.hash === "#enable_leds")
    {
        replaceHash(previous_hash);
        $.ajax("/api/enable_leds", {method: "POST"});
    }
    else if(window.location.hash === "#change_profile")
    {
        replaceHash(previous_hash);
        $.ajax("/api/change_profile", {method: "POST", data: profile_n.toString()});
    }
    else if((match = window.location.hash.match(REGEX_DEVICE)) !== null)
    {
        $(".device-settings-container").addClass("hidden-xs-up");
        $("#device-" + match[1]).removeClass("hidden-xs-up");

        $(".device-header").addClass("hidden-xs-up");
        $("#header-" + match[1]).removeClass("hidden-xs-up");

        $(".device-link").removeClass("active");
        $("#device-link-" + match[1]).addClass("active");
    }
    previous_hash = window.location.hash;
}

function replaceHash(hash)
{
    history.replaceState("", document.title, window.location.pathname
        + window.location.search + hash);
}

class DeviceSetting
{
    constructor(parent)
    {
        this.parent = parent;
        let limit_colors = this.limit_colors = 16;
        this.id = parent.attr("id");
        this.device_match = this.id.match(REGEX_DEVICE);
        if(this.device_match === null)
            throw new Error("Invalid parent id:", this.id);
        if(this.device_match[1] === "pc")
            this.device = {type: "a", num: 0};
        else if(this.device_match[1] === "gpu")
            this.device = {type: "a", num: 1};
        else
            this.device = {type: "d", num: parseInt(this.device_match[2])};

        this.limit_colors = parseInt(parent.find("#swatches-container").data("color-limit"));

        const it = this;
        parent.find("#add-color-btn").click(function()
        {
            const swatches = parent.find(".color-container");
            const num = swatches.length;
            if(num < limit_colors)
            {
                parent.find(".color-delete-btn").prop("disabled", false);
                const swatch = getColorSwatch(num);
                $(swatch).insertBefore($(this));
                refreshColorPickers();
                if(num === limit_colors - 1)
                    $(this).addClass("hidden-xs-up")
            }
            it.refreshListeners();
        });

        parent.find("#effect-select-" + this.device_match[1]).change(event =>
        {
            const data = JSON.stringify({
                type: this.device.type,
                effect: parseInt($(event.target).val())
            });
            $.ajax("/api/get_html/timing_args", {
                method: "POST",
                data: data,
                contentType: "application/json"
            }).done(response =>
            {
                if(response.status !== "success")
                {
                    console.error("Error getting args, timings: ", response);
                }
                else
                {
                    const main = parent.find("#main-container");
                    const containers = parent.find("#timing-container, #args-container, input[type=hidden]");
                    containers.remove();
                    main.append($.parseHTML(response.html));
                    this.limit_colors = limit_colors = response.limit_colors;
                    this.refreshColorsLimit();
                }
            }).fail(err =>
            {
                console.error(err);
            })
        });

        this.refreshColorsLimit();
        this.refreshListeners();
    }

    refreshColorsLimit()
    {
        let swatches = this.parent.find(".color-container");
        if(this.limit_colors > 0 && swatches.length === 0)
        {
            const swatch = getColorSwatch(0);
            $(swatch).insertBefore(this.parent.find("#add-color-btn"));
            this.parent.find(".color-delete-btn").prop("disabled", true);
        }
        swatches = this.parent.find(".color-container");
        if(swatches.length < this.limit_colors)
        {
            this.parent.find("#add-color-btn").removeClass("hidden-xs-up");
        }
        else
        {
            this.parent.find("#add-color-btn").addClass("hidden-xs-up");
            this.limit_colors === 0 ? swatches.remove() : this.parent.find(".color-container:gt(" + (this.limit_colors - 1) + ")").remove();
            const delete_btns = this.parent.find(".color-delete-btn");
            if(delete_btns.length === 1)
                delete_btns.prop("disabled", true);
        }
        this.parent.find("#header-colors").toggleClass("hidden-xs-up", this.limit_colors === 0);
        this.refreshListeners();
        refreshColorPickers();
    }

    refreshListeners()
    {
        const it = this;
        let del_buttons = this.parent.find(".color-delete-btn");
        del_buttons.off("click");
        del_buttons.click(function(e)
        {
            const swatch_count = it.parent.find(".color-container").length;
            if(swatch_count > 1)
                $(this).parent().parent().remove();
            if(swatch_count <= 2)
            {
                del_buttons.prop("disabled", true);
            }
        })
    }

    formToJson()
    {
        const array = this.parent.find("#device-form-" + this.device_match[1]).serializeArray();
        const json = {};
        json.times = [];
        json.args = {};
        json.colors = [];
        json.device = this.device;

        for(let i = 0; i < array.length; i++)
        {
            let timeMatch = array[i].name.match(/time_(.*)/);
            let argsMatch = array[i].name.match(/arg_(.*)/);

            if(timeMatch !== null)
            {
                switch(timeMatch[1])
                {
                    case "off":
                        json.times[0] = parseFloat(array[i].value);
                        break;
                    case "fadein":
                        json.times[1] = parseFloat(array[i].value);
                        break;
                    case "on":
                        json.times[2] = parseFloat(array[i].value);
                        break;
                    case "fadeout":
                        json.times[3] = parseFloat(array[i].value);
                        break;
                    case "rotation":
                        json.times[4] = parseFloat(array[i].value);
                        break;
                    case "offset":
                        json.times[5] = parseFloat(array[i].value);
                        break;
                }
            }
            else if(argsMatch !== null)
            {
                json.args[argsMatch[1]] = parseInt(array[i].value);
            }
            else if(array[i].name !== "color")
            {
                json[array[i].name] = array[i].value;
            }
        }

        json.colors = this.getColors();
        json.effect = parseInt(json.effect);

        return json;
    }

    getColors()
    {
        const colors = [];
        const swatches = this.parent.find(".color-input");

        for(let i = 0; i < swatches.length; i++)
        {
            colors.push(swatches.eq(i).val().substring(1));
        }

        return colors;
    }
}

//Source: http://wowmotty.blogspot.com/2009/06/convert-jquery-rgb-output-to-hex-color.html
let hexDigits = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"];

//Function to convert rgb color to hex format
function rgb2hex(rgb, hash = true)
{
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    return (hash ? "#" : "") + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function hex(x)
{
    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

function getColorSwatch(n)
{
    return $.parseHTML(COLOR_TEMPLATE.replace("$label", "color-" + n).replace("$color", "#FFFFFF"));
}

function refreshColorPickers()
{
    $(".colorpicker-component").colorpicker({
        useAlpha: false,
        extensions: [
            {
                name: "swatches",
                colors: {
                    "white": "#ffffff",
                    "red": "#ff0000",
                    "green": "#00ff00",
                    "blue": "#0000ff",
                    "magenta": "#ff00ff",
                    "yellow": "#ffff00",
                    "cyan": "#00ffff",
                },
                namesAsValues: false
            }
        ],
    });
}