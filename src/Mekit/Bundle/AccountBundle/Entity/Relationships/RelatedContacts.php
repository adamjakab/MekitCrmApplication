<?php
namespace Mekit\Bundle\AccountBundle\Entity\Relationships;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Mekit\Bundle\ContactBundle\Entity\Contact;

/**
 * @ORM\MappedSuperclass
 */
class RelatedContacts extends RelatedTasks {
	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Mekit\Bundle\ContactBundle\Entity\Contact", inversedBy="accounts")
	 * @ORM\JoinTable(name="mekit_rel_account_contact")
	 * @ConfigField(
	 *      defaultValues={
	 *          "dataaudit"={
	 *              "auditable"=true
	 *          }
	 *      }
	 * )
	 */
	protected $contacts;


	public function __construct() {
		parent::__construct();
		$this->contacts = new ArrayCollection();
	}

	/**
	 * @return ArrayCollection
	 */
	public function getContacts() {
		return $this->contacts;
	}

	/**
	 * @param ArrayCollection $contacts
	 * @return $this
	 */
	public function setContacts($contacts) {
		$this->contacts->clear();
		foreach ($contacts as $contact) {
			$this->addContact($contact);
		}
		return $this;
	}

	/**
	 * @param Contact $contact
	 * @return $this
	 */
	public function addContact(Contact $contact) {
		if (!$this->contacts->contains($contact)) {
			$this->contacts->add($contact);
			$contact->addAccount($this);
		}
		return $this;
	}

	/**
	 * @param Contact $contact
	 * @return $this
	 */
	public function removeContact(Contact $contact) {
		if ($this->contacts->contains($contact)) {
			$this->contacts->removeElement($contact);
			$contact->removeAccount($this);
		}
		return $this;
	}

	/**
	 * @param Contact $contact
	 * @return bool
	 */
	public function hasContact(Contact $contact) {
		return $this->getContacts()->contains($contact);
	}
}