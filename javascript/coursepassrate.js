var initForm = function(){
      $.ajax({
            type: "POST",
            url: courseUrl + 'view.php?dummy=' + new Date().getTime(),
            async: false,
            success: function(html){
                  $("#selectcourseform").append(html);
                  $('#coursepassrate').empty();
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
                        $('#coursepassrate').empty();
                        clickCaculateAction();
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
                        $('#coursepassrate').empty();
                        clickCaculateAction();
                  }
            });
      });
}

var clickCaculateAction = function(){
      $('#caculaterate').click( function () {
            showLoading();
            $.ajax({
                type: "POST",
                url: courseUrl + 'caculatepassrate.php?dummy=' + new Date().getTime(),
                data: "menucategory=" + $('#menucategory').attr('value') + "&menucourse=" + $('#menucourse').attr('value'),
                async: false,
                success: function(data){
                    closeLoading();
                    $('#coursepassrate').empty();
                    $('#coursepassrate').html(data);
                    clickCaculateAction();
                    selectCategoryAction();
                    selectCourseAction();
                },
                error: function(data){
                    closeLoading();
                }
            });
      });
}

var showLoading = function() {
      $("#loading").css("display","block");
}

var closeLoading = function() {
      $("#loading").css("display","none");
}

$(document).ready( function(){
      initForm();
      selectCategoryAction();
      clickCaculateAction();
});
