/**
 * Classic editor screenshot gallery metabox.
 */
( function ( $ ) {
	'use strict';

	var $root = $( '#adp-metabox-screenshots' );
	if ( ! $root.length ) {
		return;
	}

	var $list = $( '#adp-metabox-screenshots-list' );

	function refreshOrder() {
		$list.find( '.adp-screenshot-gallery__item' ).each( function ( index ) {
			var $item = $( this );
			$item.find( '.adp-screenshot-up' ).prop( 'disabled', index === 0 );
			$item.find( '.adp-screenshot-down' ).prop( 'disabled', index === $list.children().length - 1 );
		} );
	}

	function addItem( id, thumb, title ) {
		var $item = $( '<li class="adp-screenshot-gallery__item" draggable="true"></li>' ).attr( 'data-id', id );
		if ( thumb ) {
			$item.append( $( '<img class="adp-screenshot-gallery__thumb" alt="" />' ).attr( 'src', thumb ) );
		} else {
			$item.append( $( '<span class="adp-screenshot-gallery__placeholder"></span>' ).text( '#' + id ) );
		}
		$item.append( $( '<span class="screen-reader-text"></span>' ).text( title || '' ) );
		var $actions = $( '<div class="adp-screenshot-gallery__actions"></div>' );
		$actions.append( '<button type="button" class="button button-small adp-screenshot-up">&uarr;</button>' );
		$actions.append( '<button type="button" class="button button-small adp-screenshot-down">&darr;</button>' );
		$actions.append( '<button type="button" class="button button-small adp-screenshot-remove">&times;</button>' );
		$item.append( $actions );
		$item.append( $( '<input type="hidden" name="_adp_screenshot_ids[]" />' ).val( id ) );
		$list.append( $item );
		refreshOrder();
	}

	$( '#adp-metabox-screenshots-add' ).on( 'click', function () {
		var frame = wp.media( {
			title: 'Select Screenshots',
			button: { text: 'Add Screenshots' },
			multiple: true,
			library: { type: 'image' }
		} );
		frame.on( 'select', function () {
			frame.state().get( 'selection' ).each( function ( attachment ) {
				var data = attachment.toJSON();
				if ( $list.find( '[data-id="' + data.id + '"]' ).length ) {
					return;
				}
				var thumb = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
				addItem( data.id, thumb, data.title );
			} );
		} );
		frame.open();
	} );

	$list.on( 'click', '.adp-screenshot-remove', function () {
		$( this ).closest( '.adp-screenshot-gallery__item' ).remove();
		refreshOrder();
	} );

	$list.on( 'click', '.adp-screenshot-up', function () {
		var $item = $( this ).closest( '.adp-screenshot-gallery__item' );
		$item.prev().before( $item );
		refreshOrder();
	} );

	$list.on( 'click', '.adp-screenshot-down', function () {
		var $item = $( this ).closest( '.adp-screenshot-gallery__item' );
		$item.next().after( $item );
		refreshOrder();
	} );

	var dragItem = null;
	$list.on( 'dragstart', '.adp-screenshot-gallery__item', function () {
		dragItem = this;
	} );
	$list.on( 'dragover', '.adp-screenshot-gallery__item', function ( event ) {
		event.preventDefault();
		if ( dragItem && dragItem !== this ) {
			if ( $( dragItem ).index() < $( this ).index() ) {
				$( this ).after( dragItem );
			} else {
				$( this ).before( dragItem );
			}
		}
	} );
	$list.on( 'dragend', function () {
		dragItem = null;
		refreshOrder();
	} );

	refreshOrder();
}( jQuery ) );
