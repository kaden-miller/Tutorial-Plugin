jQuery(document).ready(function ($) {
  $(".update-note").click(function (e) {
    e.preventDefault();
    var postId = $(this).data("post-id");
    var existingNotes = $(this).siblings(".notes-content").text().trim();
    var newNotes = prompt(
      "Enter new notes (or leave blank to remove):",
      existingNotes
    );
    if (newNotes !== null) {
      $.ajax({
        url: tutorial_page_script_vars.ajaxurl,
        type: "POST",
        data: {
          action: "update_notes",
          post_id: postId,
          notes: newNotes,
        },
        success: function (response) {
          if (response.success) {
            alert("Notes updated successfully.");
            location.reload();
          } else {
            alert("Error updating notes.");
          }
        },
        error: function () {
          alert("Error updating notes.");
        },
      });
    }
  });

  $(".copyUrlWrapper").click(function () {
    var $clicked = this;
    $($clicked).find("img").toggleClass("rotate");
    $($clicked).addClass("clicked");

    setTimeout(function () {
      $($clicked).removeClass("clicked");
    }, 2000);
  });
});

// Get all the "Copy URL" buttons
var copyUrlButtons = document.querySelectorAll(".copyUrl");

// Loop through the buttons and add a click event listener to each one
copyUrlButtons.forEach(function (button) {
  button.addEventListener("click", function () {
    // Get the tutorial ID from the data-tutorial-id attribute
    var tutorialId = button.dataset.tutorialId;

    // Get the URL of the page with the tutorial item ID
    var tutorialUrl =
      window.location.href.split("#")[0] + "#" + "tutorial-item-" + tutorialId;

    // Remove any existing temporary input element
    var tempInput = document.getElementById("tempInput");
    if (tempInput) {
      document.body.removeChild(tempInput);
    }

    // Create a new temporary input element to hold the URL
    tempInput = document.createElement("input");
    tempInput.setAttribute("id", "tempInput");
    tempInput.setAttribute("value", tutorialUrl);
    document.body.appendChild(tempInput);

    // Select the contents of the input element
    tempInput.select();

    // Copy the contents of the input element to the clipboard
    document.execCommand("copy");

    // Remove the temporary input element
    document.body.removeChild(tempInput);
  });
});
