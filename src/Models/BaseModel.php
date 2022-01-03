<?php

    namespace App\Leads\Models;

    use App\Models\DB;
    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Model;
    use function abort;
    use function app;
    use function App\Models\covertStringToDate;
    use function collect;
    use function config;

    class BaseModel extends Model
    {

        protected $grouping;

        protected $formatted = [];
        protected $aggregateMethod = [];

        protected $multipleWhere;

        protected static $currencySymbol = false;

        function getCreatedAtAttribute($value)
        {
            return Carbon::parse($value)->format('m/d/Y');
        }

        function getUpdatedAtAttribute($value)
        {
            return Carbon::parse($value)->format('m/d/Y');
        }

        /**
         * Implement portal type scope to handle request from both portals
         * @param  (QueryBuilder) $query
         * @return (bool/integer)
         */
        public function scopeMy($query)
        {

            $request = app('request');
            //check if portal type exists
            if ($request->has('portal_type'))
            {

                //if request portal type and user portal type is not same
                if ($request->portal_type != $request->user()->portal_type && $request->user()->portal_type == 'customer')
                {
                    abort(401, 'Unauthorized.');
                }
                //if portal type not same through error
                if ($request->portal_type == 'customer')
                {
                    //check if company id exists
                    if ($request->has('company_id'))
                    {

                        //check if company id are not same
                        if ($request->company_id != $request->user()->company_id && $request->user()->portal_type == 'customer')
                        {
                            abort(401, 'Unauthorized.');
                        }
                        $query->where('users.company_id', $request->company_id);
                    }
                }
                else
                {
                    $query->where('users.portal_type', $request->portal_type);
                }
            }

            return $query;
        }


        /**
         *
         * @param $collectCriteria
         * @return mixed
         */
        protected function getOrderby($collectCriteria)
        {
            if (array_key_exists($collectCriteria->get('o', config('main.criteria.o')), $this->mapping))
            {
                $orderBy = $this->mapping[$collectCriteria->get('o', config('main.criteria.o'))];
                $orderBy = strstr($orderBy, ' as ', 1) ?: $orderBy;
            }
            else
            {
                $orderBy = $collectCriteria->get('o', config('main.criteria.o'));
            }

            return $orderBy;
        }

        protected function fillMappings($collectCriteria)
        {
            if (!isset($this->mapping))
            {
                $this->mapping = collect($collectCriteria->get('disp'))->keyBy(function ($item) {
                    return $item;
                })->toArray();
            }
        }

        /**
         * Scope for listing and advance level filteration
         *
         * @param (QueryBuilder) $query
         * @param (object) $criteria
         * @return mixed
         */
        public function scopeCriteria($query, $criteria)
        {
            $criteria = $this->criteriaWithDefaultLabels($criteria);
            if (empty($criteria))
            {
                $view     = $this->getDefaultGridFilter();
                $criteria = $view->grid_filters;
            }
            $collectCriteria = Collect($criteria);
            $this->fillMappings($collectCriteria);
            $sortOrder = (in_array((int)$collectCriteria->get('d'), [0, 1])) ? (int)$collectCriteria->get('d') : config('main.criteria.d');
            $takeRows  = !empty($collectCriteria->get('r')) ? $collectCriteria->get('r') : config('main.criteria.r');
            $sort      = [0 => 'asc', 1 => 'desc'];
            $orderBy   = $this->getOrderby($collectCriteria);
            $query->orderBy($orderBy, $sort[$sortOrder]);
            $query->take($takeRows);
            $filter = ["start" => ["operator" => '>='], "end" => ["operator" => '<=']];
            $this->RawFormatted();
            if ($collectCriteria->get('fs', []))
            {
                foreach ($collectCriteria->get('fs') as $columnName => $columnValue)
                {
                    //exclude column filters
                    if (isset($this->excluded))
                    {
                        if (in_array($columnName, $this->excluded))
                        {
                            continue;
                        }
                    }
                    if (isset($this->mapping[$columnName]))
                    {
                        $column     = strstr($this->mapping[$columnName], ' as ', 1);
                        $columnName = $column ? $column : $this->mapping[$columnName];
                    }
                    if (is_object($columnValue))
                    {
                        foreach ($columnValue as $key => $value)
                        {
                            $value = covertStringToDate($value);
                            if (in_array($columnName, $this->aggregateMethod))
                            {
                                $query->havingRaw('SUM(' . $columnName . ') ' . $filter[$key]['operator'] . ' ' . $value);
                            }
                            else
                            {
                                $query->where(DB::raw($columnName), $filter[$key]['operator'], $value);
                            }
                        }
                    }
                    if (is_array($columnValue))
                    {
                        if (isset($this->multipleWhere[$columnName]))
                        {
                            $query->where(function ($query) use ($columnName, $columnValue) {
                                foreach ($this->multipleWhere[$columnName] as $field)
                                {
                                    $query->orwhereIn($field, $columnValue);
                                }
                            });
                        }
                        else
                        {
                            $query->whereIn(DB::raw($columnName), $columnValue);
                        }
                    }
                    if (is_string($columnValue))
                    {
                        if (isset($this->multipleWhere[$columnName]))
                        {
                            $query->where(function ($query) use ($columnName, $columnValue) {
                                foreach ($this->multipleWhere[$columnName] as $field)
                                {
                                    $query->orWhere($field, 'like', '%' . $columnValue . '%');
                                }
                            });
                        }
                        else
                        {
                            $query->where(DB::raw($columnName), 'like', '%' . $columnValue . '%');
                        }
                    }
                }
            }
            $disp = '*';
            if (count($collectCriteria->get('disp')) > 0 && $collectCriteria->get('disp') != '*')
            {
                //            $disp = collect($this->mapping)->only($collectCriteria->get('disp'))->values()->toArray();
                $disp = [];
                foreach ($collectCriteria->get('disp') as $requested_disp)
                {
                    if (isset($this->mapping[$requested_disp]))
                    {
                        $disp[] = $this->mapping[$requested_disp];
                    }
                }
            }
            elseif (count($this->mapping) > 0)
            {
                $disp = collect($this->mapping)->values()->toArray();
            }
            $query->select($disp);

            return $query;
        }

        function RawFormatted()
        {
            foreach ($this->formatted as $key => $format)
            {
                $this->formatted[$key] = DB::raw($format);
            }
            $this->mapping = array_merge($this->mapping, $this->formatted);
        }

        /**
         * Get Default view of Grid
         *
         * @return (Array) $default
         */
        private function getDefaultGridFilter()
        {
            $views   = $this->getViews();
            $views   = Collect($views);
            $default = $views->reject(function ($view) {
                return !$view['is_default'];
            })->first();

            return (object)$default;
        }

        /**
         * Get all views
         *
         * @return (array) $saveViews
         */
        private function getViews()
        {
            $saveViews = [['grid_filters' => ['fs' => [], 'fs_clear' => [], 'disp' => '*', 'o' => 'id', 'd' => config('main.criteria.d'), 'r' => config('main.criteria.r'),], 'is_default' => 1, 'default_view' => 1, 'name' => 'Default View']];

            return $saveViews;
        }

        /**
         * Return all column with their types of Resourse Model
         */
        public function getLabels()
        {
            return $this->labels;
        }

        /**
         * Return set order of label according to criteria
         */
        public function setLabels($labelArray, $disp)
        {
            $mapResultWithEntities = [];
            foreach ($disp as $dispkey => $val)
            {
                if (isset($labelArray))
                {
                    foreach ($labelArray as $key => $value)
                    {

                        if ($val == $key)
                        {
                            $mapResultWithEntities[$key] = $labelArray[$key];
                        }
                    }
                }
            }

            return $mapResultWithEntities;
        }

        public function criteriaWithDefaultLabels($criteria)
        {
            $criteria = collect($criteria)->toArray();
            if (!empty($criteria['disp']))
            {

                $defaultKeys      = collect($this->labels)->where('is_default', true)->keys();
                $extraKeys        = $defaultKeys->diff($criteria['disp'])->toArray();
                $criteria['disp'] = collect($criteria['disp'])->merge($extraKeys);
            }

            return $criteria;
        }

        public function scopeCurrencySymbol($query)
        {
            static::$currencySymbol = true;
        }
    }
