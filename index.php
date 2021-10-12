<?php

require_once('class_Users.php');

Users::dbConnect();

$ivan = new Users(null, 'Ivan', 'Ivanov', '1985-01-02', 0, 'New York');
$ivan->getAge();
$ivan->getID();
$ivan->getGender();

$alex = new Users(1);
$alex->getAge();
$alex->getGender();
Users::getAgeFromObject($alex);
print json_encode($alex->formatPerson('all'));


