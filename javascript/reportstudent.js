var initForm = function(){
      $.ajax({
            type: "POST",
            url: studentUrl + 'view.php?dummy=' + new Date().getTime() + '&menuuser=' + userID,
            async: false,
            success: function(html){
                  $("#selectcourseform").append(html);
                  $('#checkcoursescoreform').empty();
            }
      });   
}

var selectCourseAction = function(){
      $('#menucourse').change( function () {
            $.ajax({
                  type: "POST",
                  url: studentUrl + 'view.php?dummy=' + new Date().getTime(),
                  data: "menucourse=" + $('#menucourse').attr('value') + "&menucategory=" + $('#menucategory').attr('value') + '&menuuser=' + userID,
                  async: false,
                  success: function(data){
                        $('#selectcourseform').html(data);
                        selectCourseAction();
                        checkCourseScoreAction();
                  }
            });
      });
}

var checkCourseScoreAction = function(){
      $.ajax({
            type: "POST",
            url: studentUrl + 'checkscore.php?dummy=' + new Date().getTime(),
            data: "menuaction=checkscore&menucourse=" + $('#menucourse').attr('value'),
            async: false,
            success: function(data){
                  $('#checkcoursescoreform').html(data);
            }
      });
}

$(document).ready( function(){
      initForm();
      selectCourseAction();
});
