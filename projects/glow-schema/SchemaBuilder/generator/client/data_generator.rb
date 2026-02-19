require_relative '../generator.rb'

class DataGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name, elements)
    return "#{name}Data.cs"
  end

  def translate(schema_yaml, base_schema_yaml)
    return schema_yaml
  end
end
