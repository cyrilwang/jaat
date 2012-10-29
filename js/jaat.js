function formatDate(d) {
  var date_str=('0'+d.getFullYear()).substr(-2,2)+('0'+d.getMonth()).substr(-2,2)+('0'+d.getDate()).substr(-2,2)+'-'+('0'+d.getHours()).substr(-2,2)+('0'+d.getMinutes()).substr(-2,2)+('0'+d.getSeconds()).substr(-2,2);
  return date_str;
}

function get_formatted_date(date_obj) {
	var date_str = date_obj.getFullYear()+'-'+('0'+date_obj.getMonth()).substr(-2,2)+'-'+('0'+date_obj.getDate()).substr(-2,2);
	date_str += ' '+('0'+date_obj.getHours()).substr(-2,2)+':'+('0'+date_obj.getMinutes()).substr(-2,2)+':'+('0'+date_obj.getSeconds()).substr(-2,2);
	return date_str;
}