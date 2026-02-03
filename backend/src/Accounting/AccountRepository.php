<?php

namespace Karolina\Accounting;
use \Karolina\Database\Table\Account as Model;
use \Karolina\Database\Table\Subscription as SubscriptionModel;


Class AccountRepository {


    public function getAccountById ($id) {

        $model = Model::findOrFail($id);

        if ($model) {

            $resource = $this->modelToAccount($model);

        }

        return $resource;

    }


    public function getAccountByCompanyId ($companyId) {

        $model = Model::where('company_identification', $companyId)->first();

        $account = NULL;

        if ($model) {

            $account = $this->modelToAccount($model);

        }

        return $account;

    }


    public function getSubscriptionById ($id) {

        $model = SubscriptionModel::findOrFail($id);

        if ($model) {

            $sub = $this->modelToSubscription($model);

        }

        return $sub;

    }

    public function getAccountsByCompanyId () {

        $accounts = $this->getAccounts();

        $byCompanyid = array();

        foreach ($accounts as $account) {

            $byCompanyid[$account->getCompanyIdentification()] = $account;

        }

        return $byCompanyid;
    
    } 

    public function getAccounts () {

        $models = \Karolina\Database\Table\Account::orderBy('account_id', 'desc')->get();

        $resources = array();

        foreach ($models as $model) {

            $resources[] = $this->modelToAccount($model);

        }

        return $resources;

    }

    public function saveAccount (Account $account) {

        // Check if anohter account with same company id exists
        $existing = Model::where('company_identification', $account->getCompanyIdentification())->where('account_id', '!=', $account->getId())
        ->first();

        if ($existing) {
            throw new \Exception('An account with the same company identification already exists.');
        }

        $model = $this->accountToModel($account);
        $model->platform = getenv('platform_key');
        $model->save();
        return $model->account_id;

    }


    public function saveSubscription (Subscription $subscription) {

        $model = $this->subscriptionToModel($subscription);
        $model->save();

    }

    private function modelToAccount ($model) {

            
            $account = new Account($model->currency);
            $account->__set('id', $model->account_id);
            $account->__set('name', $model->name);
            $account->__set('spaces', $model->spaces);
            $account->__set('companyIdentification', $model->company_identification);
            $account->__set('billingEmails', $model->billing_emails);
            $account->__set('status', $model->status);
            $account->__set('data', $model->data);

            foreach ($model->subscriptions as $subscriptionModel) {

                $account->addSubscription(
                    $this->modelToSubscription($subscriptionModel)
                );

            }

            return $account;

    }

    private function modelToSubscription ($model) {

        $subscription = new Subscription(
            $model->account_id,
            $model->item,
            $model->start,
            $model->end,
            $model->units,
            $model->unit_price,
            $model->currency
        );

        $subscription->__set('id', $model->subscription_id);

        return $subscription;
        

    }

    private function accountToModel (Account $account) {

        $model = Model::updateOrCreate(
            ['account_id' => $account->getId()],
            $account->getDocument()
        );

        foreach ($account->getSubscriptions() as $subscription) {
            $model->subscriptions()->updateOrCreate(
                ['subscription_id' => $subscription->getId()],
                $subscription->getDocument()
            );
        }        

        return $model;
    }


}