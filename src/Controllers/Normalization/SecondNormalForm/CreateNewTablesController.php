<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Normalization\SecondNormalForm;

use PhpMyAdmin\Controllers\InvocableController;
use PhpMyAdmin\Current;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Normalization;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Routing\Route;

use function json_decode;

#[Route('/normalization/2nf/create-new-tables', ['POST'])]
final class CreateNewTablesController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly Normalization $normalization,
    ) {
    }

    public function __invoke(ServerRequest $request): Response
    {
        $partialDependencies = json_decode($request->getParsedBodyParamAsString('pd'), true);
        $tablesName = json_decode($request->getParsedBodyParamAsString('newTablesName'));
        $res = $this->normalization->createNewTablesFor2NF(
            $partialDependencies,
            $tablesName,
            Current::$table,
            Current::$database,
        );
        $this->response->addJSON($res);

        return $this->response->response();
    }
}
