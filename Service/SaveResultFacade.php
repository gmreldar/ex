<?php

declare(strict_types=1);

namespace App\Facade;

use App\Events\GoogleSpreadsheetEvent;
use App\Events\MailChimpEvent;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class SaveResultFacade
 * @package App\Facade
 */
class SaveResultFacade
{
    /*** @var Request */
    protected $request;

    /*** @var array */
    protected $questionData;

    /**
     * SaveResultFacade constructor.
     * @param Request $request
     * @param array $questionData
     */
    public function __construct(Request $request, array $questionData)
    {
        $this->request = $request;
        $this->questionData = $questionData;
    }

    /**
     * @return array
     */
    public function save(): array
    {
        try {
            DB::beginTransaction();
            MailChimpEvent::dispatch($this->request);
            $result = Result::create($this->questionData);
            GoogleSpreadsheetEvent::dispatch($result);
            DB::commit();
        } catch (Exception $exception) {
            info($exception->getMessage());
            DB::rollBack();
            return ['message' => $exception->getMessage(), 'code' => (int) $exception->getCode()];
        }
        return [];
    }
}
