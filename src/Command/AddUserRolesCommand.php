<?php

namespace App\Command;

use App\Entity\User;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddUserRolesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('user:role:add')
            ->getDefinition()
            ->addArguments([
                new InputArgument('profileName', InputArgument::REQUIRED),
                new InputArgument('role', InputArgument::REQUIRED)
            ])
        ;
    }

    protected function proceed(InputInterface $input, OutputInterface $output)
    {
        $ss = new SymfonyStyle($input, $output);

        ['profileName' => $profileName, 'role' => $role] = $input->getArguments();

        $role = strtoupper($role);
        if (strpos($role, 'ROLE_') === false) {
            $role = "ROLE_{$role}";
        }

        try {
            $user = $this->em->createQueryBuilder()
                ->from(User::class, 'user')
                ->select('user')
                ->andWhere('user.profileName = :name')
                ->setParameter('name', $profileName)
                ->getQuery()
                ->getSingleResult();
        } catch (Exception $e) {
            $ss->error("Wasn't able to find the user. ".$e->getMessage());
            return;
        }

        if (!$user) {
            $ss->error("We don't know anything about `{$profileName}`");
            return;
        }

        $ss->note("Going to add {$role} role to {$profileName}");

        try {
            if (!$this->addRoleToUser($role, $user)) {
                $ss->note('But user already had this role');
            }
        } catch (Exception $e) {
            $ss->error($e->getMessage());
            return;
        }

        $ss->success('Done!');
    }

    protected function addRoleToUser(string $role, User $user)
    {
        if ($user->hasRole($role)) {
            return false;
        }

        $user->addRole($role);
        $this->em->persist($user);
        $this->em->flush();
        return true;
    }
}
