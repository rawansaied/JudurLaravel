<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class AuctionController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      */
//     public function index()
//     {
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         //
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(string $id)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit(string $id)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, string $id)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(string $id)
//     {
//         //
//     }
// }


namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    
    public function index()
    {
        $auctions = Auction::all();
        return response()->json($auctions);  
    }


    
  
    public function create()
    {
        
    }

  
    public function store(Request $request)
    {
        $auction = Auction::create($request->all());
        return response()->json($auction, 201);  
    }

 
    public function show($id)
    {
        $auction = Auction::findOrFail($id);
        return response()->json($auction);
    }


    public function edit($id)
    {
       
    }

   
    public function update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);
        $auction->update($request->all());
        return response()->json($auction);
    }

   
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);
        $auction->delete();
        return response()->json(null, 204); 
    }
}
