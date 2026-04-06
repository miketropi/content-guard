/* global WPCGAdmin, wp */
(function ($) {
  'use strict';

  // ─── Tab switching ────────────────────────────────────────────────────────
  $('.cogu-tab-btn').on('click', function () {
    var target = $(this).data('tab');
    $('.cogu-tab-btn').removeClass('is-active').attr('aria-selected', 'false');
    $('.cogu-tab-panel').attr('hidden', true);
    $(this).addClass('is-active').attr('aria-selected', 'true');
    $('#cogu-tab-' + target).removeAttr('hidden');
  });

  // ─── Ad type cards ────────────────────────────────────────────────────────
  function updateAdConfig(type) {
    $('.cogu-ad-type-card').removeClass('is-selected');
    $('.cogu-ad-type-radio[value="' + type + '"]').closest('.cogu-ad-type-card').addClass('is-selected');
    $('.cogu-ad-config').attr('hidden', true);
    $('.cogu-ad-config[data-ad-type="' + type + '"]').removeAttr('hidden');
  }

  $('.cogu-ad-type-radio').on('change', function () {
    updateAdConfig($(this).val());
  });

  // ─── Scope rows show/hide ─────────────────────────────────────────────────
  function updateScopeRows() {
    var selected = $('input[name="scope"]:checked').val();
    if (selected === 'selected') {
      $('.cogu-scope-row').removeAttr('hidden');
    } else {
      $('.cogu-scope-row').attr('hidden', true);
    }
  }

  $('.cogu-scope-radio').on('change', updateScopeRows);

  // ─── Video type rows ──────────────────────────────────────────────────────
  function updateVideoRows() {
    var type = $('input[name="video_type"]:checked').val();
    if (type === 'embed') {
      $('.cogu-video-mp4-row').attr('hidden', true);
      $('.cogu-video-embed-row').removeAttr('hidden');
    } else {
      $('.cogu-video-mp4-row').removeAttr('hidden');
      $('.cogu-video-embed-row').attr('hidden', true);
    }
  }

  $('.cogu-video-type-radio').on('change', updateVideoRows);

  // ─── WordPress Media Uploader ─────────────────────────────────────────────
  var mediaFrame;

  $(document).on('click', '.cogu-media-btn', function (e) {
    e.preventDefault();
    var $btn    = $(this);
    var target  = $btn.data('target');
    var type    = $btn.data('type') || 'image'; // 'image' | 'video'
    var $input  = $('#' + target);
    var $preview = $('#banner-preview');

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title:    WPCGAdmin.mediaTitle,
      button:   { text: WPCGAdmin.mediaButton },
      library:  { type: type },
      multiple: false,
    });

    mediaFrame.on('select', function () {
      var attachment = mediaFrame.state().get('selection').first().toJSON();
      $input.val(attachment.url).trigger('change');

      // Preview for banner image.
      if (type === 'image' && $preview.length) {
        $preview.html('<img src="' + attachment.url + '" alt="">');
      }
    });

    mediaFrame.open();
  });

  $(document).on('click', '.cogu-media-clear', function (e) {
    e.preventDefault();
    var target   = $(this).data('target');
    var $preview = $('#banner-preview');
    $('#' + target).val('').trigger('change');
    if ($preview.length) {
      $preview.empty();
    }
  });

  // ─── Color picker ─────────────────────────────────────────────────────────
  $('.cogu-color-picker').wpColorPicker();

}(jQuery));
