/**
 * APK Directory Core — Gutenberg editor panels.
 */
( function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.plugins || ! adpEditorPanels ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var createElement = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Button = wp.components.Button;
	var Fragment = wp.element.Fragment;
	var apiFetch = wp.apiFetch;

	var META_FIELDS = [
		{ key: '_adp_short_description', label: 'Short Description', component: 'textarea' },
		{ key: '_adp_package_name', label: 'Package Name', component: 'text' },
		{ key: '_adp_current_version', label: 'Current Version', component: 'text' },
		{ key: '_adp_android_requirement', label: 'Android Requirement', component: 'text' },
		{ key: '_adp_official_url', label: 'Official URL', component: 'text' },
		{ key: '_adp_store_url', label: 'Store URL', component: 'text' },
		{ key: '_adp_support_url', label: 'Support URL', component: 'text' },
		{ key: '_adp_privacy_url', label: 'Privacy URL', component: 'text' },
		{ key: '_adp_content_rating', label: 'Content Rating', component: 'text' },
		{ key: '_adp_disclaimer_note', label: 'Disclaimer Note', component: 'textarea' },
		{ key: '_adp_verified', label: 'Verified', component: 'toggle' },
		{ key: '_adp_status_badge', label: 'Status Badge', component: 'select', options: [
			{ label: 'None', value: 'none' },
			{ label: 'New', value: 'new' },
			{ label: 'Updated', value: 'updated' },
			{ label: 'MOD', value: 'mod' }
		]},
		{ key: '_adp_price_type', label: 'Price Type', component: 'select', options: [
			{ label: 'Free', value: 'free' },
			{ label: 'Paid', value: 'paid' },
			{ label: 'Freemium', value: 'freemium' }
		]}
	];

	function MetaField( props ) {
		var field = props.field;
		var value = props.value || '';
		var onChange = props.onChange;

		if ( field.component === 'textarea' ) {
			return createElement( TextareaControl, {
				label: field.label,
				value: value,
				onChange: onChange
			} );
		}
		if ( field.component === 'toggle' ) {
			return createElement( ToggleControl, {
				label: field.label,
				checked: !! value,
				onChange: function ( checked ) { onChange( checked ); }
			} );
		}
		if ( field.component === 'select' ) {
			return createElement( SelectControl, {
				label: field.label,
				value: value || field.options[0].value,
				options: field.options,
				onChange: onChange
			} );
		}
		return createElement( TextControl, {
			label: field.label,
			value: value,
			onChange: onChange
		} );
	}

	function ScreenshotGallery( props ) {
		var ids = Array.isArray( props.value ) ? props.value.slice() : [];
		var onChange = props.onChange;
		var previews = useState( [] );
		var previewState = previews[0];
		var setPreviews = previews[1];
		var dragIndex = useState( null );
		var setDragIndex = dragIndex[1];

		useEffect( function () {
			if ( ! ids.length ) {
				setPreviews( [] );
				return;
			}
			var cancelled = false;
			Promise.all(
				ids.map( function ( id ) {
					return apiFetch( { path: '/wp/v2/media/' + id } ).then( function ( media ) {
						return {
							id: id,
							url: media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail
								? media.media_details.sizes.thumbnail.source_url
								: media.source_url,
							title: media.title && media.title.rendered ? media.title.rendered : ''
						};
					} ).catch( function () {
						return { id: id, url: '', title: '' };
					} );
				} )
			).then( function ( items ) {
				if ( ! cancelled ) {
					setPreviews( items );
				}
			} );
			return function () { cancelled = true; };
		}, [ ids.join( ',' ) ] );

		function openMedia() {
			if ( typeof wp.media === 'undefined' ) {
				return;
			}
			var frame = wp.media( {
				title: adpEditorPanels.i18n.selectScreenshots,
				button: { text: adpEditorPanels.i18n.addScreenshots },
				multiple: true,
				library: { type: 'image' }
			} );
			frame.on( 'select', function () {
				var selection = frame.state().get( 'selection' );
				var newIds = selection.map( function ( attachment ) {
					return attachment.get( 'id' );
				} );
				var merged = ids.slice();
				newIds.forEach( function ( id ) {
					if ( merged.indexOf( id ) === -1 ) {
						merged.push( id );
					}
				} );
				onChange( merged );
			} );
			frame.open();
		}

		function removeAt( index ) {
			var next = ids.slice();
			next.splice( index, 1 );
			onChange( next );
		}

		function move( index, direction ) {
			var target = index + direction;
			if ( target < 0 || target >= ids.length ) {
				return;
			}
			var next = ids.slice();
			var temp = next[ index ];
			next[ index ] = next[ target ];
			next[ target ] = temp;
			onChange( next );
		}

		function onDragStart( index ) {
			setDragIndex( index );
		}

		function onDragOver( event, index ) {
			event.preventDefault();
			var from = dragIndex;
			if ( from === null || from === index ) {
				return;
			}
			var next = ids.slice();
			var item = next.splice( from, 1 )[0];
			next.splice( index, 0, item );
			setDragIndex( index );
			onChange( next );
		}

		return createElement(
			'div',
			{ className: 'adp-screenshot-gallery' },
			createElement(
				'div',
				{ className: 'adp-screenshot-gallery__toolbar' },
				createElement( Button, {
					variant: 'secondary',
					onClick: openMedia
				}, adpEditorPanels.i18n.addScreenshots )
			),
			! ids.length
				? createElement( 'p', { className: 'adp-screenshot-gallery__empty' }, adpEditorPanels.i18n.noScreenshots )
				: createElement(
					'ul',
					{ className: 'adp-screenshot-gallery__list' },
					ids.map( function ( id, index ) {
						var preview = previewState.find( function ( item ) { return item.id === id; } );
						return createElement(
							'li',
							{
								key: id,
								className: 'adp-screenshot-gallery__item',
								draggable: true,
								onDragStart: function () { onDragStart( index ); },
								onDragOver: function ( event ) { onDragOver( event, index ); },
								onDragEnd: function () { setDragIndex( null ); }
							},
							preview && preview.url
								? createElement( 'img', {
									src: preview.url,
									alt: preview.title || '',
									className: 'adp-screenshot-gallery__thumb'
								} )
								: createElement( 'span', { className: 'adp-screenshot-gallery__placeholder' }, '#' + id ),
							createElement(
								'div',
								{ className: 'adp-screenshot-gallery__actions' },
								createElement( Button, {
									icon: 'arrow-up-alt2',
									label: adpEditorPanels.i18n.moveUp,
									onClick: function () { move( index, -1 ); },
									disabled: index === 0
								} ),
								createElement( Button, {
									icon: 'arrow-down-alt2',
									label: adpEditorPanels.i18n.moveDown,
									onClick: function () { move( index, 1 ); },
									disabled: index === ids.length - 1
								} ),
								createElement( Button, {
									icon: 'no-alt',
									label: adpEditorPanels.i18n.remove,
									isDestructive: true,
									onClick: function () { removeAt( index ); }
								} )
							)
						);
					} )
				)
		);
	}

	function AppDetailsPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		function updateMeta( key, value ) {
			var newMeta = Object.assign( {}, meta );
			newMeta[ key ] = value;
			editPost( { meta: newMeta } );
		}

		return createElement(
			Fragment,
			null,
			createElement(
				PluginDocumentSettingPanel,
				{ name: 'adp-app-details', title: 'App Details', className: 'adp-editor-panel' },
				META_FIELDS.map( function ( field ) {
					return createElement( MetaField, {
						key: field.key,
						field: field,
						value: meta[ field.key ],
						onChange: function ( val ) { updateMeta( field.key, val ); }
					} );
				} )
			),
			createElement(
				PluginDocumentSettingPanel,
				{ name: 'adp-screenshots', title: 'Screenshots', className: 'adp-editor-panel' },
				createElement( ScreenshotGallery, {
					value: meta._adp_screenshot_ids || [],
					onChange: function ( val ) { updateMeta( '_adp_screenshot_ids', val ); }
				} )
			),
			createElement(
				PluginDocumentSettingPanel,
				{ name: 'adp-whats-new', title: "What's New", className: 'adp-editor-panel' },
				createElement( TextareaControl, {
					label: "What's New (changelog)",
					value: meta._adp_whats_new || '',
					onChange: function ( val ) { updateMeta( '_adp_whats_new', val ); }
				} )
			)
		);
	}

	registerPlugin( 'adp-app-panels', {
		render: AppDetailsPanel,
		icon: 'smartphone'
	} );
} )();
