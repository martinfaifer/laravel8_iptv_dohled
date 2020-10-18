<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\Uri;
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

        // return [Uri::where('popis', "like", "%" . $request->search . "%")->first()];
        // pokus o vyhledání napříč kanály, vyhledání v názvu nebo v url

        if (Stream::where('nazev', "like", "%" . $request->search . "%")->first()) {
            foreach (Stream::where('nazev', "like", "%" . $request->search . "%")->get() as $stream) {
                $output[] = array(
                    'result' => $stream->nazev,
                    'url' => "stream/{$stream->id}"
                );
            }

            // pole musí obsahovat název a url
            unset($request);
            return $output;
        }

        if (Uri::where('popis', "like", "%" . $request->search . "%")->first()) {
            foreach (Uri::where('popis', "like", "%" . $request->search . "%")->get() as $stream) {
                $output[] = array(
                    'result' => $stream->popis,
                    'url' => $stream->uri
                );
            }
            unset($request);
            return $output;
        } else {
            return [
                'status' => "none"
            ];
        }
    }
}
