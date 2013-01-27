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

$(document).ready(function () {
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
});
