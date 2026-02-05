require_relative '../generator.rb'

class UseCaseGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name)
    return "#{name.tableize.singularize}_use_case.rb"
  end

  def translate(schema_yaml)
    return schema_yaml
  end
end
