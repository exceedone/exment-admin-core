<?php

namespace ExmentAdminCore\Admin;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use ExmentAdminCore\Admin\Exception\Handler;
use ExmentAdminCore\Admin\Form\Builder;
use ExmentAdminCore\Admin\Form\Concerns\HandleCascadeFields;
use ExmentAdminCore\Admin\Form\Concerns\HasFields;
use ExmentAdminCore\Admin\Form\Concerns\HasFormAttributes;
use ExmentAdminCore\Admin\Form\Concerns\HasHooks;
use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Layout\Layout;
use ExmentAdminCore\Admin\Form\Row;
use ExmentAdminCore\Admin\Form\Tab;
use ExmentAdminCore\Admin\Grid\Tools\BatchEdit;
use ExmentAdminCore\Admin\Traits\ShouldSnakeAttributes;
use ExmentAdminCore\Admin\Traits\Resource;
use ExmentAdminCore\Admin\Traits\FormTrait;
use Spatie\EloquentSortable\Sortable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Form.
 */
class Form implements Renderable
{
    use Resource;
    use HasHooks;
    use HasFields;
    use HasFormAttributes;
    use HandleCascadeFields;
    use ShouldSnakeAttributes;
    /**
     * Remove flag in `has many` form.
     */
    public const REMOVE_FLAG_NAME = '_remove_';

    /**
     * Eloquent model of the form.
     *
     * @var Model|null
     */
    public $model;

    /**
     * @var \Illuminate\Validation\Validator
     */
    public $validator;

    /**
     * Validation closure.
     *
     * @var Closure|null
     */
    protected $validatorSavingCallback;

    /**
     * prepare callback
     *
     * @var Closure|null
     */
    protected $prepareCallback;

    /**
     * @var Builder
     */
    public $builder;

    /**
     * Data for save to current model from input.
     *
     * @var array<mixed>
     */
    protected $updates = [];

    /**
     * Data for save to model's relations from input.
     *
     * @var array<mixed>
     */
    protected $relations = [];

    /**
     * Input data.
     *
     * @var array<mixed>
     */
    protected $inputs = [];

    /**
     * Refrence to model's relations fields.
     *
     * @var array<mixed>
     */
    protected $relation_fields = [];

    /**
     * Refrence to fields that must be prepared before update.
     *
     * @var array
     */
    protected $must_prepare = [];

    /**
     * @var Layout
     */
    protected $layout;

    /**
     * Ignored saving fields.
     *
     * @var array<mixed>
     */
    protected $ignored = [];

    /**
     * Collected field assets.
     *
     * @var array<string, mixed>
     */
    protected static $collectedAssets = [];

    /**
     * @var Form\Tab|null
     */
    protected $tab = null;

    /**
     * Field rows in form.
     *
     * @var array<mixed>
     */
    public $rows = [];

    /**
     * @var bool
     */
    protected $isSoftDeletes = false;

    /**
     * @var bool
     */
    protected $isForceDelete = false;

    /**
     * redirect callback to list.
     * @var bool
     */
    protected $redirectList = true;

    public $fixedFooter = true;

    /**
     * If set, not call default renderException, and \Closure.
     *
     * @var \Closure|null
     */
    protected $renderException;


    /**
     * Set relation models
     *
     * @var array<mixed>|null
     */
    protected $relationModels;

    /**
     * Create a new form instance.
     *
     * @param mixed $model
     * @param \Closure $callback
     */
    public function __construct($model, Closure $callback = null)
    {
        $this->model = $model;

        $this->builder = new Builder($this);

        $this->initLayout();

        if ($callback instanceof Closure) {
            $callback($this);
        }

        $this->isSoftDeletes = in_array(SoftDeletes::class, class_uses_deep($this->model), true);

        $this->initFormAttributes();
        $this->callInitCallbacks();
    }

    /**
     * @param Field $field
     *
     * @return $this
     */
    public function pushField(Field $field): self
    {
        $field->setForm($this);

        if (!empty($field->must_prepare)) {
            $this->must_prepare[] = $field->column();
        }

        $width = $this->builder->getWidth();
        $field->setWidth($width['field'], $width['label']);

        $this->builder->fields()->push($field);
        $this->layout->addField($field);

        return $this;
    }

    /**
     * @return Model|null
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->builder;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function fields()
    {
        return $this->builder()->fields();
    }

    /**
     * Generate a edit form.
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function edit($id): self
    {
        $this->builder->setMode(Builder::MODE_EDIT);
        $this->builder->setResourceId($id);

        $this->setFieldValue($id);

        return $this;
    }

    /**
     * Generate a replicate form.
     *
     * @param mixed $id
     * @param array<mixed> $ignore
     *
     * @return $this
     */
    public function replicate($id, $ignore = [])
    {
        $this->builder->setMode(Builder::MODE_CREATE);
        $this->builder->setResourceId($id);

        $this->setFieldValue($id, true, $ignore);

        return $this;
    }

    /**
     * Use tab to split form.
     *
     * @param string  $title
     * @param Closure $content
     * @param bool    $active
     *
     * @return $this
     */
    public function tab($title, Closure $content, bool $active = false): self
    {
        $this->setTab()->append($title, $content, $active);

        return $this;
    }

