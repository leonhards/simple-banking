(function ($) {
  function formatRupiah(number) {
    return new Intl.NumberFormat("id-ID", {
      minimumFractionDigits: 0,
    }).format(number);
  }

  $(document).ready(function ($) {
    const amountInput = $("#balance, #amount");
    rpformat = amountInput.val();
    amountInput.val(formatRupiah(rpformat));

    // Format the input value on keyup
    amountInput.on("keyup", function (e) {
      // Get the raw value (allow thousand separators and decimal commas)
      let rawValue = $(this)
        .val()
        .replace(/[^0-9,]/g, "") // Remove all non-numeric characters except commas
        .replace(",", "."); // Replace comma with dot for decimal point

      // Convert to a number
      let numberValue = parseFloat(rawValue);

      // Format the number with thousand separators and decimal commas
      let formattedValue = new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0, // Ensure 2 decimal places
        maximumFractionDigits: 2, // Limit to 2 decimal places
      }).format(numberValue);

      // Update the display value
      $(this).val(formattedValue);
    });

    // Convert the formatted value back to a raw number before form submission
    $("form").on("submit", function (e) {
      let rawValue = amountInput
        .val()
        .replace(/[^0-9,]/g, "") // Remove all non-numeric characters except commas
        .replace(",", "."); // Replace comma with dot for decimal point

      // Temporarily set the input field to the raw value
      amountInput.val(rawValue);
    });
  });
})(jQuery);
