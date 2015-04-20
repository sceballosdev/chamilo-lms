<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CItemProperty
 *
 * @ORM\Table(name="c_item_property", indexes={@ORM\Index(name="idx_item_property_toolref", columns={"tool", "ref"})})
 * @ORM\Entity
 */
class CItemProperty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="iid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $iid;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="c_id", type="integer")
     */
    private $cId;

    /**
     * @var string
     *
     * @ORM\Column(name="tool", type="string", length=100, nullable=false)
     */
    private $tool;

    /**
     * @var integer
     *
     * @ORM\Column(name="insert_user_id", type="integer", nullable=false)
     */
    private $insertUserId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_date", type="datetime", nullable=false)
     */
    private $insertDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastedit_date", type="datetime", nullable=false)
     */
    private $lasteditDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="ref", type="integer", nullable=false)
     */
    private $ref;

    /**
     * @var string
     *
     * @ORM\Column(name="lastedit_type", type="string", length=100, nullable=false)
     */
    private $lasteditType;

    /**
     * @var integer
     *
     * @ORM\Column(name="lastedit_user_id", type="integer", nullable=false)
     */
    private $lasteditUserId;

    /**
     * @var integer
     *
     * @ORM\Column(name="to_group_id", type="integer", nullable=true)
     */
    private $toGroupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="to_user_id", type="integer", nullable=true)
     */
    private $toUserId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visibility", type="boolean", nullable=false)
     */
    private $visibility;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_visible", type="datetime", nullable=false)
     */
    private $startVisible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_visible", type="datetime", nullable=false)
     */
    private $endVisible;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_id", type="integer", nullable=false)
     */
    private $idSession;

    /**
     * Set tool
     *
     * @param string $tool
     * @return CItemProperty
     */
    public function setTool($tool)
    {
        $this->tool = $tool;

        return $this;
    }

    /**
     * Get tool
     *
     * @return string
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * Set insertUserId
     *
     * @param integer $insertUserId
     * @return CItemProperty
     */
    public function setInsertUserId($insertUserId)
    {
        $this->insertUserId = $insertUserId;

        return $this;
    }

    /**
     * Get insertUserId
     *
     * @return integer
     */
    public function getInsertUserId()
    {
        return $this->insertUserId;
    }

    /**
     * Set insertDate
     *
     * @param \DateTime $insertDate
     * @return CItemProperty
     */
    public function setInsertDate($insertDate)
    {
        $this->insertDate = $insertDate;

        return $this;
    }

    /**
     * Get insertDate
     *
     * @return \DateTime
     */
    public function getInsertDate()
    {
        return $this->insertDate;
    }

    /**
     * Set lasteditDate
     *
     * @param \DateTime $lasteditDate
     * @return CItemProperty
     */
    public function setLasteditDate($lasteditDate)
    {
        $this->lasteditDate = $lasteditDate;

        return $this;
    }

    /**
     * Get lasteditDate
     *
     * @return \DateTime
     */
    public function getLasteditDate()
    {
        return $this->lasteditDate;
    }

    /**
     * Set ref
     *
     * @param integer $ref
     * @return CItemProperty
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return integer
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set lasteditType
     *
     * @param string $lasteditType
     * @return CItemProperty
     */
    public function setLasteditType($lasteditType)
    {
        $this->lasteditType = $lasteditType;

        return $this;
    }

    /**
     * Get lasteditType
     *
     * @return string
     */
    public function getLasteditType()
    {
        return $this->lasteditType;
    }

    /**
     * Set lasteditUserId
     *
     * @param integer $lasteditUserId
     * @return CItemProperty
     */
    public function setLasteditUserId($lasteditUserId)
    {
        $this->lasteditUserId = $lasteditUserId;

        return $this;
    }

    /**
     * Get lasteditUserId
     *
     * @return integer
     */
    public function getLasteditUserId()
    {
        return $this->lasteditUserId;
    }

    /**
     * Set toGroupId
     *
     * @param integer $toGroupId
     * @return CItemProperty
     */
    public function setToGroupId($toGroupId)
    {
        $this->toGroupId = $toGroupId;

        return $this;
    }

    /**
     * Get toGroupId
     *
     * @return integer
     */
    public function getToGroupId()
    {
        return $this->toGroupId;
    }

    /**
     * Set toUserId
     *
     * @param integer $toUserId
     * @return CItemProperty
     */
    public function setToUserId($toUserId)
    {
        $this->toUserId = $toUserId;

        return $this;
    }

    /**
     * Get toUserId
     *
     * @return integer
     */
    public function getToUserId()
    {
        return $this->toUserId;
    }

    /**
     * Set visibility
     *
     * @param boolean $visibility
     * @return CItemProperty
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return boolean
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set startVisible
     *
     * @param \DateTime $startVisible
     * @return CItemProperty
     */
    public function setStartVisible($startVisible)
    {
        $this->startVisible = $startVisible;

        return $this;
    }

    /**
     * Get startVisible
     *
     * @return \DateTime
     */
    public function getStartVisible()
    {
        return $this->startVisible;
    }

    /**
     * Set endVisible
     *
     * @param \DateTime $endVisible
     * @return CItemProperty
     */
    public function setEndVisible($endVisible)
    {
        $this->endVisible = $endVisible;

        return $this;
    }

    /**
     * Get endVisible
     *
     * @return \DateTime
     */
    public function getEndVisible()
    {
        return $this->endVisible;
    }

    /**
     * Set idSession
     *
     * @param integer $idSession
     * @return CItemProperty
     */
    public function setIdSession($idSession)
    {
        $this->idSession = $idSession;

        return $this;
    }

    /**
     * Get idSession
     *
     * @return integer
     */
    public function getIdSession()
    {
        return $this->idSession;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return CItemProperty
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cId
     *
     * @param integer $cId
     * @return CItemProperty
     */
    public function setCId($cId)
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * Get cId
     *
     * @return integer
     */
    public function getCId()
    {
        return $this->cId;
    }
}
