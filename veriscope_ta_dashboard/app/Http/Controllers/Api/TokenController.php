<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\PassportTokenGenerate;
use Laravel\Passport\Token;


class TokenController extends Controller
{

    use PassportTokenGenerate;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = auth()->user()->id;
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection
        $tokens = new Token;
        $paginatedTokens = new Token;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $tokens = $tokens->search($input['searchTerm']);
            $paginatedTokens = $paginatedTokens->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedTokens = $paginatedTokens->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTokens = $paginatedTokens->where('user_id', $userId)->offset(($page-1) * $perPage)->limit($perPage)->get()->load('client')->filter(function ($token) {
                  return $token->client->personal_access_client && ! $token->revoked;
          })->map(function ($token) {

            $token->accessToken = $this->getPersonalAccessTokenResult(
                      $token->client_id,
                      $token->id,
                      $token->user_id,
                      $token->expires_at,
                      $token->scopes
            )->accessToken;

            return $token;
          })->values();
        } else {
          $paginatedTokens = $paginatedTokens->where('user_id', $userId)->get()->load('client')->filter(function ($token) {
                  return $token->client->personal_access_client && ! $token->revoked;
          })->map(function ($token) {

          $token->accessToken = $this->getPersonalAccessTokenResult(
                    $token->client_id,
                    $token->id,
                    $token->user_id,
                    $token->expires_at,
                    $token->scopes
           )->accessToken;

                  return $token;
          })->values();
        }

        foreach($paginatedTokens as $token) {

          $token['show'] = '<a data-id="'.$token->accessToken.'" onClick="copyToken(this);" class="btn btn--alt btn--sm">Copy Token</a> ';
          $token['revoke'] = '<a href="/backoffice/tokens/revoke/'.$token->id.'" class="btn btn--alt btn--sm">Delete</a> ';

        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $tokens->count(),
          'rows' => $paginatedTokens
        ];
    }

}
