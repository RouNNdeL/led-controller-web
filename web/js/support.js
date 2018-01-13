"use strict";

function getTiming(x)
{
    if(x < 0 || x > 255)
    {
        return 0;
    }
    if(x <= 80)
    {
        return x / 16;
    }
    if(x <= 120)
    {
        return x / 8 - 5;
    }
    if(x <= 160)
    {
        return x / 2 - 50;
    }
    if(x <= 190)
    {
        return x - 130;
    }
    if(x <= 235)
    {
        return 2 * x - 320;
    }
    if(x <= 245)
    {
        return 15 * x - 3375;
    }
    return 60 * x - 14400;
}

function convertToTiming(float)
{
    const timings = getTimings();
    for(let i = 0; i < timings.length; i++)
    {
        if(float < timings[i]) return i - 1;
    }
    return 0;
}

function getTimings()
{
    const arr = [];
    for(let i = 0; i < 256; i++)
    {
        arr[i] = getTiming(i);
    }
    return arr;
}