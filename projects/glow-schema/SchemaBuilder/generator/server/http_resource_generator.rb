require_relative '../generator.rb'

# 実態として使用するリソースクラスを出力する
class HttpResourceGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name, elements)
    sub_name = elements["name"]
    return "#{name}/#{sub_name}Resource.php"
  end
  
  # APIごとにファイルを出力するため、actionsの要素を返す
  def target_elements(yaml_elements)
    return yaml_elements["actions"]
  end

  # Structはファイルが存在してたら上書きしない
  #   プロダクト側でカスタマイズされている可能性があるため
  def is_file_overwrite?
    return false
  end

  def translate(schema_yaml, base_schema_yaml)
    # 元になるyamlを参照するため格納
    schema_yaml["base"] = base_schema_yaml

    return schema_yaml
  end

  # trueだった場合にスキップする
  def filter(name)
    # DBのテーブルを対象にしない
    name.match?(/^Mst/) || name.match?(/^Opr/)
  end
end
