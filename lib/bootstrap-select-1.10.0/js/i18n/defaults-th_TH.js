/*
 * Translated default messages for bootstrap-select.
 * Locale: EN (English)
 * Region: US (United States)
 */
(function ($) {
  $.fn.selectpicker.defaults = {
    noneSelectedText: 'ไม่ได้เลือกอะไร',
    noneResultsText: 'ไม่มีผลลัพธ์ที่ตรงกับ {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? "{0} item selected" : "{0} items selected";
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Limit reached ({n} item max)' : 'Limit reached ({n} items max)',
        (numGroup == 1) ? 'Group limit reached ({n} item max)' : 'Group limit reached ({n} items max)'
      ];
    },
    selectAllText: 'เลือกทั้งหมด',
    deselectAllText: 'ไม่เลือกทั้งหมด',
    multipleSeparator: ', '
  };
})(jQuery);
