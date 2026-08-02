/**
 * APK Directory Core — Version Manager.
 */
( function () {
	'use strict';

	var config = window.adpVersionManager;
	if ( ! config || ! config.appId ) {
		return;
	}

	var root = document.getElementById( 'adp-version-manager-root' );
	if ( ! root ) {
		return;
	}

	var versions = [];
	var editing = null;
	var formOpen = false;
	var attachmentData = null;
	var hashPreview = null;

	function api( method, path, body ) {
		var opts = {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': config.nonce
			},
			credentials: 'same-origin'
		};
		if ( body ) {
			opts.body = JSON.stringify( body );
		}
		return fetch( config.restBase + path, opts ).then( function ( res ) {
			return res.json().then( function ( data ) {
				if ( ! res.ok ) {
					throw new Error( data.message || 'Request failed' );
				}
				return data;
			} );
		} );
	}

	function load() {
		root.innerHTML = '<p>Loading versions…</p>';
		api( 'GET', '' ).then( function ( data ) {
			versions = data.versions || [];
			render();
		} ).catch( function ( err ) {
			root.innerHTML = '<p class="error">' + err.message + '</p>';
		} );
	}

	function render() {
		var html = '<div class="adp-vm-toolbar"><button type="button" class="button button-primary" id="adp-vm-add">' + config.i18n.add + '</button></div>';

		if ( formOpen ) {
			html += renderForm();
		}

		html += '<table class="widefat adp-vm-table"><thead><tr>';
		html += '<th>Version</th><th>Type</th><th>Size</th><th>SHA-256</th><th>Current</th><th>Actions</th>';
		html += '</tr></thead><tbody>';

		if ( ! versions.length ) {
			html += '<tr><td colspan="6">No versions yet.</td></tr>';
		} else {
			versions.forEach( function ( v ) {
				html += '<tr>';
				html += '<td>' + esc( v.version_name ) + '</td>';
				html += '<td>' + esc( v.download_type ) + '</td>';
				html += '<td>' + formatBytes( v.file_size_bytes ) + '</td>';
				html += '<td class="adp-vm-hash">' + esc( formatHash( v.sha256 ) ) + '</td>';
				html += '<td>' + ( v.is_current ? config.i18n.current : '—' ) + '</td>';
				html += '<td class="adp-vm-actions">';
				html += '<button type="button" class="button button-small" data-action="edit" data-id="' + v.id + '">' + config.i18n.edit + '</button> ';
				html += '<button type="button" class="button button-small" data-action="duplicate" data-id="' + v.id + '">' + config.i18n.duplicate + '</button> ';
				if ( ! v.is_current ) {
					html += '<button type="button" class="button button-small" data-action="current" data-id="' + v.id + '">' + config.i18n.setCurrent + '</button> ';
				}
				html += '<button type="button" class="button button-small" data-action="delete" data-id="' + v.id + '">' + config.i18n.delete + '</button>';
				html += '</td></tr>';
			} );
		}

		html += '</tbody></table>';
		root.innerHTML = html;
		bindEvents();
	}

	function renderForm() {
		var v = editing || {
			version_name: '',
			version_code: '',
			file_size_bytes: 0,
			file_type: 'apk',
			download_type: config.defaultDownloadType || 'media',
			external_url: '',
			changelog: '',
			attachment_id: null,
			sha256: null,
			is_current: false
		};

		if ( editing && editing.attachment_id && ! attachmentData ) {
			attachmentData = {
				id: editing.attachment_id,
				title: '',
				size: editing.file_size_bytes || 0,
				sha256: editing.sha256 || null
			};
		}

		var html = '<div class="adp-vm-form"><h3>' + ( editing ? config.i18n.edit : config.i18n.add ) + '</h3>';
		html += field( 'version_name', 'Version Name', v.version_name );
		html += field( 'version_code', 'Version Code', v.version_code, 'number' );
		html += field( 'file_size_bytes', 'File Size (bytes)', v.file_size_bytes, 'number' );
		html += selectField( 'file_type', 'File Type', v.file_type, config.fileTypes );
		html += selectField( 'download_type', 'Download Type', v.download_type, config.downloadTypes );

		var downloadType = val( 'download_type' ) || v.download_type;
		if ( downloadType === 'media' ) {
			html += renderMediaPicker( v );
		} else {
			html += field( 'external_url', 'External URL', v.external_url || '' );
		}

		html += '<p><label>Changelog<br><textarea id="adp-vm-changelog" rows="3" class="large-text">' + esc( v.changelog || '' ) + '</textarea></label></p>';
		html += '<p><button type="button" class="button button-primary" id="adp-vm-save">' + config.i18n.save + '</button> ';
		html += '<button type="button" class="button" id="adp-vm-cancel">' + config.i18n.cancel + '</button></p></div>';
		return html;
	}

	function renderMediaPicker( v ) {
		var html = '<div class="adp-vm-media" id="adp-vm-media-panel">';
		html += '<p><strong>' + config.i18n.apkFile + '</strong></p>';

		if ( attachmentData && attachmentData.id ) {
			html += '<div class="adp-vm-media__selected">';
			html += '<p><span class="adp-vm-media__title">' + esc( attachmentData.title || ( '#' + attachmentData.id ) ) + '</span></p>';
			html += '<p class="description">' + config.i18n.fileSize + ': ' + formatBytes( attachmentData.size || 0 ) + '</p>';
			html += '<p class="adp-vm-hash"><strong>SHA-256:</strong> <code id="adp-vm-hash-value">' + esc( formatHash( hashPreview || attachmentData.sha256 ) ) + '</code></p>';
			html += '<button type="button" class="button" id="adp-vm-media-change">' + config.i18n.changeFile + '</button> ';
			html += '<button type="button" class="button" id="adp-vm-media-remove">' + config.i18n.removeFile + '</button>';
			html += '<input type="hidden" id="adp-vm-attachment_id" value="' + esc( String( attachmentData.id ) ) + '" />';
			html += '</div>';
		} else {
			html += '<p class="description">' + config.i18n.noFileSelected + '</p>';
			html += '<button type="button" class="button button-secondary" id="adp-vm-media-pick">' + config.i18n.selectFile + '</button>';
			html += '<input type="hidden" id="adp-vm-attachment_id" value="" />';
		}

		html += '</div>';
		return html;
	}

	function field( name, label, value, type ) {
		type = type || 'text';
		return '<p><label>' + label + '<br><input type="' + type + '" id="adp-vm-' + name + '" value="' + esc( String( value ) ) + '" class="regular-text" /></label></p>';
	}

	function selectField( name, label, value, options ) {
		var html = '<p><label>' + label + '<br><select id="adp-vm-' + name + '">';
		options.forEach( function ( opt ) {
			var val = typeof opt === 'string' ? opt : opt.value;
			var lbl = typeof opt === 'string' ? opt : opt.label;
			html += '<option value="' + esc( val ) + '"' + ( val === value ? ' selected' : '' ) + '>' + esc( lbl ) + '</option>';
		} );
		html += '</select></label></p>';
		return html;
	}

	function bindEvents() {
		var addBtn = document.getElementById( 'adp-vm-add' );
		if ( addBtn ) {
			addBtn.addEventListener( 'click', function () {
				editing = null;
				attachmentData = null;
				hashPreview = null;
				formOpen = true;
				render();
			} );
		}

		var saveBtn = document.getElementById( 'adp-vm-save' );
		if ( saveBtn ) {
			saveBtn.addEventListener( 'click', saveForm );
		}

		var cancelBtn = document.getElementById( 'adp-vm-cancel' );
		if ( cancelBtn ) {
			cancelBtn.addEventListener( 'click', function () {
				formOpen = false;
				editing = null;
				attachmentData = null;
				hashPreview = null;
				render();
			} );
		}

		var downloadTypeSelect = document.getElementById( 'adp-vm-download_type' );
		if ( downloadTypeSelect ) {
			downloadTypeSelect.addEventListener( 'change', function () {
				render();
				formOpen = true;
				bindEvents();
			} );
		}

		var pickBtn = document.getElementById( 'adp-vm-media-pick' );
		var changeBtn = document.getElementById( 'adp-vm-media-change' );
		if ( pickBtn ) {
			pickBtn.addEventListener( 'click', openMediaPicker );
		}
		if ( changeBtn ) {
			changeBtn.addEventListener( 'click', openMediaPicker );
		}

		var removeBtn = document.getElementById( 'adp-vm-media-remove' );
		if ( removeBtn ) {
			removeBtn.addEventListener( 'click', function () {
				attachmentData = null;
				hashPreview = null;
				render();
				formOpen = true;
				bindEvents();
			} );
		}

		root.querySelectorAll( '[data-action]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var action = btn.getAttribute( 'data-action' );
				var id = parseInt( btn.getAttribute( 'data-id' ), 10 );
				handleAction( action, id );
			} );
		} );
	}

	function openMediaPicker() {
		if ( typeof wp === 'undefined' || ! wp.media ) {
			alert( config.i18n.mediaUnavailable );
			return;
		}

		var frame = wp.media( {
			title: config.i18n.selectFile,
			button: { text: config.i18n.useFile },
			multiple: false,
			library: {
				type: config.allowedMimeTypes && config.allowedMimeTypes.length ? config.allowedMimeTypes : undefined
			}
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			attachmentData = {
				id: attachment.id,
				title: attachment.title || attachment.filename || '',
				size: attachment.filesizeInBytes || attachment.filesize || 0,
				sha256: null
			};
			hashPreview = null;

			var sizeInput = document.getElementById( 'adp-vm-file_size_bytes' );
			if ( sizeInput && attachmentData.size ) {
				sizeInput.value = String( attachmentData.size );
			}

			render();
			formOpen = true;
			bindEvents();
			fetchAttachmentHash( attachment.id );
		} );

		frame.open();
	}

	function fetchAttachmentHash( attachmentId ) {
		var hashEl = document.getElementById( 'adp-vm-hash-value' );
		if ( hashEl ) {
			hashEl.textContent = config.i18n.computingHash;
		}

		api( 'GET', '/attachment/' + attachmentId + '/hash' ).then( function ( data ) {
			hashPreview = data.hash || null;
			if ( attachmentData ) {
				attachmentData.sha256 = hashPreview;
			}
			var el = document.getElementById( 'adp-vm-hash-value' );
			if ( el ) {
				el.textContent = formatHash( hashPreview );
			}
		} ).catch( function () {
			hashPreview = config.i18n.hashPending;
			var el = document.getElementById( 'adp-vm-hash-value' );
			if ( el ) {
				el.textContent = config.i18n.hashPending;
			}
		} );
	}

	function saveForm() {
		var downloadType = val( 'download_type' );
		var data = {
			version_name: val( 'version_name' ),
			version_code: parseInt( val( 'version_code' ), 10 ) || null,
			file_size_bytes: parseInt( val( 'file_size_bytes' ), 10 ) || 0,
			file_type: val( 'file_type' ),
			download_type: downloadType,
			external_url: val( 'external_url' ),
			changelog: document.getElementById( 'adp-vm-changelog' ).value
		};

		if ( downloadType === 'media' ) {
			var attachmentInput = document.getElementById( 'adp-vm-attachment_id' );
			data.attachment_id = attachmentInput ? parseInt( attachmentInput.value, 10 ) || null : null;
			if ( ! data.attachment_id ) {
				alert( config.i18n.attachmentRequired );
				return;
			}
		}

		var promise;
		if ( editing ) {
			promise = api( 'PUT', '/' + editing.id, data );
		} else {
			promise = api( 'POST', '', data );
		}

		promise.then( function () {
			formOpen = false;
			editing = null;
			attachmentData = null;
			hashPreview = null;
			load();
		} ).catch( function ( err ) {
			alert( err.message );
		} );
	}

	function handleAction( action, id ) {
		if ( action === 'edit' ) {
			editing = versions.find( function ( v ) { return v.id === id; } );
			attachmentData = null;
			hashPreview = null;
			formOpen = true;
			render();
			return;
		}
		if ( action === 'duplicate' ) {
			api( 'POST', '/' + id + '/duplicate', {} ).then( load ).catch( alert );
			return;
		}
		if ( action === 'current' ) {
			api( 'POST', '/' + id + '/set-current', {} ).then( load ).catch( alert );
			return;
		}
		if ( action === 'delete' ) {
			if ( ! confirm( config.i18n.confirmDelete ) ) {
				return;
			}
			var deleteFile = confirm( config.i18n.deleteFile );
			api( 'DELETE', '/' + id + '?delete_attachment=' + ( deleteFile ? '1' : '0' ), null ).then( load ).catch( alert );
		}
	}

	function val( name ) {
		var el = document.getElementById( 'adp-vm-' + name );
		return el ? el.value : '';
	}

	function esc( str ) {
		if ( str === null || str === undefined ) {
			return '';
		}
		var div = document.createElement( 'div' );
		div.textContent = String( str );
		return div.innerHTML;
	}

	function formatBytes( bytes ) {
		if ( ! bytes ) return '0 B';
		var units = [ 'B', 'KB', 'MB', 'GB' ];
		var i = 0;
		while ( bytes >= 1024 && i < units.length - 1 ) {
			bytes /= 1024;
			i++;
		}
		return bytes.toFixed( 1 ) + ' ' + units[ i ];
	}

	function formatHash( hash ) {
		if ( ! hash ) {
			return config.i18n.hashPending;
		}
		return hash;
	}

	load();
} )();
