function process()
{
   var stylesheet = document.getElementById("styleinfo");
   stylesheet.innerHTML = "<link rel='stylesheet' media='all' type='text/css' href='/includes/gradienttable.css'/>";

   var table = document.getElementById("myTable");
   table.classList.add("gradienttable-mini");

   var statecol = -1;

   for (var j = 0, col; col = table.rows[0].cells[j]; j++) {
      var str = col.innerHTML;
      if (str === 'state') statecol = j;
   }

   for (var i = 1, row; row = table.rows[i]; i++) {
      // iterate through rows
      // rows would be accessed using the "row" variable assigned in the for loop
      for (var j = 0, col; col = row.cells[j]; j++) {
         //iterate through columns
         //columns would be accessed using the "col" variable assigned in the for loop
         var str = col.innerHTML;
         if (str === 'Democrat') col.innerHTML = "<img src='democratic.jpg'>";
         if (str === 'Republican') col.innerHTML = "<img src='republican.jpg'>";
         // if (str === 'I') col.innerHTML = "Independent";
         if (str.indexOf("http://") == 0 || str.indexOf("https://") == 0) {
            // col.innerHTML = "<a href=\"str\">" + str + "</a>";
            anchor = "<a href='" + str + "'>" + str + "</a>";
            col.innerHTML = anchor;
            // col.innerHTML = "<a href=\"str\"></a>";
        }

         // see if we can pull the flag
         if (j == statecol) {
            var str = col.innerHTML;
            var flagstr = str.replace(/\s+/g, '-').toLowerCase() + ".jpg";
            flagstr = "<img src='/flags/" + flagstr + "' height='64' alt='" + str + "'>";
            col.innerHTML = flagstr;
         }
      }
   }
}

window.onload = process;
