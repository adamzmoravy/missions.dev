<?php

namespace Karolina;

class AccessControl
{

    private $rules = [
        [
            'target' => 'submissions::stage-2',
            'action' => 'read',
            'roles' => ['authenticated'],
            'permission' => 'deny',
            'priority' => 1
        ],
        [
            'target' => 'submissions::stage-2',
            'action' => 'read',
            'roles' => ['admin'],
            'permission' => 'allow',
            'priority' => 2
        ]
    ];

    private $user;
    private $target;
    
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;

    }

    public function user ($user)
    {
        $this->user = $user;
        return $this;
    }

    public function on ($target)
    {
        $this->target = $target;
        return $this;
    }

    private function targetMatch ($ruleTarget, $target) {
        if ($ruleTarget === $target) {
            return true;
        }
        $ruleParts = explode('::', $ruleTarget);
        $targetParts = explode('::', $target);
        if (count($ruleParts) !== count($targetParts)) {
            return false;
        }
        for ($i = 0; $i < count($ruleParts); $i++) {
            if ($ruleParts[$i] === '*') {
                continue;
            }
            if ($ruleParts[$i] !== $targetParts[$i]) {
                return false;
            }
        }
        return true;
    }

    public function can ($action)
    {
        $applicableRules = array_filter($this->rules, function ($rule) use ($action) {
            return $this->targetMatch($rule['target'], $this->target) && $rule['action'] === $action && count(array_intersect($this->getUserRoles(), $rule['roles'])) > 0;
        });

        if (count($applicableRules) === 0) {
            return false;
        }

        usort($applicableRules, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $applicableRules[0]['permission'] === 'allow';
    }


    private function getUserRoles ()
    {

        $roles = ['all'];

        if (!$this->user) {
            return $roles;
        }

        if ($this->user->getUserType() === 'member' || $this->user->getUserType() === 'admin') {
            $roles[] = 'authenticated';

            if ($this->user->getUserType() === 'admin') {
                $roles[] = 'admin';
            }
        }

        return $roles;

    }



}
