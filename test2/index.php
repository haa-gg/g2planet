<?php
/*
Overview: This one works a tad differently than the last one as I found exactly what I was looking for with a quick google search (https://github.com/jonathanwkelly/PHP-Credit-Card-Validation-Script). I used his because in a real world scenario, you do use code that other people have written (with their permission) and change it to fit what you need it to do. However if you check his version, you will see it is quite a bit more robust than what I am submitting. 

I went through and removed a huge amount of code not relevant to the project and then commented my way through the class.
*/

//Include class
include_once 'creditcards.class.php';
//Tell the class to expect a new variable
$CCV = new CreditCardValidator();
//Run the validate action in our class against our inputted value, in this case a valid visa card
$CCV->Validate("5500 1545 0000 0004");
/*
Some others to try out:
4111 1111 1111 1111
    4111 1111 1111 1
    4111 1111 1111 111
    3400 0000 0000 009
    3500 0000 0000 009
    5500 1545 0000 0004
    5940 0000 0000 0004
*/
//Actually telling the script to run our variable through the class    
$CARDINFO = $CCV->GetCardInfo();
?>

<?php 
//Output the result
echo $CARDINFO['status'];
?>
