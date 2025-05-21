<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiftcardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $gift_card = GiftCard::orderBy('amount', 'ASC')->get();
        return view('admin.giftcard.view_giftcard', compact('gift_card'));
    }

    public function addGiftcard()
    {
        return view('admin.giftcard.add_giftcard');
    }

    public function updateGiftcard($idd)
    {
        $id = base64_decode($idd);
        $gift = GiftCard::findOrFail($id);
        return view('admin.giftcard.update_giftcard', compact('id', 'gift'));
    }

    public function addGiftcardData(Request $request, $t, $iw = "")
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'count' => 'required|integer',
            'start_range' => 'required|numeric',
            'end_range' => 'required|numeric',
            'image1' => 'nullable|file|mimes:xlsx,csv,xls,pdf,doc,docx,txt,jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $typ = base64_decode($t);
        $imagePath = null;

        if ($request->hasFile('image1')) {
            $destinationPath = public_path('assets/uploads/gift_card');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file = $request->file('image1');
            $fileName = 'gift_card' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imagePath = "assets/uploads/gift_card/{$fileName}";
        }

        $data = [
            'amount' => $request->amount,
            'gift_count' => $request->count,
            'start_range' => $request->start_range,
            'end_range' => $request->end_range,
            'ip' => $request->ip(),
            'added_by' => auth('admin')->id(),
            'is_active' => 1,
            'allocated' => 1,
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        if ($typ == 1) {
            // Add new gift card
            if ($imagePath) {
                $data['image'] = basename($imagePath); // Store only the filename like CI
            }
            $last_id = GiftCard::create($data);
        } elseif ($typ == 2) {
            // Update existing gift card
            $idw = base64_decode($iw);
            $gift = GiftCard::findOrFail($idw);
            $data['image'] = $imagePath ? basename($imagePath) : $gift->image;
            $last_id = $gift->update($data);
        } else {
            return redirect()->back()->with('emessage', 'Invalid operation type');
        }

        if ($last_id) {
            return redirect()->route('admin.giftcard.index')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function deleteGift(Request $request, $id)
    {
        $decoded_id = base64_decode($id);

        // if (auth('admin')->user()->position !== 'Super Admin') {
        //     return view('errors.error500admin', ['e' => "Sorry, You Don't Have Permission To Delete Anything."]);
        // }

        $gift = GiftCard::find($decoded_id);
        if ($gift) {
            $image_path = public_path('assets/uploads/gift_card/' . $gift->image);
            if ($gift->image && file_exists($image_path)) {
                unlink($image_path);
            }
            $gift->delete();
            return redirect()->back()->with('smessage', 'Gift card deleted successfully');
        }

        return redirect()->back()->with('emessage', 'Gift card not found or already deleted');
    }

    public function updateGiftCardStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $is_active = $t === 'active' ? 1 : ($t === 'inactive' ? 0 : null);

        if (is_null($is_active)) {
            return redirect()->route('admin.giftcard.index')->with('emessage', 'Invalid status type');
        }

        $updated = GiftCard::where('id', $id)->update(['is_active' => $is_active]);

        if ($updated) {
            return redirect()->route('admin.giftcard.index')->with('smessage', 'Gift card status updated successfully');
        }

        return view('errors.error500admin', ['e' => 'Error occurred while updating the gift card status']);
    }

    public function allocated($alt_id)
    {
        $id = base64_decode($alt_id);
        $farmers = Farmer::where('giftcard_id', $id)->get();
        return view('admin.giftcard.alloted_gift', compact('farmers'));
    }
}