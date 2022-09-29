# DB設計書
喜納政凜　前黒島櫂

## 使用するDB
| DB名 | 用途 |
| :-- | :-- |
| `ORDER_SYS_DB` | 今回のシステム基盤となるDB |

## DBの情報
<table>
    <tr>
        <td>
            DB
        </td>
        <td>
            MySQL 5.6
        </td>
    </tr>
    <tr>
        <td>
            サーバーの種類
        </td>
        <td>
            MariaDB
        </td>
    </tr>
    <tr>
        <td>
            サーバーのバージョン
        </td>
        <td>
            10.4.24-MariaDB
        </td>
    </tr>
    <tr>
        <td>
            ストレージエンジン
        </td>
        <td>
            InnoDB
        </td>
    </tr>
    <tr>
        <td>
            文字コード
        </td>
        <td>
            UTF-8 Unicode (utf8mb4)
        </td>
    </tr>
</table>


## テーブル一覧
| TB名 | 所属DB | 解説 |
| :-- | -- | :-- |
| `T_STORE_TERMINAL_SESSION` | `ORDER_SYS_DB` | 端末のログインセッションを管理 |
| `T_STORE_ACCOUNT` | `ORDER_SYS_DB` | 店舗のアカウント管理 |
| `T_STORE_INFORMATION` |`ORDER_SYS_DB`| 店舗の名前と識別子を管理 |
| `T_PRODUCT_INFORMATION` |`ORDER_SYS_DB`| 商品に関する情報を管理 |
| `T_ORDER_INFORMATION_DETAIL` |`ORDER_SYS_DB`| 注文した商品の情報を管理 |
| `T_ORDER_INFORMATION_MAIN` | `ORDER_SYS_DB` | 注文とトークンを結びつける |
| `T_IMAGE_SAVE_PATH` | `ORDER_SYS_DB` | 商品の画像のURLを管理 |
| `T_QRCODE_IMAGE_PATH` | `ORDER_SYS_DB` | QRコードの画像のURLを管理 |
|`T_STOCK` | `ORDER_SYS_DB` | 在庫管理 |

### `T_STORE_TERMINAL_SESSION`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
| `login_session_id` | 店舗端末用セッションID | CHAR(40) | | PRIMARY |
| `store_id` | 店舗識別子 | CHAR(10) |
| `deleted` | 削除フラグ | BOOLEAN |


### `T_STORE_ACCOUNT`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
| `store_account_id` | 店舗用アカウント識別子 | CHAR(15) | | PRIMARY |
| `account_name` | 店舗用アカウント名 | CHAR(20) | | UNIQUE |
| `account_pass` | アカウントパスワード | Text | | |ハッシュ化した文字列を保存
| `store_id` | 店舗識別子 | CHAR(10) | |


### `T_STORE_INFORMATION`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
| `store_id` | 店舗識別子 | CHAR(10) | | PRIMARY |
| `store_name` | 店舗の名前 | VARCHAR(20) |



### `T_PRODUCT_INFORMATION`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`product_id`| 商品識別子 | CHAR(5) | | PRIMARY |
|`store_id` | 店舗識別子 | CHAR(10) | | INDEX |
|`product_name`| 商品の名前 | VARCHAR(20) |
|`product_detail`| 商品の説明 | Text |
|`product_price`| 値段 | INT(4) | | INDEX |
|`product_image_path`| 商品画像のURL | VARCHAR()|
|`product_option`|オプション情報| JSON |
|`product_genre`| 商品のジャンル | VARCHAR(20) |

### `T_ORDER_INFORMATION_DETAIL`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`order_id`|注文番号| CHAR(5) | | INDEX |
|`product_id`| 商品識別子 | CHAR(5) | | INDEX |
|`quantity`| 個数 | INT(3)|
|`passed_flag`| 受け渡し済みフラグ | BOOLEAN |
|`product_option`| オプション情報 | JSON |

### `T_ORDER_INFORMATION_MAIN`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`token`| トークン | CHAR(50)|| PRIMARY |
|`order_id`| 注文番号 | CHAR(5) |||
|`order_total_price`| 注文の合計金額|INT(5) |
|`paid_flag`| 支払い済みフラグ | BOOLEAN |
|`fingerprint`| フィンガープリント | VARCHAR(1024) || INDEX |
|`Confirmed_order_flag`| 注文確定フラグ | BOOLEAN |
|`product_image_path`| 商品画像のURL | VARCHAR() |

### `T_CART_DATA`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`fingerprint`| フィンガープリント | VARCHAR(1024) || PRIMARY ||
|`product_in_cart`| カートの中の商品 | JSON ||

### `T_SAVE_QRCODE_PATH`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`order_id`| 注文番号 | CHAR(5) || PRIMARY |
|`qrcode_image_path`| QRコードの画像のURL | VARCHAR() |

### `T_STOCK`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`product_id`| 商品識別子 | CHAR(5) || PRIMARY |
|`stock_quantity`| 在庫数 | INT(4) |
|`orderable flag`| 注文受付フラグ | BOOLEAN |