function UpdateSearchLinks()
{
	var urls = {
		'IMDB':'https://www.imdb.com/find?ref_=nv_sr_fn&q={NAME}&s=all',
		'TV.com':'http://www.tv.com/search?q={NAME}',
		'TVDB':'https://www.thetvdb.com/?string={NAME}&tab=listseries&function=Search'
	};
	
	var name = $('#f-name').val().trim();
	var links = [];
	$.each(urls, function(label, url) {
		url = url.replace('{NAME}', name);
		links.push('<a href="'+url+'">'+label+'</a>');
	});
	
	$('#searchlinks').html('Search for name in: '+links.join(' | '));
}