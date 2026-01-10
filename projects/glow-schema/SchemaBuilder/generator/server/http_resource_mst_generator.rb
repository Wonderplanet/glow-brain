require_relative '../generator.rb'

class HttpResourceMstGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name, elements)
    return "#{name}Resource.php"
  end

  def translate(schema_yaml, base_schema_yaml)
    # i18nとわけるため、namespaceを設定する
    schema_yaml["namespace"] = "Masterdata"

    schema_yaml["params"].each do |p|
      p["type"] = cast_data_type_yml_to_server_resource(p["type"])
    end

    return schema_yaml
  end

  def filter(name)
    # 先頭がMstかつ末尾がI18nではない場合に対象とする (falseを返す)
    return !(name.match?(/^Mst/) && !name.match?(/I18n$/))
  end
end
