jQuery('.done').on('click', function(){
  var data = {id: this.id};
  jQuery.ajax({
    url: '/task/done',
    type: 'POST',
    data: data,
    success: function (data) {
      alert(data.message);
      if (data.status_sucesso) {
        jQuery('.label-done span')
          .removeAttr('class')
          .addClass('label label-success')
          .text('')
          .text(data.status);
      }
    },
    error: function (data) {
      console.log(data);
    }
  });
});

jQuery('.submeter').on('click', function(){
  var data = {id: this.id};
  jQuery.ajax({
    url: '/task/submeter',
    type: 'POST',
    data: data,
    success: function (data) {
      alert(data.message);
      if (data.status_sucesso) {
        jQuery('.label-done span')
          .removeAttr('class')
          .addClass('label label-warning')
          .text('')
          .text(data.status);
      }
    },
    error: function (data) {
      alert(data);
    }
  });
});