function initBezier()
{
    const c = document.getElementById("test-canvas");
    const ctx = c.getContext("2d");
    const width = c.width;
    const height = c.height;

    const bezier = new CubicBezier(new Point(0, 0), new Point(.26, .93), new Point(.35, .35), new Point(1, 1));

    /**
     *
     * @param pos {Point} - Position
     * @param radius
     * @param fillColor
     * @param strokeWidth
     * @param strokeColor
     */
    function drawCircle(pos, radius, fillColor, strokeWidth = 0, strokeColor = "#000")
    {
        ctx.fillStyle = fillColor;
        ctx.strokeStyle = strokeColor;
        ctx.lineWidth = strokeWidth;
        ctx.beginPath();
        ctx.arc(pos.x, height - pos.y, radius, 0, radius * Math.PI);
        ctx.fill();
        if(strokeWidth > 0) ctx.stroke();
    }

    /**
     *
     * @param bezier {CubicBezier}
     * @param width
     * @param color
     */
    function drawBezier(bezier, width = 4, color = "#000")
    {
        ctx.strokeStyle = color;
        ctx.lineWidth = width;
        ctx.beginPath();
        ctx.moveTo(bezier.p0.x, height - bezier.p0.y);
        ctx.bezierCurveTo(bezier.p1.x, height - bezier.p1.y, bezier.p2.x, height - bezier.p2.y, bezier.p3.x, height - bezier.p3.y);
        ctx.stroke();
    }

    function clearCanvas()
    {
        ctx.clearRect(0, 0, width, height);
    }

    function draw()
    {
        clearCanvas();

        drawBezier(bezier.scale(200));

        window.requestAnimationFrame(draw);
    }

    window.requestAnimationFrame(draw);
}

class Point
{
    /**
     *
     * @param x {float|int}
     * @param y {float|int}
     */
    constructor(x, y)
    {
        this.x = x;
        this.y = y;
    }

    scale(scale)
    {
        return new Point(this.x * scale, this.y * scale);
    }
}

class CubicBezier
{
    /**
     *
     * @param p0 {Point}
     * @param p1 {Point}
     * @param p2 {Point}
     * @param p3 {Point}
     */
    constructor(p0, p1, p2, p3)
    {
        this.p0 = p0;
        this.p1 = p1;
        this.p2 = p2;
        this.p3 = p3;
    }

    scale(scale)
    {
        return new CubicBezier(this.p0.scale(scale), this.p1.scale(scale), this.p2.scale(scale), this.p3.scale(scale));
    }
}

$(initBezier);