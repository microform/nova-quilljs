<?php

namespace Ek0519\Quilljs;

use Froala\NovaFroalaField\Froala;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Trix\PendingAttachment;

class Quilljs extends Froala
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'quilljs';


    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->tooltip();
        $this->height();
        $this->paddingBottom();
        $this->fullWidth();
        $this->maxFileSize(2);
        $this->withMeta([
            'options'=> config('quilljs'),
        ]);
    }
    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request,
                                                $requestAttribute,
                                                $model,
                                                $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = $request[$requestAttribute];

            if (! isset($model->id)) {
                $quilljs = $this;
                $modelClass = get_class($model);
                call_user_func_array("$modelClass::created", [
                    function($object) use ($quilljs, $request) {
                        $quilljs->persistImages($request, $object);
                    }
                ]);
            }
            else {
                $this->persistImages($request, $model);
            }

        }
    }

    function persistImages(NovaRequest $request, $object) {
        if ($request->persisted && $images = json_decode($request->persisted)) {
            if (!empty($images)) {
                $this->persistedImg($images, $object);
            }
        }
    }

    public function persistedImg(array $images, $model)
    {
        foreach($images as $image) {
            $pending = PendingAttachment::where('draft_id', $image)->first();
//            debug($pending, $image, $model, $this->getStorageDisk());
            if ($pending) {
                $pending->persist($this, $model);
            }

        }

    }

    public function tooltip(bool $value=false)
    {
        return $value == true ? $this->withMeta(['tooltip'=> config('tooltip') ?? []]) : null;
    }

    public function placeholder($text)
    {
        return $this->withMeta(['placeholder'=> $text]);
    }

    public function height(int $value=300)
    {
        return $this->withMeta(['height' => $value]);
    }

    public function paddingBottom(int $value=0)
    {
        return $this->withMeta(['paddingBottom' => $value]);
    }

    public function fullWidth(bool $value=true)
    {
        return $this->withMeta(['width' => $value]);
    }

    public function maxFileSize(int $value = 2)
    {
        return $this->withMeta(['maxFileSize' => $value]);
    }
}
