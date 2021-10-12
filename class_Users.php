<?php
/*
  Класс Users
  Используется для создания объектов пользователей, сохранения их в базу, удаления из неё.



*/

class Users
{
  private $id;
  private $firstName;
  private $lastName;
  private $birthDate;
  private $gender;
  private $birthCity;
  static private $mysqldb;

  /*
    Конструктор класса используется для выборки пользователя по его ID из БД
    или определения нового пользователя.
  */
  public function __construct(
    $id = NULL, 
    $firstName = NULL, 
    $lastName = NULL, 
    $birthDate = NULL, 
    $gender = NULL, 
    $birthCity = NULL
  ) {
    if (isset($id) && is_numeric($id)) {
      $result = self::$mysqldb->query("SELECT * FROM `users` WHERE `id`=$id");
      $row = $result->fetch_assoc();
      $this->id = $row['id'];
      $this->firstName = $row['firstname'];
      $this->lastName = $row['lastname'];
      $this->birthDate = $row['birthdate'];
      $this->gender = $row['gender'];
      $this->birthCity = $row['birthcity'];
    } elseif (isset($firstName) 
              && preg_match('/^[a-zа-яё]{1}[a-zа-яё]*[\-\s]{1}[a-zа-яё]*[a-zа-яё]{1}$/i', $firstName) 
              && isset($lastName) 
              && preg_match('/^[a-zа-яё]{1}[a-zа-яё]*[\-\s]{1}[a-zа-яё]*[a-zа-яё]{1}$/i', $lastName) 
              && isset($birthDate) 
              && preg_match('/^[0-9]{4}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}$/', $birthDate) 
              && strtotime($birthDate) && isset($gender) && is_numeric($gender) 
              && ($gender == 0 || $gender == 1) && isset($birthCity)
    ) {
      $this->firstName = $firstName;
      $this->lastName = $lastName;
      $this->birthDate = $birthDate;
      $this->gender = $gender;
      $this->birthCity = $birthCity;
      $this->saveUser();
    }
  }

  /*
    Статический метод для подключения к БД
  */
  static public function dbConnect()
  {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    self::$mysqldb = new mysqli("localhost", "root", "258258", "slmax");
    return self::$mysqldb->host_info;
  }

  /*
    Метод объекта для получения полного возраста пользователя
  */
  public function getAge()
  {
    if (isset($this->id)) {
      $result = self::$mysqldb->query("SELECT (YEAR(CURRENT_DATE) - YEAR(birthdate)) "
                                    . " - (DATE_FORMAT(CURRENT_DATE, '%m%d') < DATE_FORMAT(birthdate, '%m%d')) "
                                    . "AS age FROM users WHERE `id`=$this->id");
      $row = $result->fetch_assoc();
      return $row['age'];
    }
  }

  /*
    Статический метод класса для получения полного возраста пользователя путем передачи 
    в него объекта пользователя
  */
  static public function getAgeFromObject($person)
  {
    $today = getdate();
    $birthday = getdate(strtotime($person->birthDate));
    return $today['year'] - $birthday['year'] - (($today['yday'] - $birthday['yday'] >= 0) ? 0 : 1);
  }

  /*
    Метод объекта для сохранения его состояния в БД
    Получает ID для вновь внесенного пользователя
  */
  public function saveUser()
  {
    if (isset($this->id)) {
      self::$mysqldb->query("UPDATE `users` SET `firstname`='$this->firstName', `lastname`='$this->lastName', "
                          . "`birthdate`='$this->birthDate', `gender`='$this->gender', "
                          . "`birthcity`='$this->birthCity' WHERE `id`=$this->id");
    }
    else {
      self::$mysqldb->query("INSERT INTO `users` (`firstname`, `lastname`, `birthdate`, `gender`, "
                          . "`birthcity`) VALUES ('$this->firstName', '$this->lastName', '$this->birthDate', "
                          . "'$this->gender', '$this->birthCity')");
      $this->id = self::$mysqldb->insert_id;
    }
  }

  /*
    Метод объекта для удаления его из БД по его ID
  */
  public function deleteUser()
  {
    if (isset($this->id)) {
      self::$mysqldb->query("DELETE FROM `users` WHERE `id`=$this->id");
    }
  }

  /*
    Метод объекта для получения его ID
  */
  public function getID()
  {
    return $this->id;
  }

  /*
    Публичный метод объекта для получения его человекочитаемого пола
    Вызывает приватный статический метод
  */
  public function getGender()
  {
    return $this->getHumanReadableGender($this->gender);
  }

  /*
    Статический приватный метод класса для получения человекочитаемого пола объекта
    Возвращает: 'муж' либо 'жен'
  */
  static private function getHumanReadableGender($gender)
  {
    $gender_dictionary = ['муж', 'жен'];
    return $gender_dictionary[$gender];
  }

  /*
    Метод объекта класса для форматирования его свойств 
    в качестве параметра получает строку:
    'age' - преобразование даты рождения в полный возраст
    'gender' - преобразование пола из двоичной системы в текстовую
    'all' - преобразование и пола и возраста
    Возвращает объект
  */
  public function formatPerson($option)
  {
    $person = new StdClass();
    foreach($this as $key => $value) {
      if ($key == 'birthDate' && ($option == 'age' || $option == 'all')) {
        $person->age = $this->getAge();
      } elseif ($key == 'gender' && ($option == 'gender' || $option == 'all')) {
        $person->$key = $this->getGender();
      } else $person->$key = $value;
    }
    return $person;
  }
}

