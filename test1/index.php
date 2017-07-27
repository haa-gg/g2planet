
<?php
/* Brief: Here is a CSV of our favorite local restaurants. Using this information, create a page that has a place for a user to type. As the user is typing, a list of restaurant names should be shown to the user. The results should include any restaurant whose name starts with the letters the user has typed so far and any restaurant whose cuisine starts with the letters typed so far. The page should be written in PHP and jQuery. 

Method: 
-Import the CSV over to index.php so it can be ready by jQuery
-Create an onKeypress event which looks at the imported CSV
-In the keypress event, have jQuery look for words that start with the string contained in the text box
-If it finds any matches, display them on the page
*/

//This echoes the CSV over to our document, per the PHP manual 
echo '<div class="hide">';

$row = 1;

if (($handle = fopen("restaurants.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        $num = count($data);

        $row++;
//Put out a div wrapper with both the restaurant name and cuisine type
        echo '<div class="entry ' . $data[1] . ' ' . $data[0] . ' ">';

        for ($c=0; $c < $num; $c++) {

            //Putting out spans with the individual data segments in both the class and content areas

            if($c % 2 == 0){
                echo '<span id=" ' . $data[$c] . '" class="restaurantName">' . "\n" . $data[$c] . "\n" . "</span>\n";
            }
            else{
                echo '<span id=" ' . $data[$c] . '" class="cuisineType">' . "\n" . $data[$c] . "\n" . "</span>\n";
            }
        }

        echo '</div>';

    }

    fclose($handle);

}

echo "</div>";

?>


<!-- Import jQuery -->
<script src="jquery-3.2.1.min.js"></script><br>

<!-- Here's our input -->
<input type="text" name="restaurantInfo" id="restaurantInfo">

<!-- Where the result is displayed -->
<div id="result"></div>

<script>
//Our jQuery script
$( document ).ready(function() {

//First, hide the imported list
$(".hide").hide();

/*
Really cool regex selector snippet by James Padolsey 
Example use case
Select all DIVs with classes that contain numbers:
$('div:regex(class,[0-9])'); 
*/
jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
    validLabels = /^(data|css):/,
    attr = {
        method: matchParams[0].match(validLabels) ? 
        matchParams[0].split(':')[0] : 'attr',
        property: matchParams.shift().replace(validLabels,'')
    },
    regexFlags = 'ig',
    regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

//Do things when the key comes up
$( "#restaurantInfo" ).keyup(function() {

//Empty the results div so they don't stack up after each keypress
$('#result').empty()

//looking at the value of out input
var searchString = $('#restaurantInfo').val();

//Generating a regex statement which selects all divs in the imported CSV which have classes that start with the inputted string
var searchSelector = $('.hide div:regex(class,\\b(' + searchString + '))');

//Check if there is actual ttext in the search box
if(searchString.length > 0){

//Output each selected div into the results div for view
for (var i = 0; i < searchSelector.length; i++) {
    $(searchSelector[i]).clone().appendTo("#result");
}
}

 //Remove the cuisine type from view
 $('#result .cuisineType').remove();

});

});

</script>


