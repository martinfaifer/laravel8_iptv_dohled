<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;

class SearchController extends Controller
{


    /**
     * funkce na vyhledání napříč celou aplikací
     *
     * klíč: kanal
     * klíč: url
     * klíč: editace
     * klíč: nastavení
     * klíč: ...
     *
     * @param Request $request
     * @return
     */
    public function search_in_app(Request $request)
    {

        // return $request->search;

        if (empty($request->search)) {
            return [];
        }


        // pokus o vyhledání napříč kanály, vyhledání v názvu nebo v url

        if (Stream::where('nazev', "like", "%" . $request->search . "%")->first()) {
            foreach (Stream::where('nazev', "like", "%" . $request->search . "%")->get() as $stream) {
                $output[] = array(
                    'result' => $stream->nazev,
                    'url' => "stream/{$stream->id}"
                );
            }

            // pole musí obsahovat název a url
            return $output;
        } else {
            return [
                'status' => "none"
            ];
        }
    }
}
