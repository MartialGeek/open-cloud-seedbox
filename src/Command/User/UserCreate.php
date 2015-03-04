<?php

namespace Martial\Warez\Command\User;

use Martial\Warez\User\EmailAlreadyExistsException;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\UsernameAlreadyExistsException;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreate extends Command
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a new Warez user.')
            ->addArgument('username', InputArgument::REQUIRED, 'The name used by the user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statusCode = 0;
        $user = new User();
        $user
            ->setUsername($input->getArgument('username'))
            ->setEmail($input->getArgument('email'))
            ->setPassword($input->getArgument('password'));

        try {
            $this->userService->register($user);
            $output->writeln('<info>User ' . $user->getUsername() . ' successfully created.</info>');
        } catch (EmailAlreadyExistsException $e) {
            $output->writeln('<error>The provided email is already used by another user.</error>');
            $statusCode = 1;
        } catch (UsernameAlreadyExistsException $e) {
            $output->writeln('<error>The provided username is already used by another user.</error>');
            $statusCode = 1;
        }

        return $statusCode;
    }
}
