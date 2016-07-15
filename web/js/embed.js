var host = location.host == "demo.ezding.com.tw" ? true : false;

if(host){
	$("header").load("/pitta01/layout/sec/embed/header.htm");
	$("footer").load("/pitta01/layout/sec/embed/footer.htm");
} else{
	$("header").load("/pitta01/sec/embed/header.htm");
	$("footer").load("/pitta01/sec/embed/footer.htm");
}