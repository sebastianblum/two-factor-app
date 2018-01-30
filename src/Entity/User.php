<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserInterface, \Serializable, EmailTwoFactorInterface, GoogleTwoFactorInterface, TrustedComputerInterface, BackupCodeInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @var array
     * @ORM\Column(type="simple_array")
     */
    private $backupCodes = [];

    /**
     * @var string $authCode
     * @ORM\Column(type="integer")
     */
    private $authCode;

    /**
     * @var string $googleAuthenticatorSecret
     * @ORM\Column(type="string")
     */
    private $googleAuthenticatorSecret;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    private $trusted = [];

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    public function getEmailAuthCode(): int
    {
        return $this->authCode;
    }

    public function setEmailAuthCode(int $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function addTrustedComputer(string $token, \DateTime $validUntil): void
    {
        $this->trusted[$token] = $validUntil->format("r");
    }

    public function isTrustedComputer(string $token): bool
    {
        if (isset($this->trusted[$token])) {
            $now = new \DateTime();
            $validUntil = new \DateTime($this->trusted[$token]);
            return $now < $validUntil;
        }
        return false;
    }

    public function isBackupCode(string $code): bool
    {
        return in_array($code, $this->backupCodes);
    }

    public function invalidateBackupCode(string $code): void
    {
        $key = array_search($code, $this->backupCodes);
        if($key !== false){
            unset($this->backupCodes[$key]);
        }
    }
}
