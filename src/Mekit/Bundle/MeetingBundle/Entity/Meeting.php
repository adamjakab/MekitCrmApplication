<?php
namespace Mekit\Bundle\MeetingBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mekit\Bundle\AccountBundle\Model\ExtendAccount;
use Mekit\Bundle\EventBundle\Entity\Event;
use Mekit\Bundle\EventBundle\Entity\EventInterface;
use Mekit\Bundle\EventBundle\Model\ExtendEvent;
use Mekit\Bundle\ListBundle\Entity\ListItem;
use Mekit\Bundle\MeetingBundle\Model\ExtendMeeting;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Mekit\Bundle\MeetingBundle\Entity\Repository\MeetingRepository")
 * @ORM\Table(name="mekit_meeting", indexes={
 *      @ORM\Index(name="idx_meeting_name", columns={"name"})
 * })
 * @Oro\Loggable
 * @Config(
 *      routeName="mekit_meeting_index",
 *      routeView="mekit_meeting_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-group"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "mekitevent"={
 *              "eventable"=true,
 *              "label"="mekit.meeting.entity_label",
 *              "icon"="icon-group",
 *              "view_route_name"="mekit_meeting_view",
 *              "edit_route_name"="mekit_meeting_edit"
 *          }
 *      }
 * )
 */
class Meeting extends ExtendMeeting implements EventInterface {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Soap\ComplexType("int", nillable=true)
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255, nullable=false)
	 * @Oro\Versioned
	 */
	protected $name;

	/**
	 * @var Event
	 *
	 * @ORM\OneToOne(targetEntity="Mekit\Bundle\EventBundle\Entity\Event", mappedBy="meeting", cascade={"all"}, fetch="EAGER")
	 * @Soap\ComplexType("Mekit\Bundle\EventBundle\Entity\Event", nillable=false)
	 * @ConfigField(
	 *      defaultValues={}
	 * )
	 */
	protected $event;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return Event
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @param Event $event
	 * @return $this
	 */
	public function setEvent(Event $event) {
		$this->event = $event;
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return (string)$this->getName();
	}
}