function UpdateSearchLinks()
{
	const urls = {
		'IMDB':'https://www.imdb.com/find?ref_=nv_sr_fn&q={NAME}&s=all',
		'TVDB':'https://www.thetvdb.com/search?query={NAME}'
	};
	
	const name = $('#f-name').val().trim();
	let html = '';

	if(name.length > 1)
	{
		let links = [];
		$.each(urls, function(label, url) {
			url = url.replace('{NAME}', name);
			links.push('<a href="'+url+'" target="_blank">'+label+'</a>');
		});

		html = 'Search for name in: '+links.join(' | ');
	}

	$('#searchlinks').html(html);
}
