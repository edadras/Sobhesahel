$(document).ready(function () {
  const $categoryMnu = $(".categoryMnu");
  const $categoryBox = $(".categoryBox");
  const $hdrSideBx = $(".hdrSideBx");

  /***********mouseup********/
  $(document).mouseup((ev) => {
    $(".dropSel").each(function () {
      var thisDrop = $(this);
      if (thisDrop.hasClass("active")) {
        if (!thisDrop.is(ev.target) && thisDrop.has(ev.target).length === 0) {
          thisDrop.removeClass("active");
          thisDrop.find(".dropdown-mnu").slideUp(300);
        }
      }
    });
    if ($(".hdrSideBx").hasClass("open")) {
      if (!$hdrSideBx.is(ev.target) && $hdrSideBx.has(ev.target).length === 0) {
        $(".hdrSideBx").removeClass("open");
        $("body").removeClass("hidOverflow");
      }
    }

    if ($(".categoryMnu").hasClass("open")) {
      if (
        !$categoryMnu.is(ev.target) &&
        $categoryMnu.has(ev.target).length === 0 &&
        !$categoryBox.is(ev.target) &&
        $categoryBox.has(ev.target).length === 0
      ) {
        $(".categoryMnu").slideUp().removeClass("open");
        $("body").removeClass("hidOverflow");
      }
    }
  });

  /********************open-side-menu******************/
  $(".openSidMnu").click(function () {
    $(".hdrSideBx").addClass("open");
    $("body").addClass("hidOverflow");
  });
  $(".closSideMnu").click(function () {
    $(".hdrSideBx").removeClass("open");
    $("body").removeClass("hidOverflow");
  });

  /********************open-cat-menu******************/
  if ($(".categoryMnu").length) {
    $(".openCatIcon").click(function () {
      if (!$(".categoryMnu").hasClass("open")) {
        $(".categoryMnu").slideDown(400).addClass("open");
        $("body").addClass("hidOverflow");
      } else {
        $(".categoryMnu").slideUp().removeClass("open");
        $("body").removeClass("hidOverflow");
      }
    });
  }

  /**********************custom select**********************/
  $(".dropSel").click(function () {
    $(this).toggleClass("active");
    $(this).find(".dropdown-mnu").slideToggle(300);
  });
  $(".dropSel").focusout(function () {
    $(this).removeClass("active");
    $(this).find(".dropdown-mnu").slideUp(300);
  });
  $(".dropSel .dropdown-mnu li a").click(function () {
    $(this).parents(".dropSel").find("span").html($(this).html());
    $(this).parents(".dropSel").find("input").attr("value", $(this).attr("id"));
  });

  /*******************selctTheme*******************/
  if ($(".selctTheme").length) {
    var iTag = $(".selctTheme").find("label i");
    var spanTag = $(".selctTheme").find("label span");
    $(".selctTheme1")
      .find("input")
      .change(function () {
        if ($(this).prop("checked")) {
          spanTag
            .addClass("icon-Vector-Stroke1")
            .removeClass("icon-Group-2122");
          iTag.text("حالت شب");

          $(".chngThemImg").each(function () {
            var thisImg = $(this);
            var darkSrc = thisImg.attr("darkSrc");
            thisImg.attr("src", darkSrc);
          });
        } else {
          spanTag
            .removeClass("icon-Vector-Stroke1")
            .addClass("icon-Group-2122");
          iTag.text("حالت روز");
          $(".chngThemImg").each(function () {
            var thisImg = $(this);
            var lightSrc = thisImg.attr("lightSrc");
            thisImg.attr("src", lightSrc);
          });
        }
      });

    $(".selctTheme2")
      .find("input")
      .change(function () {
        if ($(this).prop("checked")) {
          spanTag
            .addClass("icon-Vector-Stroke1")
            .removeClass("icon-Group-2122");
          iTag.text("dark");

          $(".chngThemImg").each(function () {
            var thisImg = $(this);
            var darkSrc = thisImg.attr("darkSrc");
            thisImg.attr("src", darkSrc);
          });
        } else {
          spanTag
            .removeClass("icon-Vector-Stroke1")
            .addClass("icon-Group-2122");
          iTag.text("light");
          $(".chngThemImg").each(function () {
            var thisImg = $(this);
            var lightSrc = thisImg.attr("lightSrc");
            thisImg.attr("src", lightSrc);
          });
        }
      });

    const toggleSwitch = document.querySelector(".selctTheme input");
    const currentTheme = localStorage.getItem("light");

    if (currentTheme) {
      document.documentElement.setAttribute("data-theme", currentTheme);

      if (currentTheme === "dark") {
        toggleSwitch.checked = true;
      }
    }

    function switchTheme(e) {
      if (e.target.checked) {
        document.documentElement.setAttribute("data-theme", "dark");
        localStorage.setItem("theme", "dark");
      } else {
        document.documentElement.setAttribute("data-theme", "light");
        localStorage.setItem("theme", "light");
      }
    }

    toggleSwitch.addEventListener("change", switchTheme, false);
  }

  /***********swipers********/
  if ($(".topSecRght").length) {
    const swiper = new Swiper(".topSecRght .swiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      mousewheel: false,
      keyboard: true,
      loop: false,
      centeredSlides: true,
      autoplay: false,
      navigation: {
        nextEl: ".topSecRght .swiper-button-next",
        prevEl: ".topSecRght .swiper-button-prev",
      },
    });
  }

  if ($(".topLftSldr").length) {
    const swiper = new Swiper(".topLftSldr .swiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      mousewheel: false,
      keyboard: true,
      loop: false,
      centeredSlides: true,
      autoplay: false,
      navigation: {
        nextEl: ".topLftSldr .swiper-button-next",
        prevEl: ".topLftSldr .swiper-button-prev",
      },
      scrollbar: {
        el: ".topLftSldr .swiper-scrollbar",
        hide: false,
      },
      pagination: {
        el: ".topLftSldr .swiper-pagination",
        clickable: true,
        renderBullet: function (index, className) {
          return '<span class="' + className + '">' + (index + 1) + "</span>";
        },
      },
    });
  }

  if ($(".articlsCat").length) {
    const swiper = new Swiper(".articlsCat .swiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      mousewheel: false,
      keyboard: true,
      loop: false,
      centeredSlides: true,
      autoplay: false,
      pagination: {
        el: ".articlsCat .swiper-pagination",
        clickable: true,
      },
    });
  }

  /***********video********/
  if ($(".mainVideo").length) {
    $(".mainVideo .video-js").click(function () {
      console.log("clicked");
      $(".mainVideo .vidOverlay").hide(300);
    });
  }

  if ($(".videoPgRight").length) {
    $(".videoPgRight .video-js").click(function () {
      console.log("clicked");
      $(".videoPgRight .vidOverlay").hide(300);
    });
  }

  if ($(".floutNews").length) {
    $(".clsFloutNws").click(function () {
      $(this).parent().remove();
    });
  }

  /***********************light-gallery*******************/
  if ($("#lightgallery").length) {
    lightGallery(document.getElementById("lightgallery"), {
      speed: 500,
    });
  }

  /*****************date picker*********************/
  if ($("#datepicker1").length) {
    window.pd = $("#datepicker1").persianDatepicker({
      altFormat: "LLLL",
      initialValue: false,
      observer: true,
      format: "YYYY/MM/DD",
      timePicker: {
        enabled: false,
      },
    });
  }

  if ($("#datepicker2").length) {
    window.pd = $("#datepicker2").persianDatepicker({
      altFormat: "LLLL",
      initialValue: false,
      observer: true,
      format: "YYYY/MM/DD",
      timePicker: {
        enabled: false,
      },
    });
  }

  /******************count-down*********************/
  if ($("#countdown").length) {
    $(function () {
      jQuery.fn.extend({
        countdown: function () {
          let min = 3,
            sec = 0;
          render(min, sec);

          const timer = setInterval(() => {
            if (min == 0 && sec == 0) {
              clearInterval(timer);
              return;
            }

            sec = dealSec(sec);
            min = dealMin(min, sec);
            render(min, sec);
          }, 1000);
        },
      });

      $("#countdown").countdown();
    });

    function dealSec(sec) {
      const timeRange = [...Array(60).keys()].reverse();
      const idxNow = timeRange.indexOf(sec);
      const idxNext = (idxNow + 1) % timeRange.length;
      return timeRange[idxNext];
    }

    function dealMin(min, sec) {
      const timeRange = [...Array(60).keys()].reverse();
      if (sec === 59) {
        const idxNow = timeRange.indexOf(min);
        const idxNext = (idxNow + 1) % timeRange.length;
        return timeRange[idxNext];
      }
      return min;
    }

    function render(min, sec) {
      min = ("00" + min).slice(-2);
      sec = ("00" + sec).slice(-2);

      $("#countdown").text(`${min}:${sec}`);
    }
  }

  /**********side-survay-resulte********************/
  if ($(".sidSrvyRslt").length) {
    $(".sidSrvyRslt").each(function () {
      var thisResult = $(this);
      var pWidth = thisResult.find("p").text();
      console.log(pWidth);
      thisResult.find("div span").css("width", pWidth);
      console.log(thisResult.find("div span").css("width"));
    });
  }

  /*************play-episodes********************/
  if ($(".episodCard").length) {
    $(".episodCard").each(function () {
      var thisCard = $(this);

      thisCard.find(".playIconBtn").click(function () {
        if (thisCard.hasClass("active")) {
          thisCard.removeClass("active");
          thisCard
            .find(".playIconBtn i")
            .removeClass("icon-pause-1")
            .addClass("icon-play-circle-rounded");
        } else {
          thisCard.addClass("active").siblings().removeClass("active");
          thisCard
            .find(".playIconBtn i")
            .addClass("icon-pause-1")
            .removeClass("icon-play-circle-rounded");
          thisCard
            .siblings()
            .find(".playIconBtn i")
            .removeClass("icon-pause-1")
            .addClass("icon-play-circle-rounded");
        }
      });
    });
  }

  /*****************upload image*********************/
  if ($(".fileInput").length) {
    $(".fileInput").each(function () {
      var thisFile = $(this);
      thisFile.on("change", function () {
        if (this.files[0]) {
          var picture = new FileReader();
          picture.readAsDataURL(this.files[0]);
          picture.addEventListener("load", function (event) {
            thisFile
              .parent()
              .parent()
              .find(".uploadedImage")
              .attr("src", event.target.result);
          });
        }
      });
    });
  }

  /******************form-validation**********************/
  (() => {
    "use strict";

    const forms = document.querySelectorAll(".needs-validation");

    Array.from(forms).forEach((form) => {
      form.addEventListener(
        "submit",
        (event) => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }

          form.classList.add("was-validated");
        },
        false
      );
    });
  })();

  /******************form-size*******************/
  if ($(".fontWrap").length) {
    var range = document.querySelector(".form-range"),
      rangeMarker = document.querySelector(".form-range-markers");

    // for (var i = 0; i < range.length; i++) {
    range.addEventListener("change", checkRangeFill);
    // }
    // for (var i = 0; i < rangeMarker.length; i++) {
    rangeMarker.addEventListener("change", checkRangeMarkers);
    // }

    // function for filling left side of range slider
    function checkRangeFill() {
      var val = this.value,
        min = this.getAttribute("min"),
        max = this.getAttribute("max"),
        bgPos = ((val - min) / (max - min)) * 100,
        bgColor = "#eef0f0",
        fillColor = "#ff9086";

      // create a gradient to match current position
      this.style.backgroundImage =
        "-webkit-linear-gradient(left, " +
        fillColor +
        " " +
        bgPos +
        "%, " +
        bgColor +
        " " +
        bgPos +
        "%)";
    }

    // function for filling range markers
    function checkRangeMarkers() {
      var val = this.value,
        markers = this.nextElementSibling.querySelectorAll(".marker");

      // remove active class from markers
      for (var i = 0; i < markers.length; i++) {
        markers[i].classList.remove("active", "active-on");
      }
      // add active class to markers below range value
      for (var i = 0; i < val - 1; i++) {
        markers[i].classList.add("active");
      }
      // add class for current marker
      markers[val - 1].classList.add("active-on");
    }
  }

  /**********number-code********************/
  var vcode = (function () {
    var $inputs = $(".enterPhonBx").find("input");

    $inputs.on("keyup", processInput);

    function processInput(e) {
      var x = e.charCode || e.keyCode;
      if ((x == 8 || x == 46) && this.value.length == 0) {
        var indexNum = $inputs.index(this);
        if (indexNum != 0) {
          $inputs.eq($inputs.index(this) - 1).focus();
        }
      }

      if (ignoreChar(e)) return false;
      else if (this.value.length == this.maxLength) {
        $(this).next("input").focus();
      }
    }
    function ignoreChar(e) {
      var x = e.charCode || e.keyCode;
      if (x == 37 || x == 38 || x == 39 || x == 40) return true;
      else return false;
    }
  })();
});

$(window).scroll(function () {
  if (
    $(window).scrollTop() >= 350 &&
    $(window).scrollTop() + $(window).height() != $(document).height() + 20
  ) {
    $(".headerSec").addClass("fixed , animate__animated , animate__fadeInDown");
  } else {
    $(".headerSec").removeClass(
      "fixed , animate__animated , animate__fadeInDown"
    );
  }

  if (
    $(window).scrollTop() >= 10 &&
    $(window).scrollTop() + $(window).height() != $(document).height() + 0
  ) {
    $(".floutNews").slideDown();
  } else {
    $(".floutNews").slideUp();
  }

  if ($(window).scrollTop() + $(window).height() != $(document).height() + 0) {
    $(".newsShare").fadeIn();
  } else {
    $(".newsShare").fadeOut();
  }
});
