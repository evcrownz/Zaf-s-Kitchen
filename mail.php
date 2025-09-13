<?php
$receiver = "receiver_email_address_hre";
$subject = "Email Test via PHP using Localhost";
$body = "HI, there This is a test Email send From Localhost.";
$sender = "From:sender_email_address_here";

//fuction to send email
if(mail($receiver, $subject, $body, $sender)){
    echo "Email sent successsfully to $receiver";
}else{
    echo "sorry, failed white sending mail";
}
?>