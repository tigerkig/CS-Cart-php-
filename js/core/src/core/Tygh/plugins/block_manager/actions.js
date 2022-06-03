import { params } from './params';
import { api } from './api';
import { animation } from './animation';
import $ from "jquery";

export const actions = {
    _snapBlocks: function (block) {
        var snapping = {};
        var blocks = block.parent().find(params.block_selector);

        blocks.each(function () {
            var _block = $(this);
            var index = _block.index();
            
            snapping[index] = {
                grid_id: _block.closest(params.grid_selector).data('caBlockManagerGridId'),
                order: index,
                snapping_id: _block.data('caBlockManagerSnappingId'),
                action: 'update',
            };
        });

        return snapping;
    },

    _executeAction: function (action) {
        var execute_result = false;

        if (action == 'switch') {
            execute_result = actions._blockSwitch();
        } else if (action == 'move') {
            execute_result = actions._blockMove();
        }

        return execute_result;
    },

    _blockSwitch: function () {
        var status = (params._self.data('caBlockManagerSwitch')) ? 'A' : 'D';
        var dynamic_object = 0;
        var switch_show_icon = params._self.find(params.switch_icon_show_selector);
        var switch_hide_icon = params._self.find(params.switch_icon_hide_selector);
        
        var data = {
            snapping_id: params._hover_element.data('caBlockManagerSnappingId'),
            object_id: dynamic_object,
            object_type: '',
            status: status,
            type: 'block'
        };

        api.sendRequest('update_status', '', data);

        if (status === 'A') {
            params._self.removeClass(params.block_disabled_class);
            params._hover_element.removeClass(params.block_disabled_class);
            params._self.data('caBlockManagerSwitch', false);
            switch_hide_icon.addClass(params.switch_icon_hidden_class);
            switch_show_icon.removeClass(params.switch_icon_hidden_class);
        } else {
            params._self.addClass(params.block_disabled_class);
            params._hover_element.addClass(params.block_disabled_class);
            params._self.data('caBlockManagerSwitch', true);
            switch_show_icon.addClass(params.switch_icon_hidden_class);
            switch_hide_icon.removeClass(params.switch_icon_hidden_class);
        }

        return true;
    },

    _blockMove: function () {
        var direction = params._self.data('caBlockManagerMove');
        var $elem = params._hover_element;
        var $nearElem = actions._getNearElem($elem, direction);

        // Do not move the block because it has reached the container edge
        if ($.isEmptyObject($nearElem)) {
            return true;
        }

        // Get the tactics for moving the block
        var moveType = actions._getBlockMovingTactics($elem, $nearElem, direction);

        // Set block floats
        actions._setBlockFloats($elem, $nearElem);

        // Move block
        $elem[moveType]($nearElem);

        // Update block
        actions._updateBlock($elem);

        // Animate the block movement
        animation[direction]();

        // Send a request for a new block position
        api.sendRequest('snapping', '', {
            snappings: actions._snapBlocks(params._hover_element)
        });

        return true;
    },

    _updateBlock: function ($block) {
        // Trigger event to update block
        $(window).trigger('resize');

        // Update image galery
        $block.find('.cm-image-gallery').each(function () {
            $(this).data('owlCarousel').reinit();
        });

        return true;
    },

    _setMenuPosition: function ($block) {
        var isBottomBlock = $block.offset().top < params.offset_threshold;

        $block.find(params.menu_wrapper_selector)
            .toggleClass(params.block_menu_wrapper_bottom_class, isBottomBlock);
        $block.find(params.menu_selector)
            .toggleClass( params.block_menu_bottom_class, isBottomBlock);

        return true;
    },

    _setBlockFloats: function ($block, $nearElem) {
        var isLeftAlign = ($nearElem.closest(params.grid_selector).filter(params.left_alignment_selector).length > 0);
        var isRightAlign = ($nearElem.closest(params.grid_selector).filter(params.right_alignment_selector).length > 0);

        $block.toggleClass(params.float_left_class, ((isLeftAlign && isRightAlign) || isLeftAlign));
        $block.toggleClass(params.float_right_class, ((isLeftAlign && isRightAlign) || isRightAlign));

        return true;
    },

    _getBlockMovingTactics: function ($elem, $nearElem, direction) {
        var isUpDirection = (direction === 'up');
        var isFirstElem = ($elem.prevAll(params.block_selector).first().length === 0);
        var isLastElem = ($elem.nextAll(params.block_selector).first().length === 0);
        var isBlocksPlaceElem = $nearElem.is(params.blocks_place_selector);
        var moveType = '';

        if (isBlocksPlaceElem && isUpDirection && isFirstElem) {
            moveType = 'appendTo';
        } else if (isBlocksPlaceElem && !isUpDirection && isLastElem) {
            moveType = 'prependTo';
        } else if ((isUpDirection && isFirstElem) || (!isUpDirection && !isLastElem)) {
            moveType = 'insertAfter';
        } else {
            moveType = 'insertBefore';
        }

        return moveType;
    },

    _getNearElem: function ($elem, direction, isDeepSearch) {
        var isUpDirection = (direction === 'up');
        var $nearElem = {};
        var isGridOrRowElem = false;
        var nearSelector = params.container_selector;
        var parentSelector = '';

        if ($elem.is(params.blocks_place_selector + ':not(' + params.grid_selector + ')')) {
            console.log('BLOCKS PLACE STOP');
        }

        // Set parameters:
        // by element type
        if ($elem.is(params.block_selector)) {
            nearSelector = params.block_selector;
            parentSelector = params.grid_selector;
        } else if ($elem.is(params.grid_selector)) {
            isGridOrRowElem = true;
            nearSelector = params.grid_selector;
            parentSelector = params.row_selector;
        } else if ($elem.is(params.row_selector) && $elem.closest(params.grid_selector).length) {
            isGridOrRowElem = true;
            nearSelector = params.row_selector;
            parentSelector = params.grid_selector;
        } else if ($elem.is(params.row_selector)) {
            isGridOrRowElem = true;
            nearSelector = params.row_selector;
            parentSelector = params.container_selector;
        }
        
        // by direction
        var getNearElem = isUpDirection ? 'prevAll' : 'nextAll';
        var getEdgeElem = isUpDirection ? 'last' : 'first';
        var $followingElem = $elem[getNearElem](nearSelector).first();
        
        // by deep search
        var $inspectElem = isDeepSearch ? $elem : $followingElem;
        
        var isSiblingOrDeepElem = (isDeepSearch || ($followingElem.length > 0));
        var $gridInInspectElem = $inspectElem.find(params.grid_selector);
        var hasGridInInspectedElem = ($gridInInspectElem.length > 0);
        var $blocksPlaceInInspectElem = $inspectElem.find(params.blocks_place_selector);
        var hasBlocksPlaceInInspectedElem = ($blocksPlaceInInspectElem.length > 0);
        // /Set parameters


        // Get near element
        if (isSiblingOrDeepElem && isGridOrRowElem && hasGridInInspectedElem) {
            $nearElem = actions._getNearElem($gridInInspectElem[getEdgeElem](), direction, true);
        } else if (hasBlocksPlaceInInspectedElem) {
            $nearElem = $blocksPlaceInInspectElem;
        } else if (isSiblingOrDeepElem) {
            $nearElem = $inspectElem;
        } else {
            $nearElem = parentSelector ? actions._getNearElem($elem.closest(parentSelector), direction) : {};
        }

        return $nearElem;
    }
};
