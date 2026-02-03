<?php

namespace Karolina\User;


class UserInteractor
{
    private $repository;

    public function __construct(UserRepository $repository)
    {

        $this->repository = $repository;
    }

    public function getStats () {

        return $this->repository->getUserStats();

    }

    public function getUsersById (array $idArray) {

        return $this->repository->getByIds($idArray);

    }

    public function getDocumentsByIds (array $idArray) {

        $docs = $this->repository->getDocumentsByIds($idArray);

        return $docs;

    }

}