<?php
namespace Mekit\Bundle\RelationshipBundle\EventListener;

use Mekit\Bundle\FormBundle\Form\DataTransformer\ReferenceableEntitiesToIdsTransformer;
use Mekit\Bundle\RelationshipBundle\Entity\Manager\ReferenceManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;

use Mekit\Bundle\RelationshipBundle\Entity\Referenceable;/*this is the interface all entities must have if they are referenceable*/
use Mekit\Bundle\RelationshipBundle\Entity\ReferenceableElement;
use Symfony\Component\Form\FormInterface;

class ReferenceableElementTypeListener implements EventSubscriberInterface {
	/** @var  ReferenceManager */
	protected $referenceManager;

	/** @var  EntityManager */
	protected $entityManager;

	/** @var ReferenceableEntitiesToIdsTransformer  */
	protected $idToEntityTransformer;

	/** @var  Array */
	protected $currentData;

	/** @var  Array */
	protected $submitReferences;

	/**
	 * @param ReferenceManager $referenceManager
	 */
	public function __construct(ReferenceManager $referenceManager) {
		$this->referenceManager = $referenceManager;
		$this->entityManager = $this->referenceManager->getEntityManager();
		$this->idToEntityTransformer = new ReferenceableEntitiesToIdsTransformer($this->entityManager, 'Mekit\Bundle\RelationshipBundle\Entity\ReferenceableElement');
	}

	public static function getSubscribedEvents() {
		return [
			FormEvents::PRE_SET_DATA => 'preSetData',
			FormEvents::POST_SET_DATA => 'postSetData',
			FormEvents::PRE_SUBMIT => 'preSubmit',
			FormEvents::SUBMIT => 'onSubmit'
		];
	}

	/**
	 * @param FormEvent $event
	 */
	public function preSetData(FormEvent $event) {
		if($event->getData()) {
			$this->createFields($event->getForm());
		}
	}

	/**
	 * The referenceableElementType form to which we are adding this subscriber has the ReferenceableElement entity
	 * as underlying entity bound to the form. This ReferenceableElement is that of the owning entity we are editing.
	 * The relationship to the owning entity is OneToOne and we can get it with ReferenceableElement::getBaseEntity or
	 * with Entity specific (getAccount, getContact, etc.) getters.
	 *
	 * @param FormEvent $event
	 */
	public function postSetData(FormEvent $event) {
		/** @var ReferenceableElement $entity */
		if(($entity = $event->getData())) {
			if(!$entity instanceof ReferenceableElement) {
				throw new UnexpectedTypeException($entity, 'Mekit\Bundle\RelationshipBundle\Entity\ReferenceableElement');
			}
			$this->currentData = $this->getReferencesDataArray($entity);
			$this->populateFields($event->getForm());
		}
	}

	/**
	 * Confronts currentData(holds ReferenceableElement entities) with postData(comma separated list of ids of ReferenceableElements)
	 * and calculates which should be added/removed from references
	 * @param FormEvent $event
	 */
	public function preSubmit(FormEvent $event) {
		//$this->currentData
		if(($postData = $event->getData())) {
			$currentIds = [];
			$postedIds = [];

			/** @var String $v */
			foreach($postData as $v) {
				if (!empty($v)) {
					$ids = explode(",", $v);
					if (count($ids)) {
						$ids = array_map('intval', $ids);
						$postedIds = array_merge($postedIds, $ids);
					}
				}
			}

			/** @var ReferenceableElement[] $references */
			foreach($this->currentData as $references) {
				foreach($references as $referenceableElement) {
					$currentIds[] = $referenceableElement->getId();
				}
			}

			$addIds = array_values(array_diff($postedIds, $currentIds));
			$removeIds = array_values(array_diff($currentIds, $postedIds));


			$this->submitReferences["add"] = $this->idToEntityTransformer->reverseTransform($addIds);
			$this->submitReferences["remove"] = $this->idToEntityTransformer->reverseTransform($removeIds);

			echo("<hr />CURRENT: " . json_encode($currentIds));
			echo("<hr />POST: " . json_encode($postedIds));

			echo("<hr />ADD: " . json_encode($addIds));
			echo("<hr />REMOVE: " . json_encode($removeIds));

		}
	}

	public function onSubmit(FormEvent $event) {
		/** @var ReferenceableElement $entity */
		if(($entity = $event->getData())) {
			echo("<hr />REFS TO ADD: " . count($this->submitReferences["add"]));
			echo("<hr />REFS REMOVE: " . count($this->submitReferences["remove"]));

			/** @var ReferenceableElement $referenceableElement */
			foreach($this->submitReferences["add"] as $referenceableElement) {
				$entity->addReference($referenceableElement);
			}
			/** @var ReferenceableElement $referenceableElement */
			foreach($this->submitReferences["remove"] as $referenceableElement) {
				$entity->removeReference($referenceableElement);
			}


			//die();
		}
	}

	/**
	 * Creates all reference fields dynamically for all referenceable entities
	 *
	 * @param FormInterface $form
	 */
	protected function createFields(FormInterface $form) {
		$referenceableConfigs = $this->referenceManager->getReferenceableEntityConfigurations();
		foreach($referenceableConfigs as $config) {
			$entityName = $config->getId()->getClassName();
			$entityLabel = $config->get("label", false, $entityName);
			$entitySearchColumns = $config->get("autocomplete_search_columns", false, []);
			$fieldName = str_replace('\\', '_', $entityName);
			$fieldOptions = [
				'mapped' => false,
				'required' => false,
				'label' => $entityLabel,
				'configs' => [
					'entity_name' => $entityName, /* The entity type we want to select */
					'entity_fields' => $entitySearchColumns,
					'entity_id' => 0 /* @todo: this is unused and can be removed */
				]
			];
			$form->add($fieldName, 'referenceable_element_multi_select2', $fieldOptions);
			//echo '<br />' . $entityName . " - " . json_encode($entitySearchColumns);
		}
	}

	/**
	 * Populates fields with data
	 *
	 * @param FormInterface $form
	 */
	protected function populateFields(FormInterface $form) {
		$fields = $form->all();
		foreach($fields as $field) {
			$fieldConfigsArray = $field->getConfig()->getOption("configs", []);
			$fieldEntityName =  (isset($fieldConfigsArray["entity_name"]) ? $fieldConfigsArray["entity_name"] : false);
			if($fieldEntityName) {
				//echo '<br />' . $field->getName() . ": " . $fieldEntityName;
				if(array_key_exists($fieldEntityName, $this->currentData)) {
					$field->setData($this->currentData[$fieldEntityName]);
				}
			}
		}
	}

	/**
	 * Builds array of references connected to $referenceableElement indexed by className so we can feed them to specific form fields
	 * @todo: for now this holds specific entities because ReferenceableEntitiesToIdsTransformer is wrong!!!
	 *
	 * @param ReferenceableElement $referenceableElement
	 * @return array
	 */
	protected function getReferencesDataArray(ReferenceableElement $referenceableElement) {
		$answer = [];
		$references = $referenceableElement->getReferences();
		foreach($references as $reference) {
			$baseEntity = $reference->getBaseEntity();
			$baseEntityClass = $this->referenceManager->getRealClassName($baseEntity);
			$answer[$baseEntityClass][] = $reference;
		}
		return $answer;
	}
}