<?php
namespace Mekit\Bundle\ListBundle\Controller;

use Mekit\Bundle\ListBundle\Entity\ListGroup;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ListController
 */
class ListController extends Controller
{

	/**
	 * @Route(
	 *      "/{_format}",
	 *      name="mekit_list_index",
	 *      requirements={"_format"="html|json"},
	 *      defaults={"_format" = "html"}
	 * )
	 * @Template
	 * @AclAncestor("mekit_list_view")
	 * @return array
	 */
	public function indexAction() {
		return array(
			'entity_class' => $this->container->getParameter('mekit_list.listgroup.entity.class')
		);
	}

	/**
	 * @Route("/view/{id}", name="mekit_list_view", requirements={"id"="\d+"})
	 * @Template
	 * @Acl(
	 *      id="mekit_list_view",
	 *      type="entity",
	 *      class="MekitListBundle:ListGroup",
	 *      permission="VIEW"
	 * )
	 * @param ListGroup $listGroup
	 * @return array
	 */
	public function viewAction(ListGroup $listGroup) {
		return [
			'entity' => $listGroup
		];
	}

	/**
	 * @param ListGroup $entity
	 * @return array
	 */
	protected function update(ListGroup $entity = null) {
		if (!$entity) {
			$entity = $this->getListGroupManager()->createEntity();
		}

		return $this->get('oro_form.model.update_handler')->handleUpdate(
			$entity, $this->get('mekit_list.form.listgroup'), function (ListGroup $entity) {
			return array(
				'route' => 'mekit_list_update',
				'parameters' => array('id' => $entity->getId())
			);
		}, function (ListGroup $entity) {
			return array(
				'route' => 'mekit_list_view',
				'parameters' => array('id' => $entity->getId())
			);
		}, $this->get('translator')->trans('mekit.list.controller.list.saved.message'),
			$this->get('mekit_list.form.handler.listgroup')
		);
	}

	/**
	 * @return ApiEntityManager
	 */
	protected function getListGroupManager() {
		return $this->get('mekit_list.listgroup.manager.api');
	}

	/**
	 * @Route("/update/{id}", name="mekit_list_update", requirements={"id"="\d+"})
	 * @Acl(
	 *      id="mekit_list_update",
	 *      type="entity",
	 *      permission="EDIT",
	 *      class="MekitListBundle:ListGroup"
	 * )
	 * @Template()
	 * @param ListGroup $listGroup
	 * @return array
	 */
	public function updateAction(ListGroup $listGroup) {
		return $this->update($listGroup);
	}

	/**
	 * @Route("/widget/info/{id}", name="mekit_list_widget_info", requirements={"id"="\d+"})
	 * @AclAncestor("mekit_list_view")
	 * @Template(template="MekitListBundle:List/widget:listGroupInfo.html.twig")
	 * @param ListGroup $listGroup
	 * @return array
	 */
	public function infoAction(ListGroup $listGroup) {
		return [
			'entity' => $listGroup
		];
	}

	/**
	 * @Route("/widget/listitems/{id}", name="mekit_list_widget_listitems", requirements={"id"="\d+"})
	 * @AclAncestor("mekit_list_view")
	 * @Template(template="MekitListBundle:List/widget:listItems.html.twig")
	 * @param ListGroup $listGroup
	 * @return array
	 */
	public function listItemsAction(ListGroup $listGroup) {
		return [
			'entity' => $listGroup
		];
	}
}