<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 27.03.19
 * Time: 9:30
 */

namespace Sf4\ApiSecurity\Response;

use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Sf4\Api\Response\AbstractResponse;
use Sf4\Api\Utils\Traits\SerializerTrait;
use Sf4\ApiSecurity\Dto\Request\GoogleLoginRequestDto;
use Sf4\ApiSecurity\Dto\Response\GoogleLoginResponseDto;
use Sf4\ApiSecurity\Exception\InvalidGoogleTokenException;
use Sf4\ApiUser\Dto\Response\DetailDto;
use Sf4\ApiUser\Entity\UserInterface;
use Sf4\ApiUser\Repository\UserRepository;
use Sf4\ApiUser\EntitySaver\UserCreator;
use Exception;

class GoogleLoginResponse extends AbstractResponse
{
    use SerializerTrait;

    public const PROP_APP_NAME = 'google_app_name';
    public const PROP_CLIENT_ID = 'google_client_id';

    protected const TOKEN = 'id_token';

    private const ID ='id';
    private const SUB = 'sub';
    private const NAME = 'name';
    private const EMAIL = 'email';
    private const PICTURE = 'picture';
    private const AVATAR = 'avatar';
    private const USER = 'user';

    /**
     * @throws InvalidGoogleTokenException
     */
    public function init()
    {
        $dto = new GoogleLoginResponseDto();
        $this->populateResponseDto($dto);
        $this->setResponseDto($dto);
    }

    /**
     * @param GoogleLoginResponseDto $dto
     * @throws InvalidGoogleTokenException
     * @throws Exception
     */
    protected function populateResponseDto(GoogleLoginResponseDto $dto): void
    {
        $data = [];
        $idToken = $this->getGoogleIdToken();
        $applicationName = $this->getParameter(static::PROP_APP_NAME);
        $clientId = $this->getParameter(static::PROP_CLIENT_ID);

        if ($idToken && $applicationName && $clientId) {
            $client = new Google_Client();
            $client->setApplicationName($applicationName);
            $client->setClientId($clientId);

            $ticket = $client->verifyIdToken($idToken);
            if ($ticket && is_array($ticket)) {
                $data = [
                    static::ID => $ticket[static::SUB],
                    static::NAME => $ticket[static::NAME],
                    static::EMAIL => $ticket[static::EMAIL],
                    static::AVATAR => $ticket[static::PICTURE],
                    static::USER => $this->getUserDetailDto(
                        $ticket[static::SUB],
                        $ticket[static::EMAIL],
                        $ticket[static::NAME],
                        $ticket[static::PICTURE]
                    )
                ];
            }
        }
        if (empty($data)) {
            throw new InvalidGoogleTokenException(
                $this->translate(
                    InvalidGoogleTokenException::TRANSLATION_MESSAGE_KEY
                )
            );
        }
        $detailDto = null;
        if (isset($data[static::USER])) {
            $detailDto = $data[static::USER];
            unset($data[static::USER]);
        }
        $this->populateDto($dto, $data);
        $dto->setUser($detailDto);
    }

    /**
     * @return string|null
     */
    protected function getGoogleIdToken(): ?string
    {
        $request = $this->getRequest();
        /** @var GoogleLoginRequestDto $requestDto */
        $requestDto = $request ? $request->getDto() : null;

        return $requestDto ? $requestDto->getIdToken() : null;
    }

    /**
     * @param string $googleId
     * @param string $email
     * @param string $name
     * @param string $avatar
     * @return DetailDto|null
     * @throws Exception
     */
    protected function getUserDetailDto(
        string $googleId,
        string $email,
        string $name,
        string $avatar
    ): ?DetailDto {
        $request = $this->getRequest();
        $requestHandler = $request ? $request->getRequestHandler() : null;
        $repositoryFactory = $requestHandler ? $requestHandler->getRepositoryFactory() : null;
        /** @var UserRepository $repository */
        $repository = $repositoryFactory ? $repositoryFactory->create(
            UserRepository::TABLE_NAME
        ) : null;
        $entity = $repository ? $repository->findOneBy([
            'google_id' => $googleId
        ]) : null;

        if (null === $entity) {
            $entity = $this->createNewUser($googleId, $email, $name, $avatar);
        }

        if ($entity) {
            $data = $this->objectToArray($entity, [
                'id',
                'uuid',
                'password',
                'googleId',
                'createdAt',
                'updatedAt',
                'deletedAt',
                'createdBy',
                'updatedBy',
                'deletedBy',
                '__initializer__',
                '__cloner__',
                '__isInitialized__'
            ]);
            $data = array_merge($data, $data['userDetail']);
            unset($data['userDetail']);

            $dto = new DetailDto();
            $dto->populate($data);
            $dto->setId($entity->getUuid()->toString());

            return $dto;
        }

        return null;
    }

    /**
     * @param string $googleId
     * @param string $email
     * @param string $name
     * @param string $avatar
     * @return UserInterface|null
     * @throws Exception
     */
    protected function createNewUser(
        string $googleId,
        string $email,
        string $name,
        string $avatar
    ): ?UserInterface {
        $entityManager = $this->getEntityManager();
        if ($entityManager) {
            $userCreator = new UserCreator($entityManager);
            $userData = $this->splitName($name);
            $data = [
                'google_id' => $googleId,
                'create_api_token' => true,
                'firstName' => $userData[0],
                'lastName' => $userData[1],
                static::EMAIL => $email,
                static::AVATAR => $avatar
            ];
            return $userCreator->create($data);
        }

        return null;
    }

    /**
     * @param $name
     * @return array
     */
    protected function splitName($name): array
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace(
            '#.*\s([\w-]*)$#',
            '$1',
            $name
        );
        $first_name = trim(preg_replace('#'.$last_name.'#', '', $name));
        return array($first_name, $last_name);
    }

    /**
     * @return EntityManagerInterface|null
     */
    protected function getEntityManager(): ?EntityManagerInterface
    {
        $requestHandler = $this->getRequestHandler();
        if ($requestHandler) {
            return $requestHandler->getEntityManager();
        }

        return null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function getParameter(string $name): ?string
    {
        $request = $this->getRequest();
        if ($request) {
            $requestHandler = $request->getRequestHandler();
            if ($requestHandler) {
                return $requestHandler->getParameter($name);
            }
        }

        return null;
    }
}
