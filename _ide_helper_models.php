<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Type
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Type whereUpdatedAt($value)
 */
	class Type extends \Eloquent {}
}

namespace App{
/**
 * App\ProductSum
 *
 * @property int $id
 * @property int $product_id
 * @property int $color_id
 * @property int $size_id
 * @property int $place_id
 * @property int $type_id
 * @property int $sold
 * @property string|null $sold_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Color $color
 * @property-read \App\Place $place
 * @property-read \App\Product $product
 * @property-read \App\Size $size
 * @property-read \App\Type $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum wherePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereSold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereSoldAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductSum whereUpdatedAt($value)
 */
	class ProductSum extends \Eloquent {}
}

namespace App{
/**
 * App\Product
 *
 * @property int $id
 * @property string|null $brand
 * @property string $model
 * @property string|null $price_arrival
 * @property string $price_sell
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePriceArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePriceSell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereUpdatedAt($value)
 */
	class Product extends \Eloquent {}
}

namespace App{
/**
 * App\Color
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Color newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Color newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Color query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Color whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Color whereName($value)
 */
	class Color extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $place_id
 * @property string $role
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\Place $place
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\Size
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Size whereName($value)
 */
	class Size extends \Eloquent {}
}

namespace App{
/**
 * App\Place
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Place newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Place newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Place query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Place whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Place whereName($value)
 */
	class Place extends \Eloquent {}
}

