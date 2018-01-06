/**
 * Created by Krzysiek on 10/08/2017.
 */
const REGEX_COLOR = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
const REGEX_DEVICE = /(pc|gpu|fan-(\d))/;
const COLOR_TEMPLATE =
    "<div class=\"color-container\">" +
    "<div class=\"color-swatch-container\" style=\"margin-right: 4px;\">" +
    "<div class=\"input-group color-swatch\">" +
    "<span class=\"input-group-addon\">" +
    "<input type=\"radio\" aria-label=\"$label\" name=\"color\">" +
    "</span>" +
    "<div class=\"color-box\" style=\"background-color: $color\"></div>" +
    "</div>" +
    "</div>" +
    "<button class=\"btn btn-danger color-delete-btn\"><span class=\"oi oi-trash\"></span></button>" +
    "</div>";

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

    $.ajax("/api/get_string.php?name=profile_delete_confirm").done(response =>
    {
        string_confirm_delete = response.string;
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
                warning_profile.find("a#current_profile_url").attr("href", "/profile/"+globals.highlight_profile_index);
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
        devices.push(registerDevice($(this)));
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
        for(let i = 0; i < devices.length; i++)
        {
            forms["devices"][i] = devices[i].formToJson();
        }
        $.ajax("/api/save/profile", {
            method: "POST",
            data: JSON.stringify(forms),
            contentType: "application/json"
        }).done(response => {showSnackbar(response.message)}).fail(err => console.error);
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
        $("#device-"+match[1]).removeClass("hidden-xs-up");

        $(".device-header").addClass("hidden-xs-up");
        $("#header-"+match[1]).removeClass("hidden-xs-up");

        $(".device-link").removeClass("active");
        $("#device-link-"+match[1]).addClass("active");
    }
    previous_hash = window.location.hash;
}

function replaceHash(hash)
{
    history.replaceState("", document.title, window.location.pathname
        + window.location.search + hash);
}

