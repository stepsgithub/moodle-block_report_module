var initForm = function(){
      $.ajax({
            type: "POST",
            url: courseUrl + 'view.php?dummy=' + new Date().getTime(),
            async: false,
            success: function(html){
                  $("#selectcourseform").append(html);
                  $('#setupcoursescoreform').empty();
            }
      });   
}

var selectCategoryAction = function(){
      $('#menucategory').change( function () {
            $.ajax({
                  type: "POST",
                  url: courseUrl + 'view.php?dummy=' + new Date().getTime(),
                  data: "menucategory=" + $('#menucategory').attr('value'),
                  async: false,
                  success: function(data){
                        $('#selectcourseform').html(data);
                        selectCategoryAction();
                        selectCourseAction();
                        $('#setupcoursescoreform').empty();
                  }
            });
      });
}

var selectCourseAction = function(){
      $('#menucourse').change( function () {
            $.ajax({
                  type: "POST",
                  url: courseUrl + 'view.php?dummy=' + new Date().getTime(),
                  data: "menucourse=" + $('#menucourse').attr('value') + "&menucategory=" + $('#menucategory').attr('value'),
                  async: false,
                  success: function(data){
                        $('#selectcourseform').html(data);
                        selectCategoryAction();
                        selectCourseAction();
                        setupCourseScoreAction();
                  }
            });
      });
}

var setupCourseScoreAction = function(){
      $.ajax({
            type: "POST",
            url: courseUrl + 'setupscore.php?dummy=' + new Date().getTime(),
            data: "menuaction=setupscore&menucourse=" + $('#menucourse').attr('value'),
            async: false,
            success: function(data){
                  $('#setupcoursescoreform').html(data);
            }
      });
}

$(document).ready( function(){
      initForm();
      selectCategoryAction();
});