    /**
     * Get Tab instance.
     *
     * @return Tab
     */
    public function getTab()
    {
        return $this->tab;
    }

    /**
     * Set Tab instance.
     *
     * @return Tab
     */
    public function setTab(): Tab
    {
        if ($this->tab === null) {
            $this->tab = new Tab($this);
        }

        return $this->tab;
    }

    /**
     * Destroy data entity and remove files.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            if (($ret = $this->callDeleting($id)) instanceof Response) {
                return $ret;
            }

            collect(explode(',', $id))->filter()->each(function ($id) {
                /** @var SoftDeletableModel $builder */
                $builder = $this->model()->newQuery();

                if ($this->isSoftDeletes) {
                    $builder = $builder->withTrashed();
                }

                $model = $builder->with($this->getRelations())->findOrFail($id);

                if (($this->isSoftDeletes && $model->trashed()) || $this->isForceDelete) {
                    $this->deleteFiles($model, true);
                    $model->forceDelete();

                    return;
                }

                $this->deleteFiles($model);
                $model->delete();
            });

            if (($ret = $this->callDeleted()) instanceof Response) {
                return $ret;
            }

            $response = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } catch (\Exception $exception) {
            \Log::error($exception);
            $response = [
                'status'  => false,
                'message' => $exception->getMessage() ?: trans('admin.delete_failed'),
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove files in record.
     *
     * @param Model $model
     * @param bool  $forceDelete
     *
     * @return void
     */
    protected function deleteFiles(Model $model, $forceDelete = false)
    {
        // If it's a soft delete, the files in the data will not be deleted.
        if (!$forceDelete && $this->isSoftDeletes) {
            return;
        }

        $data = $model->toArray();

        $this->builder->fields()->filter(function ($field) {
            return $field instanceof Field\File;
        })->each(function (Field\File $file) use ($data) {
            $file->setOriginal($data);
            $file->destroy();
        });
    }

    /**
     * Store a new record.
     * @param null|mixed $data
     *
     * @return mixed
     */
    public function store($data = null)
    {
        if(!$data){
            $data = \request()->all();
        }

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            return $this->responseValidationError($validationMessages);
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }

        \DB::transaction(function () {
            $inserts = $this->prepareInsert($this->updates);

            foreach ($inserts as $column => $value) {
                $this->model->setAttribute($column, $value);
            }

            $this->model->save();
            $this->storeJancode($this->model);

            $this->updateRelation($this->relations);

            try{
                if (($response = $this->callSavedInTransaction()) instanceof Response) {
                    return $response;
                }    
            }catch(\Exception $ex){
                \Log::error($ex);
                DB::rollback();
                throw $ex;
            }
        });

        if (($response = $this->callSaved()) instanceof Response) {
            return $response;
        }

        if ($response = $this->ajaxResponse(trans('admin.save_succeeded'))) {
            return $response;
        }

        return $this->redirectAfterStore();
    }

    /**
     * Save Jancode
     *
     * @param mixed $model
     *
     * @return void
     */
    protected function storeJancode($model)
    {
        $jan_code = request()->get('jan_code');
        $table_code = request()->get('table_code');
        if ($jan_code && $table_code) {
            DB::table('jan_codes')
                ->insert([
                    'table_id' => $table_code,
                    'target_id' => $model->id,
                    'jan_code' => $jan_code,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_user_id' => \Exment::user()->base_user_id,
                    'updated_user_id' => \Exment::user()->base_user_id,
                ]);
        };
    }
    
    /**
     * @param MessageBag $message
     *
     * @return $this|\Illuminate\Http\JsonResponse
     */
    protected function responseValidationError(MessageBag $message)
    {
        if (\request()->ajax() && !\request()->pjax()) {
            return response()->json([
                'status'     => false,
                'validation' => $message,
                'message'    => $message->first(),
            ]);
        }

        return back()->withInput()->withErrors($message);
    }


    /**
     * Get ajax response.
     *
     * @param string $message
     *
     * @return bool|\Illuminate\Http\JsonResponse
     */
    protected function ajaxResponse($message)
    {
        $request = Request::capture();

        // ajax but not pjax
        if ($request->ajax() && !$request->pjax()) {
            return response()->json([
                'status'  => true,
                'message' => $message,
                // 'display' => $this->applayFieldDisplay(),
            ]);
        }

        return false;
    }

    /**
     * @return array<mixed>
     */
    protected function applayFieldDisplay()
    {
        $editable = [];

        /** @var Field $field */
        foreach ($this->fields() as $field) {
            if (!\request()->has($field->column())) {
                continue;
            }

            $newValue = $this->model->fresh()->getAttribute($field->column());

            if ($newValue instanceof Arrayable) {
                $newValue = $newValue->toArray();
            }

            if ($field instanceof Field\BelongsTo || $field instanceof Field\BelongsToMany) {
                $selectable = $field->getSelectable();

                if (method_exists($selectable, 'display')) {
                    $display = $selectable::display();

                    $editable[$field->column()] = $display->call($this->model, $newValue);
                }
            }
        }

        return $editable;
    }

    /**
     * Prepare input data for insert or update.
     *
     * @param array<mixed> $data
     *
     * @return mixed
     */
    protected function prepare($data = [])
    {
        if (($response = $this->callSubmitted()) instanceof Response) {
            return $response;
        }

        $this->inputs = array_merge($this->removeIgnoredFields($data), $this->inputs);

        if($this->prepareCallback){
            $func = $this->prepareCallback;
            $this->inputs = $func($this->inputs);
        }

        if (($response = $this->callSaving()) instanceof Response) {
            return $response;
        }

        $this->relations = $this->getRelationInputs($this->inputs);

        $this->updates = Arr::except($this->inputs, array_keys($this->relations));
    }

    /**
     * Remove ignored fields from input.
     *
     * @param array<mixed> $input
     *
     * @return array<mixed>
     */
    protected function removeIgnoredFields($input): array
    {
        Arr::forget($input, $this->ignored);

        return $input;
    }

    /**
     * Get inputs for relations.
     *
     * @param array<mixed> $inputs
     *
     * @return array<mixed>
     */
    protected function getRelationInputs($inputs = []): array
    {
        $relations = [];

        foreach ($inputs as $column => $value) {
            if ((method_exists($this->model, $column)
                || method_exists($this->model, $column = Str::camel($column)))
                && !method_exists(Model::class, $column)
            ) {
                $relation = call_user_func([$this->model, $column]);

                if ($relation instanceof Relations\Relation) {
                    $relations[$column] = $value;
                }
            }
        }

        return $relations;
    }

    /**
     * Handle update.
     *
     * @param int  $id
     * @param mixed $data
     *
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|mixed|null|Response
     */
    public function update($id, $data = null)
    {
        $formId = request()->get('formid');
        $data = ($data) ?: request()->all();

        $isEditable = $this->isEditable($data);

        if (($data = $this->handleColumnUpdates($id, $data)) instanceof Response) {
            return $data;
        }

        /** @var SoftDeletableModel $builder */
        $builder = $this->model();

        if ($this->isSoftDeletes) {
            $builder = $builder->withTrashed();
        }

        $this->model = $builder->with($this->getRelations())->findOrFail($id);

        $this->setFieldOriginalValue();

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            if (!$isEditable) {
                return back()->withInput()->withErrors($validationMessages);
            }

            return response()->json(['errors' => Arr::dot($validationMessages->getMessages())], 422);
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }

        \DB::transaction(function () {
            $updates = $this->prepareUpdate($this->updates);

            foreach ($updates as $column => $value) {
                /* @var Model $this->model */
                $this->model->setAttribute($column, $value);
            }

            $this->model->save();

            $this->updateRelation($this->relations);
            
            try{
                if (($response = $this->callSavedInTransaction()) instanceof Response) {
                    return $response;
                }    
            }catch(\Exception $ex){
                DB::rollback();
                throw $ex;
            }
        });
        if ($formId) {
            return $this->redirectAfterUpdate($id);
        }
        if (($result = $this->callSaved()) instanceof Response) {
            return $result;
        }

        if ($response = $this->ajaxResponse(trans('admin.update_succeeded'))) {
            return $response;
        }

        return $this->redirectAfterUpdate($id);
    }

    
    /**
     * validatorSavingCallback
     *
     * @param Closure $callback
     * @return $this
     */
    public function validatorSavingCallback(Closure $callback){
        $this->validatorSavingCallback = $callback;

        return $this;
    }
    

    /**
     * prepareCallback. Please return inputs array
     *
     * @param Closure $callback
     * @return $this
     */
    public function prepareCallback(Closure $callback){
        $this->prepareCallback = $callback;

        return $this;
    }


    /**
     * Handle validation update.
     *
     * @param int  $id
     * @param mixed $data
     *
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|mixed|null|Response
     */
    public function validationUpdate($id, $data = null)
    {
        $data = ($data) ?: request()->all();

        $isEditable = $this->isEditable($data);

        if (($data = $this->handleColumnUpdates($id, $data)) instanceof Response) {
            return $data;
        }

        /** @var SoftDeletableModel $builder */
        $builder = $this->model();

        if ($this->isSoftDeletes) {
            $builder = $builder->withTrashed();
        }

        $this->model = $builder->with($this->getRelations())->findOrFail($id);

        $this->setFieldOriginalValue();

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            if (!$isEditable) {
                return back()->withInput()->withErrors($validationMessages);
            }

            return response()->json(['errors' => Arr::dot($validationMessages->getMessages())], 422);
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }

        \ExmentDB::transaction(function () {
            $updates = $this->prepareUpdate($this->updates);

            foreach ($updates as $column => $value) {
                /* @var Model $this->model */
                $this->model->setAttribute($column, $value);
            }

            $this->model->save();

            $this->updateRelation($this->relations);
            
            try{
                if (($response = $this->callSavedInTransaction()) instanceof Response) {
                    return $response;
                }    
            }catch(\Exception $ex){
                DB::rollback();
                throw $ex;
            }
        });

        if (($result = $this->callSaved()) instanceof Response) {
            return $result;
        }

        if ($response = $this->ajaxResponse(trans('admin.update_succeeded'))) {
            return $response;
        }

        return $this->redirectAfterUpdate($id);
    }

    /**
     * Handle update before validation.
     *
     * @param int  $id
     * @param mixed $data
     *
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|mixed|null|Response
     */
    public function setupUpdate($id, $data = null)
    {
        $data = ($data) ?: request()->all();

        $isEditable = $this->isEditable($data);

        if (($data = $this->handleColumnUpdates($id, $data)) instanceof Response) {
            return $data;
        }

        /** @var SoftDeletableModel $builder */
        $builder = $this->model();

        if ($this->isSoftDeletes) {
            $builder = $builder->withTrashed();
        }

        $this->model = $builder->with($this->getRelations())->findOrFail($id);

        $this->setFieldOriginalValue();

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            return [
                'validationMessages' => $validationMessages,
                'result' => false,
            ];
        }

        return [
            'builder' => $builder,
            'data' => $data,
            'result' => true,
        ];
    }

    /**
     * Get RedirectResponse after store.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectAfterStore()
    {
        $resourcesPath = $this->getResource(0);
        $key           = $this->model->getKey();

        return $this->redirectAfterSaving($resourcesPath, $key);
    }

    /**
     * Get RedirectResponse after update.
     *
     * @param mixed $key
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectAfterUpdate($key)
    {
        $resourcesPath = $this->getResource(-1);

        return $this->redirectAfterSaving($resourcesPath, $key);
    }

    /**
     * Get RedirectResponse after data saving.
     *
     * @param string $resourcesPath
     * @param string $key
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function redirectAfterSaving($resourcesPath, $key)
    {
        $redirect = $this->builder()->getFooter()->getRedirect($resourcesPath, $key, request('after-save'));

        admin_toastr(trans('admin.save_succeeded'));
        
        if(isset($redirect)){
            return $redirect;
        }

        if($this->redirectList) {
            $url = rtrim($resourcesPath, '/');
        } else {
            $url = request(Builder::PREVIOUS_URL_KEY) ?: $resourcesPath;
        }
        return redirect($url);
    }

    /**
     * Check if request is from editable.
     *
     * @param array<mixed> $input
     *
     * @return bool
     */
    protected function isEditable(array $input = []): bool
    {
        return array_key_exists('_editable', $input) || array_key_exists('_edit_inline', $input);
    }

    /**
     * Handle updates for single column.
     *
     * @param int   $id
     * @param array<mixed> $data
     *
     * @return array<mixed>|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|Response
     */
    protected function handleColumnUpdates($id, $data)
    {
        $data = $this->handleEditable($data);

        if ($this->handleOrderable($id, $data)) {
            return response([
                'status'  => true,
                'message' => trans('admin.update_succeeded'),
            ]);
        }

        return $data;
    }

    /**
     * Handle editable update.
     *
     * @param array<mixed> $input
     *
     * @return array<mixed>
     */
    protected function handleEditable(array $input = []): array
    {
        if (array_key_exists('_editable', $input)) {
            $name  = $input['name'];
            $value = $input['value'];

            Arr::forget($input, ['pk', 'value', 'name']);
            Arr::set($input, $name, $value);
        }

        return $input;
    }

    /**
     * Handle orderable update.
     *
     * @param int   $id
     * @param array<mixed> $input
     *
     * @return bool
     */
    protected function handleOrderable($id, array $input = [])
    {
        if (array_key_exists('_orderable', $input)) {
            /** @var SortableModel $model */
            $model = $this->model->find($id);

            if ($model instanceof Sortable) {
                $input['_orderable'] == 1 ? $model->moveOrderUp() : $model->moveOrderDown();

                return true;
            }
        }

        return false;
    }

    /**
     * Update relation data.
     *
     * @param array<mixed> $relationsData
     *
     * @return void
     */
    protected function updateRelation($relationsData)
    {
        // makes sure prepared values for relations can be passed
        // for example MultiFile deletions / sortings
        //echo "<pre>".print_r($relationsData, 1)."</pre>";
        //echo "<pre>".print_r($this->relation_fields, 1)."</pre>";
        //exit;

        foreach ($this->relation_fields as $field) {
            if (!isset($relationsData[$field]) && in_array($field, $this->must_prepare)) {
                $relationsData[$field] = false;
            }
        }

        foreach ($relationsData as $name => $values) {
            if (!method_exists($this->model, $name)) {
                continue;
            }

            $relation = $this->model->$name();

            $oneToOneRelation = $relation instanceof Relations\HasOne
                || $relation instanceof Relations\MorphOne
                || $relation instanceof Relations\BelongsTo;

            $isRelationUpdate = true;
            $prepared         = $this->prepareUpdate([$name => $values], $oneToOneRelation, $isRelationUpdate);

            if (empty($prepared)) {
                continue;
            }

            switch (true) {
                case $relation instanceof Relations\BelongsToMany:
                case $relation instanceof Relations\MorphToMany:
                    if (isset($prepared[$name])) {
                        $relation->sync($prepared[$name]);
                    }
                    break;
                case $relation instanceof Relations\HasOne:
                case $relation instanceof Relations\MorphOne:
                    $related = $this->model->getRelationValue($name) ?: $relation->getRelated();

                    foreach ($prepared[$name] as $column => $value) {
                        $related->setAttribute($column, $value);
                    }

                    // save child
                    $relation->save($related);
                    break;
                case $relation instanceof Relations\BelongsTo:
                case $relation instanceof Relations\MorphTo:
                    $related = $this->model->getRelationValue($name) ?: $relation->getRelated();

                    foreach ($prepared[$name] as $column => $value) {
                        $related->setAttribute($column, $value);
                    }

                    // save parent
                    $related->save();

                    // save child (self)
                    $relation->associate($related)->save();
                    break;
                case $relation instanceof Relations\HasMany:
                case $relation instanceof Relations\MorphMany:
                    if (!empty($prepared[$name])) {
                        foreach ($prepared[$name] as $related) {
                            /** @var Relations\HasOneOrMany $relation */
                            $relation = $this->model->$name();

                            $keyName = $relation->getRelated()->getKeyName();

                            /** @var Model $child */
                            $child = $relation->findOrNew(Arr::get($related, $keyName));

                            if (Arr::get($related, static::REMOVE_FLAG_NAME) == 1) {
                                $child->delete();
                                continue;
                            }

                            Arr::forget($related, static::REMOVE_FLAG_NAME);

                            $child->fill($related);

                            $child->save();
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Prepare input data for update.
     *
     * @param array<mixed> $updates
     * @param bool  $oneToOneRelation If column is one-to-one relation.
     *
     * @return array<mixed>
     */
    protected function prepareUpdate(array $updates, $oneToOneRelation = false, $isRelationUpdate = false): array
    {
        $prepared = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $columns = $field->column();

            // If column not in input array data, then continue.
            if (!$field->getInternal() && !Arr::has($updates, $columns)) {
                continue;
            }

            if ($this->isInvalidColumn($columns, $oneToOneRelation || $field->isJsonType)
                || (in_array($columns, $this->relation_fields) && !$isRelationUpdate)) {
                continue;
            }

            $value = $this->getDataByColumn($updates, $columns);
            $value = $field->prepare($value);

            // only process values if not false
            if ($value !== false) {
                if (is_array($columns)) {
                    foreach ($columns as $name => $column) {
                        Arr::set($prepared, $column, $value[$name]);
                    }
                } elseif (is_string($columns)) {
                    Arr::set($prepared, $columns, $value);
                }
            }
        }

        return $prepared;
    }

    /**
     * @param string|array<mixed> $columns
     * @param bool         $containsDot
     *
     * @return bool
     */
    protected function isInvalidColumn($columns, $containsDot = false): bool
    {
        foreach ((array) $columns as $column) {
            if ((!$containsDot && Str::contains($column, '.'))
                || ($containsDot && !Str::contains($column, '.'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare input data for insert.
     *
     * @param mixed $inserts
     *
     * @return array<mixed>
     */
    protected function prepareInsert($inserts): array
    {
        if ($this->isHasOneRelation($inserts)) {
            $inserts = Arr::dot($inserts);
        }

        foreach ($inserts as $column => $value) {
            if (($field = $this->getFieldByColumn($column)) === null) {
                unset($inserts[$column]);
                continue;
            }
            $inserts[$column] = $field->prepare($value);
        }

        // set internal value.
        foreach ($this->builder->fields() as $field) {
            // If column not in input array data, then continue.
            if (!$field->getInternal()) {
                continue;
            }
            
            $column = $field->column();
            $inserts[$column] = $field->prepare(null);
        }

        $prepared = [];

        foreach ($inserts as $key => $value) {
            if ($value !== false) {
                Arr::set($prepared, $key, $value);
            }
        }

        return $prepared;
    }

    /**
     * Prepare data for confirm.
     *
     * @param mixed $inserts
     *
     * @return array<mixed>
     */
    protected function prepareConfirm($inserts)
    {
        if ($this->isHasOneRelation($inserts)) {
            $inserts = Arr::dot($inserts);
        }

        foreach ($inserts as $column => $value) {
            if (is_null($field = $this->getFieldByColumn($column))) {
                unset($inserts[$column]);
                continue;
            }

            $inserts[$column] = $field->prepareConfirm($value);
        }

        // set internal value.
        foreach ($this->builder->fields() as $field) {
            // If column not in input array data, then continue.
            if (!$field->getInternal()) {
                continue;
            }
            
            $column = $field->column();
            $inserts[$column] = $field->prepareConfirm(null);
        }

        $prepared = [];

        foreach ($inserts as $key => $value) {
            Arr::set($prepared, $key, $value);
        }

        return $prepared;
    }

    /**
     * Is input data is has-one relation.
     *
     * @param array<mixed> $inserts
     *
     * @return bool
     */
    protected function isHasOneRelation($inserts): bool
    {
        $first = current($inserts);

        if (!is_array($first)) {
            return false;
        }

        if (is_array(current($first))) {
            return false;
        }

        return Arr::isAssoc($first);
    }

    /**
     * Ignore fields to save.
     *
     * @param string|array<mixed> $fields
     *
     * @return $this
     */
    public function ignore($fields): self
    {
        $this->ignored = array_merge($this->ignored, (array) $fields);

        return $this;
    }

    /**
     * @param array<mixed>        $data
     * @param string|array<mixed> $columns
     *
     * @return array<mixed>|mixed
     */
    protected function getDataByColumn($data, $columns)
    {
        if (is_string($columns)) {
            return Arr::get($data, $columns, false);
        }

        if (is_array($columns)) {
            $value = [];
            foreach ($columns as $name => $column) {
                if (!Arr::has($data, $column)) {
                    continue;
                }
                $value[$name] = Arr::get($data, $column, false);
            }

            return $value;
        }
        // if not found return false
        // false values won't be save
        return false;
    }

    /**
     * Find field object by column.
     *
     * @param mixed $column
     *
     * @return mixed
     */
    protected function getFieldByColumn($column)
    {
        return $this->builder->fields()->first(
            function (Field $field) use ($column) {
                if (is_array($field->column())) {
                    return in_array($column, $field->column());
                }

                return $field->column() == $column;
            }
        );
    }

    /**
     * Set original data for each field.
     *
     * @return void
     */
    protected function setFieldOriginalValue()
    {
        $values = $this->model->toArray();

        $this->builder->fields()->each(function (Field $field) use ($values) {
            $field->setOriginal($values);
        });
    }

    /**
     * Set all fields value in form.
     *
     * @param mixed $id
     * @param bool $replicate
     * @param array<mixed> $ignore
     *
     * @return void
     */
    protected function setFieldValue($id, $replicate = false, $ignore = [])
    {
        $relations = $this->getRelations();

        /** @var SoftDeletableModel $builder */
        $builder = $this->model();

        if ($this->isSoftDeletes) {
            $builder = $builder->withTrashed();
        }

        if($id instanceof \Illuminate\Database\Eloquent\Model){
            $this->model = $id;
        }else{
            $this->model = $builder->with($relations)->findOrFail($id);
        }

        if($replicate){
            $this->model = $this->replicateModel($this->model, $relations, $ignore);
        }

        $this->callEditing();

        $data = $this->model->toArray();

        $this->builder->fields()->each(function (Field $field) use ($data) {
            if (!in_array($field->column(), $this->ignored, true)) {
                $field->fill($data);
            }
        });
    }

    /**
     * Get model by inputs
     *
     * @param array<mixed> $data
     * @param Model|null $model if set base model, set args
     *
     * @return Model|Response
     */
    public function getModelByInputs(array $data = null, ?Model $model = null)
    {
        if(is_null($data)){
            $data = request()->all();
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }

        $inserts = $this->prepareConfirm($this->updates);

        if($model){
            $this->model = $model;
        }

        foreach ($inserts as $column => $value) {
            $this->model->setAttribute($column, $value);
        }

        // Now, I only call this function, If need, set such as array.
        $this->getRelationModelByInputs($data);

        return $this->model;
    }

    /**
     * Get relation models
     *
     * @param array<mixed>|null $inputs
     * @return array<mixed>|null
     */
    public function getRelationModelByInputs(array $inputs = null)
    {
        if(!is_null($this->relationModels)){
            return $this->relationModels;
        }

        if(is_null($inputs)){
            $inputs = request()->all();
        }

        $relations = [];
        foreach ($inputs as $column => $value) {
            
            if (!method_exists($this->model, $column)) {
                continue;
            }

            $relation = call_user_func([$this->model, $column]);

            if (!($relation instanceof Relations\Relation)) {
                continue;
            }
            
            $value = array_filter($value);
            if ($relation instanceof Relations\BelongsToMany || $relation instanceof Relations\MorphToMany) {
                $relations[$column] = (clone $relation->getRelated())->query()->findMany($value);
                continue;
            }
            
            // create child model
            foreach($value as $v){
                if(is_null($v)){
                    continue;
                }
                if (Arr::get($v, Form::REMOVE_FLAG_NAME) == 1) {
                    continue;
                }

                $prepared = $this->prepareConfirm([$column => $value]);

                $model = clone $relation->getRelated();
                $model->fill($v);

                $relations[$column][] = $model;
            }
        }

        $this->relationModels = $relations;
        return $relations;
    }


    /**
     * @param mixed $oldModel
     * @param mixed $relations
     * @param array<mixed> $ignore
     * @return mixed
     */
    protected function replicateModel($oldModel, $relations, $ignore = []){
        $model = $oldModel->replicate()->setRelations([]);

        foreach($ignore as $i){
            unset($model->{$i});
        }

        foreach($relations as $relation){
            // if set hasmany, set model as relation
            if(!($oldModel->{$relation}() instanceof \Illuminate\Database\Eloquent\Relations\HasMany)){
                $model->setRelation($relation, $oldModel->{$relation});
                continue;
            }

            // set hasmany values
            $items = [];
            foreach($oldModel->{$relation} as $childModel){
                $childCopyModel = $childModel->replicate()->setRelations([]);
                // remove parent id
                $keyName = $oldModel->{$relation}()->getForeignKeyName();
                unset($childCopyModel->{$keyName});

                $items[] = $childCopyModel;
            }
            $model->setRelation($relation, collect($items));
        }

        return $model;
    }

    /**
     * Add a fieldset to form.
     *
     * @param string  $title
     * @param Closure $setCallback
     *
     * @return Field\Fieldset
     */
    public function fieldset(string $title, Closure $setCallback)
    {
        $fieldset = new Field\Fieldset();

        $this->html($fieldset->start($title))->plain();

        $setCallback($this);

        $this->html($fieldset->end())->plain();

        return $this;
    }

    /**
     * Validate this form fields, and return redirect if has errors
     *
     * @param array<mixed> $input
     *
     * @return \Illuminate\Http\RedirectResponse|true
     */
    public function validateRedirect($input)
    {
        $message = $this->validationMessages($input);
        if($message !== false){
            return back()->withInput()->withErrors($message);
        }
        return true;
    }
    

    /**
     * Get validation messages.
     *
     * @param array<mixed> $input
     *
     * @return MessageBag|bool
     */
    public function validationMessages($input)
    {
        $failedValidators = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            if (!$validator = $field->getValidator($input)) {
                continue;
            }
            if (($validator instanceof Validator) && !$validator->passes()) {
                $failedValidators[] = $validator;
            }
        }

        $message = $this->mergeValidationMessages($failedValidators);

        if($this->validatorSavingCallback){
            $func = $this->validatorSavingCallback;
            $func($input, $message, $this);
        }
        // if contains function 'validatorSaving' in model, call
        if(method_exists($this->model, 'validatorSaving')){
            if(is_array($validateResult = $this->model->validatorSaving($input))){
                $message = $message->merge($validateResult);
            }
        }

        return $message->any() ? $message : false;
    }

    /**
     * Merge validation messages from input validators.
     *
     * @param \Illuminate\Validation\Validator[] $validators
     *
     * @return MessageBag
     */
    protected function mergeValidationMessages($validators): MessageBag
    {
        $messageBag = new MessageBag();

        foreach ($validators as $validator) {
            $messageBag = $messageBag->merge($validator->messages());
        }

        return $messageBag;
    }

    /**
     * Get all relations of model from callable.
     *
     * @return array<mixed>
     */
    public function getRelations(): array
    {
        $relations = $columns = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $columns[] = $field->column();
        }

        foreach (Arr::flatten($columns) as $column) {
            if (Str::contains($column, '.')) {
                list($relation) = explode('.', $column);

                if (method_exists($this->model, $relation)
                    && !method_exists(Model::class, $relation)
                    && $this->model->$relation() instanceof Relations\Relation
                ) {
                    $relations[] = $relation;
                }
            } elseif (method_exists($this->model, $column)
                && !method_exists(Model::class, $column)
            ) {
                $relations[] = $column;
            }
        }

        $this->relation_fields = array_unique($relations);

        return $this->relation_fields;
    }

    /**
     * Set action for form.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action): self
    {
        $this->builder()->setAction($action);

        return $this;
    }

    /**
     * Set field and label width in current form.
     *
     * @param int $fieldWidth
     * @param int $labelWidth
     *
     * @return $this
     */
    public function setWidth($fieldWidth = 8, $labelWidth = 2): self
    {
        $this->builder()->fields()->each(function ($field) use ($fieldWidth, $labelWidth) {
            /* @var Field $field  */
            $field->setWidth($fieldWidth, $labelWidth);
        });

        $this->builder()->setWidth($fieldWidth, $labelWidth);

        return $this;
    }

    /**
     * Set view for form.
     *
     * @param string $view
     *
     * @return $this
     */
    public function setView($view): self
    {
        $this->builder()->setView($view);

        return $this;
    }

    /**
     * Set title for form.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title = ''): self
    {
        $this->builder()->setTitle($title);

        return $this;
    }

    /**
     * Set Force delete
     *
     * @param bool $val
     *
     * @return $this
     */
    public function setIsForceDelete(bool $val = true)
    {
        $this->isForceDelete = $val;

        return $this;
    }
    
    /**
     * Set a submit confirm.
     *
     * @param string $message
     * @param string $on
     *
     * @return $this
     */
    public function confirm(string $message, $on = null)
    {
        if ($on && !in_array($on, ['create', 'edit'])) {
            throw new \InvalidArgumentException("The second paramater `\$on` must be one of ['create', 'edit']");
        }

        if ($on == 'create' && !$this->isCreating()) {
            return;
        }

        if ($on == 'edit' && !$this->isEditing()) {
            return;
        }

        $this->builder()->confirm($message);

        return $this;
    }

    /**
     * Add a row in form.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function row(Closure $callback): self
    {
        $this->rows[] = new Row($callback, $this);

        return $this;
    }

    /**
     * Tools setting for form.
     *
     * @param Closure $callback
     *
     * @return void
     */
    public function tools(Closure $callback)
    {
        $callback->call($this, $this->builder->getTools());
    }

    /**
     * @param Closure|null $callback
     *
     * @return Form\Tools|void
     */
    public function header(Closure $callback = null)
    {
        if (func_num_args() === 0) {
            return $this->builder->getTools();
        }

        $callback->call($this, $this->builder->getTools());
    }

    /**
     * Indicates if current form page is creating.
     *
     * @return bool
     */
    public function isCreating(): bool
    {
        return Str::endsWith(\request()->route()->getName(), ['.create', '.store']);
    }

    /**
     * Indicates if current form page is editing.
     *
     * @return bool
     */
    public function isEditing(): bool
    {
        return Str::endsWith(\request()->route()->getName(), ['.edit', '.update']);
    }

    /**
     * Set submit label.
     *
     * @return $this
     */
    public function submitLabel(string $submitLabel)
    {
        $this->builder()->getFooter()->submitLabel($submitLabel);

        return $this;
    }

    /**
     * Set submit label as save.
     *
     * @return $this
     */
    public function submitLabelSave()
    {
        $this->builder()->getFooter()->submitLabelSave();

        return $this;
    }

    /**
     * Disable Pjax.
     *
     * @return $this
     */
    public function disablePjax()
    {
        $this->builder()->disablePjax();

        return $this;
    }

    /**
     * Disable Validate.
     *
     * @return $this
     */
    public function disableValidate()
    {
        $this->builder()->disableValidate();

        return $this;
    }

    /**
     * Disable form submit.
     *
     * @param bool $disable
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableSubmit(bool $disable = true): self
    {
        $this->builder()->getFooter()->disableSubmit($disable);

        return $this;
    }

    /**
     * Disable form reset.
     *
     * @param bool $disable
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableReset(bool $disable = true): self
    {
        $this->builder()->getFooter()->disableReset($disable);

        return $this;
    }

    /**
     * Disable View Checkbox on footer.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableViewCheck(bool $disable = true): self
    {
        $this->builder()->getFooter()->disableViewCheck($disable);

        return $this;
    }

    /**
     * Disable Editing Checkbox on footer.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableEditingCheck(bool $disable = true): self
    {
        $this->builder()->getFooter()->disableEditingCheck($disable);

        return $this;
    }

    /**
     * Disable Creating Checkbox on footer.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableCreatingCheck(bool $disable = true): self
    {
        $this->builder()->getFooter()->disableCreatingCheck($disable);

        return $this;
    }

    /**
     * Disable Redirect to List.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableRedirectList(bool $disable = true)
    {
        $this->redirectList = $disable;

        return $this;
    }

    /**
     * add footer check item.
     *
     * $footerCheck : 
     *     [
     *         'value': 'foo', // this check value name
     *         'label': 'FOO', // this check label
     *         'redirect': \Closure, //set callback. Please redirect.
     *     ]
     *
     * @param array<mixed> $submitRedirect
     * @return $this
     */
    public function submitRedirect(array $submitRedirect)
    {
        $this->builder()->getFooter()->submitRedirect($submitRedirect);

        return $this;
    }

    /**
     * Footer setting for form.
     *
     * @param Closure $callback
     *
     * @return \ExmentAdminCore\Admin\Form\Footer|void
     */
    public function footer(Closure $callback = null)
    {
        if (func_num_args() === 0) {
            return $this->builder()->getFooter();
        }

        $callback($this->builder()->getFooter());
    }

    /**
     * Set if true, not call default renderException, and \Closure.
     *
     * @return  self
     */ 
    public function renderException(\Closure $renderException)
    {
        $this->renderException = $renderException;

        return $this;
    }

    /**
     * Get current resource route url.
     *
     * @param int $slice
     *
     * @return string
     */
    public function resource($slice = -2): string
    {
        return $this->getResource($slice);
    }

    /**
     * Render the form contents.
     *
     * @return string
     */
    public function render()
    {
        try {
            return $this->builder->render();
        } catch (\Exception $e) {
            if($this->renderException){
                return call_user_func($this->renderException, $e);
            }

            return Handler::renderException($e);
        }
    }

    /**
     * Get or set input data.
     *
     * @param string $key
     * @param mixed   $value
     *
     * @return array<mixed>|mixed
     */
    public function input($key, $value = null)
    {
        if ($value === null) {
            return Arr::get($this->inputs, $key);
        }

        return Arr::set($this->inputs, $key, $value);
    }

    /**
     * Add a new layout column.
     *
     * @param int      $width
     * @param \Closure $closure
     *
     * @return $this
     */
    public function column($width, Closure $closure): self
    {
        $width = $width < 1 ? round(12 * $width) : $width;

        $this->layout->column($width, $closure);

        return $this;
    }

    /**
     * Initialize filter layout.
     */
    protected function initLayout()
    {
        $this->layout = new Layout($this);
    }

    /**
     * Getter.
     *
     * @param string $name
     *
     * @return array<mixed>|mixed
     */
    public function __get($name)
    {
        return $this->input($name);
    }

    /**
     * Setter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        /** @phpstan-ignore-next-line should delete return */
        return Arr::set($this->inputs, $name, $value);
    }

    /**
     * __isset.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->inputs[$name]);
    }

    /**
     * Generate a Field object and add to form builder if Field exists.
     *
     * @param string $method
     * @param array<mixed>  $arguments
     *
     * @return Field
     */
    public function __call($method, $arguments)
    {
        if ($className = static::findFieldClass($method)) {
            $column = Arr::get($arguments, 0, ''); //[0];

            $element = new $className($column, array_slice($arguments, 1));

            $this->pushField($element);

            return $element;
        }

        admin_error('Error', 'Field type [' . e($method) . '] does not exist.');

        return new Field\Nullable();
    }

    /**
     * @return Layout
     */
    public function getLayout(): Layout
    {
        return $this->layout;
    }
}
