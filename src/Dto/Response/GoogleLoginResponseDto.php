<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 27.03.19
 * Time: 9:36
 */

namespace Sf4\ApiSecurity\Dto\Response;

use Sf4\Api\Dto\Response\AbstractResponseDto;
use Sf4\ApiUser\Dto\Response\DetailDto;

class GoogleLoginResponseDto extends AbstractResponseDto
{
    /** @var string|null $id */
    protected $id;

    /** @var string|null $name */
    protected $name;

    /** @var string|null $email */
    protected $email;

    /** @var string|null $image */
    protected $image;

    /** @var DetailDto|null $user */
    protected $user;

    public function toArray(): array
    {
        return $this->objectToArray($this, []);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return DetailDto|null
     */
    public function getUser(): ?DetailDto
    {
        return $this->user;
    }

    /**
     * @param DetailDto|null $user
     */
    public function setUser(?DetailDto $user): void
    {
        $this->user = $user;
    }
}
