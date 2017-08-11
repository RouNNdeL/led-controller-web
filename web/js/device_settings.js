/**
 * Created by Krzysiek on 10/08/2017.
 */
"use strict";
const REGEX_COLOR = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
const COLOR_TEMPLATE =
    "<div>" +
    "<div class=\"color-swatch-container\" style=\"margin-right: 4px;\">" +
    "<div class=\"input-group color-swatch\">" +
    "<span class=\"input-group-addon\">" +
    "<input type=\"radio\" aria-label=\"$label\" name=\"color\">" +
    "</span>" +
    "<div class=\"color-box\" style=\"background-color: $color\"></div>" +
    "</div>" +
    "</div>" +
    "<button class=\"btn btn-danger color-delete-btn\"><span class=\"glyphicon glyphicon-trash\"></span></button>" +
    "</div>";

const SELECTOR_RADIOS = "input[type=radio][name=color]";

$(function()
{
    let previous_color_value;
    const container = document.getElementById("color-picker");
    const picker = new CP(container, false);

    picker.fit = function()
    {
        this.picker.style.left = this.picker.style.top = ""; // do nothing ...
    };
    picker.picker.classList.add('static');
    picker.enter(container);
    picker.set($(".color-box").eq(0).css("background-color"));

    const color_input = $("#color-input");
    picker.on("change", function(color)
    {
        color_input.val("#" + color);
        color_input.change();
        previous_color_value = "#" + color;
    });

    color_input.change(function(e)
    {
        let val = $(this).val();
        if(val[0] !== "#")
        {
            $(this).val("#" + val);
            val = "#" + val;
        }

        if(REGEX_COLOR.exec(val) !== null)
        {
            $("input:checked[type=radio][name=color]")
                .parent().siblings(".color-box").css("background-color", val);

            if(e.originalEvent)
            {
                picker.set(val);
            }
            previous_color_value = val;
        }
        else
        {
            if(previous_color_value === null)
                previous_color_value = "#FFFFFF";
            $(this).val(previous_color_value);
        }
    });

    function refreshListeners()
    {
        const radios = $(SELECTOR_RADIOS);
        radios.off("change");
        radios.change(function()
        {
            let color = $(this).parent().siblings(".color-box").css("background-color");
            picker.set(color);
            color_input.val(rgb2hex(color));
        });

        const delete_btns = $(".color-delete-btn");
        delete_btns.off("click");
        delete_btns.click(function()
        {
            const color_count = $(".color-swatch-container").length;
            if(color_count > 1)
            {
                if($(this).parent().find(SELECTOR_RADIOS)[0].checked)
                {
                    const radios = $(SELECTOR_RADIOS);
                    const index = radios.index($(this).parent().find(SELECTOR_RADIOS));
                    console.log(index);
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
    }

    refreshListeners();

    $("#add-color-btn").click(function()
    {
        const swatches = $(".color-swatch-container");
        const num = swatches.length + 1;
        if(num === 2)
            $(".color-delete-btn").css("visibility", "");
        if(num < 16)
        {
            const swatch = getColorSwatch(num);
            $(swatch).insertAfter(swatches.eq(num - 2).parent());
            refreshListeners();
        }
        else
        {
            $(this).css("display", "none");
        }
    });
});

//Source: http://wowmotty.blogspot.com/2009/06/convert-jquery-rgb-output-to-hex-color.html
let hexDigits = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"];

//Function to convert rgb color to hex format
function rgb2hex(rgb)
{
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function hex(x)
{
    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

function getColorSwatch(n)
{
    return $.parseHTML(COLOR_TEMPLATE.replace("$label", "color-" + n).replace("$color", "#FFFFFF"));
}
