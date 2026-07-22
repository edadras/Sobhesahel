$(".tabBox").each(function () {
  var thisTAb = $(this);
  thisTAb.find(".tablinks").click(function (e) {
    e.preventDefault();
    var thisLink = $(this);
    var tabId = thisLink.attr("tabId");
    var tabId = "#" + tabId;
    thisTAb.find(".tabcontent").hide();
    thisTAb.find(tabId).show();
    thisLink.parent().siblings().find(".tablinks").removeClass("active");
    thisLink.addClass("active");
  });
});

// function openDL(evt,dlName){
// 	var i, tabcontent,tablinks;
// 	tabcontent = document.getElementsByClassName("tabcontent");
// 	for(i = 0; i < tabcontent.length; i++){
// 		tabcontent[i].style.display="none";
// 	}
// 	tablinks = document.getElementsByClassName("tablinks");
// 	for(i = 0; i < tablinks.length; i++){
// 		tablinks[i].className = tablinks[i].className.replace("active","");
// 	}
// 	evt.preventDefault();
// 	document.getElementById(dlName).style.display = "block";
// 	evt.currentTarget.className += " active";
// }
