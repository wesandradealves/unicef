<?php

namespace Drupal\umio_helpers\Service;

/**
 * Define the FormValidator class.
 */
class FormValidator {

  /**
   * Size of the CNPJ field.
   *
   * @var int
   */
  const SIZE_CNPJ = 14;

  /**
   * Size of the CPF field.
   *
   * @var int
   */
  const SIZE_CPF = 11;

  /**
   * Size of the Postal Code field.
   *
   * @var int
   */
  const SIZE_POSTAL_CODE = 8;

  /**
   * Size of the Phone field.
   *
   * @var int
   */
  const SIZE_PHONE = 11;

  /**
   * Size of the Telephone field.
   *
   * @var int
   */
  const SIZE_TELEPHONE = 10;

  /**
   * Returns a boolean value declaring if the argument is valid as an e-mail.
   */
  public function validateEmail(string $email) : bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? TRUE : FALSE;
  }

  /**
   * Returns a boolean value declaring if the argument is valid as an Telephone.
   */
  public function validateTelephone(string $telephone) : bool {
    $regexTelephone = "/^\([0-9]{2}\)\s[0-9]{4}\-[0-9]{4}$/";
    return (bool) preg_match($regexTelephone, $telephone);
  }

  /**
   * Check if the argument is valid as an Telephone Extension.
   */
  public function validateTelephoneExtension(string $telephoneExtension) : bool {
    $regexTelephoneExtension = "/^[0-9]{1,4}$/";
    return (bool) preg_match($regexTelephoneExtension, $telephoneExtension);
  }

  /**
   * Returns a boolean value declaring if the argument is valid as an Cell.
   */
  public function validatePhone(string $phone) : bool {
    $regexPhone = "/^\([0-9]{2}\)\s[0-9]{5}\-[0-9]{4}$/";
    return (bool) preg_match($regexPhone, $phone);
  }

  /**
   * Returns a boolean value declaring if the argument is valid as an Post code.
   */
  public function validatePostalCode(string $postalCode) : bool {
    $regexPostalCode = "/^[0-9]{5}-[0-9]{3}$/";
    return (bool) preg_match($regexPostalCode, $postalCode);
  }

  /**
   * Returns a boolean value declaring if the argument is valid as an CNPJ.
   */
  public function validateCnpj(string $cnpj) : bool {
    $cnpjStatus = FALSE;
    $regexCNPJ = "/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/";

    if (preg_match($regexCNPJ, $cnpj)) {
      $cnpj = $this->unmaskNumeric($cnpj, $this::SIZE_CNPJ);
      if (is_numeric($cnpj) && strlen($cnpj) == 14) {

        $t = 5;
        $d = 6;

        $sum1 = 0;
        $sum2 = 0;

        for ($i = 0; $i < 13; $i++) {

          $t = $t == 1 ? 9 : $t;
          $d = $d == 1 ? 9 : $d;

          $sum2 += ((INT) $cnpj[$i]) * $d;

          if ($i < 12) {
            $sum1 += ((INT) $cnpj[$i]) * $t;
          }

          $d--;
          $t--;
        }

        $digit1 = $sum1 % 11 < 2 ? 0 : 11 - $sum1 % 11;
        $digit2 = $sum2 % 11 < 2 ? 0 : 11 - $sum2 % 11;

        $cnpjStatus = (($cnpj[12] == $digit1) and ($cnpj[13] == $digit2));
      }
    }
    return $cnpjStatus;
  }

  /**
   * Returns a boolean value declaring if the argument is valid as an CPF.
   */
  public function validateCpf(string $cpf) : bool {

    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    if (strlen($cpf) != 11) {
      return FALSE;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
      return FALSE;
    }

    for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
        $d += intval($cpf[$c]) * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;
      if ($cpf[$c] != $d) {
        return FALSE;
      }
    }
    return TRUE;

  }

  /**
   * Validate if the password are following the minimum strong paramters.
   */
  public function validatePasswordStrong(String $password) : array {

    $validation = [];
    $validation['messages'] = [];

    if (strlen($password) < 8) {
      $validation['messages'][] = t("Password too short!");
    }

    if (!preg_match("#[0-9]+#", $password)) {
      $validation['messages'][] = t("Password must include at least one number!");
    }

    if (!preg_match("#[a-zA-Z]+#", $password)) {
      $validation['messages'][] = t("Password must include at least one letter!");
    }

    $validation['status'] = count($validation['messages']) ? FALSE : TRUE;

    return $validation;
  }

  /**
   * Return the same entrie but without any character that ins't a number.
   */
  public function unmaskNumeric(string $data, int $length) : string {
    $data = (string) preg_replace("/[^0-9]/", "", $data);
    $data = str_pad($data, $length, '0', STR_PAD_LEFT);

    return $data;
  }

  /**
   * Checks if url is valid.
   *
   * @param string $url
   *   URL to be validated.
   *
   * @return bool
   *   Boolean to check if is a valid url.
   */
  public function validateUrl(string $url):bool {
    return (bool) preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url);
  }

}
