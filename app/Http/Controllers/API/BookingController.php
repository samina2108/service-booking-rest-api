<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    // Customers see their own bookings; admins see all.
    public function index(Request $request)
    {
        $query = Booking::with('service', 'user')
            ->latest();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(10),
        ]);
    }

    // Create a booking for the logged-in customer.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:2000',
        ]);

        $service = Service::where('is_active', true)
            ->findOrFail($validated['service_id']);

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'service_id' => $service->id,
            'booking_date' => $validated['booking_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'price' => $service->price,
            'payment_status' => 'unpaid',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load('service'),
        ], 201);
    }

    // Customer can only view their own booking.
    public function show(Request $request, $id)
    {
        $query = Booking::with('service', 'user');

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        $booking = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    // Customer can cancel their own pending booking.
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where(
            'user_id',
            $request->user()->id
        )->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be cancelled.',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking,
        ]);
    }

    // Admin changes the booking status.
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'confirmed',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'data' => $booking,
        ]);
    }


   
public function updatePaymentStatus(Request $request, $id)
{
    $validated = $request->validate([
        'payment_status' => 'required|in:unpaid,paid,refunded',
    ]);

    $booking = Booking::findOrFail($id);

    $booking->update([
        'payment_status' => $validated['payment_status'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Payment status updated successfully',
        'data' => $booking,
    ]);
}

}