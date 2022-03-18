<?php

namespace App\Traits;

trait ILikeSearchable
{
    /**
     *
     */
    function scopeSearch($query, $term ) {
        // $term = strtolower($term);//doesn't work for account addresses
        if(isset($this->searchable)) {
            foreach($this->searchable as $i => $col) {
                if(stripos($col, '.') !== false) {
                  // if the term has a child element (eg user.first_name)
                  // then make subqueries.
                  $sub = explode('.', $col);
                  if($i > 0) {
                    $query->orWhereHas($sub[0], function($q) use($sub, $term, $i) {
                        $q->where($sub[1], 'ILIKE', "%{$term}%");
                    });
                  } else {
                    $query->whereHas($sub[0], function($q) use($sub, $term, $i) {
                        $q->where($sub[1], 'ILIKE', "%{$term}%");
                    });
                  }
                } else {
                  // search the base model normally
                  if($i > 0) {
                      $query->orWhere($col, 'ILIKE', "%{$term}%");
                  } else {
                      $query->where($col, 'ILIKE', "%{$term}%");
                  }
                }
            }
        }
        //dd($query->toSql());

        return $query;
    }
}
