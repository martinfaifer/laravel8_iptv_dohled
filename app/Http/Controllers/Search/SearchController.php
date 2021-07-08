<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public $result = [];


    public function search(Request $request): array
    {
        if (empty($request->search)) {
            return $this->result;
        }

        if (is_null($request->search)) {
            return $this->result;
        }

        // vyhledávání kanálů dle názvu
        $result_streams = Stream::where('nazev', 'like', "%" . $request->search . "%")->get()->toArray();
        if (!empty($result_streams)) {
            foreach ($result_streams as $result_stream) {
                $this->result[] = [
                    'id' => uniqid(),
                    'nazev' => $result_stream['nazev'],
                    'description' => "stream",
                    'url' => "/stream/{$result_stream['id']}"
                ];
            }
        }

        return $this->result;
    }
}
