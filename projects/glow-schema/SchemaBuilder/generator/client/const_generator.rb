require_relative '../generator.rb'

class ConstGenerator < Generator
  def yaml_element_name
    return "const"
  end

  def file_name(name, elements)
    return "#{name}.cs"
  end

  def translate(schema_yaml, base_schema_yaml)
    return schema_yaml
  end
end
