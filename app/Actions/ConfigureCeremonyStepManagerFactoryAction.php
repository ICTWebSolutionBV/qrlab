<?php

namespace App\Actions;

use Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction as BaseAction;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class ConfigureCeremonyStepManagerFactoryAction extends BaseAction
{
    public function execute(): CeremonyStepManagerFactory
    {
        $csmFactory = parent::execute();

        $csmFactory->setSecuredRelyingPartyId([
            parse_url(config('app.url'), PHP_URL_HOST),
        ]);

        return $csmFactory;
    }
}
