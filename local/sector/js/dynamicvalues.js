
$(document).on('change', '#id_sector', function() { 
    
var slecteedsectorid = $(this).find("option:selected").val();
 if (slecteedsectorid !== null) {
Ajax.call([{
methodname: 'local_getsegments',
args: { 'id':slecteedsectorid,
}
}])[0].done(function(response) { var data = JSON.parse(response);

var segments = '<option value=>--Select Segment--</option>';
for(var i=0; i< data.length; i++) {
segments += '<option value = ' + data[i].id + ' >' + data[i].title +'</option>';
}
$("#id_segment").html(faculties);

return; }).fail(function(err)
{ var template = '<option value=>--Select Department--</option>';
$('#id_segment').html(template);
return;
}); 
}
}); 



