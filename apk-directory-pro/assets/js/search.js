(function () {
	'use strict';

	var input = document.getElementById('adp-search-input');
	var list = document.getElementById('adp-search-suggestions');
	if (!input || !list || !window.adpSearch) return;

	var debounceTimer = null;
	var abortController = null;
	var activeIndex = -1;

	function escapeHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	function highlight(text, query) {
		var re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
		return escapeHtml(text).replace(re, '<mark>$1</mark>');
	}

	function clearSuggestions() {
		list.innerHTML = '';
		list.hidden = true;
		input.setAttribute('aria-expanded', 'false');
		activeIndex = -1;
	}

	function renderResults(results, query) {
		list.innerHTML = '';
		if (!results.length) {
			clearSuggestions();
			return;
		}
		results.forEach(function (item, i) {
			var li = document.createElement('li');
			var a = document.createElement('a');
			a.href = item.url;
			a.id = 'adp-suggestion-' + i;
			a.setAttribute('role', 'option');
			var label = item.type === 'post' ? 'Article' : 'App';
			a.innerHTML = '<strong>' + highlight(item.title, query) + '</strong> <span class="adp-search-type">' + label + '</span>';
			if (item.developer) {
				a.innerHTML += '<br><small>' + escapeHtml(item.developer) + '</small>';
			}
			li.appendChild(a);
			list.appendChild(li);
		});
		list.hidden = false;
		input.setAttribute('aria-expanded', 'true');
	}

	function fetchSuggestions(query) {
		if (abortController) abortController.abort();
		abortController = new AbortController();

		fetch(adpSearch.restUrl + '?q=' + encodeURIComponent(query), {
			signal: abortController.signal,
			headers: { 'Accept': 'application/json' }
		})
			.then(function (res) {
				if (!res.ok) throw new Error('search failed');
				return res.json();
			})
			.then(function (data) {
				renderResults(data.results || [], query);
			})
			.catch(function () {});
	}

	input.addEventListener('input', function () {
		var q = input.value.trim();
		clearTimeout(debounceTimer);
		if (q.length < 2) {
			clearSuggestions();
			return;
		}
		debounceTimer = setTimeout(function () {
			fetchSuggestions(q);
		}, 250);
	});

	input.addEventListener('keydown', function (e) {
		var items = list.querySelectorAll('a');
		if (!items.length) return;

		if (e.key === 'ArrowDown') {
			e.preventDefault();
			activeIndex = Math.min(activeIndex + 1, items.length - 1);
			items[activeIndex].focus();
			items.forEach(function (el, i) {
				el.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
			});
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			activeIndex = Math.max(activeIndex - 1, 0);
			items[activeIndex].focus();
		} else if (e.key === 'Escape') {
			clearSuggestions();
		}
	});

	document.addEventListener('click', function (e) {
		if (!input.contains(e.target) && !list.contains(e.target)) {
			clearSuggestions();
		}
	});
})();
