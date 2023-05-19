<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;


// NB: 

// - News API provides a maximum request count for developer APIs. 
//  When that count is exceeded, requests can only be made within the next 24 hours.

class NewsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $news_api_collection = new Collection();
            $new_york_times_collection = new Collection();
            $the_guardian_collection = new Collection();

            // get the currently authenticated user
            $user = Auth::guard('api')->user();
            if(!$user) {
                throw new Exception();
            }

        //    if the user is currently filtering by source
            if($request->source) {
                if($request->source == config('constants.sources.new_york_times')) {
                     $new_york_times_collection = $this->getNewsFromNewYorkTimes($request, $user);
                } else if ($request->source == config('constants.sources.guardian')) {
                    $the_guardian_collection = $this->getNewsFromTheGuardian($request, $user);
                } else {
                    $news_api_collection = $this->getNewsFromNewsAPI($request, $user); 
                }
            } else {
                // if user is not filtering by source, fetch news from the user's preferred sources
                $my_sources = $user->sources->pluck('name')->all();
                
                if(in_array(config('constants.sources.guardian'), $my_sources)) {
                    $the_guardian_collection = $this->getNewsFromTheGuardian($request, $user);
                } 

                if(in_array(config('constants.sources.new_york_times'), $my_sources)) {
                     $new_york_times_collection = $this->getNewsFromNewYorkTimes($request, $user);
                }

                if(in_array(config('constants.sources.news_api'), $my_sources)) {
                    $news_api_collection = $this->getNewsFromNewsAPI($request, $user);
                }
            }

            // combine the news from different sources to create a dynamic news feed
            $collection = $news_api_collection->merge($the_guardian_collection)->shuffle();
            $collection = ($collection->merge($new_york_times_collection))->shuffle();

            return response()->json([
                'status' => 200,
                'news' => $collection
            ]);

        } catch(\Exception $e) {
            return response()-json([
                'status' => 400,
                'error' => $e->getMessage(),
                'message' => 'Something went wrong.'
            ]);
        }
    }

    // Get news from News API
    public function getNewsFromNewsAPI($filter, $user) 
    {
        if($filter->category) {
            // only the 'top-headlines' endpoint allows filtering by category
            $response = Http::get('https://newsapi.org/v2/top-headlines', [
                'q' => $this->getSearchKeywords($filter->q, $user),
                'page' => $filter->page,
                'pageSize' => 10,
                'country' => 'us',
                'category' => $filter->category,
                'apiKey' => config('constants.news_api_key')
            ]);
        } else {
            $response = Http::get('https://newsapi.org/v2/everything', [
                'q' => $this->getSearchKeywords($filter->q, $user),
                'page' => $filter->page,
                'sortBy' => 'publishedAt',
                'pageSize' => 10,
                'from' => $filter->from ?? null,
                'to' => $filter->to ?? null,
                'apiKey' => config('constants.news_api_key')
            ]);
        }

        $collection = new Collection();

        $json_response = $response->json();
        if($json_response['status'] == 'ok') {
            $collection = collect($json_response['articles']);
        }

        return $collection;
    }

    // Load news from the Guardian API
    public function getNewsFromTheGuardian($filter, $user)
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'page' => $filter->page,
            'orderBy' => 'newest',
            'page-size' => 10,
            'q' => $this->getSearchKeywords($filter->q, $user),
            'section' => $filter->category ?? null,
            'from-date' => $filter->from ?? null,
            'to-date' => $filter->to ?? null,
            'api-key' => config('constants.guardian_api_key')
        ]);

        $collection = new Collection();

        $json_response = ($response->json())['response'];
        if($json_response['status'] == 'ok') {
            $collection = collect($json_response['results']);
        }
        return $collection;
    }

    // Load news from New York Times
    public function getNewsFromNewYorkTimes($filter, $user)
    {

        $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
            'q' => $filter->q ?? null,
            'fq' => $this->getNewsDesk($user),
            'begin_date' => $filter->from ?? null,
            'end_date' => $filter->to ?? null,
            'page' => $filter->page,
            'api-key' => config('constants.nyt.api_key'),
        ]);

        $collection = new Collection();
        $json_response = $response->json();
        if($json_response['status'] == 'OK') {
            $collection = ($json_response['response'])['docs'];
        }
        return $collection;
    }

    public function getSearchKeywords($keyword, $user): string {
        // if user isn't searching by keyword, return news to user based on their preferences, i.e search by their categories
        if(isset($keyword)) {
            return $keyword;
        } else {
            $preference_categories = $user->categories->pluck('name')->all();
            return implode(' OR ', $preference_categories); 
        }
    }

    public function getNewsDesk($user)
    {
        $preference_categories = $user->categories->pluck('name')->all(); 
        $list = [];
        foreach($preference_categories as $category) {
            array_push($list, '"' . $category . '"');
        }
        return 'news_desk:(' . implode(', ', $list) . ')';
    }
    
// Load archives from new york times (NB: Too large response and cannot be filtered)  
    public function computeNYTArchiveUrl($year, $month): string 
    {
        return 'https://api.nytimes.com/svc/archive/v1/' . $year . '/'. $month . '.json';
    }

    public function computePopularNTYArticles($parameter, $month): string 
    {
        return 'https://api.nytimes.com/svc/mostpopular/v2/' . $parameter . '/'. $month . '.json';
    }
}