function registerDevice(parent)
{
    let limit_colors = 16;
    let id = parent.attr("id");
    const device_match = id.match(REGEX_DEVICE);
    if(device_match === null)
        throw new Error("Invalid parent id:", id);
    let device;
    if(device_match[1] === "pc")
        device = {type: "a", num: 0};
    else if(device_match[1] === "gpu")
        device = {type: "a", num: 1};
    else
        device = {type: "d", num: parseInt(device_match[2])};

    let previous_color_value = "#FFFFFF";
    //TODO: Fix or change color picker
    const container = parent.find("#color-picker-"+device_match[1])[0];
    const picker = new CP(container, false);
    limit_colors = parseInt(parent.find("#swatches-container").data("color-limit"));

    picker.fit = function()
    {
        this.picker.style.left = this.picker.style.top = ""; // do nothing ...
    };
    picker.picker.classList.add('static');
    picker.enter(container);
    picker.set(parent.find(".color-box").eq(0).css("background-color"));

    const color_input = parent.find("#color-input-"+device_match[1]);
    picker.on("change", function(color)
    {
        color_input.val("#" + color);
        color_input.change();
        previous_color_value = "#" + color;
    });

    color_input.change(event =>
    {
        let val = $(event.target).val();
        if(val[0] !== "#")
        {
            $(event.target).val("#" + val);
            val = "#" + val;
        }

        if(REGEX_COLOR.exec(val) !== null)
        {
            parent.find("input:checked[type=radio][name=color]")
                .parent().siblings(".color-box").css("background-color", val);

            if(event.originalEvent)
            {
                picker.set(val);
            }
            previous_color_value = val;
        }
        else
        {
            $(event.target).val(previous_color_value);
        }
    });

    color_input.on("input", event =>
    {
        let val = $(event.target).val();
        if(val[0] !== "#")
        {
            $(event.target).val("#" + val);
            val = "#" + val;
        }
        if(REGEX_COLOR.exec(val) !== null)
        {
            parent.find("input:checked[type=radio][name=color]")
                .parent().siblings(".color-box").css("background-color", val);

            if(event.originalEvent)
            {
                picker.set(val);
            }
            previous_color_value = val;
        }
    });

    function refreshListeners()
    {
        const radios = parent.find(SELECTOR_RADIOS);
        radios.off("change");
        radios.change(function()
        {
            let color = $(this).parent().siblings(".color-box").css("background-color");
            picker.set(color);
            color_input.val(rgb2hex(color));
        });

        const delete_btns = parent.find(".color-delete-btn");
        delete_btns.off("click");
        delete_btns.click(function()
        {
            const color_count = parent.find(".color-container").length;
            if(color_count > 1)
            {
                if($(this).parent().find(SELECTOR_RADIOS)[0].checked)
                {
                    const radios = parent.find(SELECTOR_RADIOS);
                    const index = radios.index($(this).find(SELECTOR_RADIOS));

                    let select_index;
                    if(index === 0)
                        select_index = 1;
                    else
                        select_index = index - 1;
                    radios.eq(select_index).click();
                }
                $(this).parent().remove();
            }
            if(color_count === 2)
                delete_btns.css("visibility", "hidden");
        });

        const containers = parent.find(".color-swatch-container");
        containers.off("click");
        containers.click(function(e)
        {
            if(e.originalEvent)
            {
                $(this).find(SELECTOR_RADIOS).click();
            }
        });
    }

    function refreshColorsLimit()
    {
        let swatches = parent.find(".color-container");
        if(limit_colors > 0 && swatches.length === 0)
        {
            const swatch = getColorSwatch(0);
            $(swatch).insertBefore(parent.find("#add-color-btn"));
            $(swatch).find(SELECTOR_RADIOS)[0].checked = true;
            refreshListeners();
        }
        swatches = parent.find(".color-container");
        if(swatches.length < limit_colors)
        {
            parent.find("#add-color-btn").css("display", "");
        }
        else
        {
            parent.find("#add-color-btn").css("display", "none");
            limit_colors === 0 ? swatches.remove() : parent.find(".color-container:gt(" + (limit_colors - 1) + ")").remove();
            const delete_btns = parent.find(".color-delete-btn");
            if(delete_btns.length === 1)
                delete_btns.css("visibility", "hidden");

            let radios = parent.find(".color-container " + SELECTOR_RADIOS);
            if(!radios.is(":checked") && limit_colors > 0)
            {
                let last = radios.last();
                last[0].checked = true;
                let color = last.parent().siblings(".color-box").css("background-color");
                picker.set(color);
                color_input.val(rgb2hex(color));
            }
        }
    }

    refreshListeners();

    refreshColorsLimit();

    parent.find("#add-color-btn").click(function()
    {
        const swatches = parent.find(".color-container");
        const num = swatches.length;
        if(num === 1)
            parent.find(".color-delete-btn").css("visibility", "");
        if(num < limit_colors)
        {
            const swatch = getColorSwatch(num);
            $(swatch).insertBefore($(this));
            refreshListeners();
            if(num === limit_colors - 1)
                $(this).css("display", "none");
        }
    });

    parent.find("#effect-select-"+device_match[1]).change(event =>
    {
        const data = JSON.stringify({
            type: device.type,
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
                const container = parent.find("#timing-arg-container");
                container.empty();
                container.append($.parseHTML(response.html));
                limit_colors = response.limit_colors;
                refreshColorsLimit();
            }
        }).fail(err =>
        {
            console.error(err);
        })
    });

    function formToJson()
    {
        const array = parent.find("#device-form-"+device_match[1]).serializeArray();
        const json = {};
        json.times = [];
        json.args = {};
        json.colors = [];
        json.device = device;

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

        json.colors = getColors();

        return json;
    }

    function getColors()
    {
        const colors = [];
        const swatches = parent.find(".color-swatch-container div.color-box");

        for(let i = 0; i < swatches.length; i++)
        {
            colors.push(rgb2hex(swatches.eq(i).css("background-color"), false));
        }

        return colors;
    }

    this.formToJson = formToJson;

    return this;
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