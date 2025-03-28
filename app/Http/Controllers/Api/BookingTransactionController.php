<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionApiResource;
use App\Models\BookingTransaction;
use App\Models\Cosmetic;
use GuzzleHttp\Psr7\FnStream;
use PhpParser\Node\Expr\FuncCall;

class BookingTransactionController extends Controller
{
    
    public function store(StoreBookingTransactionRequest $request)
    {
        try
        {
            $validatedData = $request->validated();


            // Store the proof file
            if($request->hasFile('proof'))
            {
                $filepaths = $request->file('proof')->store('proofs', 'public');
                $validatedData['proof'] = $filepaths;
            }

            //retrieve product and calculate the total price and quantity
            $products = $request->input('cosmetic_ids');
            $totalPrice = 0;
            $totalQuantity = 0;

            $cosmeticsIds = array_column($products, 'id');
            $cosmetics = Cosmetic::whereIn('id', $cosmeticsIds)->get();

            foreach ($products as $product)
            {
                $cosmetic = $cosmetics->firstwhere('id', $product['id']);
                $totalQuantity += $product['quantity'];
                $totalPrice += $cosmetic->price * $product['quantity'];

                $tax = 0.12 * $totalPrice;
                $grandTotal = $totalPrice + $tax;

                //populate the booking transaction data
                $validatedData['total_amount'] = $grandTotal;
                $validatedData['total_tax_amount'] = $tax;
                $validatedData['sub_total_amount'] = $totalPrice;
                $validatedData['is_paid'] = false;
                $validatedData['booking_trx_id'] = BookingTransaction::generateUniqueTrxId();
                $validatedData['quantity'] = $totalQuantity;

                //create the booking transaction
                $bookingTransaction = BookingTransaction::create($validatedData);

                //create the transaction details each product
                foreach($products as $product)
                {
                    $cosmetic = $cosmetics->firstwhere('id', $product['id']);
                    $bookingTransaction->transactionDetails()->create([
                        'cosmetic_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $cosmetic->price
                    ]);
                }

                return new BookingTransactionApiResource($bookingTransaction->load('transactionDetails'));
            }
        }
        catch(\Exception $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function booking_details(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'booking_trx_id' => 'required|string',

        ]);

        $booking = BookingTransaction::where('email', $request->email)
        ->where('booking_trx_id', $request->booking_trx_id)
        ->with([
            'transactionDetails',
            'transactionDetails.cosmetic',
        ])
        ->first();

        if(!$booking)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }
        return new BookingTransactionApiResource($booking);

    }
}
