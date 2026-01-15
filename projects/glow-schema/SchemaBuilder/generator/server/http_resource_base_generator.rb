require_relative '../generator.rb'

# HTTPリソースの基底クラスを出力する
class HttpResourceBaseGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name, elements)
    sub_name = elements["name"]
    return "#{name}/Base/Base#{sub_name}Resource.php"
  end

  # APIごとにファイルを出力するため、actionsの要素を返す
  def target_elements(yaml_elements)
    return yaml_elements["actions"]
  end

  def translate(schema_yaml, base_schema_yaml)
    # 元になるyamlを参照するため格納
    schema_yaml["base"] = base_schema_yaml

    # レスポンスのデータ方を作成
    # 記載されているデータ型に、Structsを付与する
    schema_yaml["response_type"] = server_method_type(schema_yaml["response"])

    return schema_yaml
  end

  # trueだった場合にスキップする
  def filter(name)
    name.match?(/^Mst/) || name.match?(/^Opr/)
  end
end
