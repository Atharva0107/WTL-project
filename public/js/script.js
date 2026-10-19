/* EASYRIDE JAVASCRIPT
   This file adds behaviour to the pages: quick quote calculation,
   vehicle search, FAQ expand/collapse, and contact form feedback.
   HTML elements are found by their id or CSS class. */


/*
  This event waits until the HTML page is loaded.
  Then JavaScript connects to forms, search boxes and buttons.
*/
document.addEventListener('DOMContentLoaded', function () {

  // 1. QUICK QUOTE CALCULATOR
  const form = document.getElementById('quoteForm');

  if (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      const days = Number(document.getElementById('days').value);
      const rate = Number(document.getElementById('rate').value);
      const output = document.getElementById('quoteResult');

      if (days < 1 || !Number.isFinite(days)) {
        output.textContent = 'Enter at least 1 day.';
        return;
      }

      output.textContent =
        'Estimated total: ₹' + (days * rate).toLocaleString('en-IN');
    });
  }

  // 2. VEHICLE SEARCH
  const search = document.getElementById('vehicleSearch');

  if (search) {
    search.addEventListener('input', function () {
      document.querySelectorAll('[data-vehicle]').forEach(function (card) {
        card.hidden = !card.textContent
          .toLowerCase()
          .includes(search.value.toLowerCase());
      });
    });
  }

  // 3. FAQ QUESTIONS
  document.querySelectorAll('.faq-question').forEach(function (button) {
    button.addEventListener('click', function () {
      const answer = button.nextElementSibling;
      answer.hidden = !answer.hidden;
    });
  });

  // 4. CONTACT US FORM
  const contactForm = document.getElementById('contactForm');

  if (contactForm) {
    contactForm.addEventListener('submit', function (event) {
      event.preventDefault();

      const name = document.getElementById('name').value.trim();
      const result = document.getElementById('contactResult');

      result.textContent =
        'Thank you, ' + name + '! Your message has been Sent.';

      contactForm.reset();
    });
  }

});
