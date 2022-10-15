# カートデータJSONフォーマット
喜納政凜　前黒島櫂

## JSONにまとめるデータ
| データ名 | 和名 | 用途 |
| :-- | :-- | :-- |
|`product_id`| 商品識別子 | 商品の識別子 |
|`product_option`| オプション情報 |　商品のオプション情報 |
|`quantity`| 個数 | 商品の個数 |

## カートJSONフォーマット
```
[
    {
        "product_id": "商品識別子",
        "product_option": [
            "オプション名": {
                "options": [
                    {"name": "名前", "price": "値段"}
                ]
            }
        ],
        "quantity": 商品の個数
    }
]
```