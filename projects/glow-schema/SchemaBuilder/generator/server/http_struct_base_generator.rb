require_relative '../generator.rb'

# 各dataの構造体クラスを出力する
#   自動生成の中身が入っているBaseクラスを出力する
#   プロダクト側で参照されるStructクラスはhttp_struct_generatorで出力する
class HttpStructBaseGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name, elements)
    return "Base/Base#{name}Data.php"
  end

  def translate(schema_yaml, base_schema_yaml)
        # paramsを参照するgetterメソッド
        schema_yaml["get_methods"] = []
        # paramsを参照するsetterメソッド
        schema_yaml["set_methods"] = []
        # 型の変換
        schema_yaml["cast_params"] = []
        # メンバ変数
        schema_yaml["member_variables"] = []

        if schema_yaml["params"].nil?
          # paramsがない場合は空の配列を作成
          schema_yaml["params"] = []
        else
          schema_yaml["params"].each do |param|
            # float型か(stringに変換される前はfloat型であったことを保持しておく)
            type_was_float = server_is_float(param["type"]);
            param_name = server_param_name(param["name"])
            method_type = server_method_type(param["type"])
            # 元々はFloat型であったことをコメントとして残す
            if type_was_float
              param["type_was_float_description"] = "Float型からString型に変換済み"
            end

            # getterとsetterのデータを作成
            schema_yaml["get_methods"].push(
              "method_name" => server_method_getter_name(param["name"]),
              "method_type" => method_type,
            )
            schema_yaml["set_methods"].push(
              "method_name" => server_method_setter_name(param["name"]),
              "method_type" => method_type,
            )
            # パラメータ名の先頭を小文字にする
            schema_yaml["cast_params"].push(
              "name" => param_name,
              "type" => server_cast_type(param["type"]),
              "description" => param["description"],
            )

            # メンバ変数として追加
            #  型はメソッドで返すものになるため、メソッドの型をそのまま使用する
            schema_yaml["member_variables"].push(
              "name" => param_name,
              "type" => method_type,
              "description" => param["description"],
              "type_was_float_description" => param["type_was_float_description"]
            )

          end
        end

        # snakecaseの判定
        # snakecaseキーがtrueでなければfalseを入れておく
        schema_yaml["snakecase"] = schema_yaml["snakecase"] ? "true" : "false"

    return schema_yaml
  end
end
