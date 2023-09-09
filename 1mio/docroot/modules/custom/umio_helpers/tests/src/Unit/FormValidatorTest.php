<?php

namespace Drupal\Tests\umio_helpers\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\umio_helpers\Service\FormValidator;

/**
 * Define the routine FormValidatorTest.
 *
 * @ingroup umio_helpers
 *
 * @group umio_helpers
 */
class FormValidatorTest extends UnitTestCase {

  /**
   * Define the validateEmail() tests routine.
   */
  public function testValidateEmail() {

    $formValidator = new FormValidator();

    $email = "1mio-dev@ciandtcom";
    $result = $formValidator->validateEmail($email);
    $this->assertEquals(FALSE, $result, "Trying to assert {$email} as FALSE and got {$result}");

    $email = "1mio-devciandt.com";
    $result = $formValidator->validateEmail($email);
    $this->assertEquals(FALSE, $result, "Trying to assert {$email} as FALSE and got {$result}");

    $email = "1mio-dev@ciandt.com";
    $result = $formValidator->validateEmail($email);
    $this->assertEquals(TRUE, $result, "Trying to assert {$email} as true and got {$result}");

  }

  /**
   * Define the validateTelephone() tests routine.
   */
  public function testValidateTelephone() {

    $formValidator = new FormValidator();

    $telephone = "2143215678";
    $result = $formValidator->validateTelephone($telephone);
    $this->assertEquals(FALSE, $result);

    $telephone = "(21) 4321-5678";
    $result = $formValidator->validateTelephone($telephone);
    $this->assertEquals(TRUE, $result);

  }

  /**
   * Define the validatePhone() tests routine.
   */
  public function testValidatePhone() {

    $formValidator = new FormValidator();

    $phone = "21912345678";
    $result = $formValidator->validatePhone($phone);
    $this->assertEquals(FALSE, $result);

    $phone = "(21) 91234-5678";
    $result = $formValidator->validatePhone($phone);
    $this->assertEquals(TRUE, $result);
  }

  /**
   * Define the validatePostalCode() tests routine.
   */
  public function testValidatePostalCode() {

    $formValidator = new FormValidator();

    $postalCode = "37195000";
    $result = $formValidator->validatePostalCode($postalCode);
    $this->assertEquals(FALSE, $result);

    $postalCode = "37195-000";
    $result = $formValidator->validatePostalCode($postalCode);
    $this->assertEquals(TRUE, $result);
  }

  /**
   * Define the validateCnpj() tests routine.
   */
  public function testValidateCnpj() {

    $formValidator = new FormValidator();

    $cnpj = "03223607000188";
    $result = $formValidator->validateCnpj($cnpj);
    $this->assertEquals(FALSE, $result);

    $cnpj = "03.223.607/0001-88";
    $result = $formValidator->validateCnpj($cnpj);
    $this->assertEquals(FALSE, $result);

    $cnpj = "06353601000164";
    $result = $formValidator->validateCnpj($cnpj);
    $this->assertEquals(FALSE, $result);

    $cnpj = "06.353.601/0001-64";
    $result = $formValidator->validateCnpj($cnpj);
    $this->assertEquals(TRUE, $result);
  }

  /**
   * Define the validateCpf() tests routine.
   */
  public function testValidateCpf() {
    $formValidator = new FormValidator();

    $invalidCpf = "02306305028";
    $result = $formValidator->validateCpf($invalidCpf);
    $this->assertEquals(FALSE, $result);

    $invalidCpfWithMask = "023.063.050-28";
    $result = $formValidator->validateCpf($invalidCpfWithMask);
    $this->assertEquals(FALSE, $result);

    $cpf = "023.063.050-21";
    $result = $formValidator->validateCpf($cpf);
    $this->assertEquals(TRUE, $result);

    $cpf = "02306305021";
    $result = $formValidator->validateCpf($cpf);
    $this->assertEquals(TRUE, $result);
  }

  /**
   * Define the unmask tests routine.
   */
  public function testUnmaskFunctions() {

    $formValidator = new FormValidator();

    $telephone = "(21) 4321-5678";
    $telephone_unmasked = "2143215678";
    $result = $formValidator->unmaskNumeric($telephone, $formValidator::SIZE_TELEPHONE);
    $this->assertEquals($telephone_unmasked, $result);

    $phone = "(21) 91234-5678";
    $phone_unmasked = "21912345678";
    $result = $formValidator->unmaskNumeric($phone, $formValidator::SIZE_PHONE);
    $this->assertEquals($phone_unmasked, $result);

    $cnpj = "06.353.601/0001-64";
    $cnpj_unmasked = "06353601000164";
    $result = $formValidator->unmaskNumeric($cnpj, $formValidator::SIZE_CNPJ);
    $this->assertEquals($cnpj_unmasked, $result);

    $cpf = "023.063.050-21";
    $cpf_unmasked = "02306305021";
    $result = $formValidator->unmaskNumeric($cpf, $formValidator::SIZE_CPF);
    $this->assertEquals($cpf_unmasked, $result);
  }

}
