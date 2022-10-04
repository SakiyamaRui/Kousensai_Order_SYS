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
|`T_CART_DATA`| `ORDER_SYS_DB` | カートデータ管理 |
|`T_PRODUCT_OPTIONS`| `ORDER_SYS_DB` | 商品のオプションの在庫管理 |
|`T_NOTICE_DATA`| `ORDER_SYS_DB` | プッシュ通知に関するデータを管理 |

## データ・テーブル詳細
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
| `store_name` | 店舗の名前 | VARCHAR(25) |


### `T_PRODUCT_INFORMATION`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`product_id`| 商品識別子 | CHAR(5) | | PRIMARY |
|`store_id` | 店舗識別子 | CHAR(10) | | INDEX |
|`product_name`| 商品の名前 | VARCHAR(50) |
|`product_detail`| 商品の説明 | Text |
|`product_price`| 値段 | INT(4) |
|`product_image_url`| 商品画像のURL | CHAR(55) | 〇 |
|`orderable_flag`| 注文受付フラグ | BOOLEAN |

### `T_ORDER_INFORMATION_DETAIL`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`order_id`|注文番号| CHAR(5) | | INDEX |
|`product_id`| 商品識別子 | CHAR(5) | | INDEX |
|`quantity`| 個数 | INT(3)|
|`passed_flag`| 受け渡し済みフラグ | BOOLEAN |
|`made_flag`| 作成済みフラグ | BOOLEAN |
|`product_option`| オプション情報 | JSON |
|`unit_price`| 単価 | INT(4) |

### `T_ORDER_INFORMATION_MAIN`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`token`| トークン | CHAR(50)|| PRIMARY |
|`order_id`| 注文番号 | CHAR(5) |
|`order_total_price`| 注文の合計金額 | INT(5) |
|`confirmed_order_flag`| 注文確定フラグ | BOOLEAN |
|`session_token`| セッショントークン | CHAR(50) || INDEX |
|`product_image_path`| 商品画像のURL | CHAR(55) |
|`order_time`| 注文時間 | DATETIME |
|`pickup_now`| 今すぐ受け取り | BOOLEAN || INDEX |
|`pickup_time`| 受け取り希望時間 | DATETIME || INDEX |

### `T_CART_DATA`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`session_token`| セッショントークン | CHAR(50) || PRIMARY |
|`product_in_cart`| カートの中の商品 | JSON |

### `T_PRODUCT_OPTIONS`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`product_id`| 商品識別子 | CHAR(5) || PRIMARY |
|`option_name`| オプション名 | VARCHAR(30) || INDEX |
|`option_value`| オプションの値 | VARCHAR(30) | 〇 |
|`default_value`| オプションの値のデフォルト値 | BOOLEAN |
|`option_index`| インデックス番号 | INT(2) |
|`user_display_flag`| ユーザーに表示するかどうかフラグ | BOOLEAN |
|`option_remaining_stock`| オプションの残りの在庫数 | INT(4) |
|`add_option_price`| オプションの追加の値段 | INT(4) |

### `T_NOTICE_DATA`
| 項目(カラム名) | 項目(和名) | 型・サイズ | ヌル | インデックス | その他 |
| :-- | :-- | :-: | :-: | :-: | :-- |
|`session_token`| セッショントークン | CHAR(50) || PRIMARY |
|`fingerprint`| フィンガープリント | CHAR(32) |
|`end_point`| エンドポイント | CHAR(188) | 〇 |
|`public_key`| 公開鍵 | CHAR(88) | 〇 |
|`authentication_token`| 認証トークン | CHAR(24) | 〇 |