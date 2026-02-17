<?php

namespace App\Http\Controllers\Api;

use App\DTOs\TransactionDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionCollectionPaginated;
use App\Http\Resources\TransactionResource;
use App\Services\PaymentService;
use App\Http\Requests\ProcessPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function __construct(protected PaymentService $paymentService) { }


    public function index()
    {
        $transactions = $this->paymentService->getTransactionHistory();

        return new TransactionCollection($transactions);
    }

    public function getTransactions(Request $request){
        $perPage = $request->query('per_page', 10);
        $transactions = $this->paymentService->getTransactionHistoryPaginated($perPage);

        return new TransactionCollectionPaginated($transactions);
    }

    public function store(ProcessPaymentRequest $request)
    {
        // Create DTO from validated request
        $dto = new TransactionDTO(
            amount: $request->validated('amount'),
            currency: strtoupper($request->validated('currency')),
            cardNumber: $request->validated('cardNumber'),
            cardHolder: $request->validated('cardHolder'),
        );

        // Process Payment
        $transaction = $this->paymentService->processPayment($dto);

        // Return appropriate response based on status
        $statusCode = $transaction->isApproved()
            ? Response::HTTP_CREATED
            : Response::HTTP_OK;

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode($statusCode);
    }
}
