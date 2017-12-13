var width=window.innerWidth;
// var height=window.innerHeight;
if(width>=750){
	width=750;
}
var html=document.getElementsByTagName("html")[0];
html.style.fontSize=width/3.2+"px";
var body=document.getElementsByTagName("body")[0];
html.style.width=width+"px";
// html.style.height=height+"px";
// 
window.onload=function(){
    //初始化
    var WIDTH=width;
    var HEIGHT=html.offsetHeight;
    var canvas=document.getElementById('canvas');
    var context=canvas.getContext('2d');
    canvas.width=WIDTH;
    canvas.height=HEIGHT;

    draw(context)

}
window.onresize=function(){
    var width=window.innerWidth;
    // var height=window.innerHeight;
    if(width>=750){
        width=750;
    }
    var html=document.getElementsByTagName("html")[0];
    html.style.fontSize=width/3.2+"px";
    var body=document.getElementsByTagName("body")[0];
    html.style.width=width+"px";
    // html.style.height=height+"px";
    ///初始化
    var WIDTH=width;
    var HEIGHT=html.offsetHeight;

    var canvas=document.getElementById('canvas');
    var context=canvas.getContext('2d');
    canvas.width=WIDTH;
    canvas.height=HEIGHT;

    draw(context)
}
function draw(context){
    //长方形
    context.save()
    context.translate(canvas.width*0.1,canvas.height*0.03)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(0,0)
    context.lineTo(10,0)
    context.lineTo(10,50)
    context.lineTo(0,50)
    context.closePath()
    context.stroke()
    context.restore()
    //斜线
    context.save()
    context.translate(canvas.width*0.6,canvas.height*0.02)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(30,0)
    context.lineTo(0,30)
    context.closePath()
    context.lineWidth=2
    context.stroke()
    context.restore()
    //三角形
    context.save()
    context.translate(canvas.width*0.3,canvas.height*0.04)
    context.beginPath()
    context.fillStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(10,5)
    context.lineTo(0,10)
    context.lineTo(0,0)
    context.closePath()
    context.fill()
    context.restore()
    //圆形
    context.save()
    context.translate(canvas.width*0.85,canvas.height*0.055)
    context.beginPath()
    context.fillStyle="#0000fd"
    context.arc(0,0,5,0,Math.PI*2)
    context.closePath()
    context.fill()
    context.restore()
    //三角形
    context.save()
    context.translate(canvas.width*0.9,canvas.height*0.09)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(0,0)
    context.lineTo(20,10)
    context.lineTo(0,20)
    context.lineTo(0,0)
    context.closePath()
    context.stroke()
    context.restore()
    //斜断线
    context.save()
    context.translate(canvas.width*0.05,canvas.height*0.12)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.moveTo(30,0)
    context.lineTo(15,15)
    context.moveTo(10,20)
    context.lineTo(0,30)
    context.closePath()
    context.lineWidth=2
    context.stroke()
    context.restore()
    //折线
    context.save()
    context.translate(canvas.width*0.9,canvas.height*0.13)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(0,40)
    context.lineTo(15,40)
    context.stroke()
    context.restore()
    //圆形
    context.save()
    context.translate(canvas.width*0.1,canvas.height*0.2)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.arc(0,0,5,0,Math.PI*2)
    context.closePath()
    context.stroke()
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.arc(0,0,5,0,Math.PI*2)
    context.closePath()
    context.stroke()
    context.restore()
    //组合
    context.save()
    context.translate(canvas.width*0.4,canvas.height*0.2)
    context.beginPath()
    context.strokeStyle="#0000f4"
    context.arc(19,19,5,0,Math.PI*2)
    context.stroke()
    context.closePath()
    context.beginPath()
    context.strokeStyle="#f00"
    context.rect(0,0,20,20)
    context.stroke()
    context.closePath()
    context.beginPath()
    context.strokeStyle="#f00"
    context.rect(0,0,20,20)
    context.stroke()
    context.closePath()
    context.restore()
    //曲折线
    context.save()
    context.translate(canvas.width*0.7,canvas.height*0.19)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(0,5)
    context.lineTo(5,5)
    context.lineTo(5,10)
    context.lineTo(10,10)
    context.lineTo(10,15)
    context.lineTo(15,15)
    context.lineTo(15,20)
    context.stroke()
    context.restore()
    //圆矩形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(3,0)
    context.lineTo(53,0)
    context.arc(53,3,3,Math.PI*2*0.75,Math.PI*2*0.25)
    context.lineTo(3,6)
    context.arc(3,3,3,Math.PI*2*0.25,Math.PI*2*0.75)
    context.stroke()
    context.restore()
    //三角形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(10,5)
    context.lineTo(0,10)
    context.lineTo(0,0)
    context.closePath()
    context.fill()
    context.restore()
    //断线
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#ff6160"
    context.moveTo(0,0)
    context.lineTo(80,0)
    context.moveTo(90,0)
    context.lineTo(120,0)
    context.closePath()
    context.lineWidth=2
    context.stroke()
    context.restore()
    //圆形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.arc(0,0,5,0,Math.PI*2)
    context.closePath()
    context.stroke()
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.arc(0,0,5,0,Math.PI*2)
    context.closePath()
    context.stroke()
    context.restore()
    //组合
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#0000f4"
    context.arc(19,19,5,0,Math.PI*2)
    context.stroke()
    context.closePath()
    context.beginPath()
    context.strokeStyle="#f00"
    context.rect(0,0,20,20)
    context.stroke()
    context.closePath()
    context.beginPath()
    context.strokeStyle="#f00"
    context.rect(0,0,20,20)
    context.stroke()
    context.closePath()
    context.restore()
    //折线
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(0,40)
    context.lineTo(15,40)
    context.stroke()
    context.restore()
    //圆矩形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(3,0)
    context.lineTo(53,0)
    context.arc(53,3,3,Math.PI*2*0.75,Math.PI*2*0.25)
    context.lineTo(3,6)
    context.arc(3,3,3,Math.PI*2*0.25,Math.PI*2*0.75)
    context.stroke()
    context.restore()
    //圆矩形
    context.save()
    context.translate(-canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.strokeStyle="#f00"
    context.moveTo(3,0)
    context.lineTo(53,0)
    context.arc(53,3,3,Math.PI*2*0.75,Math.PI*2*0.25)
    context.lineTo(3,6)
    context.arc(3,3,3,Math.PI*2*0.25,Math.PI*2*0.75)
    context.stroke()
    context.restore()
    //三角形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#0000fd"
    context.moveTo(0,0)
    context.lineTo(10,5)
    context.lineTo(0,10)
    context.lineTo(0,0)
    context.closePath()
    context.fill()
    context.restore()
    //三角形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#ff6160"
    context.moveTo(0,0)
    context.lineTo(15,5)
    context.lineTo(0,10)
    context.lineTo(0,0)
    context.closePath()
    context.fill()
    context.restore()
    //矩形
    context.save()
    context.translate(canvas.width*Math.random(),canvas.height*0.3+canvas.height*Math.random()*0.5)
    context.rotate(Math.random()*360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#ff6160"
    context.rect(0,0,20,15)
    context.closePath()
    context.fill()
    context.restore()

    //三角形
    context.save()
    context.translate(canvas.width*0.16,canvas.height*0.86)
    context.rotate(135/360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#ff6160"
    context.moveTo(0,0)
    context.lineTo(15,5)
    context.lineTo(0,10)
    context.lineTo(0,0)
    context.closePath()
    context.fill()
    context.restore()
    //矩形
    context.save()
    context.translate(canvas.width*0.7,canvas.height*0.84)
    context.rotate(-45/360*Math.PI*2)
    context.beginPath()
    context.fillStyle="#ff6160"
    context.rect(0,0,20,15)
    context.closePath()
    context.fill()
    context.restore()

    //大边框
    context.save()
    context.beginPath()
    context.lineWidth=10
    context.strokeStyle="#ff6160"
    context.rect(0,0,canvas.width,canvas.height)
    context.stroke()
    context.closePath()
    context.restore()
}