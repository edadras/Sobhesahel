<?php
/**
 * GitHub: RoyalHaze
 * Date: 6/10/25
 * Time: 5:41 PM
 **/

namespace App\Traits;

use App\Models\TitleValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTitleValues
{
    public function titleValues(): MorphMany
    {
        return $this->morphMany(TitleValue::class, 'titleable');
    }

    /**
     * Set a title-value pair with optional JSON data fields.
     *
     * @param string $title
     * @param mixed $value
     * @param array $dataFields
     * @return TitleValue
     */
    public function setTitleValue(string $title, $value, array $dataFields = []): TitleValue
    {
        return $this->titleValues()->updateOrCreate(
            ['title' => $title],
            [
                'value' => $value,
                'data_fields' => $dataFields,
            ]
        );
    }

    /**
     * Get a title-value pair by title.
     *
     * @param string $title
     * @return TitleValue|null
     */
    public function getTitleValue(string $title): ?TitleValue
    {
        return $this->titleValues()->where('title', $title)->first();
    }

    /**
     * Get the value for a given title.
     *
     * @param string $title
     * @param mixed $default
     * @return mixed
     */
    public function getTitleValueField(string $title, $default = null)
    {
        $titleValue = $this->getTitleValue($title);
        return $titleValue ? $titleValue->value : $default;
    }

    /**
     * Get a specific data field from the JSON data_fields column.
     *
     * @param string $title
     * @param string $field
     * @param mixed $default
     * @return mixed
     */
    public function getDataField(string $title, string $field, $default = null)
    {
        $titleValue = $this->getTitleValue($title);
        return $titleValue && isset($titleValue->data_fields[$field]) ? $titleValue->data_fields[$field] : $default;
    }

    /**
     * Delete a title-value pair.
     *
     * @param string $title
     * @return bool
     */
    public function deleteTitleValue(string $title): bool
    {
        return $this->titleValues()->where('title', $title)->delete();
    }
}
