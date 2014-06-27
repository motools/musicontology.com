// ----------------------------- Filters

function show_html_examples() {
  $.each($(".mo-html-example"), function(i, example) {
    $(example).show();
  });
}

function show_json_examples() {
  $.each($(".mo-json-example"), function(i, example) {
    $(example).show();
  });
}

function show_turtle_examples() {
  $.each($(".mo-turtle-example"), function(i, example) {
    $(example).show();
  });
}

function hide_html_examples() {
  $.each($(".mo-html-example"), function(i, example) {
    $(example).hide();
  });
}

function hide_json_examples() {
  $.each($(".mo-json-example"), function(i, example) {
    $(example).hide();
  });
}

function hide_turtle_examples() {
  $.each($(".mo-turtle-example"), function(i, example) {
    $(example).hide();
  });
}

function hide_formats() {
  $.each($(".mo-format"), function (i, format) {
    $(format).hide();
  });
}


// ----------------------------- Random BG image

function bgChange()
{
  // FOR THE WEB
var images = [        
            {image:'/img/bg1.jpg', author: 'Theilr'},
            {image:'/img/bg2.jpg', author: 'Pablo Garcia'},
            {image:'/img/bg3.jpg', author: 'Mark Ingle'},
            {image:'/img/bg4.jpg', author: 'Julia Folsom'},
            {image:'/img/bg5.jpg', author: 'Claudio Matsuoka'},
            {image:'/img/bg6.jpg', author: 'Charles Thompson'},
];

var randomImage = images[Math.floor(Math.random() * images.length)]


if(typeof(Storage)!=="undefined")
  {
  if (sessionStorage.bgid)
    {
      $('html').css({
        'background-image': 'url(' + sessionStorage.bgid + ')'
      });
      $('.author').text(sessionStorage.bgauthor);
    }
  else
    {
      sessionStorage.bgid=randomImage.image;
      sessionStorage.bgauthor=randomImage.author;
      $('html').css({
        'background-image': 'url(' + sessionStorage.bgid + ')'
      });
      $('.author').text(sessionStorage.bgauthor);
    }
  }
else
  {
    $('html').css({
      'background-image': 'url(../img/bg5.jpg)'
    });
    $('.author').text('Claudio Matsuoka');
  }
}

// ------------------------------------------------------------------------------------------

$(document).ready(function () {

bgChange();


//Filters
$(".format-html").click(function() {
    hide_formats();
    show_html_examples();
    hide_json_examples();
    hide_turtle_examples();
  });
  $(".format-json").click(function() {
    hide_formats();
    show_json_examples();
    hide_html_examples();
    hide_turtle_examples();
  });
  $(".format-turtle").click(function() {
    hide_formats();
    show_turtle_examples();
    hide_json_examples();
    hide_html_examples();
  });

// Sliding nav

$('#magic-line').width($('.active').width());
$('#magic-line').css('left', $('.active').position().left);

$(window).resize(function(){
  $('#magic-line').width($('.active').width());
  $('#magic-line').css('left', $('.active').position().left);
})

$(".navbar-links ul li").hover(function(){
    $('#magic-line').width($(this).width());
    $('#magic-line').css('left', $(this).position().left);
    $('#magic-line').addClass('animate');
});
$(".navbar-links").mouseleave(function(){
    $('#magic-line').width($('.active').width());
    $('#magic-line').css('left', $('.active').position().left);
});

// Collapsed nav
$('.navtoggle').click(function(){
  $('.navbar-links').toggleClass('expanded');
});

//Anchor scroll
$(function() {
  $('a[href*=#]:not([href=#])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      location.hash = this.hash;
      if (target.length) {
        $('html,body').animate({
          scrollTop: target.offset().top
        }, 400);
        return false;
      }
    }
  });
});

  // /////////////////////////////
});
