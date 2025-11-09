// assets/js/main.js
document.addEventListener('DOMContentLoaded', function(){
  // simple client-side validation example
  const forms = document.querySelectorAll('form[needs-validation]');
  forms.forEach(f => f.addEventListener('submit', function(e){
    const invalid = Array.from(f.querySelectorAll('[required]')).some(i => !i.value.trim());
    if(invalid){ e.preventDefault(); alert('Please fill required fields.'); }
  }));
});
