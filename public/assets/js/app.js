/**
 * Mail Query Tracker (MQT) - Application Main JavaScript
 */

$(document).ready(function () {
  // Restore Collapsed Sidebar State from LocalStorage
  if (localStorage.getItem('mqt_sidebar_collapsed') === 'true' && $(window).width() >= 768) {
    $('#wrapper').addClass('collapsed');
  }

  // Sidebar Toggle Handler (Collapses on Desktop, Toggles Off-Canvas on Mobile)
  $(document).on('click', '#sidebarToggle', function (e) {
    e.preventDefault();
    if ($(window).width() < 768) {
      $('#wrapper').toggleClass('toggled');
    } else {
      $('#wrapper').toggleClass('collapsed');
      localStorage.setItem('mqt_sidebar_collapsed', $('#wrapper').hasClass('collapsed'));
    }
  });

  // Auto-initialize & Auto-dismiss Floating Toasts
  if ($('.toast').length && typeof bootstrap !== 'undefined') {
    $('.toast').each(function () {
      var bsToast = new bootstrap.Toast(this, { delay: 5000, autohide: true });
      bsToast.show();
    });
  }

  // Suppress DataTables alert popups globally for all tables across the application
  if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }

  // Auto-initialize DataTables safely on any .datatable element
  if ($.fn.DataTable) {
    $.fn.dataTable.ext.errMode = 'none';

    $('.datatable').each(function () {
      var $tbl = $(this);

      // Determine header columns count
      var thCount = $tbl.find('thead tr:first th').length || $tbl.find('thead tr:first td').length || 1;
      var $firstTd = $tbl.find('tbody tr:first td');

      // If tbody has an empty state row with a single cell using colspan
      if ($tbl.find('tbody tr').length === 1 && $firstTd.length === 1 && $firstTd.attr('colspan')) {
        $firstTd.attr('colspan', thCount);
      }

      // Initialize DataTable safely
      if (!$.fn.DataTable.isDataTable(this)) {
        try {
          $(this).DataTable({
            responsive: true,
            pageLength: 25,
            order: [], // Flexible order to prevent invalid index errors on empty or colspan rows
            language: {
              search: "_INPUT_",
              searchPlaceholder: "Search records...",
              emptyTable: "No data available in table"
            }
          });
        } catch (err) {
          console.warn("DataTables initialization gracefully handled:", err);
        }
      }
    });
  }

  // Dynamic Activity loader based on selected Division dropdown (Cascading Top-Down Hierarchy)
  $(document).on('change', '#division_id, select[name="division_id"]', function () {
    var divisionId = $(this).val();
    var activitySelect = $('#activity_id, select[name="activity_id"]');
    var subActivitySelect = $('#sub_activity_id, select[name="sub_activity_id"]');
    
    activitySelect.empty().append('<option value="">Loading activities...</option>');
    subActivitySelect.empty().append('<option value="">Select Activity First</option>');

    $.ajax({
      url: BASE_URL + '/api/activities',
      type: 'GET',
      data: { division_id: divisionId },
      dataType: 'json',
      success: function (response) {
        activitySelect.empty().append('<option value="">Select Parent Activity</option>');
        if (response.success && response.data.length > 0) {
          $.each(response.data, function (index, item) {
            activitySelect.append('<option value="' + item.id + '">' + item.activity_name + '</option>');
          });
        } else {
          activitySelect.append('<option value="">No Activities found for this Division</option>');
        }
      },
      error: function () {
        activitySelect.empty().append('<option value="">Error loading activities</option>');
      }
    });
  });

  // Dynamic Sub-Activity loader based on selected Activity dropdown
  $(document).on('change', '#activity_id', function () {
    var activityId = $(this).val();
    var subActivitySelect = $('#sub_activity_id');
    
    subActivitySelect.empty().append('<option value="">Loading sub-activities...</option>');

    if (!activityId) {
      subActivitySelect.empty().append('<option value="">Select Sub-Activity</option>');
      return;
    }

    $.ajax({
      url: BASE_URL + '/api/sub-activities',
      type: 'GET',
      data: { activity_id: activityId },
      dataType: 'json',
      success: function (response) {
        subActivitySelect.empty().append('<option value="">Select Sub-Activity</option>');
        if (response.success && response.data.length > 0) {
          $.each(response.data, function (index, item) {
            var userAttr = item.default_user_id ? ' data-user="' + item.default_user_id + '"' : '';
            subActivitySelect.append('<option value="' + item.id + '" data-tat="' + item.default_tat_hours + '"' + userAttr + '>' + item.sub_activity_name + ' (' + item.default_tat_hours + 'h SLA)</option>');
          });
        } else {
          subActivitySelect.append('<option value="">No Sub-Activities found</option>');
        }
      },
      error: function () {
        subActivitySelect.empty().append('<option value="">Error loading sub-activities</option>');
      }
    });
  });

  // Auto-calculate TAT Datetime field & auto-select allocated employee on Sub-Activity change
  $(document).on('change', '#sub_activity_id, select[name="sub_activity_id"]', function () {
    var selectedOption = $(this).find('option:selected');
    var tatHours = selectedOption.data('tat');
    var defaultUser = selectedOption.data('user');
    var tatInput = $('#tat_datetime');
    var allocatedSelect = $('#allocated_to, select[name="allocated_to"]');

    if (tatHours && tatInput.length && !tatInput.val()) {
      var now = new Date();
      now.setHours(now.getHours() + parseInt(tatHours));
      var formatted = now.toISOString().slice(0, 16);
      tatInput.val(formatted);
    }

    if (defaultUser && allocatedSelect.length) {
      allocatedSelect.val(defaultUser);
    }
  });

  // Notification Bell Polling / Unread Count
  function loadNotifications() {
    $.ajax({
      url: BASE_URL + '/api/notifications',
      type: 'GET',
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          var count = res.unread_count || 0;
          var badge = $('#notif-count-badge');
          if (count > 0) {
            badge.text(count).removeClass('d-none');
          } else {
            badge.addClass('d-none');
          }

          var list = $('#notif-dropdown-list');
          list.empty();
          if (res.notifications.length > 0) {
            $.each(res.notifications, function (i, n) {
              list.append(
                '<a href="' + (n.link || '#') + '" class="dropdown-item py-2 border-bottom">' +
                '<div class="fw-bold fs-7">' + n.title + '</div>' +
                '<div class="text-muted fs-8">' + n.message + '</div>' +
                '<small class="text-secondary fs-9">' + n.created_at + '</small>' +
                '</a>'
              );
            });
          } else {
            list.append('<div class="dropdown-item text-center text-muted py-3">No new notifications</div>');
          }
        }
      }
    });
  }

  if ($('#notif-count-badge').length) {
    loadNotifications();
    setInterval(loadNotifications, 30000); // refresh every 30s
  }

  // Collapsible Sidebar Groups - Accordion Chevron & Toggle Synchronization
  try {
    localStorage.removeItem('mqt_open_sb_groups');
  } catch (e) {}

  $('#sidebar-menu-accordion').on('show.bs.collapse', '.sidebar-submenu', function () {
    var id = $(this).attr('id');
    $('[data-bs-target="#' + id + '"]').removeClass('collapsed').attr('aria-expanded', 'true');
  });

  $('#sidebar-menu-accordion').on('hide.bs.collapse', '.sidebar-submenu', function () {
    var id = $(this).attr('id');
    $('[data-bs-target="#' + id + '"]').addClass('collapsed').attr('aria-expanded', 'false');
  });
});
