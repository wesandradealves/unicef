(function ($, Drupal, drupalSettings) {
  "use strict";

  // The Element Id that the page willbe scrolled to.
  let scrollToElementId = '';

  // The selector for both manual load and automatic pager.
  let pagerSelector = '[data-drupal-views-infinite-scroll-pager]';

  // The selector for the automatic pager.
  let contentWrapperSelector = '[data-drupal-views-infinite-scroll-content-wrapper]';

  // The selector for the filter buttton.
  let filterButtonSelectorWrapper = '.filter--submit-umio-filter';
  let filterButtonSelector = '[data-drupal-selector="edit-submit-feeds-jovens"]';

  /**
   * Remove the wrapper and define a animation function to show the cards.
   *
   * @param $viewNewRows
   *   New content detached from the DOM.
   */
   $.fn.removeInfiniteScrollViewWrapper = (viewNewRows) => {
    const timeEachFadeInEffect = 200;
    let totalEachFadeInEffect = 0;

    return viewNewRows.map( (item, obj) => {

      let updated_results = [];
      let updated_objects = obj.children;

      for (let i = 0; i < updated_objects.length; i++) {
        totalEachFadeInEffect += timeEachFadeInEffect;
        let updated_object = obj.children[i];

        if (scrollToElementId == '') {
          scrollToElementId = updated_object.attributes[1].value;
        }

        updated_object.animate([
          {opacity: 0},
          {opacity: 1},
        ], {
          duration: timeEachFadeInEffect,
          iterations: 1,
          delay: totalEachFadeInEffect - timeEachFadeInEffect,
          easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
          direction: 'normal',
          fill: 'both'
        });

        updated_results.push(updated_object);

      }

      return updated_results;
    });

  }

  /**
   * Insert a views infinite scroll view into the document.
   *
   * @param {jQuery} $newView
   *   New content detached from the DOM.
   */
  $.fn.customizedInfiniteScrollInsertView = function ($newView) {

    // Extract the view DOM ID from the view classes.
    let matches = /(js-view-dom-id-\w+)/.exec(this.attr('class'));

    if (!matches) {
      return;
    }

    let currentViewId = matches[1].replace('js-view-dom-id-', 'views_dom_id:');

    // Get the existing ajaxViews object.
    let view = Drupal.views.instances[currentViewId];

    // Remove once so that the exposed form and pager are processed on
    // behavior attach.
    view.$view.removeOnce('ajax-pager');
    view.$exposed_form.removeOnce('exposed-form');

    // Make sure infinite scroll can be reinitialized.
    let clonedButton = view.$view.find(filterButtonSelector).clone(true);
    let buttonWrapper = view.$view.find(filterButtonSelectorWrapper);
    $(filterButtonSelector).remove();

    let existingPager = view.$view.find(pagerSelector);

    let newRows = $newView.find(contentWrapperSelector).children();
    let newPager = $newView.find(pagerSelector);

    // Calls the function to put on UMIO card pattern.
    newRows = $.fn.removeInfiniteScrollViewWrapper(newRows);

    view.$view.find(contentWrapperSelector)
      // Trigger a jQuery event on the wrapper to inform that new content was
      // loaded and allow other scripts to respond to the event.
      .trigger('views_infinite_scroll.new_content', newRows.clone())
      // Add the new rows to existing view.
      .append(newRows);

    if (newRows.lenght) {
      // Set the page to scroll onto the new cards.
      let scrollToElementSelector = 'article[data-quickedit-entity-id="' + scrollToElementId + '"]';
      document.querySelector(scrollToElementSelector).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"}).once();
    }

    // Replace the pager link with the new link and ajaxPageState values.
    existingPager.replaceWith(newPager);
    view.$view.find('ul.js-pager__items > li > a').each($.proxy(view.attachPagerLinkAjax, view));

    // Remove the old events and adds the button without any event on it.
    buttonWrapper.append(clonedButton);

    // Run views and VIS behaviors.
    Drupal.attachBehaviors(view.$view[0]);
  };

  /**
   * Insert newViews info after filtering.
   *
   * @param $newView
   *   New content detached from the DOM.
   */
   $.fn.customizedFilterInsertView = function ($newView) {

    // Extract the view DOM ID from the view classes.
    let matches = /(js-view-dom-id-\w+)/.exec(this.attr('class'));

    if (!matches) {
      return;
    }

    let currentViewId = matches[1].replace('js-view-dom-id-', 'views_dom_id:');

    // Get the existing ajaxViews object.
    let view = Drupal.views.instances[currentViewId];

    // Remove once so that the exposed form and pager are processed on
    // behavior attach.
    view.$view.removeOnce('ajax-pager');
    view.$exposed_form.removeOnce('exposed-form');

    // Make sure infinite scroll can be reinitialized.
    let clonedButton = view.$view.find(filterButtonSelector).clone(true);
    let buttonWrapper = view.$view.find(filterButtonSelectorWrapper);
    $(filterButtonSelector).remove();

    // Make sure infinite scroll can be reinitialized.
    let existingPager = view.$view.find(pagerSelector);

    let newRows = $newView.find(contentWrapperSelector).children();
    let newPager = $newView.find(pagerSelector);

    // Calls the function to put on UMIO card pattern.
    newRows = $.fn.removeInfiniteScrollViewWrapper(newRows);

    view.$view.find(contentWrapperSelector)
      // Clear all old nodes.
      .empty()
      // Add the new rows to existing view.
      .append(newRows);

    if (newRows.lenght) {
      // Set the page to scroll onto the new cards.
      let scrollToElementSelector = 'article[data-quickedit-entity-id="' + scrollToElementId + '"]';
      document.querySelector(scrollToElementSelector).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"}).once();
    }

    // Replace the pager link with the new link and ajaxPageState values.
    existingPager.replaceWith(newPager);
    view.$view.find('ul.js-pager__items > li > a').each($.proxy(view.attachPagerLinkAjax, view));

    // Remove the old events and adds the button without any event on it.
    buttonWrapper.append(clonedButton);

    // Set a couple of variables to check if any filter was inserted.
    let filter_fulltext = $('#edit-search-api-fulltext').val();
    let filter_feeds_opportunities_type = $('#edit-umio-feeds-opportunities-type').val();
    let filter_feeds_opportunities = $('#edit-umio-feeds-opportunities').val();
    let filter_feeds_opportunity_model = $('#edit-umio-feeds-opportunity-model').val();
    let filter_feeds_opportunity_locality = $('#edit-umio-feeds-opportunity-locality').val();

    if (filter_fulltext != "" || filter_feeds_opportunities_type != 'All' || filter_feeds_opportunities != 'All' || filter_feeds_opportunity_model != 'All' || filter_feeds_opportunity_locality != 'All' ) {
      // If it does, shows up the filter button as filtered.
      $('.filter--open-button').addClass('filter--open-button--is-filtered');
    } else {
      // Else remove classes to show as filtered and add the display none state.
      $('.filter--clear-button, .filter--clear-button-outside').addClass('d-none');
      $('.filter--open-button').removeClass('filter--open-button--is-filtered');
    }
    // Click on the filter to open it if filtered.
    $('.filter--open-button').click();

    if ($('div.umio-card--grid-container > article').length > 0) {
      $('.filter--print-button').removeClass('d-none');
      $('.filter--open-button').css('grid-area', 'filter');
    } else {
      $('.filter--print-button').addClass('d-none');
      $('.filter--open-button').css('grid-area', 'print');
    }

    // Run views and VIS behaviors.
    Drupal.attachBehaviors(view.$view[0]);
  };

  Drupal.behaviors.customizationInfiniteScroll = {
    attach: function (context, settings) {
      $(document).ready(function () {
        $(document, context).once('customizationInfiniteScroll').each( function () {

          let view_content = $('div.view-content > div.views-infinite-scroll-content-wrapper');

          view_content.removeAttr("data-drupal-views-infinite-scroll-content-wrapper");
          view_content.removeClass("views-infinite-scroll-content-wrapper");

        });
      });

    }
  }

} (jQuery, Drupal, drupalSettings));
