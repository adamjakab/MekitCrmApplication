<?php
namespace Mekit\Bundle\AccountBundle\Tests\Unit\Entity\Relationships;

use Mekit\Bundle\TestBundle\Helpers\MekitUnitEntityTest;
use Mekit\Bundle\AccountBundle\Entity\Account;
use Mekit\Bundle\ContactInfoBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;

class AddressesTest extends MekitUnitEntityTest {

	public function testAddresses() {
		$addressOne = new Address();
		$addressOne->setCountry(new Country('US'));
		$addressTwo = new Address();
		$addressTwo->setCountry(new Country('UK'));
		$addressThree = new Address();
		$addressThree->setCountry(new Country('IT'));
		$addresses = array($addressOne, $addressTwo);

		$entity = new Account();
		$this->assertSame($entity, $entity->resetAddresses($addresses));
		$actual = $entity->getAddresses();
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
		$this->assertEquals($addresses, $actual->toArray());

		$this->assertSame($entity, $entity->addAddress($addressTwo));
		$actual = $entity->getAddresses();
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
		$this->assertEquals($addresses, $actual->toArray());

		$this->assertSame($entity, $entity->addAddress($addressThree));
		$actual = $entity->getAddresses();
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
		$this->assertEquals(array($addressOne, $addressTwo, $addressThree), $actual->toArray());

		$this->assertSame($entity, $entity->removeAddress($addressOne));
		$actual = $entity->getAddresses();
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
		$this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());

		$this->assertSame($entity, $entity->removeAddress($addressOne));
		$actual = $entity->getAddresses();
		$this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
		$this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());
	}

	public function testGetPrimaryAddress() {
		$entity = new Account();
		$this->assertNull($entity->getPrimaryAddress());

		$address = new Address();
		$entity->addAddress($address);
		$this->assertNull($entity->getPrimaryAddress());

		$address->setPrimary(true);
		$this->assertSame($address, $entity->getPrimaryAddress());

		$newPrimary = new Address();
		$entity->addAddress($newPrimary);

		$entity->setPrimaryAddress($newPrimary);
		$this->assertSame($newPrimary, $entity->getPrimaryAddress());

		$this->assertFalse($address->isPrimary());
	}
}