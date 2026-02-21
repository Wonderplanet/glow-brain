require_relative '../generator.rb'

# HTTPリクエストのクラスを出力する
class HttpRequestGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name, elements)
    sub_name = elements["name"]
    return "#{name}/#{sub_name}Request.php"
  end

  # APIごとにファイルを出力するため、actionsの要素を返す
  def target_elements(yaml_elements)
    return yaml_elements["actions"]
  end

  def translate(schema_yaml, base_schema_yaml)
    # 元になるyamlを参照するため格納
    schema_yaml["base"] = base_schema_yaml

    # メソッドコメント用の出力
    # paramsを参照するgetterメソッド
    schema_yaml["methods"] = []
    # 型の変換
    schema_yaml["cast_params"] = []
    # 必須パラメータ
    schema_yaml["required_params"] = []
    # スネークケースで取得するパラメータ
    schema_yaml["snakecase_params"] = []
    # メンバ変数
    schema_yaml["member_variables"] = []

    if schema_yaml["params"].nil?
      # paramsがない場合は空の配列を作成
      schema_yaml["params"] = []
    else
      schema_yaml["params"].each do |param|
        # float型か(stringに変換される前はfloat型であったことを保持しておく)
        type_was_float = server_is_float(param["type"]);
        # getterにするため、先頭を大文字にする
        method_type = server_method_type(param["type"])
        schema_yaml["methods"].push(
          "method_name" => server_method_getter_name(param["name"]),
          "method_type" => method_type,
        )

        # リクエストパラメータの型変換
        # 一部のパラメータキーはスネークケースになるので、それに合わせる
        # TODO: 後にすべてキャメルケースとしたい https://app.clickup.com/t/86ennhu82
        param_name = server_param_name(param["name"])
        # リクエスト名は、スネークケースの指定がある場合はスネークケースにする
        #   そうでない場合はparam_nameと同じ
        request_name = param["snakecase"] ? camel_to_snake(param_name) : param_name

        # typeに補正をかける
        # PHP側でキャストするため、Strict型には\App\Http\Structs\Api\を付与する
        type = server_cast_type(param["type"])
        schema_yaml["cast_params"].push(
          "name" => param_name,
          "type" => type,
        )

        # required: trueの場合は、required_paramsに追加
        #   実際に送られてくるパラメータのキーで記述する必要があるため、
        #   スネークケースで指定されている場合は、snakecase_paramsで記述する
        if param["required"]
          schema_yaml["required_params"].push(request_name)
        end

        # snake caseで取得する場合は、snakecase_paramsに追加
        if param["snakecase"]
          schema_yaml["snakecase_params"].push(param_name)
        end

        # 元々はFloat型であったことをコメントとして残す
        if type_was_float
          param["type_was_float_description"] = "Float型からString型に変換済み"
        end

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

    return schema_yaml
  end

  # trueだった場合にスキップする
  def filter(name)
    name.match?(/^Mst/) || name.match?(/^Opr/)
  end
end
