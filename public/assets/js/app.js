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

  // Initialize DataTables automatically on any .datatable element
  if ($.fn.DataTable) {
    $('.datatable').DataTable({
      responsive: true,
      pageLength: 25,
      order: [[0, 'desc']],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search records..."
      }
    });
  }

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
            subActivitySelect.append('<option value="' + item.id + '" data-tat="' + item.default_tat_hours + '">' + item.sub_activity_name + ' (' + item.default_tat_hours + 'h SLA)</option>');
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

  // Auto-calculate TAT Datetime field on Sub-Activity change if empty
  $(document).on('change', '#sub_activity_id', function () {
    var selectedOption = $(this).find('option:selected');
    var tatHours = selectedOption.data('tat');
    var tatInput = $('#tat_datetime');

    if (tatHours && tatInput.length && !tatInput.val()) {
      var now = new Date();
      now.setHours(now.getHours() + parseInt(tatHours));
      var formatted = now.toISOString().slice(0, 16);
      tatInput.val(formatted);
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
});
