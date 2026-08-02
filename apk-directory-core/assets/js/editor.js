( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.element ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var PanelRow = wp.components.PanelRow;

	var META_FIELDS = [
		{ key: 'short_description', label: 'Short description', control: 'textarea' },
		{ key: 'package_name', label: 'Package name', control: 'text' },
		{ key: 'current_version', label: 'Current version', control: 'text' },
		{ key: 'version_code', label: 'Version code', control: 'number' },
		{ key: 'file_size_bytes', label: 'File size (bytes)', control: 'number' },
		{ key: 'android_requirement', label: 'Android requirement', control: 'text' },
		{ key: 'release_date', label: 'Release date (YYYY-MM-DD)', control: 'text' },
		{ key: 'updated_date', label: 'Updated date (YYYY-MM-DD)', control: 'text' },
		{ key: 'price_type', label: 'Price type', control: 'select', options: [
			{ label: 'Free', value: 'free' },
			{ label: 'Paid', value: 'paid' },
			{ label: 'Freemium', value: 'freemium' }
		] },
		{ key: 'price_amount', label: 'Price amount', control: 'number' },
		{ key: 'price_currency', label: 'Price currency', control: 'text' },
		{ key: 'official_url', label: 'Official URL', control: 'url' },
		{ key: 'store_url', label: 'Store URL', control: 'url' },
		{ key: 'support_url', label: 'Support URL', control: 'url' },
		{ key: 'privacy_url', label: 'Privacy URL', control: 'url' },
		{ key: 'license', label: 'License', control: 'text' },
		{ key: 'content_rating', label: 'Content rating', control: 'text' },
		{ key: 'status_badge', label: 'Status badge', control: 'select', options: [
			{ label: 'None', value: 'none' },
			{ label: 'New', value: 'new' },
			{ label: 'Updated', value: 'updated' },
			{ label: 'MOD', value: 'mod' }
		] },
		{ key: 'editor_rating', label: 'Editor rating (0-5)', control: 'number' },
		{ key: 'video_url', label: 'Video URL', control: 'url' },
		{ key: 'disclaimer_note', label: 'Disclaimer note', control: 'text' }
	];

	var HTML_FIELDS = [
		{ key: 'whats_new', label: "What's new" },
		{ key: 'mod_features', label: 'MOD features' }
	];

	function metaKey( key ) {
		return '_adp_' + key;
	}

	function MetaField( props ) {
		var field = props.field;
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;
		var value = meta[ metaKey( field.key ) ] || '';

		function onChange( newVal ) {
			editPost( { meta: { [ metaKey( field.key ) ]: newVal } } );
		}

		if ( field.control === 'textarea' ) {
			return el( TextareaControl, {
				label: field.label,
				value: value,
				onChange: onChange
			} );
		}
		if ( field.control === 'select' ) {
			return el( SelectControl, {
				label: field.label,
				value: value || field.options[ 0 ].value,
				options: field.options,
				onChange: onChange
			} );
		}
		return el( TextControl, {
			label: field.label,
			type: field.control === 'number' ? 'number' : 'text',
			value: value,
			onChange: onChange
		} );
	}

	function HtmlMetaField( props ) {
		var field = props.field;
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;
		var value = meta[ metaKey( field.key ) ] || '';

		return el( TextareaControl, {
			label: field.label,
			value: value,
			onChange: function ( v ) {
				editPost( { meta: { [ metaKey( field.key ) ]: v } } );
			}
		} );
	}

	function AppDetailsPanel() {
		return el(
			PluginDocumentSettingPanel,
			{ name: 'adp-app-details', title: 'App Details', className: 'adp-editor-panel' },
			META_FIELDS.slice( 0, 6 ).map( function ( f ) {
				return el( PanelRow, { key: f.key }, el( MetaField, { field: f } ) );
			} )
		);
	}

	function PricingPanel() {
		return el(
			PluginDocumentSettingPanel,
			{ name: 'adp-pricing', title: 'Pricing & URLs', className: 'adp-editor-panel' },
			META_FIELDS.slice( 6, 14 ).map( function ( f ) {
				return el( PanelRow, { key: f.key }, el( MetaField, { field: f } ) );
			} )
		);
	}

	function SafetyPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		return el(
			PluginDocumentSettingPanel,
			{ name: 'adp-safety', title: 'Safety & Display', className: 'adp-editor-panel' },
			el( PanelRow, null, el( MetaField, { field: META_FIELDS[ 14 ] } ) ),
			el( PanelRow, null, el( MetaField, { field: META_FIELDS[ 15 ] } ) ),
			el( PanelRow, null, el( MetaField, { field: META_FIELDS[ 16 ] } ) ),
			el( PanelRow, null, el( MetaField, { field: META_FIELDS[ 17 ] } ) ),
			el( ToggleControl, {
				label: 'Verified record',
				checked: !! meta[ metaKey( 'verified' ) ],
				onChange: function ( v ) {
					editPost( { meta: { [ metaKey( 'verified' ) ]: v } } );
				}
			} ),
			el( ToggleControl, {
				label: 'Disable table of contents',
				checked: !! meta[ metaKey( 'disable_toc' ) ],
				onChange: function ( v ) {
					editPost( { meta: { [ metaKey( 'disable_toc' ) ]: v } } );
				}
			} )
		);
	}

	function ContentPanel() {
		return el(
			PluginDocumentSettingPanel,
			{ name: 'adp-content', title: "What's New & MOD", className: 'adp-editor-panel' },
			HTML_FIELDS.map( function ( f ) {
				return el( PanelRow, { key: f.key }, el( HtmlMetaField, { field: f } ) );
			} )
		);
	}

	function VideoPanel() {
		return el(
			PluginDocumentSettingPanel,
			{ name: 'adp-video', title: 'Video', className: 'adp-editor-panel' },
			el( PanelRow, null, el( MetaField, { field: META_FIELDS[ 16 ] } ) )
		);
	}

	function AppEditorPanels() {
		return el(
			Fragment,
			null,
			el( AppDetailsPanel ),
			el( PricingPanel ),
			el( VideoPanel ),
			el( ContentPanel ),
			el( SafetyPanel )
		);
	}

	registerPlugin( 'adp-app-editor', {
		render: AppEditorPanels,
		icon: 'smartphone'
	} );
} )( window.wp );
