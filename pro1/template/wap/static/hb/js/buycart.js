// 点击编辑切换结算和删除
$('.edit').on('click', function () {
  $('.pay').toggle();
  $('.del').toggle();
});

// 复选框操作
$('.goods input').on('click', function (param) {
  // console.log(this.checked);
  var that = this.checked;
  // $(this).parent().parent().children('ul').find("input[name='checkbox']")[0].checked=this.checked;
  $.each($(this).parent().parent().children('ul').find("input[name='checkbox']"), function (index, val) {
    console.log(val);
    val.checked = that;
  })
})

// 总选
$('.pay input').on('click', function (param) {

})